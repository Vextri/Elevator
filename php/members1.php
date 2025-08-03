<?php
/**
 * Members1.php - Access Requests Database Management Interface
 * Manages the access_requests1 database with its 3 tables
 */

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

// Database connection for access_requests1
$mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$message = '';
$messageType = '';
$editRecord = null;
$activeTab = $_GET['tab'] ?? 'requests';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert_request':
                    // Insert new access request
                    $stmt = $mysqli->prepare("INSERT INTO requests (username, password, email, student_card, approved) VALUES (?, ?, ?, ?, 0)");
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt->bind_param("ssss", $_POST['username'], $hashed_password, $_POST['email'], $_POST['student_card']);
                    
                    if ($stmt->execute()) {
                        $message = "New access request added successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'approve_request':
                    // Approve a request
                    $stmt = $mysqli->prepare("UPDATE requests SET approved = 1 WHERE id = ?");
                    $stmt->bind_param("i", $_POST['request_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Request approved successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'deny_request':
                    // Deny a request
                    $stmt = $mysqli->prepare("UPDATE requests SET approved = -1 WHERE id = ?");
                    $stmt->bind_param("i", $_POST['request_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Request denied successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'delete_request':
                    // Delete a request
                    $stmt = $mysqli->prepare("DELETE FROM requests WHERE id = ?");
                    $stmt->bind_param("i", $_POST['request_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Request deleted successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'edit_request':
                    // Load record for editing
                    $stmt = $mysqli->prepare("SELECT * FROM requests WHERE id = ?");
                    $stmt->bind_param("i", $_POST['request_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $editRecord = $result->fetch_assoc();
                    $stmt->close();
                    break;
                    
                case 'update_request':
                    // Update existing request
                    $sql = "UPDATE requests SET username = ?, email = ?, student_card = ?";
                    $params = [$_POST['username'], $_POST['email'], $_POST['student_card']];
                    $types = "sss";
                    
                    // Only update password if provided
                    if (!empty($_POST['password'])) {
                        $sql .= ", password = ?";
                        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $types .= "s";
                    }
                    
                    $sql .= " WHERE id = ?";
                    $params[] = $_POST['request_id'];
                    $types .= "i";
                    
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    
                    if ($stmt->execute()) {
                        $message = "Request updated successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'insert_log':
                    // Insert new access log
                    $stmt = $mysqli->prepare("INSERT INTO access_logs (student_card, success, reason, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
                    $success = $_POST['success'] == '1' ? 1 : 0;
                    $stmt->bind_param("sisss", $_POST['student_card'], $success, $_POST['reason'], $_POST['ip_address'], $_POST['user_agent']);
                    
                    if ($stmt->execute()) {
                        $message = "New access log added successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'edit_log':
                    // Load log record for editing
                    $stmt = $mysqli->prepare("SELECT * FROM access_logs WHERE log_id = ?");
                    $stmt->bind_param("i", $_POST['log_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $editRecord = $result->fetch_assoc();
                    $activeTab = 'logs';
                    $stmt->close();
                    break;
                    
                case 'update_log':
                    // Update existing access log
                    $stmt = $mysqli->prepare("UPDATE access_logs SET student_card = ?, success = ?, reason = ?, ip_address = ?, user_agent = ? WHERE log_id = ?");
                    $success = $_POST['success'] == '1' ? 1 : 0;
                    $stmt->bind_param("sisssi", $_POST['student_card'], $success, $_POST['reason'], $_POST['ip_address'], $_POST['user_agent'], $_POST['log_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Access log updated successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'delete_log':
                    // Delete access log
                    $stmt = $mysqli->prepare("DELETE FROM access_logs WHERE log_id = ?");
                    $stmt->bind_param("i", $_POST['log_id']);
                    
                    if ($stmt->execute()) {
                        $message = "Access log deleted successfully!";
                        $messageType = 'success';
                    }
                    $stmt->close();
                    break;
                    
                case 'clear_logs':
                    // Clear old access logs
                    $days = intval($_POST['days']);
                    $stmt = $mysqli->prepare("DELETE FROM access_logs WHERE access_time < DATE_SUB(NOW(), INTERVAL ? DAY)");
                    $stmt->bind_param("i", $days);
                    
                    if ($stmt->execute()) {
                        $affected = $stmt->affected_rows;
                        $message = "Cleared $affected old log entries!";
                        $messageType = 'success';
                    }
                    $stmt->close();
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
    // Get requests
    $requests_result = $mysqli->query("SELECT * FROM requests ORDER BY created_at DESC");
    $requests = $requests_result->fetch_all(MYSQLI_ASSOC);
    
    // Get access logs
    $logs_result = $mysqli->query("SELECT * FROM access_logs ORDER BY access_time DESC LIMIT 50");
    $access_logs = $logs_result->fetch_all(MYSQLI_ASSOC);
    
    // Get table info to discover the third table
    $tables_result = $mysqli->query("SHOW TABLES");
    $tables = [];
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }
    
} catch (Exception $e) {
    $message = "Error loading data: " . $e->getMessage();
    $messageType = 'error';
    $requests = [];
    $access_logs = [];
    $tables = [];
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Requests Database Management</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/projectsVI.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
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
        .btn-info { background: #17a2b8; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px; }
        table th, table td { padding: 8px; border: 1px solid #dee2e6; text-align: left; }
        table th { background: #e9ecef; font-weight: bold; }
        .actions { white-space: nowrap; }
        .back-nav { text-align: center; margin: 20px 0; }
        .tabs { display: flex; border-bottom: 2px solid #dee2e6; margin: 20px 0; }
        .tab { padding: 12px 24px; background: #f8f9fa; border: 1px solid #dee2e6; border-bottom: none; cursor: pointer; margin-right: 2px; text-decoration: none; color: #495057; }
        .tab.active { background: white; border-bottom: 2px solid white; margin-bottom: -2px; font-weight: bold; color: #007bff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-denied { color: #dc3545; font-weight: bold; }
        .log-success { background: #d4edda; }
        .log-failed { background: #f8d7da; }
        h2 { color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
        h3 { color: #6c757d; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-nav">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            <a href="members.php" class="btn btn-info">Elevator Network DB</a>
        </div>
        
        <h1>Access Requests Database Management</h1>
        <p><strong>Database:</strong> access_requests1 | <strong>Tables Found:</strong> <?php echo implode(', ', $tables); ?></p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Navigation Tabs -->
        <div class="tabs">
            <a href="?tab=requests" class="tab <?php echo $activeTab === 'requests' ? 'active' : ''; ?>">User Requests</a>
            <a href="?tab=logs" class="tab <?php echo $activeTab === 'logs' ? 'active' : ''; ?>">Access Logs</a>
            <a href="?tab=admin" class="tab <?php echo $activeTab === 'admin' ? 'active' : ''; ?>">Admin Tools</a>
        </div>
        
        <!-- User Requests Tab -->
        <div class="tab-content <?php echo $activeTab === 'requests' ? 'active' : ''; ?>">
            <!-- Add/Edit Request Form -->
            <div class="form-section">
                <h3><?php echo $editRecord ? 'Edit' : 'Add'; ?> Access Request</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editRecord ? 'update_request' : 'insert_request'; ?>">
                    <?php if ($editRecord): ?>
                        <input type="hidden" name="request_id" value="<?php echo $editRecord['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" value="<?php echo $editRecord['username'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?php echo $editRecord['email'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Password <?php echo $editRecord ? '(leave blank to keep current)' : ''; ?>:</label>
                            <input type="password" name="password" <?php echo $editRecord ? '' : 'required'; ?>>
                        </div>
                        <div class="form-group">
                            <label>Student Card ID:</label>
                            <input type="text" name="student_card" value="<?php echo $editRecord['student_card'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn <?php echo $editRecord ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo $editRecord ? 'Update Request' : 'Add Request'; ?>
                    </button>
                    
                    <?php if ($editRecord): ?>
                        <a href="?tab=requests" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Display Requests -->
            <div class="form-section">
                <h3>👥 User Access Requests</h3>
                <p><strong>Total Requests:</strong> <?php echo count($requests); ?></p>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Student Card</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo $request['id']; ?></td>
                            <td><?php echo htmlspecialchars($request['username']); ?></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td><?php echo htmlspecialchars($request['student_card']); ?></td>
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
                                <?php if ($request['approved'] == 0): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve_request">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="deny_request">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Deny</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="edit_request">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" class="btn btn-warning">Edit</button>
                                </form>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this request?')">
                                    <input type="hidden" name="action" value="delete_request">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
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
            <!-- Add/Edit Access Log Form -->
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
                            <label>Reason/Notes:</label>
                            <textarea name="reason" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;"><?php echo $editRecord['reason'] ?? ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>IP Address:</label>
                            <input type="text" name="ip_address" value="<?php echo $editRecord['ip_address'] ?? $_SERVER['REMOTE_ADDR']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>User Agent:</label>
                        <input type="text" name="user_agent" value="<?php echo $editRecord['user_agent'] ?? $_SERVER['HTTP_USER_AGENT']; ?>">
                    </div>
                    
                    <button type="submit" class="btn <?php echo ($editRecord && isset($editRecord['log_id'])) ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo ($editRecord && isset($editRecord['log_id'])) ? 'Update Log' : 'Add Log'; ?>
                    </button>
                    
                    <?php if ($editRecord && isset($editRecord['log_id'])): ?>
                        <a href="?tab=logs" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Display Access Logs -->
            <div class="form-section">
                <h3>Access Logs (Last 50 entries)</h3>
                <p><strong>Total Shown:</strong> <?php echo count($access_logs); ?></p>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
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
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this access log?')">
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
        
        <!-- Admin Tools Tab -->
        <div class="tab-content <?php echo $activeTab === 'admin' ? 'active' : ''; ?>">
            <div class="form-section">
                <h3>Database Administration</h3>
                
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <h4>Database Tables</h4>
                    <p><strong>Database:</strong> access_requests1</p>
                    <p><strong>Tables found:</strong></p>
                    <ul>
                        <?php foreach ($tables as $table): ?>
                            <li><code><?php echo $table; ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <h4>Clear Old Access Logs</h4>
                    <p>Remove access log entries older than specified days:</p>
                    <form method="POST" onsubmit="return confirm('This will permanently delete old log entries. Continue?')">
                        <input type="hidden" name="action" value="clear_logs">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label>Delete logs older than:</label>
                            <input type="number" name="days" value="30" min="1" max="365" style="width: 80px;">
                            <span>days</span>
                            <button type="submit" class="btn btn-danger">Clear Logs</button>
                        </div>
                    </form>
                </div>
                
                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <h4>Database Statistics</h4>
                    <ul>
                        <li><strong>Total User Requests:</strong> <?php echo count($requests); ?></li>
                        <li><strong>Approved Requests:</strong> <?php echo count(array_filter($requests, function($r) { return $r['approved'] == 1; })); ?></li>
                        <li><strong>Pending Requests:</strong> <?php echo count(array_filter($requests, function($r) { return $r['approved'] == 0; })); ?></li>
                        <li><strong>Denied Requests:</strong> <?php echo count(array_filter($requests, function($r) { return $r['approved'] == -1; })); ?></li>
                        <li><strong>Recent Access Logs:</strong> <?php echo count($access_logs); ?> (showing last 50)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
