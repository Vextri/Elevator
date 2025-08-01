<?php
/**
 * Elevator Network Database Functions
 * Deliverable: Update function + Transaction version with exception handling
 */

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
        // Input validation
        if (empty($table) || empty($updates) || empty($primaryKey) || empty($primaryValue)) {
            throw new InvalidArgumentException("All parameters are required for transaction update");
        }
        
        // Prevent primary key updates
        if (array_key_exists($primaryKey, $updates)) {
            throw new InvalidArgumentException("Cannot update primary key field in transaction: $primaryKey");
        }
        
        // Validate table exists
        $allowedTables = ['elevatorNetwork', 'canComponents'];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Table '$table' is not allowed in transaction");
        }
        
        // Start transaction
        $this->connection->beginTransaction();
        
        try {
            // First, verify record exists
            $checkSql = "SELECT COUNT(*) FROM `$table` WHERE `$primaryKey` = ?";
            $checkStmt = $this->connection->prepare($checkSql);
            $checkStmt->execute([$primaryValue]);
            
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("Record with $primaryKey = $primaryValue does not exist");
            }
            
            // Validate specific fields
            if (isset($updates['currentFloor']) && ($updates['currentFloor'] < 1 || $updates['currentFloor'] > 10)) {
                throw new InvalidArgumentException("Invalid floor number: " . $updates['currentFloor']);
            }
            
            if (isset($updates['status']) && !in_array($updates['status'], ['online', 'offline', 'maintenance'])) {
                throw new InvalidArgumentException("Invalid status: " . $updates['status']);
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
                throw new Exception("No rows were updated in transaction");
            }
            
            // Commit transaction
            $this->connection->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback on any error
            $this->connection->rollback();
            throw new Exception("Transaction failed: " . $e->getMessage());
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
}
?>
