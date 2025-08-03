
<?php

abstract class Node {
    protected int $id;
    protected int $floor;
    private static int $nextId = 1;
    
    public function __construct(int $floor = 1) {
        $this->id = self::$nextId++;
        $this->floor = $floor;
    }
    
    // Getters
    public function getId(): int {
        return $this->id;
    }
    
    public function getFloor(): int {
        return $this->floor;
    }
    
    // Setters
    protected function setFloor(int $floor): void {
        if ($floor < 1) {
            throw new InvalidArgumentException("Floor must be 1 or greater");
        }
        $this->floor = $floor;
    }
    
    // Static method to get next available ID
    public static function getNextId(): int {
        return self::$nextId;
    }
    
    // Static method to reset ID counter (useful for testing)
    public static function resetIdCounter(): void {
        self::$nextId = 1;
    }
    
    // Abstract method that child classes must implement
    abstract public function getStatus(): string;
}
?>