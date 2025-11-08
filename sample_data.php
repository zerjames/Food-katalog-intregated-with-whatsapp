<?php
// Script untuk menambahkan data sample produk
// Jalankan sekali saja: http://localhost/rumah_fro/sample_data.php

require __DIR__ . '/inc/db.php';

$sample_products = [
	[
		'name' => 'Nugget Ayam 500g',
		'price' => 25000,
		'cost' => 15000,
		'stock' => 50,
		'image' => '', // Admin bisa upload gambar nanti
		'active' => 1
	],
	[
		'name' => 'Sosis Sapi 1kg',
		'price' => 45000,
		'cost' => 30000,
		'stock' => 30,
		'image' => '',
		'active' => 1
	],
	[
		'name' => 'Kentang Goreng 1kg',
		'price' => 18000,
		'cost' => 12000,
		'stock' => 25,
		'image' => '',
		'active' => 1
	],
	[
		'name' => 'Bakso Sapi 500g',
		'price' => 22000,
		'cost' => 15000,
		'stock' => 40,
		'image' => '',
		'active' => 1
	],
	[
		'name' => 'Dumpling Ayam 20pcs',
		'price' => 35000,
		'cost' => 25000,
		'stock' => 20,
		'image' => '',
		'active' => 1
	]
];

try {
	foreach ($sample_products as $product) {
		add_product($product);
	}
	
	echo "Sample data berhasil ditambahkan!<br>";
	echo "<a href='/rumah_fro/'>Lihat Website</a> | <a href='/rumah_fro/admin/'>Admin Dashboard</a>";
	
} catch (Exception $e) {
	echo "Error: " . $e->getMessage();
}
?>
