<?php
// Remove PHP database connection - let JavaScript handle everything via API
// This makes index.php work exactly like test_elevator.html
$curFlr = 1; // Default floor, will be updated by JavaScript
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.css" type="text/css" rel="stylesheet"/>
    <link href="../css/projectsVI.css" type="text/css" rel="stylesheet"/>
    <link href="../css/elevator.css" type="text/css" rel="stylesheet"/>
    <title>Elevator Controller GUI</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #888888;
            font-family: Arial, sans-serif;
        }
        
        header {
            background: #000000;
            padding: 20px;
            text-align: center;
        }
        
        header h1 {
            margin: 0;
            color: #FFD700;
            font-size: 2.5rem;
            font-style: italic;
        }
        
        .main-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 3rem;
            padding: 20px;
            min-height: 60vh;
        }
        
        .elevator-building {
            background: #2c3e50;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .building-title {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .elevator-shaft {
            width: 120px;
            height: 360px;
            background: linear-gradient(to bottom, #34495e, #2c3e50);
            border: 3px solid #1abc9c;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
        }
        
        .floor-markers {
            position: absolute;
            left: -50px;
            top: 0;
            height: 100%;
            width: 40px;
        }
        
        .floor-marker {
            position: absolute;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #34495e;
            border-radius: 50%;
            border: 2px solid #1abc9c;
        }
        
        .floor-marker:nth-child(1) { top: 30px; }    /* Floor 3 */
        .floor-marker:nth-child(2) { top: 150px; }   /* Floor 2 */
        .floor-marker:nth-child(3) { bottom: 30px; } /* Floor 1 */
        
        .elevator-car {
            position: absolute;
            width: 100px;
            height: 80px;
            background: linear-gradient(45deg, #3498db, #2980b9);
            border-radius: 8px;
            left: 10px;
            bottom: 20px; /* Floor 1 position */
            transition: bottom 2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
            border: 2px solid #1abc9c;
        }
        
        .elevator-car.floor-1 { bottom: 20px; }
        .elevator-car.floor-2 { bottom: 140px; }
        .elevator-car.floor-3 { bottom: 260px; }
        
        .elevator-car.moving {
            box-shadow: 0 5px 25px rgba(26, 188, 156, 0.6);
        }
        
        .car-details {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .car-doors {
            position: absolute;
            top: 0;
            width: 100%;
            height: 100%;
        }
        
        .car-door {
            width: 45px;
            height: 60px;
            background: #2c3e50;
            position: absolute;
            top: 10px;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        
        .car-door.left { left: 5px; }
        .car-door.right { right: 5px; }
        
        .car-door.open { width: 15px; }
        
        .car-light {
            width: 8px;
            height: 8px;
            background: #f39c12;
            border-radius: 50%;
            position: absolute;
            top: 5px;
            right: 5px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .connection-status {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            padding: 8px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .connection-status.connected {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .connection-status.disconnected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .lockout-status {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .lockout-status.locked-out {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
            font-weight: bold;
        }
        
        .lockout-status.operational {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .back-to-dashboard {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #6c757d;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            z-index: 1000;
        }
        
        .back-to-dashboard:hover {
            background: #545b62;
            color: white;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-indicator.connected {
            background-color: #28a745;
            animation: blink 2s infinite;
        }
        
        .status-indicator.disconnected {
            background-color: #dc3545;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        
        .door-controls {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .door-btn {
            width: 50px;
            height: 40px;
            background: linear-gradient(145deg, #95a5a6, #7f8c8d);
            border: 2px solid #5d6d7e;
            border-radius: 8px;
            font-size: 1rem;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .door-btn:hover:not(:disabled) {
            background: linear-gradient(145deg, #bdc3c7, #95a5a6);
            transform: translateY(-1px);
        }
        
        .door-btn.selected {
            background: linear-gradient(145deg, #27ae60, #229954);
            color: white;
            border-color: #1e8449;
        }
        
        /* Original Control Panel Styling */
        .control-panel {
            background: #E8E8E8;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        .status-message {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255,255,255,0.9);
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            max-width: 300px;
        }
        
        .floor-display {
            background: #333333;
            color: #FF0000;
            font-family: 'Courier New', monospace;
            font-size: 2rem;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            min-width: 80px;
            text-align: center;
            letter-spacing: 3px;
        }
        
        .arrow-btn {
            width: 80px;
            height: 60px;
            background: linear-gradient(145deg, #D4AF37, #B8860B);
            border: 2px solid #8B7355;
            border-radius: 10px;
            font-size: 2rem;
            color: #5D4E37;
            cursor: pointer;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.3);
            transition: all 0.2s;
        }
        
        .arrow-btn:hover:not(:disabled) {
            background: linear-gradient(145deg, #FFD700, #DAA520);
            transform: translateY(-2px);
        }
        
        .arrow-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }
        
        .arrow-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .floor-buttons {
            display: flex;
            gap: 15px;
        }
        
        .floor-btn {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(145deg, #C0C0C0, #A0A0A0);
            border: 3px solid #808080;
            font-size: 1.8rem;
            font-weight: bold;
            color: #333333;
            cursor: pointer;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.3);
            transition: all 0.2s;
        }
        
        .floor-btn:hover:not(:disabled) {
            background: linear-gradient(145deg, #E0E0E0, #C0C0C0);
            transform: translateY(-2px);
        }
        
        .floor-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }
        
        .floor-btn.active {
            background: linear-gradient(145deg, #4169E1, #0000CD);
            color: white;
            border-color: #000080;
        }
        
        .floor-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header>
        <h1>Elevator Controls</h1>
    </header>

    <div class="main-container">
        <!-- Visual Elevator Building -->
        <div class="elevator-building">
            <div class="building-title">Elevator Test System</div>
            <div class="elevator-shaft">
                <!-- Floor Markers -->
                <div class="floor-markers">
                    <div class="floor-marker">3</div>
                    <div class="floor-marker">2</div>
                    <div class="floor-marker">1</div>
                </div>
                
                <!-- Elevator Car -->
                <div id="elevator-car" class="elevator-car floor-1">
                    <div class="car-doors">
                        <div id="door-left" class="car-door left"></div>
                        <div id="door-right" class="car-door right"></div>
                    </div>
                    <div class="car-light"></div>
                </div>
            </div>
        </div>
        
        <!-- Original Control Panel -->
        <div class="control-panel">
            <!-- Connection Status -->
            <div id="connectionStatus" class="connection-status disconnected">
                <div class="status-indicator disconnected"></div>
                Checking connection...
            </div>
            
            <!-- Lockout Status -->
            <div id="lockoutStatus" class="lockout-status operational" style="display: none;">
                System Operational
            </div>
            
            <!-- Floor Display -->
            <div class="floor-display">
                F <span id="current-floor"><?php echo $curFlr; ?></span>
            </div>
            
            <!-- Up Arrow -->
            <button type="button" id="up-btn" class="arrow-btn" onclick="moveElevator('up')" title="Up">↑</button>
            
            <!-- Floor Buttons -->
            <div class="floor-buttons">
                <button type="button" class="floor-btn" onclick="moveElevator(1)">1</button>
                <button type="button" class="floor-btn" onclick="moveElevator(2)">2</button>
                <button type="button" class="floor-btn" onclick="moveElevator(3)">3</button>
            </div>
            
            <!-- Down Arrow -->
            <button type="button" id="down-btn" class="arrow-btn" onclick="moveElevator('down')" title="Down">↓</button>
            
            <!-- Door Controls -->
            <div class="door-controls">
                <button type="button" class="door-btn" id="open-btn" onclick="operateDoor('open')" title="Open Doors">◄►</button>
                <button type="button" class="door-btn" id="close-btn" onclick="operateDoor('close')" title="Close Doors">►◄</button>
            </div>
        </div>
    </div>

    <div class="status-message" id="status-message"></div>

    <!-- Navigation -->
    <div style="text-align: center; margin: 20px 0; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
        <a href="dashboard.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
            ← Back to Dashboard
        </a>
    </div>

    <script>
        let currentFloor = <?php echo $curFlr; ?>;
        let pollInterval;
        let isLockedOut = false;
        
        // Start polling when page loads
        window.onload = function() {
            updateFloorDisplay();
            animateElevatorToFloor(currentFloor);
            startPolling();
            
            // Get initial status including connection
            pollFloorStatus();
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
            fetch('test_elevator_api.php')
                .then(response => response.json())
                .then(data => {
                    // Update connection status
                    updateConnectionStatus(data.database_connected || false);
                    
                    // Update lockout status
                    updateLockoutStatus(data.is_locked_out || false, data.lockout_reason, data.locked_by);
                    
                    if (data.success && data.current_floor !== currentFloor) {
                        currentFloor = data.current_floor;
                        updateFloorDisplay();
                        animateElevatorToFloor(currentFloor);
                    }
                })
                .catch(error => {
                    console.error('Polling error:', error);
                    updateConnectionStatus(false);
                });
        }
        
        function moveElevator(direction) {
            // Check if system is locked out
            if (isLockedOut) {
                const statusEl = document.getElementById('status-message');
                statusEl.innerHTML = 'Elevator operations are locked out for safety';
                statusEl.style.display = 'block';
                return;
            }
            
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
            const statusEl = document.getElementById('status-message');
            statusEl.innerHTML = `Moving to floor ${targetFloor}...`;
            statusEl.style.display = 'block';
            
            // Disable all buttons during movement
            setButtonsEnabled(false);
            
            fetch('test_elevator_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=move_floor&newfloor=${targetFloor}`
            })
            .then(response => response.json())
            .then(data => {
                const statusEl = document.getElementById('status-message');
                // Update connection status
                updateConnectionStatus(data.database_connected || false);
                
                // Update lockout status
                updateLockoutStatus(data.is_locked_out || false, data.lockout_reason, data.locked_by);
                
                if (data.success) {
                    currentFloor = data.current_floor;
                    updateFloorDisplay();
                    animateElevatorToFloor(currentFloor);
                    statusEl.innerHTML = data.message;
                    
                    // Clear status message after 3 seconds
                    setTimeout(() => {
                        statusEl.innerHTML = '';
                        statusEl.style.display = 'none';
                    }, 3000);
                } else {
                    statusEl.innerHTML = 'Error: ' + data.message;
                }
                
                // Re-enable buttons (but check lockout status)
                setButtonsEnabled(!isLockedOut);
            })
            .catch(error => {
                console.error('Error:', error);
                updateConnectionStatus(false);
                const statusEl = document.getElementById('status-message');
                statusEl.innerHTML = 'Network error occurred';
                setButtonsEnabled(!isLockedOut);
            });
        }
        
        function updateLockoutStatus(lockedOut, reason, lockedBy) {
            isLockedOut = lockedOut;
            const lockoutDiv = document.getElementById('lockoutStatus');
            
            lockoutDiv.style.display = 'block';
            
            if (lockedOut) {
                lockoutDiv.className = 'lockout-status locked-out';
                lockoutDiv.innerHTML = `🔒 ELEVATOR LOCKED OUT<br><small>Reason: ${reason || 'Safety lockout'}<br>By: ${lockedBy || 'Administrator'}</small>`;
                
                // Disable all elevator controls
                setButtonsEnabled(false);
            } else {
                lockoutDiv.className = 'lockout-status operational';
                lockoutDiv.innerHTML = '✅ System Operational';
                
                // Re-enable controls if not locked out
                setButtonsEnabled(true);
            }
        }
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
        
        function animateElevatorToFloor(floor) {
            const elevatorCar = document.getElementById('elevator-car');
            // Remove existing floor classes
            elevatorCar.classList.remove('floor-1', 'floor-2', 'floor-3');
            // Add new floor class for smooth CSS transition
            elevatorCar.classList.add(`floor-${floor}`);
            
            // Also add moving class for additional visual feedback
            elevatorCar.classList.add('moving');
            setTimeout(() => {
                elevatorCar.classList.remove('moving');
            }, 2000);
        }
        
        function updateConnectionStatus(connected) {
            const statusDiv = document.getElementById('connectionStatus');
            const indicator = statusDiv.querySelector('.status-indicator');
            
            if (connected) {
                statusDiv.className = 'connection-status connected';
                indicator.className = 'status-indicator connected';
                statusDiv.innerHTML = '<div class="status-indicator connected"></div>Database Connected';
            } else {
                statusDiv.className = 'connection-status disconnected';
                indicator.className = 'status-indicator disconnected';
                statusDiv.innerHTML = '<div class="status-indicator disconnected"></div>Database Disconnected';
            }
        }
        
        function operateDoor(action) {
            const leftDoor = document.getElementById('door-left');
            const rightDoor = document.getElementById('door-right');
            const btn = document.getElementById(action + '-btn');
            const otherBtn = document.getElementById(action === 'open' ? 'close-btn' : 'open-btn');
            
            // Highlight the clicked button
            btn.classList.add('selected');
            otherBtn.classList.remove('selected');
            
            if (action === 'open') {
                leftDoor.classList.add('open');
                rightDoor.classList.add('open');
            } else {
                leftDoor.classList.remove('open');
                rightDoor.classList.remove('open');
            }
            
            // Remove button highlight after 3 seconds
            setTimeout(() => {
                btn.classList.remove('selected');
            }, 3000);
        }
        
        function setButtonsEnabled(enabled) {
            const allButtons = document.querySelectorAll('button');
            allButtons.forEach(btn => {
                btn.disabled = !enabled;
            });
            
            // Re-apply floor-specific disabled states if enabling and not locked out
            if (enabled && !isLockedOut) {
                updateFloorDisplay();
            }
        }
        
        // Stop polling when page unloads
        window.addEventListener('beforeunload', stopPolling);
    </script>
</body>
</html>


