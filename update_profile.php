<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=your_password");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $first_name = filter_var($data['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($data['last_name'], FILTER_SANITIZE_STRING);
    $address = filter_var($data['address'], FILTER_SANITIZE_STRING);
    $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    $query = "UPDATE users SET email = $1, first_name = $2, last_name = $3, address = $4, phone = $5 WHERE user_id = $6";
    $result = pg_query_params($conn, $query, [$email, $first_name, $last_name, $address, $phone, $user_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile.']);
    }
}

pg_close($conn);
?>