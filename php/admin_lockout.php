<?php
// admin_lockout.php - Admin panel for elevator lockout/tagout control
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

// Connect to lockout database (temporarily using root user)
$mysqli = new mysqli("localhost", "root", "", "elevator_lockout_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Connect to existing user database for authentication
$user_mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1"); //This is a password I made up for the sake of the project it is not confidential
if ($user_mysqli->connect_error) {
    // Debug: Log the error and show a more helpful message
    error_log("Admin lockout DB connection failed: " . $user_mysqli->connect_error);
    die("User database connection failed. Please check database configuration. Error: " . $user_mysqli->connect_error);
}

// Check if user exists in access_requests1 (simple logged-in check)
$user_check = $user_mysqli->prepare("SELECT username, email FROM requests WHERE id = ?");
if (!$user_check) {
    die("Database query preparation failed: " . $user_mysqli->error);
}
$user_check->bind_param("i", $_SESSION['user_id']);
$user_check->execute();
$result = $user_check->get_result();
$user_data = $result->fetch_assoc();

// If logged in and user exists, they can access lockout controls
$is_admin = ($user_data !== null);

if (!$is_admin) {
    // Debug: Show more detailed error
    die("Access denied. User ID " . $_SESSION['user_id'] . " not found in access_requests1.requests table. Please ensure you're logged in properly.");
}

$message = '';
$message_type = '';

// Handle lockout/unlock actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $reason = trim($_POST['reason'] ?? '');
    
    if ($action === 'lockout' && !empty($reason)) {
        // Lock out the elevator
        $sql = "INSERT INTO elevator_lockout (elevator_id, is_locked_out, locked_by_user_id, locked_by_username, lockout_reason) 
                VALUES (1, TRUE, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                is_locked_out = TRUE, 
                locked_by_user_id = ?, 
                locked_by_username = ?, 
                lockout_reason = ?, 
                lockout_timestamp = CURRENT_TIMESTAMP,
                unlock_timestamp = NULL";
        $stmt = $mysqli->prepare($sql);
        $username = $_SESSION['username'] ?? 'Admin';
        $stmt->bind_param("isssss", $_SESSION['user_id'], $username, $reason, $_SESSION['user_id'], $username, $reason);
        
        if ($stmt->execute()) {
            $message = "Elevator successfully locked out. Reason: " . htmlspecialchars($reason);
            $message_type = 'success';
            
            // Logging is handled by the elevator_lockout table itself - no additional logging needed!
        } else {
            $message = "Error locking out elevator: " . $stmt->error;
            $message_type = 'error';
        }
        
    } elseif ($action === 'unlock') {
        // Unlock the elevator
        $sql = "UPDATE elevator_lockout SET 
                is_locked_out = FALSE, 
                unlock_timestamp = CURRENT_TIMESTAMP 
                WHERE elevator_id = 1 AND is_locked_out = TRUE";
        
        if ($mysqli->query($sql)) {
            $message = "Elevator successfully unlocked and returned to normal operation.";
            $message_type = 'success';
            
            // Logging is handled by the elevator_lockout table itself - no additional logging needed!
        } else {
            $message = "Error unlocking elevator: " . $mysqli->error;
            $message_type = 'error';
        }
    } elseif ($action === 'lockout' && empty($reason)) {
        $message = "Lockout reason is required.";
        $message_type = 'error';
    }
}

// Get current lockout status
$lockout_query = "SELECT * FROM elevator_lockout WHERE elevator_id = 1 ORDER BY id DESC LIMIT 1";
$lockout_result = $mysqli->query($lockout_query);
$lockout_status = $lockout_result->fetch_assoc();
$is_locked_out = $lockout_status && $lockout_status['is_locked_out'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator Lockout/Tagout Control</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .lockout-panel {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 2.5rem;
        }
        
        .status-locked {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .status-active {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .control-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .lockout-btn {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .lockout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(220, 53, 69, 0.4);
        }
        
        .unlock-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .unlock-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4);
        }
        
        .reason-input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }
        
        .reason-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .history-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }
        
        .history-item {
            padding: 15px;
            border: 1px solid #ddd;
            margin: 10px 0;
            border-radius: 8px;
            background: white;
        }
        
        .history-item.locked {
            border-left: 5px solid #dc3545;
        }
        
        .history-item.unlocked {
            border-left: 5px solid #28a745;
        }
        
        .nav-links {
            text-align: center;
            margin-top: 30px;
        }
        
        .nav-links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="lockout-panel">
        <div class="header">
            <h1> Elevator Lockout/Tagout Control</h1>
            <p>Safety system for maintenance and emergency situations</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($is_locked_out): ?>
            <div class="status-locked">
                ⚠️ ELEVATOR IS LOCKED OUT - MAINTENANCE MODE ⚠️<br><br>
                <strong>Reason:</strong> <?= htmlspecialchars($lockout_status['lockout_reason']) ?><br>
                <strong>Locked by:</strong> <?= htmlspecialchars($lockout_status['locked_by_username']) ?><br>
                <strong>Locked since:</strong> <?= $lockout_status['lockout_timestamp'] ?><br><br>
                <em>All elevator operations are disabled for safety.</em>
            </div>
            
            <div class="control-form">
                <h3>Unlock Elevator</h3>
                <p>Click below to restore normal elevator operation:</p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to UNLOCK the elevator and restore normal operation?')">
                    <input type="hidden" name="action" value="unlock">
                    <button type="submit" class="unlock-btn">
                        UNLOCK ELEVATOR
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="status-active">
                ✅ ELEVATOR IS ACTIVE AND OPERATIONAL<br>
                All systems normal - elevator available for use
            </div>
            
            <div class="control-form">
                <h3>Lockout Elevator</h3>
                <p>Use this feature for maintenance, emergencies, or safety concerns:</p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to LOCK OUT the elevator? This will disable all elevator operations.')">
                    <input type="hidden" name="action" value="lockout">
                    <label for="reason"><strong>Lockout Reason (Required):</strong></label>
                    <textarea name="reason" id="reason" class="reason-input" 
                              placeholder="Enter detailed reason for lockout (e.g., Scheduled maintenance, Emergency repair, Safety inspection, etc.)" 
                              required></textarea>
                    <button type="submit" class="lockout-btn">
                        LOCKOUT ELEVATOR
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="history-section">
            <h3> Recent Lockout History</h3>
            <?php
            $history_query = "SELECT * FROM elevator_lockout WHERE elevator_id = 1 ORDER BY id DESC LIMIT 10";
            $history_result = $mysqli->query($history_query);
            
            if ($history_result->num_rows > 0):
                while ($row = $history_result->fetch_assoc()): ?>
                    <div class="history-item <?= $row['is_locked_out'] ? 'locked' : 'unlocked' ?>">
                        <strong><?= $row['is_locked_out'] ? 'LOCKOUT' : 'UNLOCK' ?></strong><br>
                        <strong>Reason:</strong> <?= htmlspecialchars($row['lockout_reason']) ?><br>
                        <strong>By:</strong> <?= htmlspecialchars($row['locked_by_username']) ?><br>
                        <strong>Time:</strong> <?= $row['lockout_timestamp'] ?>
                        <?php if ($row['unlock_timestamp']): ?>
                            <br><strong>Unlocked:</strong> <?= $row['unlock_timestamp'] ?>
                            <?php
                            $lock_time = new DateTime($row['lockout_timestamp']);
                            $unlock_time = new DateTime($row['unlock_timestamp']);
                            $duration = $lock_time->diff($unlock_time);
                            ?>
                            <br><strong>Duration:</strong> <?= $duration->format('%h hours, %i minutes') ?>
                        <?php endif; ?>
                    </div>
                <?php endwhile;
            else: ?>
                <p>No lockout history found.</p>
            <?php endif; ?>
        </div>
        
        <div class="nav-links">
            <a href="../html/test_elevator.html">Elevator Controls</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>

<?php $mysqli->close(); ?>
