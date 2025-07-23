<?php
// elevator_api.php - API for AJAX elevator operations
header('Content-Type: application/json');

// Database connection function
function get_database_connection() {
    try {
        $db = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese', 'ese');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        return null;
    }
}

function check_lockout_status(): array {
    try {
        $db = new PDO('mysql:host=localhost;dbname=elevator_lockout_db', 'root', '');
        $query = 'SELECT is_locked_out, lockout_reason, locked_by_username, lockout_timestamp 
                  FROM elevator_lockout 
                  WHERE elevator_id = 1 
                  ORDER BY lockout_timestamp DESC 
                  LIMIT 1';
        $result = $db->query($query);
        $lockout_data = $result->fetch(PDO::FETCH_ASSOC);
        
        if ($lockout_data) {
            return [
                'is_locked_out' => (bool)$lockout_data['is_locked_out'],
                'lockout_reason' => $lockout_data['lockout_reason'],
                'locked_by' => $lockout_data['locked_by_username'],
                'lockout_time' => $lockout_data['lockout_timestamp']
            ];
        } else {
            return ['is_locked_out' => false];
        }
    } catch (PDOException $e) {
        return ['is_locked_out' => false, 'error' => $e->getMessage()];
    }
}

function update_elevatorNetwork(int $node_ID, int $new_floor = 1): int {
    $db = get_database_connection();
    if (!$db) return 0;
    
    try {
        // Create table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS elevatorNetwork (
            nodeID INT PRIMARY KEY,
            currentFloor INT DEFAULT 1
        )");
        
        // Insert or update the elevator position
        $query = 'INSERT INTO elevatorNetwork (nodeID, currentFloor) 
                  VALUES (:id, :floor) 
                  ON DUPLICATE KEY UPDATE currentFloor = :floor';
        $statement = $db->prepare($query);
        $statement->bindValue('floor', $new_floor);
        $statement->bindValue('id', $node_ID);
        $statement->execute();
        
        return $new_floor;
    } catch (PDOException $e) {
        return 0;
    }
}

function get_currentFloor(): int {
    $db = get_database_connection();
    if (!$db) return 0;

    try {
        // Create table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS elevatorNetwork (
            nodeID INT PRIMARY KEY,
            currentFloor INT DEFAULT 1
        )");
        
        // Insert default record if none exists
        $db->exec("INSERT IGNORE INTO elevatorNetwork (nodeID, currentFloor) VALUES (1, 1)");
        
        $stmt = $db->prepare('SELECT currentFloor FROM elevatorNetwork WHERE nodeID = 1');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? intval($result['currentFloor']) : 1;
    } catch (PDOException $e) {
        return 0;
    }
}

function check_database_connection(): bool {
    $db = get_database_connection();
    return $db !== null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $db_connected = check_database_connection();
    
    if (!$db_connected) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'database_connected' => false,
            'is_locked_out' => false
        ]);
        exit;
    }
    
    // Check lockout status first
    $lockout_status = check_lockout_status();
    
    if ($lockout_status['is_locked_out']) {
        echo json_encode([
            'success' => false,
            'database_connected' => true,
            'is_locked_out' => true,
            'lockout_reason' => $lockout_status['lockout_reason'],
            'locked_by' => $lockout_status['locked_by'],
            'message' => 'Elevator is locked out: ' . $lockout_status['lockout_reason']
        ]);
        exit;
    }
    
    if ($action === 'move_floor') {
        $new_floor = intval($_POST['newfloor'] ?? 1);
        if ($new_floor >= 1 && $new_floor <= 3) {
            $current_floor = update_elevatorNetwork(1, $new_floor);
            if ($current_floor > 0) {
                echo json_encode([
                    'success' => true,
                    'current_floor' => $current_floor,
                    'message' => "Moved to floor {$current_floor}",
                    'database_connected' => true,
                    'is_locked_out' => false
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update elevator position',
                    'database_connected' => true,
                    'is_locked_out' => false
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid floor number (1-3 only)',
                'database_connected' => true,
                'is_locked_out' => false
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action',
            'database_connected' => true,
            'is_locked_out' => false
        ]);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Return current floor status with lockout info
    $db_connected = check_database_connection();
    $current_floor = $db_connected ? get_currentFloor() : 0;
    $lockout_status = $db_connected ? check_lockout_status() : ['is_locked_out' => false];
    
    echo json_encode([
        'success' => $db_connected,
        'current_floor' => $current_floor,
        'database_connected' => $db_connected,
        'is_locked_out' => $lockout_status['is_locked_out'],
        'lockout_reason' => $lockout_status['lockout_reason'] ?? null,
        'locked_by' => $lockout_status['locked_by'] ?? null,
        'lockout_time' => $lockout_status['lockout_time'] ?? null,
        'message' => $db_connected ? 'Connected to elevator system' : 'Database connection failed'
    ]);
}
?>
