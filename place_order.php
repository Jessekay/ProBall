<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=your_password");

$user_id = $_SESSION['user_id'];
$query = "SELECT SUM(price * quantity) as total FROM cart WHERE user_id = $1";
$result = pg_query_params($conn, $query, [$user_id]);
$total = pg_fetch_result($result, 0, 'total');

if ($total > 0) {
    $query = "INSERT INTO orders (user_id, total_amount) VALUES ($1, $2) RETURNING order_id";
    $result = pg_query_params($conn, $query, [$user_id, $total]);
    $order_id = pg_fetch_result($result, 0, 'order_id');

    $query = "DELETE FROM cart WHERE user_id = $1";
    pg_query_params($conn, $query, [$user_id]);

    echo json_encode(['success' => true, 'message' => 'Order placed successfully.', 'order_id' => $order_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
}

pg_close($conn);
?>