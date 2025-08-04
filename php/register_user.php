<?php
// register_user.php - Register new user with card data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1"); //This is a password I made up for the sake of the project it is not confidential

    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $student_card = isset($_POST['student_card']) ? trim($_POST['student_card']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $approved = isset($_POST['approved']) ? intval($_POST['approved']) : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        if ($action !== 'register_user') {
            throw new Exception('Invalid action');
        }
        
        // Validate required fields
        if (empty($id) || empty($student_card) || empty($email) || empty($username) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        // Check if ID already exists
        $check_id_sql = "SELECT id FROM requests WHERE id = ?";
        $check_id_stmt = $mysqli->prepare($check_id_sql);
        $check_id_stmt->bind_param("s", $id);
        $check_id_stmt->execute();
        $id_result = $check_id_stmt->get_result();
        
        if ($id_result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID number already exists in database'
            ]);
            $check_id_stmt->close();
            $mysqli->close();
            exit;
        }
        $check_id_stmt->close();
        
        // Check if card already exists
        $check_card_sql = "SELECT student_card FROM requests WHERE student_card = ?";
        $check_card_stmt = $mysqli->prepare($check_card_sql);
        $check_card_stmt->bind_param("s", $student_card);
        $check_card_stmt->execute();
        $card_result = $check_card_stmt->get_result();
        
        if ($card_result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Student card already registered in database'
            ]);
            $check_card_stmt->close();
            $mysqli->close();
            exit;
        }
        $check_card_stmt->close();
        
        // Check if username already exists
        $check_username_sql = "SELECT username FROM requests WHERE username = ?";
        $check_username_stmt = $mysqli->prepare($check_username_sql);
        $check_username_stmt->bind_param("s", $username);
        $check_username_stmt->execute();
        $username_result = $check_username_stmt->get_result();
        
        if ($username_result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Username already exists in database'
            ]);
            $check_username_stmt->close();
            $mysqli->close();
            exit;
        }
        $check_username_stmt->close();
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Get current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Insert new user
        $insert_sql = "INSERT INTO requests (id, student_card, email, username, password, reason, created_at, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $mysqli->prepare($insert_sql);
        $insert_stmt->bind_param("sssssssi", $id, $student_card, $email, $username, $hashed_password, $reason, $created_at, $approved);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'id' => $id,
                'username' => $username,
                'email' => $email,
                'student_card' => $student_card,
                'approved' => $approved,
                'created_at' => $created_at
            ]);
        } else {
            throw new Exception('Failed to insert user: ' . $insert_stmt->error);
        }
        
        $insert_stmt->close();
    } else {
        throw new Exception('Only POST requests allowed');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$mysqli->close();
?>
