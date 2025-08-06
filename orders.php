<?php
header('Content-Type: application/json');
ob_start(); // Start output buffering

// Disable error display, enable logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start session
session_start();

// Database connection
$conn = pg_connect("host=localhost dbname=proball user=postgres password=12092001");
if (!$conn) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    error_log('Database connection failed: ' . pg_last_error());
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    error_log('Orders.php: User not authenticated');
    pg_close($conn);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle GET request (fetch orders)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status_filter = $_GET['status'] ?? 'all';
    try {
        $query = "SELECT o.order_id, o.order_date, o.total_amount, o.status, 
                         oi.order_item_id, oi.product_name, oi.sport, oi.price, oi.quantity
                  FROM orders o
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  WHERE o.user_id = $1";
        if ($status_filter !== 'all') {
            $query .= " AND o.status = $2";
            $params = [$user_id, $status_filter];
        } else {
            $params = [$user_id];
        }

        $result = pg_query_params($conn, $query, $params);
        if (!$result) {
            throw new Exception('Error fetching orders: ' . pg_last_error($conn));
        }

        $orders = [];
        $current_order_id = null;
        while ($row = pg_fetch_assoc($result)) {
            if ($row['order_id'] !== $current_order_id) {
                $orders[$row['order_id']] = [
                    'order_id' => (int)$row['order_id'],
                    'order_date' => $row['order_date'],
                    'total_amount' => (float)$row['total_amount'],
                    'status' => $row['status'],
                    'items' => []
                ];
                $current_order_id = $row['order_id'];
            }
            if ($row['order_item_id']) {
                $orders[$row['order_id']]['items'][] = [
                    'order_item_id' => (int)$row['order_item_id'],
                    'product_name' => $row['product_name'],
                    'sport' => $row['sport'],
                    'price' => (float)$row['price'],
                    'quantity' => (int)$row['quantity']
                ];
            }
        }

        ob_end_clean();
        echo json_encode(['success' => true, 'orders' => array_values($orders)]);
        error_log("Orders fetched successfully for user_id=$user_id, status=$status_filter");
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        error_log('Orders fetch failed: ' . $e->getMessage());
    }
    pg_close($conn);
    exit;
}

// Handle POST request (cancel order)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $action = $input['action'] ?? '';
        if ($action !== 'cancel_order') {
            throw new Exception('Invalid action');
        }

        $order_id = (int)($input['order_id'] ?? 0);
        if ($order_id <= 0) {
            throw new Exception('Invalid order ID');
        }

        // Verify order belongs to user and is cancellable
        $query = "SELECT status FROM orders WHERE order_id = $1 AND user_id = $2";
        $result = pg_query_params($conn, $query, [$order_id, $user_id]);
        if (!$result || pg_num_rows($result) === 0) {
            throw new Exception('Order not found or not authorized');
        }

        $order = pg_fetch_assoc($result);
        if ($order['status'] !== 'Pending') {
            throw new Exception('Only pending orders can be cancelled');
        }

        // Update order status to Cancelled
        $query = "UPDATE orders SET status = 'Cancelled' WHERE order_id = $1 AND user_id = $2";
        $result = pg_query_params($conn, $query, [$order_id, $user_id]);
        if (!$result) {
            throw new Exception('Error cancelling order: ' . pg_last_error($conn));
        }

        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        error_log("Order $order_id cancelled successfully for user_id=$user_id");
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        error_log('Order cancellation failed: ' . $e->getMessage());
    }
    pg_close($conn);
    exit;
}

// Invalid request method
ob_end_clean();
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
pg_close($conn);
?>