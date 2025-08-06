<?php
header('Content-Type: application/json');
ob_start();
session_start();
echo json_encode(['logged_in' => isset($_SESSION['user_id'])]);
ob_end_clean();
?>