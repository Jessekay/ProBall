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

// Handle GET request (fetch all products)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT product_id, product_name, sport, price, image_path FROM products";
        $result = pg_query($conn, $query);

        if (!$result) {
            throw new Exception('Error fetching products: ' . pg_last_error($conn));
        }

        $products = [];
        while ($row = pg_fetch_assoc($result)) {
            $products[] = [
                'product_id' => (int)$row['product_id'],
                'product_name' => $row['product_name'],
                'sport' => $row['sport'],
                'price' => (float)$row['price'],
                'image_path' => $row['image_path'] ?: 'images/default-product.png'
            ];
        }

        ob_end_clean();
        echo json_encode(['success' => true, 'products' => $products]);
        error_log('Products fetched successfully');
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        error_log('Products fetch failed: ' . $e->getMessage());
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