<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Unauthorized']);
	exit;
}

require __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;

if ($product_id <= 0 || $qty <= 0) {
	echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
	exit;
}

try {
	// Ambil data produk
	$product = get_product($product_id);
	
	if (!$product) {
		throw new Exception('Produk tidak ditemukan');
	}
	
	if ($product['stock'] < $qty) {
		throw new Exception('Stok tidak mencukupi');
	}
	
	// Update stok
	$product['stock'] -= $qty;
	update_product($product_id, $product);
	
	// Catat penjualan
	add_sale($product_id, $qty, $product['price'], $product['cost']);
	
	echo json_encode(['success' => true, 'message' => 'Berhasil menandai sebagai terjual']);
	
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
