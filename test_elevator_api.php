<?php
// test_elevator_api.php - Test API connected to real elevator database
header('Content-Type: application/json');

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
        $rows = $db->query('SELECT currentFloor FROM elevatorNetwork');
        foreach ($rows as $row) {
            $current_floor = $row[0];
        }
        return ['success' => true, 'floor' => $current_floor ?? 1, 'connected' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'floor' => 0, 'connected' => false, 'error' => $e->getMessage()];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'move_floor') {
        $new_floor = intval($_POST['newfloor'] ?? 1);
        if ($new_floor >= 1 && $new_floor <= 3) {
            $result = update_elevatorNetwork(1, $new_floor);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'current_floor' => $result['floor'],
                    'connected' => $result['connected'],
                    'message' => "Moved to floor {$result['floor']}"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'connected' => $result['connected'],
                    'message' => 'Database error: ' . ($result['error'] ?? 'Unknown error')
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'connected' => false,
                'message' => 'Invalid floor number'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'connected' => false,
            'message' => 'Invalid action'
        ]);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Return current floor status with connection info
    $result = get_currentFloor();
    echo json_encode([
        'success' => $result['success'],
        'current_floor' => $result['floor'],
        'connected' => $result['connected'],
        'message' => $result['success'] ? 'Connected to elevator database' : 'Database connection failed'
    ]);
}
?>
