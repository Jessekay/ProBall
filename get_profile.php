<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=12092001");

$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, first_name, last_name, address, phone FROM users WHERE user_id = $1";
$result = pg_query_params($conn, $query, [$user_id]);
$user = pg_fetch_assoc($result);

echo json_encode($user);

pg_close($conn);
?>