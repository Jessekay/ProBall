<?php
header('Content-Type: application/json');

session_start();
$conn = pg_connect("host=localhost dbname=proball user=postgres password=your_password");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = filter_var($data['order_id'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$order_id || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID or user not logged in.']);
        exit;
    }

    // Verify the order belongs to the user
    $query = "SELECT user_id FROM orders WHERE order_id = $1";
    $result = pg_query_params($conn, $query, [$order_id]);
    $order = pg_fetch_assoc($result);

    if (!$order || $order['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Order not found or unauthorized.']);
        exit;
    }

    // Delete the order
    $query = "DELETE FROM orders WHERE order_id = $1";
    $result = pg_query_params($conn, $query, [$order_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Order canceled successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error canceling order.']);
    }
}

pg_close($conn);
?>