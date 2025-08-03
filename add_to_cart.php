<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=your_password");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id']; // Assume user is logged in
    $product_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $sport = filter_var($data['sport'], FILTER_SANITIZE_STRING);
    $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

    if (!$user_id || !$product_name || !$sport || !$price || !$quantity) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $query = "INSERT INTO cart (user_id, product_name, sport, price, quantity) VALUES ($1, $2, $3, $4, $5)";
    $result = pg_query_params($conn, $query, [$user_id, $product_name, $sport, $price, $quantity]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding to cart.']);
    }
}

pg_close($conn);
?>