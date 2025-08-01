<?php
/**
 * Members.php - Elevator Network Management Interface
 * Deliverable: Insert/Display (4 marks) + Modify/Delete (8 marks)
 */

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

require_once 'database_functions.php';

$db = new ElevatorNetworkDB();
$message = '';
$messageType = '';
$editRecord = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert':
                    // Insert new elevator network node - 4 marks deliverable
                    $data = [
                        'nodeName' => $_POST['nodeName'],
                        'nodeType' => $_POST['nodeType'],
                        'ipAddress' => $_POST['ipAddress'],
                        'currentFloor' => $_POST['currentFloor'],
                        'status' => $_POST['status']
                    ];
                    
                    if ($db->insertRecord('elevatorNetwork', $data)) {
                        $message = "New elevator network node added successfully!";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'update':
                    // Update existing record using transactions - 8 marks deliverable
                    $updates = [
                        'nodeName' => $_POST['nodeName'],
                        'nodeType' => $_POST['nodeType'],
                        'ipAddress' => $_POST['ipAddress'],
                        'currentFloor' => $_POST['currentFloor'],
                        'status' => $_POST['status']
                    ];
                    
                    if ($db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', $_POST['nodeID'])) {
                        $message = "Elevator network node updated successfully using transactions!";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'delete':
                    // Delete existing record - 8 marks deliverable
                    if ($db->deleteRecord('elevatorNetwork', 'nodeID', $_POST['nodeID'])) {
                        $message = "Elevator network node deleted successfully!";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'edit':
                    // Load record for editing
                    $editRecord = $db->getRecord('elevatorNetwork', 'nodeID', $_POST['nodeID']);
                    break;
            }
        }
    } catch (InvalidFloorException $e) {
        $message = "Floor Error: " . $e->getMessage();
        $messageType = 'error';
    } catch (NetworkCommunicationException $e) {
        $message = "Network Error: " . $e->getMessage();
        $messageType = 'error';
    } catch (CANBusException $e) {
        $message = "CAN Bus Error: " . $e->getMessage();
        $messageType = 'error';
    } catch (ElevatorDatabaseException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = 'error';
    } catch (InvalidNodeConfigException $e) {
        $message = "Configuration Error: " . $e->getMessage();
        $messageType = 'error';
    } catch (ElevatorException $e) {
        $message = "Elevator System Error: " . $e->getMessage();
        $messageType = 'error';
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all records for display - 4 marks deliverable
try {
    $elevatorNodes = $db->getAllRecords('elevatorNetwork');
    $canComponents = $db->getAllRecords('canComponents');
} catch (Exception $e) {
    $message = "Error loading data: " . $e->getMessage();
    $messageType = 'error';
    $elevatorNodes = [];
    $canComponents = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator Network Management - Members Area</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/projectsVI.css">
    <style>
        .container { max-width: 1000px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message { padding: 12px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border: 1px solid #dee2e6; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
        .form-group { margin: 10px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn { padding: 8px 16px; margin: 3px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-secondary { background: #6c757d; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { padding: 8px; border: 1px solid #dee2e6; text-align: left; font-size: 14px; }
        table th { background: #e9ecef; font-weight: bold; }
        .actions { white-space: nowrap; }
        .back-nav { text-align: center; margin: 20px 0; }
        .deliverable-badge { background: #17a2b8; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; margin-left: 10px; }
        h2 { color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
        h3 { color: #6c757d; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-nav">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        
        <h1>Elevator Network Management</h1>
        <p><strong>Database Deliverables:</strong> Primary/Foreign Keys, Unique Constraints, Indexes, Update Functions, Transactions, CRUD Operations</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Insert/Update Form -->
        <div class="form-section">
            <h3><?php echo $editRecord ? 'Edit' : 'Add'; ?> Elevator Network Node 
            </h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editRecord ? 'update' : 'insert'; ?>">
                <?php if ($editRecord): ?>
                    <input type="hidden" name="nodeID" value="<?php echo $editRecord['nodeID']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Node Name:</label>
                        <input type="text" name="nodeName" value="<?php echo $editRecord['nodeName'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Node Type:</label>
                        <select name="nodeType" required>
                            <option value="elevator" <?php echo ($editRecord['nodeType'] ?? '') === 'elevator' ? 'selected' : ''; ?>>Elevator</option>
                            <option value="controller" <?php echo ($editRecord['nodeType'] ?? '') === 'controller' ? 'selected' : ''; ?>>Controller</option>
                            <option value="sensor" <?php echo ($editRecord['nodeType'] ?? '') === 'sensor' ? 'selected' : ''; ?>>Sensor</option>
                            <option value="door" <?php echo ($editRecord['nodeType'] ?? '') === 'door' ? 'selected' : ''; ?>>Door</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>IP Address (Unique Key):</label>
                        <input type="text" name="ipAddress" value="<?php echo $editRecord['ipAddress'] ?? ''; ?>" 
                               pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" required>
                    </div>
                    <div class="form-group">
                        <label>Current Floor:</label>
                        <input type="number" name="currentFloor" value="<?php echo $editRecord['currentFloor'] ?? '1'; ?>" 
                               min="1" max="10" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status (Indexed Field):</label>
                    <select name="status" required>
                        <option value="online" <?php echo ($editRecord['status'] ?? '') === 'online' ? 'selected' : ''; ?>>Online</option>
                        <option value="offline" <?php echo ($editRecord['status'] ?? '') === 'offline' ? 'selected' : ''; ?>>Offline</option>
                        <option value="maintenance" <?php echo ($editRecord['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                </div>
                
                <button type="submit" class="btn <?php echo $editRecord ? 'btn-warning' : 'btn-success'; ?>">
                    <?php echo $editRecord ? 'Update Node (Transaction)' : 'Add Network Node'; ?>
                </button>
                
                <?php if ($editRecord): ?>
                    <a href="members.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Display Records -->
        <div class="form-section">
            <h3>Elevator Network Nodes (Parent Table)
                <span class="deliverable-badge"></span>
            </h3>
            <p><strong>Features:</strong> Primary Key (nodeID), Unique Key (ipAddress), Index (status, nodeType)</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Node ID (PK)</th>
                        <th>Name</th>
                        <th>Type (Indexed)</th>
                        <th>IP Address (Unique)</th>
                        <th>Floor</th>
                        <th>Status (Indexed)</th>
                        <th>Last Update</th>
                        <th>Actions <span class="deliverable-badge"></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($elevatorNodes as $node): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($node['nodeID']); ?></td>
                        <td><?php echo htmlspecialchars($node['nodeName']); ?></td>
                        <td><?php echo htmlspecialchars($node['nodeType']); ?></td>
                        <td><?php echo htmlspecialchars($node['ipAddress']); ?></td>
                        <td><?php echo htmlspecialchars($node['currentFloor']); ?></td>
                        <td><span style="color: <?php echo $node['status'] === 'online' ? 'green' : ($node['status'] === 'offline' ? 'red' : 'orange'); ?>">
                            <?php echo htmlspecialchars($node['status']); ?>
                        </span></td>
                        <td><?php echo htmlspecialchars($node['lastUpdate']); ?></td>
                        <td class="actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="nodeID" value="<?php echo $node['nodeID']; ?>">
                                <button type="submit" class="btn btn-warning">Edit</button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this node and all related CAN components?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="nodeID" value="<?php echo $node['nodeID']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- CAN Components Display -->
        <div class="form-section">
            <h3>CAN Network Components (Child Table with Foreign Key)</h3>
            <p><strong>Features:</strong> Foreign Key (nodeID → elevatorNetwork.nodeID), Unique Key (canAddress)</p>
            
            <table>
                <thead>
                    <tr>
                        <th>CAN ID (PK)</th>
                        <th>Node ID (FK)</th>
                        <th>Node Name</th>
                        <th>CAN Address (Unique)</th>
                        <th>Component Type (Indexed)</th>
                        <th>Status</th>
                        <th>Last Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Create lookup array for node names
                    $nodeNames = array_column($elevatorNodes, 'nodeName', 'nodeID');
                    
                    foreach ($canComponents as $component): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($component['canID']); ?></td>
                        <td><?php echo htmlspecialchars($component['nodeID']); ?></td>
                        <td><?php echo htmlspecialchars($nodeNames[$component['nodeID']] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($component['canAddress']); ?></td>
                        <td><?php echo htmlspecialchars($component['componentType']); ?></td>
                        <td><span style="color: <?php echo $component['status'] === 'active' ? 'green' : ($component['status'] === 'inactive' ? 'red' : 'orange'); ?>">
                            <?php echo htmlspecialchars($component['status']); ?>
                        </span></td>
                        <td><?php echo htmlspecialchars($component['lastMessage']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    
    </div>
</body>
</html>
