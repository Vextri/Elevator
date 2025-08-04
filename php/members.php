<?php
/**
 * Members.php - Complete Database Management Interface
 * Manages access_requests1 database tables: requests, access_logs, users
 */

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

// Database connection
try {
    $db = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1"); //This is a password I made up for the sake of the project it is not confidential
    if ($db->connect_error) {
        // Try alternative connection
        $db = new mysqli("localhost", "root", "", "access_requests1");
        if ($db->connect_error) {
            throw new Exception("Database connection failed: " . $db->connect_error);
        }
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$message = '';
$messageType = '';
$editRecord = null;
$activeTab = $_GET['tab'] ?? 'requests';

// Get all tables for management
$tables = [];
$result = $db->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert_request':
                    // Insert new access request
                    $stmt = $db->prepare("INSERT INTO requests (student_id, name, email, reason, status, approved) VALUES (?, ?, ?, ?, 'pending', 0)");
                    $stmt->bind_param("ssss", $_POST['student_id'], $_POST['name'], $_POST['email'], $_POST['reason']);
                    
                    if ($stmt->execute()) {
                        $message = "New access request added successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Error adding request: " . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                    break;
                    
                case 'update_request':
                    // Update existing request
                    $stmt = $db->prepare("UPDATE requests SET student_id=?, name=?, email=?, reason=?, approved=? WHERE id=?");
                    $stmt->bind_param("ssssis", $_POST['student_id'], $_POST['name'], $_POST['email'], $_POST['reason'], $_POST['approved'], $_POST['id']);
                    
                    if ($stmt->execute()) {
                        $message = "Access request updated successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Error updating request: " . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                    break;
                    
                case 'delete_request':
                    // Delete request
                    $stmt = $db->prepare("DELETE FROM requests WHERE id=?");
                    $stmt->bind_param("i", $_POST['id']);
                    
                    if ($stmt->execute()) {
                        $message = "Access request deleted successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Error deleting request: " . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                    break;
                    
                case 'insert_log':
                    // Insert new access log
                    $stmt = $db->prepare("INSERT INTO access_logs (student_card, access_time, success, reason, ip_address) VALUES (?, NOW(), ?, ?, ?)");
                    $stmt->bind_param("siss", $_POST['student_card'], $_POST['success'], $_POST['reason'], $_POST['ip_address']);
                    
                    if ($stmt->execute()) {
                        $message = "New access log added successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Error adding log: " . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                    $activeTab = 'logs';
                    break;
                    
                case 'update_log':
                    // Update existing log
                    $stmt = $db->prepare("UPDATE access_logs SET student_card=?, success=?, reason=?, ip_address=? WHERE log_id=?");
                    $stmt->bind_param("sissi", $_POST['student_card'], $_POST['success'], $_POST['reason'], $_POST['ip_address'], $_POST['log_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Access log updated successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Error updating log: " . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                    $activeTab = 'logs';
                    break;
                    
                case 'delete_log':
                    // Delete log
                    $stmt = $db->prepare("DELETE FROM access_logs WHERE log_id=?");
                    $stmt->bind_param("i", $_POST['log_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Access log deleted successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Error deleting log: " . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                    $activeTab = 'logs';
                    break;
                    
                case 'edit_request':
                    // Load request for editing
                    $stmt = $db->prepare("SELECT * FROM requests WHERE id = ?");
                    $stmt->bind_param("i", $_POST['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $editRecord = $result->fetch_assoc();
                    $stmt->close();
                    break;
                    
                case 'edit_log':
                    // Load log for editing
                    $stmt = $db->prepare("SELECT * FROM access_logs WHERE log_id = ?");
                    $stmt->bind_param("i", $_POST['log_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $editRecord = $result->fetch_assoc();
                    $editRecord['log_id'] = $editRecord['log_id']; // Ensure log_id is set
                    $stmt->close();
                    $activeTab = 'logs';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all data for display
try {
    $requests = [];
    $access_logs = [];
    
    // Get requests
    $result = $db->query("SELECT * FROM requests ORDER BY created_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    
    // Get access logs (limit to recent 50 for performance)
    $result = $db->query("SELECT * FROM access_logs ORDER BY access_time DESC LIMIT 50");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $access_logs[] = $row;
        }
    }
} catch (Exception $e) {
    $message = "Error loading data: " . $e->getMessage();
    $messageType = 'error';
    $requests = [];
    $access_logs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - Complete Access Control System</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/projectsVI.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message { padding: 12px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Tab System */
        .tabs { display: flex; border-bottom: 2px solid #dee2e6; margin: 20px 0; }
        .tab { padding: 12px 24px; background: #f8f9fa; border: 1px solid #dee2e6; border-bottom: none; cursor: pointer; margin-right: 2px; text-decoration: none; color: #495057; }
        .tab.active { background: white; border-bottom: 2px solid white; margin-bottom: -2px; font-weight: bold; color: #007bff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Form Styling */
        .form-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border: 1px solid #dee2e6; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
        .form-group { margin: 10px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .form-group textarea { height: 80px; resize: vertical; }
        
        /* Button Styling */
        .btn { padding: 8px 16px; margin: 3px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-secondary { background: #6c757d; color: white; }
        
        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { padding: 8px; border: 1px solid #dee2e6; text-align: left; font-size: 14px; }
        table th { background: #e9ecef; font-weight: bold; }
        .actions { white-space: nowrap; }
        
        /* Status Styling */
        .status-approved { background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .status-denied { background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .status-pending { background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .log-success { background: #d4edda; }
        .log-failed { background: #f8d7da; }
        
        /* Navigation */
        .back-nav { text-align: center; margin: 20px 0; }
        h2 { color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
        h3 { color: #6c757d; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-nav">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            <a href="setup_center.php" class="btn btn-info">Setup Center</a>
        </div>
        
        <h1>Complete Database Management System</h1>
        <p><strong>Database:</strong> access_requests1 | <strong>Tables Found:</strong> <?php echo implode(', ', $tables); ?></p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Navigation Tabs -->
        <div class="tabs">
            <a href="?tab=requests" class="tab <?php echo $activeTab === 'requests' ? 'active' : ''; ?>">Access Requests</a>
            <a href="?tab=logs" class="tab <?php echo $activeTab === 'logs' ? 'active' : ''; ?>">Access Logs</a>
            <a href="?tab=structure" class="tab <?php echo $activeTab === 'structure' ? 'active' : ''; ?>">Database Structure</a>
        </div>
        
        <!-- Access Requests Tab -->
        <div class="tab-content <?php echo $activeTab === 'requests' ? 'active' : ''; ?>">
            <!-- Add/Edit Request Form -->
            <div class="form-section">
                <h3><?php echo ($editRecord && !isset($editRecord['log_id'])) ? 'Edit' : 'Add'; ?> Access Request</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo ($editRecord && !isset($editRecord['log_id'])) ? 'update_request' : 'insert_request'; ?>">
                    <?php if ($editRecord && !isset($editRecord['log_id'])): ?>
                        <input type="hidden" name="id" value="<?php echo $editRecord['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Student ID:</label>
                            <input type="text" name="student_id" value="<?php echo $editRecord['student_id'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name:</label>
                            <input type="text" name="name" value="<?php echo $editRecord['name'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address:</label>
                            <input type="email" name="email" value="<?php echo $editRecord['email'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Approval Status:</label>
                            <select name="approved" required>
                                <option value="0" <?php echo ($editRecord['approved'] ?? 0) == 0 ? 'selected' : ''; ?>>Pending</option>
                                <option value="1" <?php echo ($editRecord['approved'] ?? 0) == 1 ? 'selected' : ''; ?>>Approved</option>
                                <option value="-1" <?php echo ($editRecord['approved'] ?? 0) == -1 ? 'selected' : ''; ?>>Denied</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason for Access:</label>
                        <textarea name="reason" required><?php echo $editRecord['reason'] ?? ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn <?php echo ($editRecord && !isset($editRecord['log_id'])) ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo ($editRecord && !isset($editRecord['log_id'])) ? 'Update Request' : 'Add Request'; ?>
                    </button>
                    
                    <?php if ($editRecord && !isset($editRecord['log_id'])): ?>
                        <a href="?tab=requests" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Display Requests -->
            <div class="form-section">
                <h3>Access Requests Management</h3>
                <p><strong>Total Requests:</strong> <?php echo count($requests); ?></p>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['id']); ?></td>
                            <td><?php echo htmlspecialchars($request['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td><?php echo htmlspecialchars(substr($request['reason'], 0, 50)) . (strlen($request['reason']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <?php 
                                $status = $request['approved'];
                                if ($status == 1) {
                                    echo '<span class="status-approved">Approved</span>';
                                } elseif ($status == -1) {
                                    echo '<span class="status-denied">Denied</span>';
                                } else {
                                    echo '<span class="status-pending">Pending</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo $request['created_at']; ?></td>
                            <td class="actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="edit_request">
                                    <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" class="btn btn-warning">Edit</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this request?')">
                                    <input type="hidden" name="action" value="delete_request">
                                    <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Access Logs Tab -->
        <div class="tab-content <?php echo $activeTab === 'logs' ? 'active' : ''; ?>">
            <!-- Add/Edit Log Form -->
            <div class="form-section">
                <h3><?php echo ($editRecord && isset($editRecord['log_id'])) ? 'Edit' : 'Add'; ?> Access Log</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo ($editRecord && isset($editRecord['log_id'])) ? 'update_log' : 'insert_log'; ?>">
                    <?php if ($editRecord && isset($editRecord['log_id'])): ?>
                        <input type="hidden" name="log_id" value="<?php echo $editRecord['log_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Student Card ID:</label>
                            <input type="text" name="student_card" value="<?php echo $editRecord['student_card'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Success Status:</label>
                            <select name="success" required>
                                <option value="1" <?php echo ($editRecord['success'] ?? 1) == 1 ? 'selected' : ''; ?>>Success</option>
                                <option value="0" <?php echo ($editRecord['success'] ?? 1) == 0 ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Reason/Description:</label>
                            <input type="text" name="reason" value="<?php echo $editRecord['reason'] ?? 'Normal access attempt'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>IP Address:</label>
                            <input type="text" name="ip_address" value="<?php echo $editRecord['ip_address'] ?? $_SERVER['REMOTE_ADDR']; ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn <?php echo ($editRecord && isset($editRecord['log_id'])) ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo ($editRecord && isset($editRecord['log_id'])) ? 'Update Log' : 'Add Log Entry'; ?>
                    </button>
                    
                    <?php if ($editRecord && isset($editRecord['log_id'])): ?>
                        <a href="?tab=logs" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Display Access Logs -->
            <div class="form-section">
                <h3>Access Logs Management</h3>
                <p><strong>Recent Logs:</strong> <?php echo count($access_logs); ?> (showing last 50)</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Student Card</th>
                            <th>Access Time</th>
                            <th>Success</th>
                            <th>Reason</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($access_logs as $log): ?>
                        <tr class="<?php echo $log['success'] ? 'log-success' : 'log-failed'; ?>">
                            <td><?php echo $log['log_id']; ?></td>
                            <td><?php echo htmlspecialchars($log['student_card']); ?></td>
                            <td><?php echo $log['access_time']; ?></td>
                            <td><?php echo $log['success'] ? 'Success' : 'Failed'; ?></td>
                            <td><?php echo htmlspecialchars($log['reason']); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td class="actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="edit_log">
                                    <input type="hidden" name="log_id" value="<?php echo $log['log_id']; ?>">
                                    <button type="submit" class="btn btn-warning">Edit</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this log entry?')">
                                    <input type="hidden" name="action" value="delete_log">
                                    <input type="hidden" name="log_id" value="<?php echo $log['log_id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Database Structure Tab -->
        <div class="tab-content <?php echo $activeTab === 'structure' ? 'active' : ''; ?>">
            <div class="form-section">
                <h3>Database Structure Overview</h3>
                <p>Complete view of all tables and their structures in the access_requests1 database.</p>
                
                <?php
                // Display structure for each table
                foreach ($tables as $table) {
                    echo "<h4>Table: $table</h4>";
                    $structure = $db->query("DESCRIBE $table");
                    if ($structure) {
                        echo "<table>";
                        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                        while ($field = $structure->fetch_array()) {
                            echo "<tr>";
                            echo "<td>" . $field['Field'] . "</td>";
                            echo "<td>" . $field['Type'] . "</td>";
                            echo "<td>" . $field['Null'] . "</td>";
                            echo "<td>" . $field['Key'] . "</td>";
                            echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
                            echo "<td>" . $field['Extra'] . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        // Show record count
                        $count_result = $db->query("SELECT COUNT(*) as count FROM $table");
                        if ($count_result) {
                            $count = $count_result->fetch_assoc()['count'];
                            echo "<p><strong>Total Records:</strong> $count</p>";
                        }
                        echo "<hr>";
                    }
                }
                ?>
                
                <div style="margin-top: 20px;">
                    <a href="check_database_structure.php" class="btn btn-info">Detailed Database Check</a>
                    <a href="test_database.php" class="btn btn-success">Test Database Functions</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
