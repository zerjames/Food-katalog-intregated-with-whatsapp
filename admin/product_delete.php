<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	header('Location: login.php');
	exit;
}

require __DIR__ . '/../inc/db.php';
$config = require __DIR__ . '/../inc/config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
	header('Location: ./');
	exit;
}

// Ambil data produk untuk hapus gambar
$product = get_product($product_id);
if ($product && !empty($product['image'])) {
	$imagePath = $config['uploads_dir'] . $product['image'];
	if (file_exists($imagePath)) {
		unlink($imagePath);
	}
}

// Hapus produk
delete_product($product_id);

header('Location: ./');
exit;
