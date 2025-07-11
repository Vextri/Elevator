<?php
// elevator_api.php - API for AJAX elevator operations
header('Content-Type: application/json');

function update_elevatorNetwork(int $node_ID, int $new_floor = 1): int {
    $db1 = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
    $query = 'UPDATE elevatorNetwork 
            SET currentFloor = :floor
            WHERE nodeID = :id';
    $statement = $db1->prepare($query);
    $statement->bindvalue('floor', $new_floor);
    $statement->bindvalue('id', $node_ID);
    $statement->execute();	
    
    return $new_floor;
}

function get_currentFloor(): int {
    $db = null;
    try {
        $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
    } catch (PDOException $e) {
        return 0;
    }
    if (!$db) return 0;

    $rows = $db->query('SELECT currentFloor FROM elevatorNetwork');
    foreach ($rows as $row) {
        $current_floor = $row[0];
    }
    return $current_floor ?? 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'move_floor') {
        $new_floor = intval($_POST['newfloor'] ?? 1);
        if ($new_floor >= 1 && $new_floor <= 3) {
            $current_floor = update_elevatorNetwork(1, $new_floor);
            echo json_encode([
                'success' => true,
                'current_floor' => $current_floor,
                'message' => "Moved to floor {$current_floor}"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid floor number'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Just return current floor status
    $current_floor = get_currentFloor();
    echo json_encode([
        'success' => true,
        'current_floor' => $current_floor
    ]);
}
?>
