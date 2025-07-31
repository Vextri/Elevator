<?php

require_once 'Node.php';
require_once 'FloorNode.php';

class ElevatorCar extends Node {
    private string $status;
    private static int $totalElevators = 0;
    private static array $elevatorCars = [];
    
    public function __construct(int $floor = 1, string $status = "idle") {
        parent::__construct($floor);
        $this->status = $status;
        self::$totalElevators++;
        self::$elevatorCars[$this->id] = $this;
        
        // Update floor node to show elevator is present
        $floorNode = FloorNode::getFloorNode($floor);
        if ($floorNode) {
            $floorNode->setElevatorAtFloor(true);
        }
    }
    
    // Override getFloor method as specified in UML
    public function getFloor(): int {
        return parent::getFloor();
    }
    
    // Override setFloor method as specified in UML
    public function setFloor(int $floor): void {
        if ($floor < 1) {
            throw new InvalidArgumentException("Floor must be 1 or greater");
        }
        
        // Clear elevator from current floor
        $currentFloorNode = FloorNode::getFloorNode($this->floor);
        if ($currentFloorNode) {
            $currentFloorNode->setElevatorAtFloor(false);
        }
        
        // Update elevator floor
        parent::setFloor($floor);
        
        // Set elevator at new floor
        $newFloorNode = FloorNode::getFloorNode($floor);
        if ($newFloorNode) {
            $newFloorNode->setElevatorAtFloor(true);
        }
        
        $this->status = "moving";
    }
    
    // Getter for status
    public function getStatus(): string {
        return "Elevator {$this->id}: Floor {$this->floor} - {$this->status}";
    }
    
    // Setter for status
    public function setStatus(string $status): void {
        $this->status = $status;
    }
    
    // Move elevator up
    public function moveUp(): void {
        $this->setFloor($this->floor + 1);
    }
    
    // Move elevator down
    public function moveDown(): void {
        if ($this->floor > 1) {
            $this->setFloor($this->floor - 1);
        }
    }
    
    // Static method to get total number of elevators
    public static function getTotalElevators(): int {
        return self::$totalElevators;
    }
    
    // Static method to get all elevator cars
    public static function getAllElevatorCars(): array {
        return self::$elevatorCars;
    }
    
    // Static method to find elevator by ID
    public static function getElevatorById(int $id): ?ElevatorCar {
        return self::$elevatorCars[$id] ?? null;
    }
    
    public function __toString(): string {
        return $this->getStatus();
    }
}
?>