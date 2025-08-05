<?php
header('Content-Type: application/json');

// Disable error display in production (enable during development)
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

// Start session if needed
session_start();

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

// Handle GET request (fetch profile data)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User not logged in");
        }

        $user_id = $_SESSION['user_id'];
        
        $query = "SELECT username, email, first_name, last_name, 
                  TO_CHAR(created_at, 'YYYY') as join_year 
                  FROM users WHERE user_id = $1";
        $result = pg_query_params($conn, $query, [$user_id]);
        
        if (!$result) {
            throw new Exception("Error fetching profile data");
        }
        
        $user = pg_fetch_assoc($result);
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Clean output buffer before sending JSON
        ob_end_clean();
        
        echo json_encode([
            'success' => true,
            'user' => [
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'join_date' => $user['join_year']
            ]
        ]);
        exit;
        
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle POST request (update profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User not logged in");
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON input");
        }

        // Extract and sanitize data (only fields from registration)
        $user_id = $_SESSION['user_id'];
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $first_name = filter_var($input['first_name'] ?? '', FILTER_SANITIZE_STRING);
        $last_name = filter_var($input['last_name'] ?? '', FILTER_SANITIZE_STRING);

        // Validation
        if (empty($email) || empty($first_name) || empty($last_name)) {
            throw new Exception("First name, last name and email are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email is already taken by another user
        $emailCheck = pg_query_params($conn, 
            "SELECT user_id FROM users WHERE email = $1 AND user_id != $2", 
            [$email, $user_id]);
        
        if (pg_num_rows($emailCheck) > 0) {
            throw new Exception("Email already registered by another user");
        }

        // Update user in database (only fields from registration)
        $result = pg_query_params($conn,
            "UPDATE users SET 
                email = $1, 
                first_name = $2, 
                last_name = $3,
                username = $4
             WHERE user_id = $5
             RETURNING username, email, first_name, last_name",
            [
                $email,
                $first_name,
                $last_name,
                strtolower("$first_name.$last_name"), // Generate username like first.last
                $user_id
            ]);

        if (!$result) {
            throw new Exception("Error updating profile: " . pg_last_error($conn));
        }

        $updatedUser = pg_fetch_assoc($result);
        
        // Update session with new username if changed
        $_SESSION['username'] = $updatedUser['username'];
        $_SESSION['email'] = $updatedUser['email'];
        
        // Clean output buffer before sending JSON
        ob_end_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'username' => $updatedUser['username'],
                'email' => $updatedUser['email'],
                'first_name' => $updatedUser['first_name'],
                'last_name' => $updatedUser['last_name']
            ]
        ]);
        exit;
        
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Clean up
pg_close($conn);
?>