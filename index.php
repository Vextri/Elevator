<?php
// Remove the POST handling - we'll use AJAX instead
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

// Get initial floor for page load
$curFlr = get_currentFloor();
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.css" type="text/css" rel="stylesheet"/>
    <link href="css/projectsVI.css" type="text/css" rel="stylesheet"/>
    <link href="css/elevator.css" type="text/css" rel="stylesheet"/>
    <title>Elevator Controller GUI</title>
    <style>
        .elevator-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
        }
        .floor-buttons, .arrow-buttons {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        .floor-btn, .arrow-btn {
            width: 60px;
            height: 60px;
            margin: 0 10px;
            font-size: 2rem;
            border-radius: 50%;
            border: 2px solid #333;
            background: #f8f9fa;
            cursor: pointer;
            transition: background 0.2s;
        }
        .floor-btn.active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .arrow-btn {
            font-size: 2.5rem;
            background: #e2e6ea;
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align:center;">Elevator Controls</h1>
    </header>

    <div class="elevator-panel">
        <h2>Current floor: <span id="current-floor" style="color:#007bff;"><?php echo $curFlr; ?></span></h2>
        <div id="elevator-controls">
            <div class="arrow-buttons">
                <!-- UP arrow: should INCREASE floor -->
                <button type="button" id="up-btn" class="arrow-btn" onclick="moveElevator('up')" title="Up">&#8593;</button>
            </div>
            <div class="floor-buttons">
                <button type="button" class="floor-btn" onclick="moveElevator(1)">1</button>
                <button type="button" class="floor-btn" onclick="moveElevator(2)">2</button>
                <button type="button" class="floor-btn" onclick="moveElevator(3)">3</button>
            </div>
            <div class="arrow-buttons">
                <!-- DOWN arrow: should DECREASE floor -->
                <button type="button" id="down-btn" class="arrow-btn" onclick="moveElevator('down')" title="Down">&#8595;</button>
            </div>
        </div>
        <div id="status-message" style="margin-top: 1rem; color: #666;"></div>
    </div>

    <script>
        let currentFloor = <?php echo $curFlr; ?>;
        let pollInterval;
        
        // Start polling when page loads
        window.onload = function() {
            updateFloorDisplay();
            startPolling();
        };
        
        function startPolling() {
            pollInterval = setInterval(pollFloorStatus, 2000); // Poll every 2 seconds
        }
        
        function stopPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
            }
        }
        
        function pollFloorStatus() {
            fetch('elevator_api.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.current_floor !== currentFloor) {
                        currentFloor = data.current_floor;
                        updateFloorDisplay();
                    }
                })
                .catch(error => {
                    console.error('Polling error:', error);
                });
        }
        
        function moveElevator(direction) {
            let targetFloor;
            
            if (direction === 'up') {
                targetFloor = Math.min(3, currentFloor + 1);
            } else if (direction === 'down') {
                targetFloor = Math.max(1, currentFloor - 1);
            } else {
                targetFloor = parseInt(direction); // Direct floor number
            }
            
            if (targetFloor === currentFloor) {
                return; // Already on target floor
            }
            
            // Show loading state
            document.getElementById('status-message').innerHTML = `Moving to floor ${targetFloor}...`;
            
            // Disable all buttons during movement
            setButtonsEnabled(false);
            
            fetch('elevator_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=move_floor&newfloor=${targetFloor}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentFloor = data.current_floor;
                    updateFloorDisplay();
                    document.getElementById('status-message').innerHTML = data.message;
                    
                    // Clear status message after 3 seconds
                    setTimeout(() => {
                        document.getElementById('status-message').innerHTML = '';
                    }, 3000);
                } else {
                    document.getElementById('status-message').innerHTML = 'Error: ' + data.message;
                }
                
                // Re-enable buttons
                setButtonsEnabled(true);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('status-message').innerHTML = 'Network error occurred';
                setButtonsEnabled(true);
            });
        }
        
        function updateFloorDisplay() {
            document.getElementById('current-floor').textContent = currentFloor;
            
            // Update floor button states
            const floorButtons = document.querySelectorAll('.floor-btn');
            floorButtons.forEach((btn, index) => {
                if (index + 1 === currentFloor) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            // Update arrow button states
            const upBtn = document.getElementById('up-btn');
            const downBtn = document.getElementById('down-btn');
            
            upBtn.disabled = (currentFloor >= 3);
            downBtn.disabled = (currentFloor <= 1);
        }
        
        function setButtonsEnabled(enabled) {
            const allButtons = document.querySelectorAll('button');
            allButtons.forEach(btn => {
                btn.disabled = !enabled;
            });
            
            // Re-apply floor-specific disabled states if enabling
            if (enabled) {
                updateFloorDisplay();
            }
        }
        
        // Stop polling when page unloads
        window.addEventListener('beforeunload', stopPolling);
    </script>
</body>
</html>
 
 
