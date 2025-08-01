<?php
/**
 * Elevator Network Database Functions
 * Deliverable: Update function + Transaction version with exception handling
 */

require_once 'elevator_exceptions.php';

class ElevatorNetworkDB {
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=localhost;dbname=elevator_network",
                "root", "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update any field in a given table (except Primary Key) - 8 marks
     * @param string $table - Table name
     * @param array $updates - Associative array of field => value pairs
     * @param string $primaryKey - Primary key field name
     * @param mixed $primaryValue - Primary key value
     * @return bool - Success status
     * @throws Exception - On error or invalid input
     */
    public function updateRecord($table, $updates, $primaryKey, $primaryValue) {
        // Input validation
        if (empty($table) || empty($updates) || empty($primaryKey) || empty($primaryValue)) {
            throw new InvalidArgumentException("All parameters are required");
        }
        
        // Prevent primary key updates
        if (array_key_exists($primaryKey, $updates)) {
            throw new InvalidArgumentException("Cannot update primary key field: $primaryKey");
        }
        
        // Validate allowed tables
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Table '$table' is not allowed");
        }
        
        try {
            // Build dynamic SQL
            $setParts = [];
            $values = [];
            
            foreach ($updates as $field => $value) {
                $setParts[] = "`$field` = ?";
                $values[] = $value;
            }
            
            $values[] = $primaryValue;
            
            $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `$primaryKey` = ?";
            
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("No rows were updated. Record may not exist.");
            }
            
            return true;
            
        } catch (PDOException $e) {
            throw new Exception("Database update failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update record using transactions with exception handling - 4 marks
     * @param string $table - Table name
     * @param array $updates - Fields to update
     * @param string $primaryKey - Primary key field
     * @param mixed $primaryValue - Primary key value
     * @return bool - Success status
     * @throws Exception - On any error
     */
    public function updateRecordWithTransaction($table, $updates, $primaryKey, $primaryValue) {
        // Input validation with custom exceptions
        if (empty($table) || empty($updates) || empty($primaryKey) || empty($primaryValue)) {
            throw new InvalidNodeConfigException($primaryValue ?? 'unknown', 'update_parameters', 'empty', 'All parameters are required for transaction update');
        }
        
        // Prevent primary key updates
        if (array_key_exists($primaryKey, $updates)) {
            throw new InvalidNodeConfigException($primaryValue, $primaryKey, $updates[$primaryKey], 'Cannot update primary key field in transaction');
        }
        
        // Validate table exists
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new ElevatorDatabaseException('update', $table, 'Table is not allowed in transaction');
        }
        
        // Start transaction
        $this->connection->beginTransaction();
        
        try {
            // First, verify record exists
            $checkSql = "SELECT COUNT(*) FROM `$table` WHERE `$primaryKey` = ?";
            $checkStmt = $this->connection->prepare($checkSql);
            $checkStmt->execute([$primaryValue]);
            
            if ($checkStmt->fetchColumn() == 0) {
                throw new ElevatorDatabaseException('update', $table, "Record with $primaryKey = $primaryValue does not exist");
            }
            
            // Validate elevator-specific business rules
            if (isset($updates['currentFloor'])) {
                $floor = (int)$updates['currentFloor'];
                if ($floor < 1 || $floor > 10) {
                    throw new InvalidFloorException($floor, 10, 1);
                }
            }
            
            if (isset($updates['status']) && !in_array($updates['status'], ['online', 'offline', 'maintenance'])) {
                throw new InvalidNodeConfigException($primaryValue, 'status', $updates['status'], 'Invalid status value');
            }
            
            // Simulate network communication check
            if (isset($updates['ipAddress'])) {
                if (!filter_var($updates['ipAddress'], FILTER_VALIDATE_IP)) {
                    throw new NetworkCommunicationException($primaryValue, $updates['ipAddress'], 'Invalid IP address format');
                }
                
                // Simulate network ping check (in real system, you'd actually ping)
                if ($updates['ipAddress'] === '192.168.1.999') {
                    throw new NetworkCommunicationException($primaryValue, $updates['ipAddress'], 'Host unreachable - no response to ping');
                }
            }
            
            // Build update SQL
            $setParts = [];
            $values = [];
            
            foreach ($updates as $field => $value) {
                $setParts[] = "`$field` = ?";
                $values[] = $value;
            }
            
            $values[] = $primaryValue;
            
            $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `$primaryKey` = ?";
            
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($values);
            
            if (!$result || $stmt->rowCount() === 0) {
                throw new ElevatorDatabaseException('update', $table, 'No rows were updated in transaction');
            }
            
            // Simulate CAN bus update for components
            if ($table === 'elevatorNetwork' && isset($updates['status']) && $updates['status'] === 'maintenance') {
                // In real system, this would send CAN message to put device in maintenance mode
                $this->simulateCANBusUpdate($primaryValue, 'maintenance_mode', true);
            }
            
            // Commit transaction
            $this->connection->commit();
            return true;
            
        } catch (ElevatorException $e) {
            // Handle our custom elevator exceptions
            $this->connection->rollback();
            error_log("Elevator System Error: " . $e->getMessage() . " Context: " . json_encode($e->getContext()));
            throw $e; // Re-throw to be handled by calling function
            
        } catch (PDOException $e) {
            // Handle database exceptions
            $this->connection->rollback();
            throw new ElevatorDatabaseException('update', $table, $e->getMessage());
            
        } catch (Exception $e) {
            // Handle any other unexpected exceptions
            $this->connection->rollback();
            throw new ElevatorException("Unexpected error during transaction: " . $e->getMessage(), 9999, ['original_error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get all records from a table
     */
    public function getAllRecords($table) {
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Table '$table' is not allowed");
        }
        
        $stmt = $this->connection->prepare("SELECT * FROM `$table` ORDER BY " . 
            ($table === 'elevatorNetwork' ? 'nodeID' : 'canID'));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Insert new record
     */
    public function insertRecord($table, $data) {
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Table '$table' is not allowed");
        }
        
        // Remove empty values
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null;
        });
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute(array_values($data));
    }
    
    /**
     * Delete record
     */
    public function deleteRecord($table, $primaryKey, $primaryValue) {
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Table '$table' is not allowed");
        }
        
        $sql = "DELETE FROM `$table` WHERE `$primaryKey` = ?";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([$primaryValue]);
    }
    
    /**
     * Get a single record
     */
    public function getRecord($table, $primaryKey, $primaryValue) {
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Table '$table' is not allowed");
        }
        
        $sql = "SELECT * FROM `$table` WHERE `$primaryKey` = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$primaryValue]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Simulate CAN bus communication
    private function simulateCANBusUpdate($nodeId, $command, $value) {
        // In real system, this would communicate with actual CAN bus
        // For demo, we'll simulate potential CAN errors
        
        // Get node info for CAN address
        $node = $this->getRecord('elevatorNetwork', 'nodeID', $nodeId);
        if (!$node) {
            throw new CANBusException('unknown', 'unknown', "Node $nodeId not found for CAN update");
        }
        
        // Simulate CAN address lookup
        $canQuery = "SELECT canAddress, componentType FROM canComponents WHERE nodeID = ?";
        $stmt = $this->connection->prepare($canQuery);
        $stmt->execute([$nodeId]);
        $canComponents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($canComponents as $component) {
            // Simulate CAN communication errors
            if ($component['canAddress'] === '0x104') {
                throw new CANBusException($component['canAddress'], $component['componentType'], 'CAN controller not responding - possible hardware failure');
            }
            
            // Simulate successful CAN update
            error_log("CAN Bus: Sent command '$command=$value' to {$component['canAddress']} ({$component['componentType']})");
        }
    }
}
?>
