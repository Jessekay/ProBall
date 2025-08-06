<?php
header('Content-Type: application/json');
ob_start(); // Start output buffering

// Disable error display, enable logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start session
session_start();

// Log session data for debugging
error_log('check_session.php: session_id=' . session_id() . ', user_id=' . ($_SESSION['user_id'] ?? 'not set'));

// Return session status
ob_end_clean();
echo json_encode(['logged_in' => isset($_SESSION['user_id']), 'user_id' => $_SESSION['user_id'] ?? null]);
?>