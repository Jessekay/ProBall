<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=12092001");

$user_id = $_SESSION['user_id'];
$query = "SELECT order_id, total_amount, order_date, status FROM orders WHERE user_id = $1 ORDER BY order_date DESC";
$result = pg_query_params($conn, $query, [$user_id]);

$orders = [];
while ($row = pg_fetch_assoc($result)) {
    $orders[] = $row;
}

echo json_encode(['orders' => $orders]);

pg_close($conn);
?>