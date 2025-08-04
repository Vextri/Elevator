<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit;
}

// Get login method for display
$login_method = $_SESSION['login_method'] ?? 'unknown';
$username = $_SESSION['username'] ?? 'Unknown User';
$user_id = $_SESSION['user_id'] ?? 'Unknown ID';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator Control Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        
        .login-info {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
            transition: transform 0.3s ease;
        }
        
        .login-info:hover {
            transform: translateY(-2px);
        }
        
        .login-info.card-login {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);
        }
        
        .login-info.manual-login {
            background: linear-gradient(45deg, #f39c12, #e67e22);
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.3);
        }
        
        .login-icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .nav-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .nav-section:hover {
            transform: translateY(-5px);
        }
        
        .nav-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.4em;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(45deg, #3498db, #2980b9);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            color: #2c3e50;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            transform: translateX(5px);
            border-left-color: #e74c3c;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .nav-link-icon {
            width: 20px;
            height: 20px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #3498db;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover .nav-link-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .primary-section .nav-link:hover {
            background: linear-gradient(45deg, #27ae60, #229954);
            border-left-color: #f1c40f;
        }
        
        .secondary-section .nav-link:hover {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            border-left-color: #f39c12;
        }
        
        .tools-section .nav-link:hover {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
            border-left-color: #e67e22;
        }
        
        .stats-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            margin-top: 25px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .logout-section {
            grid-column: 1 / -1;
            text-align: center;
            margin-top: 10px;
        }
        
        .logout-btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(231, 76, 60, 0.4);
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="dashboard-container fade-in">
    <!-- Header Section -->
    <div class="header">
        <h1>Elevator Control Dashboard</h1>
        <p class="subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        
        <!-- Login Method Information -->
        <div class="login-info <?php echo ($login_method === 'card_scan') ? 'card-login' : 'manual-login'; ?>">
            <div class="login-icon">
                <?php echo ($login_method === 'card_scan') ? 'C' : 'M'; ?>
            </div>
            <div>
                <strong>Login Method:</strong> 
                <?php if ($login_method === 'card_scan'): ?>
                    Card Scan Authentication
                <?php elseif ($login_method === 'manual_login'): ?>
                    Manual Login
                <?php else: ?>
                    <?php echo htmlspecialchars($login_method); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navigation Grid -->
    <div class="main-content">
        <!-- Primary Controls -->
        <div class="nav-section primary-section">
            <h2>
                <div class="section-icon">PC</div>
                Primary Controls
            </h2>
            <div class="nav-links">
                <a href="../html/test_elevator.html" class="nav-link">
                    <div class="nav-link-icon">E</div>
                    Elevator Control
                </a>
                <a href="admin_lockout.php" class="nav-link">
                    <div class="nav-link-icon">L</div>
                    Admin Lockout
                </a>
                <a href="setup_center.php" class="nav-link">
                    <div class="nav-link-icon">S</div>
                    System Setup
                </a>
            </div>
        </div>

        <!-- Management Tools -->
        <div class="nav-section secondary-section">
            <h2>
                <div class="section-icon">M</div>
                Management
            </h2>
            <div class="nav-links">
                <a href="members.php" class="nav-link">
                    <div class="nav-link-icon">D</div>
                    Database Management
                </a>
                <a href="members1.php" class="nav-link">
                    <div class="nav-link-icon">A</div>
                    Access Requests
                </a>
                <a href="user_requests.php" class="nav-link">
                    <div class="nav-link-icon">U</div>
                    User Approvals
                </a>
            </div>
        </div>

        <!-- Testing & Diagnostics -->
        <div class="nav-section tools-section">
            <h2>
                <div class="section-icon">T</div>
                Testing & Tools
            </h2>
            <div class="nav-links">
                <a href="exception_test.php" class="nav-link">
                    <div class="nav-link-icon">X</div>
                    Exception Testing
                </a>
                <a href="../diagnostics/diagnostics.php" class="nav-link">
                    <div class="nav-link-icon">H</div>
                    Height Diagnostics
                </a>
                <a href="../jsdoom-dosbox/index.html" target="_blank" class="nav-link">
                    <div class="nav-link-icon">G</div>
                    JS DOOM Game
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Bar -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-value" id="current-time">
                <?php echo date('H:i:s'); ?>
            </div>
            <div class="stat-label">Current Time</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">
                <?php 
                // Show login method status
                echo ($login_method === 'card_scan') ? 'CARD' : 'MANUAL';
                ?>
            </div>
            <div class="stat-label">Auth Method</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">ACTIVE</div>
            <div class="stat-label">System Status</div>
        </div>
        <div class="stat-item">
            <div class="stat-value" id="session-time">
                <?php 
                // Set login time if not already set
                if (!isset($_SESSION['login_time'])) {
                    $_SESSION['login_time'] = time();
                }
                
                // Show session duration
                if (isset($_SESSION['login_time']) && is_numeric($_SESSION['login_time'])) {
                    $duration = time() - $_SESSION['login_time'];
                    echo gmdate('H:i:s', $duration);
                } else {
                    echo '00:00:00';
                }
                ?>
            </div>
            <div class="stat-label">Session Time</div>
        </div>
        
        <!-- Logout Section -->
        <div class="logout-section">
            <a href="logout.php" class="logout-btn">
                <div class="nav-link-icon">EXIT</div>
                Secure Logout
            </a>
        </div>
    </div>
</div>

<script>
// Store the login time from PHP
const loginTime = <?php echo isset($_SESSION['login_time']) ? $_SESSION['login_time'] : 'null'; ?>;

// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
    // Update current time every second
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-GB', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('current-time').textContent = timeString;
    }
    
    // Update session time every second
    function updateSessionTime() {
        if (loginTime) {
            const now = Math.floor(Date.now() / 1000); // Current time in seconds
            const sessionDuration = now - loginTime;
            
            const hours = Math.floor(sessionDuration / 3600);
            const minutes = Math.floor((sessionDuration % 3600) / 60);
            const seconds = sessionDuration % 60;
            
            const timeString = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
                
            document.getElementById('session-time').textContent = timeString;
        }
    }
    
    // Update times immediately
    updateCurrentTime();
    updateSessionTime();
    
    // Set intervals to update every second
    setInterval(updateCurrentTime, 1000);
    setInterval(updateSessionTime, 1000);

    // Animate sections on load
    const sections = document.querySelectorAll('.nav-section, .stats-bar');
    sections.forEach((section, index) => {
        setTimeout(() => {
            section.style.animation = 'fadeIn 0.6s ease-out';
        }, index * 100);
    });

    // Add click effects to nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                pointer-events: none;
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: 20px;
                height: 20px;
                left: ${e.clientX - this.offsetLeft - 10}px;
                top: ${e.clientY - this.offsetTop - 10}px;
            `;
            
            this.style.position = 'relative';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>
