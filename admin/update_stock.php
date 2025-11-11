<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Unauthorized']);
	exit;
}

require __DIR__ . '/../inc/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
$new_stock = isset($_POST['new_stock']) ? (int)$_POST['new_stock'] : -1;

if (empty($product_id) || $new_stock < 0) {
	echo json_encode(['success' => false, 'message' => 'ID produk atau jumlah stok tidak valid.']);
	exit;
}

try {
	$product = get_product($product_id);
	
	if (!$product) {
		throw new Exception('Produk tidak ditemukan.');
	}
	
	// Update stok produk
	$product['stock'] = $new_stock;
	update_product($product_id, $product);
	
	echo json_encode(['success' => true, 'message' => 'Stok berhasil diperbarui.']);
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}