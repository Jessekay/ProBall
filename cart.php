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
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    error_log('Cart access failed: No user session');
    exit;
}

// Handle GET request (fetch cart items)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT cart_id, product_name, sport, price, quantity, added_at, 
                  (SELECT image_path FROM products WHERE product_name = c.product_name LIMIT 1) as image_path 
                  FROM cart c WHERE user_id = $1";
        $result = pg_query_params($conn, $query, [$user_id]);

        if (!$result) {
            throw new Exception('Error fetching cart items: ' . pg_last_error($conn));
        }

        $items = [];
        while ($row = pg_fetch_assoc($result)) {
            $items[] = [
                'cart_id' => (int)$row['cart_id'],
                'product_name' => $row['product_name'],
                'sport' => $row['sport'],
                'price' => (float)$row['price'],
                'quantity' => (int)$row['quantity'],
                'added_at' => $row['added_at'],
                'image_path' => $row['image_path'] ?: 'images/default-product.png'
            ];
        }

        ob_end_clean();
        echo json_encode(['success' => true, 'items' => $items]);
        error_log('Cart fetched successfully for user_id: ' . $user_id);
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        error_log('Cart fetch failed: ' . $e->getMessage());
    }
    pg_close($conn);
    exit;
}

// Handle POST request (add, update, or remove cart items)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $action = $input['action'] ?? '';
        $user_id = $_SESSION['user_id'];

        if ($action === 'add_item') {
            $product_name = filter_var($input['product_name'] ?? '', FILTER_SANITIZE_STRING);
            $quantity = filter_var($input['quantity'] ?? 1, FILTER_VALIDATE_INT);

            if (!$product_name || $quantity < 1) {
                throw new Exception('Invalid product name or quantity');
            }

            // Check if product exists
            $product_check = pg_query_params($conn, 
                "SELECT product_name, sport, price, image_path FROM products WHERE product_name = $1", 
                [$product_name]);
            if (pg_num_rows($product_check) === 0) {
                throw new Exception('Product not found');
            }

            $product = pg_fetch_assoc($product_check);
            $sport = $product['sport'];
            $price = $product['price'];
            $image_path = $product['image_path'];

            // Check if item is already in cart
            $cart_check = pg_query_params($conn, 
                "SELECT cart_id, quantity FROM cart WHERE user_id = $1 AND product_name = $2", 
                [$user_id, $product_name]);
            
            if (pg_num_rows($cart_check) > 0) {
                $row = pg_fetch_assoc($cart_check);
                $new_quantity = $row['quantity'] + $quantity;
                $result = pg_query_params($conn, 
                    "UPDATE cart SET quantity = $1 WHERE cart_id = $2", 
                    [$new_quantity, $row['cart_id']]);
                if (!$result) {
                    throw new Exception('Error updating cart item: ' . pg_last_error($conn));
                }
                $message = 'Item quantity updated in cart';
            } else {
                $result = pg_query_params($conn, 
                    "INSERT INTO cart (user_id, product_name, sport, price, quantity) 
                     VALUES ($1, $2, $3, $4, $5)", 
                    [$user_id, $product_name, $sport, $price, $quantity]);
                if (!$result) {
                    throw new Exception('Error adding item to cart: ' . pg_last_error($conn));
                }
                $message = 'Item added to cart';
            }

            ob_end_clean();
            echo json_encode(['success' => true, 'message' => $message]);
            error_log('Cart item added/updated for user_id: ' . $user_id . ', product_name: ' . $product_name);
        } elseif ($action === 'update_quantity') {
            $cart_id = filter_var($input['cart_id'] ?? 0, FILTER_VALIDATE_INT);
            $quantity_change = filter_var($input['quantity_change'] ?? 0, FILTER_VALIDATE_INT);

            if (!$cart_id || !$quantity_change) {
                throw new Exception('Invalid cart ID or quantity change');
            }

            $cart_check = pg_query_params($conn, 
                "SELECT quantity FROM cart WHERE cart_id = $1 AND user_id = $2", 
                [$cart_id, $user_id]);
            if (pg_num_rows($cart_check) === 0) {
                throw new Exception('Cart item not found');
            }

            $row = pg_fetch_assoc($cart_check);
            $new_quantity = $row['quantity'] + $quantity_change;
            if ($new_quantity < 1) {
                $result = pg_query_params($conn, 
                    "DELETE FROM cart WHERE cart_id = $1 AND user_id = $2", 
                    [$cart_id, $user_id]);
                if (!$result) {
                    throw new Exception('Error removing cart item: ' . pg_last_error($conn));
                }
                $message = 'Item removed from cart';
            } else {
                $result = pg_query_params($conn, 
                    "UPDATE cart SET quantity = $1 WHERE cart_id = $2 AND user_id = $3", 
                    [$new_quantity, $cart_id, $user_id]);
                if (!$result) {
                    throw new Exception('Error updating cart quantity: ' . pg_last_error($conn));
                }
                $message = 'Cart quantity updated';
            }

            ob_end_clean();
            echo json_encode(['success' => true, 'message' => $message]);
            error_log('Cart quantity updated for user_id: ' . $user_id . ', cart_id: ' . $cart_id);
        } elseif ($action === 'remove_item') {
            $cart_id = filter_var($input['cart_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$cart_id) {
                throw new Exception('Invalid cart ID');
            }

            $result = pg_query_params($conn, 
                "DELETE FROM cart WHERE cart_id = $1 AND user_id = $2", 
                [$cart_id, $user_id]);
            if (!$result) {
                throw new Exception('Error removing cart item: ' . pg_last_error($conn));
            }

            ob_end_clean();
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            error_log('Cart item removed for user_id: ' . $user_id . ', cart_id: ' . $cart_id);
        } else {
            throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        error_log('Cart action failed: ' . $e->getMessage());
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