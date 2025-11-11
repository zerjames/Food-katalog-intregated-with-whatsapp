<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	header('Location: login.php');
	exit;
}

require __DIR__ . '/../inc/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] == '1') {
	reset_sales_data();
	echo json_encode(['success' => true]);
} else {
	echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
exit;
?>
