
<?php

require_once 'Node.php';

class FloorNode extends Node {
    private bool $elevatorAtFloor;
    private static array $floorNodes = [];
    
    public function __construct(int $floor = 1, bool $elevatorAtFloor = false) {
        parent::__construct($floor);
        $this->elevatorAtFloor = $elevatorAtFloor;
        self::$floorNodes[$this->floor] = $this;
    }
    
    // Getter for elevatorAtFloor
    public function isElevatorAtFloor(): bool {
        return $this->elevatorAtFloor;
    }
    
    // Setter for elevatorAtFloor
    public function setElevatorAtFloor(bool $elevatorAtFloor): void {
        $this->elevatorAtFloor = $elevatorAtFloor;
    }
    
    // Static method to get all floor nodes
    public static function getAllFloorNodes(): array {
        return self::$floorNodes;
    }
    
    // Static method to get a specific floor node
    public static function getFloorNode(int $floor): ?FloorNode {
        return self::$floorNodes[$floor] ?? null;
    }
    
    // Static method to clear elevator from all floors
    public static function clearAllElevators(): void {
        foreach (self::$floorNodes as $floorNode) {
            $floorNode->setElevatorAtFloor(false);
        }
    }
    
    // Implementation of abstract method
    public function getStatus(): string {
        $elevatorStatus = $this->elevatorAtFloor ? "Elevator present" : "No elevator";
        return "Floor {$this->floor}: {$elevatorStatus}";
    }
    
    public function __toString(): string {
        return $this->getStatus();
    }
}
?>