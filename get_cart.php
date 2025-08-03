<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=12092001");

$user_id = $_SESSION['user_id'];
$query = "SELECT product_name, sport, price, quantity FROM cart WHERE user_id = $1";
$result = pg_query_params($conn, $query, [$user_id]);

$items = [];
while ($row = pg_fetch_assoc($result)) {
    $items[] = $row;
}

echo json_encode(['items' => $items]);

pg_close($conn);
?>