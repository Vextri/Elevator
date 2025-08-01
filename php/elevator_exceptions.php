<?php
/**
 * Custom Exception Classes for Elevator System
 * Deliverable: Handle unexpected input, communication errors, system errors
 */

// Base elevator exception
class ElevatorException extends Exception {
    protected $errorCode;
    protected $context;
    
    public function __construct($message, $errorCode = 0, $context = [], Exception $previous = null) {
        parent::__construct($message, $errorCode, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }
    
    public function getContext() {
        return $this->context;
    }
}

// Invalid floor request exception
class InvalidFloorException extends ElevatorException {
    public function __construct($requestedFloor, $maxFloor = 10, $minFloor = 1) {
        $message = "Invalid floor request: Floor $requestedFloor does not exist. Valid floors: $minFloor-$maxFloor";
        $context = [
            'requested_floor' => $requestedFloor,
            'min_floor' => $minFloor,
            'max_floor' => $maxFloor,
            'error_type' => 'INVALID_FLOOR'
        ];
        parent::__construct($message, 1001, $context);
    }
}

// Network communication error exception
class NetworkCommunicationException extends ElevatorException {
    public function __construct($nodeId, $ipAddress, $errorDetails = '') {
        $message = "Communication error with node $nodeId ($ipAddress): $errorDetails";
        $context = [
            'node_id' => $nodeId,
            'ip_address' => $ipAddress,
            'error_details' => $errorDetails,
            'error_type' => 'NETWORK_ERROR'
        ];
        parent::__construct($message, 2001, $context);
    }
}

// CAN Bus communication error exception
class CANBusException extends ElevatorException {
    public function __construct($canAddress, $componentType, $errorDetails = '') {
        $message = "CAN Bus error on address $canAddress ($componentType): $errorDetails";
        $context = [
            'can_address' => $canAddress,
            'component_type' => $componentType,
            'error_details' => $errorDetails,
            'error_type' => 'CAN_BUS_ERROR'
        ];
        parent::__construct($message, 3001, $context);
    }
}

// Database connection/operation exception
class ElevatorDatabaseException extends ElevatorException {
    public function __construct($operation, $table, $errorDetails = '') {
        $message = "Database error during $operation on table '$table': $errorDetails";
        $context = [
            'operation' => $operation,
            'table' => $table,
            'error_details' => $errorDetails,
            'error_type' => 'DATABASE_ERROR'
        ];
        parent::__construct($message, 4001, $context);
    }
}

// Invalid node configuration exception
class InvalidNodeConfigException extends ElevatorException {
    public function __construct($nodeId, $configField, $value, $reason = '') {
        $message = "Invalid configuration for node $nodeId: $configField = '$value'. $reason";
        $context = [
            'node_id' => $nodeId,
            'config_field' => $configField,
            'invalid_value' => $value,
            'reason' => $reason,
            'error_type' => 'INVALID_CONFIG'
        ];
        parent::__construct($message, 5001, $context);
    }
}

// Safety system exception
class ElevatorSafetyException extends ElevatorException {
    public function __construct($safetySystem, $reason, $nodeId = null) {
        $message = "Safety system alert: $safetySystem - $reason" . ($nodeId ? " (Node: $nodeId)" : "");
        $context = [
            'safety_system' => $safetySystem,
            'reason' => $reason,
            'node_id' => $nodeId,
            'error_type' => 'SAFETY_ALERT',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        parent::__construct($message, 6001, $context);
    }
}
?>
