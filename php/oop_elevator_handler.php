<?php
session_start();

// Include our OOP elevator classes
require_once '../classes/Node.php';
require_once '../classes/FloorNode.php';
require_once '../classes/ElevatorCar.php';

if ($_POST && isset($_POST['newfloor']) && isset($_POST['elevator_id'])) {
    $elevator_id = (int)$_POST['elevator_id'];
    $new_floor = (int)$_POST['newfloor'];
    
    // Get the elevator object and update it
    $elevator = ElevatorCar::getElevatorById($elevator_id);
    if ($elevator) {
        $elevator->setFloor($new_floor);
        $elevator->setStatus("idle");
    }
}

// Redirect back to index
header('Location: index1.php');
exit;
?>