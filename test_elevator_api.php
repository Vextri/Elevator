<?php
// test_elevator_api.php - Test API connected to real elevator database
header('Content-Type: application/json');

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

function update_elevatorNetwork(int $node_ID, int $new_floor = 1): array {
    try {
        $db1 = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        $query = 'UPDATE elevatorNetwork 
                SET currentFloor = :floor
                WHERE nodeID = :id';
        $statement = $db1->prepare($query);
        $statement->bindvalue('floor', $new_floor);
        $statement->bindvalue('id', $node_ID);
        $statement->execute();	
        
        return ['success' => true, 'floor' => $new_floor, 'connected' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'floor' => 0, 'connected' => false, 'error' => $e->getMessage()];
    }
}

function get_currentFloor(): array {
    try {
        $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        $query = 'SELECT currentFloor FROM elevatorNetwork WHERE nodeID = 1';
        $result = $db->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $current_floor = $row['currentFloor'] ?? 1;
        
        return ['success' => true, 'floor' => $current_floor, 'connected' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'floor' => 0, 'connected' => false, 'error' => $e->getMessage()];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
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
            $result = update_elevatorNetwork(1, $new_floor);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'current_floor' => $result['floor'],
                    'database_connected' => $result['connected'],
                    'is_locked_out' => false,
                    'message' => "Moved to floor {$result['floor']}"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'database_connected' => $result['connected'],
                    'is_locked_out' => false,
                    'message' => 'Database error: ' . ($result['error'] ?? 'Unknown error')
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'database_connected' => false,
                'is_locked_out' => false,
                'message' => 'Invalid floor number'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'database_connected' => false,
            'is_locked_out' => false,
            'message' => 'Invalid action'
        ]);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Return current floor status with connection info and lockout status
    $result = get_currentFloor();
    $lockout_status = check_lockout_status();
    
    echo json_encode([
        'success' => $result['success'],
        'current_floor' => $result['floor'],
        'database_connected' => $result['connected'],
        'is_locked_out' => $lockout_status['is_locked_out'],
        'lockout_reason' => $lockout_status['lockout_reason'] ?? null,
        'locked_by' => $lockout_status['locked_by'] ?? null,
        'lockout_time' => $lockout_status['lockout_time'] ?? null,
        'message' => $result['success'] ? 'Connected to elevator database' : 'Database connection failed'
    ]);
}
?>
