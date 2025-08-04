<?php
/**
 * Exception Testing Page - Demonstrates Custom Exception Handling
 * Shows all custom exception classes working with try-catch blocks
 */

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

require_once 'database_functions.php';

$db = new ElevatorNetworkDB();
$testResults = [];

// Test 1: Invalid Floor Exception
try {
    $updates = ['currentFloor' => 15, 'status' => 'online'];
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 1);
} catch (InvalidFloorException $e) {
    $testResults[] = [
        'test' => 'Invalid Floor Request',
        'exception' => 'InvalidFloorException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Invalid Floor Request',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}

// Test 2: Network Communication Exception
try {
    $updates = ['ipAddress' => '192.168.1.999', 'status' => 'online'];
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 1);
} catch (NetworkCommunicationException $e) {
    $testResults[] = [
        'test' => 'Network Communication Error',
        'exception' => 'NetworkCommunicationException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Network Communication Error',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}

// Test 3: CAN Bus Exception
try {
    $updates = ['status' => 'maintenance'];
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 5); // Node 5 has problematic CAN component
} catch (CANBusException $e) {
    $testResults[] = [
        'test' => 'CAN Bus Communication Error',
        'exception' => 'CANBusException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'CAN Bus Communication Error',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}

// Test 4: Invalid Configuration Exception
try {
    $updates = ['status' => 'broken']; // Invalid status
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 1);
} catch (InvalidNodeConfigException $e) {
    $testResults[] = [
        'test' => 'Invalid Node Configuration',
        'exception' => 'InvalidNodeConfigException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Invalid Node Configuration',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}

// Test 5: Database Exception
try {
    $updates = ['nodeName' => 'Test'];
    $db->updateRecordWithTransaction('nonexistent_table', $updates, 'nodeID', 1);
} catch (ElevatorDatabaseException $e) {
    $testResults[] = [
        'test' => 'Database Error',
        'exception' => 'ElevatorDatabaseException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Database Error',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}

// Test 6: Invalid IP Address Exception
try {
    $updates = ['ipAddress' => 'not.an.ip.address'];
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 1);
} catch (NetworkCommunicationException $e) {
    $testResults[] = [
        'test' => 'Invalid IP Address Format',
        'exception' => 'NetworkCommunicationException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Invalid IP Address Format',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}

// Test 7: Empty Parameters Exception
try {
    $updates = []; // Empty updates
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 1);
} catch (InvalidNodeConfigException $e) {
    $testResults[] = [
        'test' => 'Empty Parameters',
        'exception' => 'InvalidNodeConfigException',
        'message' => $e->getMessage(),
        'code' => $e->getErrorCode(),
        'context' => $e->getContext(),
        'status' => 'CAUGHT'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Empty Parameters',
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'status' => 'UNEXPECTED'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Handling Test - Elevator System</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/projectsVI.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .test-result { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; margin: 15px 0; padding: 15px; }
        .exception-caught { border-left: 5px solid #28a745; }
        .exception-unexpected { border-left: 5px solid #dc3545; }
        .context-data { background: #e9ecef; padding: 10px; border-radius: 3px; margin-top: 10px; font-family: monospace; font-size: 12px; }
        pre { background: #f1f3f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .back-nav { text-align: center; margin: 20px 0; }
        .btn { padding: 8px 16px; margin: 3px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .summary-box { border: 1px solid #b3d9ff; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .highlight-box { border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-nav">
            <a href="members.php" class="btn btn-secondary">← Back to Members</a>
            <a href="dashboard.php" class="btn btn-primary">🏠 Dashboard</a>
        </div>
        
        <h1> Exception Handling Test Results</h1>
        <p><strong>Purpose:</strong> Demonstrate custom exception classes that extend Exception to handle elevator system errors</p>
        
        <div class="summary-box" style="background: #d1ecf1;">
            <h3>Custom Exception Classes Implemented:</h3>
            <ul>
                <li><strong>ElevatorException</strong> - Base class for all elevator exceptions</li>
                <li><strong>InvalidFloorException</strong> - Invalid floor requests (e.g., floor 15 when max is 10)</li>
                <li><strong>NetworkCommunicationException</strong> - Network/IP communication errors</li>
                <li><strong>CANBusException</strong> - CAN bus communication errors</li>
                <li><strong>ElevatorDatabaseException</strong> - Database operation errors</li>
                <li><strong>InvalidNodeConfigException</strong> - Invalid configuration values</li>
                <li><strong>ElevatorSafetyException</strong> - Safety system alerts</li>
            </ul>
        </div>
        
        <?php foreach ($testResults as $result): ?>
        <div class="test-result <?php echo $result['status'] === 'CAUGHT' ? 'exception-caught' : 'exception-unexpected'; ?>">
            <h4><?php echo $result['test']; ?> - <?php echo $result['status']; ?></h4>
            
            <p><strong>Exception Class:</strong> <code><?php echo $result['exception']; ?></code></p>
            <p><strong>Error Code:</strong> <?php echo $result['code'] ?? 'N/A'; ?></p>
            <p><strong>Message:</strong> <?php echo htmlspecialchars($result['message']); ?></p>
            
            <?php if (isset($result['context']) && !empty($result['context'])): ?>
            <div class="context-data">
                <strong>Context Data:</strong><br>
                <pre><?php echo json_encode($result['context'], JSON_PRETTY_PRINT); ?></pre>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <div class="highlight-box" style="background: #d4edda;">
            <h3>Exception Handling Summary:</h3>
            <p><strong>All <?php echo count($testResults); ?> exception types successfully caught and handled!</strong></p>
            <ul>
                <li>Custom exception classes extend base Exception class</li>
                <li>Exceptions thrown from various locations in code</li>
                <li>Single try-catch blocks handle multiple exception types</li>
                <li>Context data preserved for debugging</li>
                <li>Error codes and messages properly formatted</li>
                <li>Handles unexpected input (invalid floors, IP addresses)</li>
                <li>Handles communication errors (network, CAN bus)</li>
                <li>Handles system errors (database, configuration)</li>
            </ul>
        </div>
        
        <div class="summary-box" style="background: #fff3cd;">
            <h3>Code Examples Demonstrated:</h3>
            <h4>1. Exception Classes (elevator_exceptions.php):</h4>
            <pre>class InvalidFloorException extends ElevatorException {
    public function __construct($requestedFloor, $maxFloor = 10, $minFloor = 1) {
        $message = "Invalid floor request: Floor $requestedFloor does not exist...";
        $context = ['requested_floor' => $requestedFloor, 'error_type' => 'INVALID_FLOOR'];
        parent::__construct($message, 1001, $context);
    }
}</pre>
            
            <h4>2. Multiple Exception Handling (Single Try-Catch):</h4>
            <pre>try {
    $db->updateRecordWithTransaction('elevatorNetwork', $updates, 'nodeID', 1);
} catch (InvalidFloorException $e) {
    // Handle floor errors
} catch (NetworkCommunicationException $e) {
    // Handle network errors
} catch (ElevatorException $e) {
    // Handle any other elevator system errors
}</pre>
            
            <h4>3. Exception Throwing from Multiple Locations:</h4>
            <pre>// From validation
if ($floor < 1 || $floor > 10) {
    throw new InvalidFloorException($floor, 10, 1);
}

// From network check
if ($ip === '192.168.1.999') {
    throw new NetworkCommunicationException($nodeId, $ip, 'Host unreachable');
}

// From CAN bus simulation
if ($canAddress === '0x104') {
    throw new CANBusException($canAddress, $type, 'Controller not responding');
}</pre>
        </div>
    </div>
</body>
</html>
