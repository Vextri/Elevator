<?php
// Enhanced request_access.php with better error handling and diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - try multiple configurations for portability
$credentials = [
    ['username' => 'Blaise', 'password' => 'Gitdead32!32'],
    ['username' => 'root', 'password' => ''],
    ['username' => 'root', 'password' => 'ese'],
    ['username' => 'ese', 'password' => 'ese']
];

$servername = "localhost";
$dbname = "access_requests1";

function testConnection($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn->connect_error ? false : $conn;
}

// Find working connection
$conn = null;
$working_creds = null;

foreach ($credentials as $cred) {
    $test_conn = testConnection($servername, $cred['username'], $cred['password'], $dbname);
    if ($test_conn) {
        $conn = $test_conn;
        $working_creds = $cred;
        break;
    }
}

if (!$conn) {
    showErrorPage("Connection failed: Unable to connect with any of the configured credentials. Please check your MySQL setup.");
    exit;
}

// Function to display themed pages
function showPage($type, $title, $message, $details = '') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../css/bootstrap.css" type="text/css" rel="stylesheet"/>
        <link href="../css/projectsVI.css" type="text/css" rel="stylesheet"/>
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            .result-container {
                max-width: 500px;
                margin: 3rem auto;
                padding: 2.5rem;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                border: 1px solid #e0e6ed;
                text-align: center;
            }
            
            .result-header {
                margin-bottom: 1.5rem;
                font-size: 2rem;
                font-weight: 300;
                padding-bottom: 1rem;
                border-bottom: 2px solid #dee2e6;
            }
            
            .result-icon {
                font-size: 4rem;
                margin-bottom: 1rem;
            }
            
            .result-message {
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
                line-height: 1.6;
            }
            
            .result-details {
                background: #f8f9fa;
                border-radius: 6px;
                padding: 1rem;
                margin-bottom: 1.5rem;
                font-size: 0.95rem;
                color: #6c757d;
            }
            
            .success {
                border-left: 4px solid #28a745;
            }
            
            .success .result-header {
                color: #28a745;
                border-bottom-color: #28a745;
            }
            
            .success .result-icon {
                color: #28a745;
            }
            
            .error {
                border-left: 4px solid #dc3545;
            }
            
            .error .result-header {
                color: #dc3545;
                border-bottom-color: #dc3545;
            }
            
            .error .result-icon {
                color: #dc3545;
            }
            
            .warning {
                border-left: 4px solid #ffc107;
            }
            
            .warning .result-header {
                color: #e0a800;
                border-bottom-color: #ffc107;
            }
            
            .warning .result-icon {
                color: #e0a800;
            }
            
            .action-buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-size: 1rem;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-block;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #0078d7, #005fa3);
                color: white;
            }
            
            .btn-primary:hover {
                background: linear-gradient(135deg, #005fa3, #004578);
                transform: translateY(-1px);
                color: white;
                text-decoration: none;
            }
            
            .btn-secondary {
                background: linear-gradient(135deg, #6c757d, #545b62);
                color: white;
            }
            
            .btn-secondary:hover {
                background: linear-gradient(135deg, #545b62, #383d41);
                transform: translateY(-1px);
                color: white;
                text-decoration: none;
            }
            
            .btn-success {
                background: linear-gradient(135deg, #28a745, #1e7e34);
                color: white;
            }
            
            .btn-success:hover {
                background: linear-gradient(135deg, #1e7e34, #155724);
                transform: translateY(-1px);
                color: white;
                text-decoration: none;
            }
        </style>
    </head>
    <body class="request-access-bg">
        <div class="result-container <?php echo $type; ?>">
            <div class="result-icon">
                <?php 
                switch($type) {
                    case 'success': echo '✓'; break;
                    case 'error': echo '✗'; break;
                    case 'warning': echo '⚠'; break;
                    default: echo 'ℹ'; break;
                }
                ?>
            </div>
            <h1 class="result-header"><?php echo htmlspecialchars($title); ?></h1>
            <div class="result-message"><?php echo $message; ?></div>
            <?php if ($details): ?>
                <div class="result-details"><?php echo $details; ?></div>
            <?php endif; ?>
            <div class="action-buttons">
                <a href="../html/website.html" class="btn btn-primary">Home</a>
                <a href="../html/request_access.html" class="btn btn-success">Submit Another Request</a>
                <a href="../html/login.html" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function showSuccessPage($requestId) {
    $message = "Your access request has been submitted successfully and is being reviewed by an administrator.";
    $details = "Request ID: " . htmlspecialchars($requestId) . "<br>You will be notified once your request has been processed.";
    showPage('success', 'Request Submitted', $message, $details);
}

function showErrorPage($message, $details = '') {
    showPage('error', 'Request Error', $message, $details);
}

function showWarningPage($message, $details = '') {
    showPage('warning', 'Request Warning', $message, $details);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input data
    if (empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['reason']) || empty($_POST['fullname'])) {
        showErrorPage("All fields are required!", "Please go back and complete all required fields in the form.");
    } else {
        // Get form data and validate
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $reason = trim($_POST['reason']);
        
        // Additional validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            showErrorPage("Invalid email format!", "Please provide a valid email address.");
        } elseif (strlen($username) < 3) {
            showErrorPage("Username too short!", "Username must be at least 3 characters long.");
        } elseif (strlen($password) < 6) {
            showErrorPage("Password too short!", "Password must be at least 6 characters long.");
        } elseif (strlen($fullname) < 2) {
            showErrorPage("Name too short!", "Full name must be at least 2 characters long.");
        } else {
            // Escape strings for SQL
            $fullname = $conn->real_escape_string($fullname);
            $email = $conn->real_escape_string($email);
            $username = $conn->real_escape_string($username);
            $reason = $conn->real_escape_string($reason);
            
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Check for existing username or email first
            $check_sql = "SELECT id FROM requests WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_sql);
            
            if ($check_stmt) {
                $check_stmt->bind_param("ss", $username, $email);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    showWarningPage("Username or email already exists!", "This username or email address is already in our system. Please try a different username or contact an administrator if you need help.");
                } else {
                    // Proceed with insertion - explicitly exclude id to ensure AUTO_INCREMENT works
                    $sql = "INSERT INTO requests (fullname, email, username, password, reason, created_at, approved) VALUES (?, ?, ?, ?, ?, NOW(), FALSE)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("sssss", $fullname, $email, $username, $hashedPassword, $reason);
                        
                        if ($stmt->execute()) {
                            $new_id = $conn->insert_id;
                            showSuccessPage($new_id);
                        } else {
                            showErrorPage("Database error occurred!", "Error: " . $stmt->error . "<br>Please try again or contact support if the problem persists.");
                        }
                        $stmt->close();
                    } else {
                        showErrorPage("System error occurred!", "Unable to prepare database statement. Please try again later.");
                    }
                }
                $check_stmt->close();
            } else {
                showErrorPage("System error occurred!", "Unable to check existing records. Please try again later.");
            }
        }
    }
    
    // Close the connection
    $conn->close();
} else {
    // If accessed directly without POST, redirect to the form
    header("Location: ../html/request_access.html");
    exit;
}
?>