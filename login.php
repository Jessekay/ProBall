<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=your_password");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = $data['password'];

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $query = "SELECT user_id, password FROM users WHERE email = $1";
    $result = pg_query_params($conn, $query, [$email]);
    $user = pg_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        echo json_encode(['success' => true, 'message' => 'Login successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
}

pg_close($conn);
?>