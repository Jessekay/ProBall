<?php
// Disable error display in production (enable during development)
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

// Start session if needed for auto-login
session_start();

header('Content-Type: application/json');

// Database connection with error handling
try {
    $conn = pg_connect("host=localhost dbname=proball user=postgres password=12092001");
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode([
        'success' => false,
        'message' => 'Only POST method allowed'
    ]));
}

try {
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input");
    }

    // Extract and sanitize data
    $first_name = filter_var($input['first_name'] ?? '', FILTER_SANITIZE_STRING);
    $last_name = filter_var($input['last_name'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    // Generate username
    $username = strtolower("{$first_name}.{$last_name}");

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        throw new Exception("All fields are required");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters");
    }

    // Check if email exists
    $emailCheck = pg_query_params($conn, 
        "SELECT user_id FROM users WHERE email = $1", 
        [$email]
    );
    
    if (pg_num_rows($emailCheck) > 0) {
        throw new Exception("Email already registered");
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    if (!$password_hash) {
        throw new Exception("Password hashing failed");
    }

    // Insert user
    $result = pg_query_params($conn,
        "INSERT INTO users (username, email, password, first_name, last_name) 
         VALUES ($1, $2, $3, $4, $5) RETURNING user_id",
        [$username, $email, $password_hash, $first_name, $last_name]
    );

    if (!$result) {
        throw new Exception("Registration failed: " . pg_last_error($conn));
    }

    // Get new user ID
    $user = pg_fetch_assoc($result);
    
    // Set session for auto-login
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Clear output buffer before sending JSON
    ob_end_clean();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful!',
        'redirect' => 'products.html',
        'user_id' => $user['user_id']
    ]);
    exit;

} catch (Exception $e) {
    // Clean any output before error response
    ob_end_clean();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

// Clean up
pg_close($conn);
?>