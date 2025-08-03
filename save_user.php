<?php
header('Content-Type: application/json');

$conn = pg_connect("host=localhost dbname=proball user=postgres password=your_password");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    $first_name = filter_var($data['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($data['last_name'], FILTER_SANITIZE_STRING);

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    $query = "INSERT INTO users (username, email, password, first_name, last_name) VALUES ($1, $2, $3, $4, $5)";
    $result = pg_query_params($conn, $query, [$username, $email, $password, $first_name, $last_name]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error registering user.']);
    }
}

pg_close($conn);
?>