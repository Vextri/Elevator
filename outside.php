
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
    <title>Outside Elevator</title>
    <link href="css/projectsVI.css" type="text/css" rel="stylesheet"/>
    <style>
        .elevator-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2rem;
        }
        .panel-vertical {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 320px; /* Adjust as needed */
            justify-content: center;
            position: relative;
        }
        .floor-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .door-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 0;
        }
        .spacer {
            height: 70px; /* Adjust this value to align with the "2" button */
        }
        .floor-btn {
            width: 60px;
            height: 60px;
            font-size: 2rem;
            border-radius: 50%;
            border: 2px solid #333;
            background: #f8f9fa;
            cursor: pointer;
            margin: 0.5rem 0;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .floor-btn.active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .door-btn.selected {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }
    </style>
</head>
<body>
 <div class="elevator-panel">
        <h2>Current floor: <span id="current-floor" style="color:#007bff;"><?php echo $curFlr; ?></span></h2>
        <div id="elevator-controls">
            <div class="panel-vertical">
                <div class="floor-buttons">
                    <button type="button" class="floor-btn" onclick="callElevator(3)">3</button>
                    <button type="button" class="floor-btn" onclick="callElevator(2)">2</button>
                    <button type="button" class="floor-btn" onclick="callElevator(1)">1</button>
                </div>
                <div class="spacer"></div>
                <div class="door-buttons">
                    <button type="button" class="floor-btn door-btn" id="open-btn" onclick="operateDoor('open')" title="Open Door">&lt;&gt;</button>
                    <button type="button" class="floor-btn door-btn" id="close-btn" onclick="operateDoor('close')" title="Close Door">&gt;&lt;</button>
                </div>
            </div>
        </div>
        <div id="status-message" style="margin-top: 1rem; color: #666; text-align: center;"></div>
    </div>

    <script>
        let currentFloor = <?php echo $curFlr; ?>;
        let pollInterval;
        let doorBtnTimeout = null;
        
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
        
        function callElevator(targetFloor) {
            if (targetFloor === currentFloor) {
                document.getElementById('status-message').innerHTML = `Elevator is already on floor ${targetFloor}`;
                setTimeout(() => {
                    document.getElementById('status-message').innerHTML = '';
                }, 3000);
                return;
            }
            
            // Show loading state
            document.getElementById('status-message').innerHTML = `Calling elevator to floor ${targetFloor}...`;
            
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
                    document.getElementById('status-message').innerHTML = `Elevator arrived at floor ${data.current_floor}`;
                    
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
        
        function operateDoor(action) {
            const btn = action === 'open' ? document.getElementById('open-btn') : document.getElementById('close-btn');
            const otherBtn = action === 'open' ? document.getElementById('close-btn') : document.getElementById('open-btn');
            
            // Highlight the clicked door button
            highlightDoor(btn, otherBtn);
            
            // Show status message
            const actionText = action === 'open' ? 'Opening' : 'Closing';
            document.getElementById('status-message').innerHTML = `${actionText} doors...`;
            
            // Clear status message after 2 seconds
            setTimeout(() => {
                document.getElementById('status-message').innerHTML = '';
            }, 2000);
        }
        
        function highlightDoor(btnToHighlight, btnToUnhighlight) {
            btnToHighlight.classList.add('selected');
            btnToUnhighlight.classList.remove('selected');
            if (doorBtnTimeout) clearTimeout(doorBtnTimeout);
            doorBtnTimeout = setTimeout(() => {
                btnToHighlight.classList.remove('selected');
            }, 5000);
        }
        
        function updateFloorDisplay() {
            document.getElementById('current-floor').textContent = currentFloor;
            
            // Update floor button states
            const floorButtons = document.querySelectorAll('.floor-btn:not(.door-btn)');
            floorButtons.forEach((btn) => {
                const floor = parseInt(btn.textContent);
                if (floor === currentFloor) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
        
        function setButtonsEnabled(enabled) {
            const floorButtons = document.querySelectorAll('.floor-btn:not(.door-btn)');
            floorButtons.forEach(btn => {
                btn.disabled = !enabled;
            });
            
            // Door buttons are always enabled
        }
        
        // Stop polling when page unloads
        window.addEventListener('beforeunload', stopPolling);
    </script>
</body>
</html>