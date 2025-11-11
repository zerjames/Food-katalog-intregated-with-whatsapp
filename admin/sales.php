<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	header('Location: login.php');
	exit;
}

require __DIR__ . '/../inc/db.php';
$config = require __DIR__ . '/../inc/config.php';

// Ambil data produk
$products = get_products();

// Ambil data penjualan untuk summary
$sales_summary = get_sales_summary();
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Produk yang Laku - Admin</title>
	<style>
		/* Custom Alert/Modal Style */
		.custom-modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0,0,0,0.5);
			z-index: 1000;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.custom-modal-box {
			background: white;
			padding: 24px;
			border-radius: 12px;
			width: 95vw;
			max-width: 420px;
			box-shadow: var(--shadow-xl);
		}



		/* Product Grid Styles */
		.products-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 16px;
		}

		.product-card {
			background: white;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 16px;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
			transition: box-shadow 0.2s;
		}

		.product-card:hover {
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		.product-image {
			width: 100%;
			height: 120px;
			border-radius: 8px;
			overflow: hidden;
			margin-bottom: 12px;
			background: #f3f4f6;
		}

		.product-image img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.no-image {
			width: 100%;
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #6b7280;
			font-size: 14px;
		}

		.product-info h4 {
			margin: 0 0 4px 0;
			font-size: 16px;
			font-weight: 600;
			color: #111827;
			line-height: 1.4;
		}

		.product-info .stock {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.product-actions {
			margin-top: 12px;
		}

		.full-width {
			width: 100%;
		}

		/* Responsive adjustments */
		@media (max-width: 480px) {
			.products-grid {
				grid-template-columns: 1fr;
			}
		}


	</style>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>Produk yang Laku</h2>
		</div>
		<a href="index.php" class="button secondary" style="margin-top: 16px;">← Kembali ke Dashboard</a>

		<!-- Produk yang Laku -->
		<div class="mt-6">
			<h3>Produk yang Laku</h3>
			<p style="color: #666; margin-bottom: 16px;">Pilih produk yang terjual dan masukkan jumlahnya untuk mengurangi stok dan mencatat keuntungan.</p>
			<div class="mt-2" style="max-width: 400px; margin-bottom: 16px;">
				<input type="text" id="salesSearchInput" class="input" placeholder="Cari produk...">
			</div>

			<?php if ($products): ?>
				<div class="products-grid" id="salesGrid">
					<?php foreach ($products as $p): ?>
						<div class="product-card" data-name="<?php echo htmlspecialchars($p['name']); ?>">
							<div class="product-image">
								<?php if (!empty($p['image'])): ?>
									<img src="<?php echo $config['uploads_url'] . htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
								<?php else: ?>
									<div class="no-image">No Image</div>
								<?php endif; ?>
							</div>
							<div class="product-info">
								<h4><?php echo htmlspecialchars($p['name']); ?></h4>
								<p class="stock">Stok: <?php echo (int)$p['stock']; ?> pcs</p>
							</div>
							<div class="product-actions">
								<button onclick="markAsSold(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', <?php echo (int)$p['stock']; ?>)" class="button success full-width">Tandai Terjual</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="card">
					<p class="center">Belum ada produk</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Modal untuk input jumlah terjual -->
	<div id="soldModal" class="custom-modal-overlay" style="display: none;">
		<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 12px; width: 95vw; max-width: 420px;">
			<h3 style="margin: 0 0 16px 0;">Tandai Produk Terjual</h3>
			<div id="modalProductInfo" style="margin-bottom: 16px; padding: 12px; background: #f8f9fa; border-radius: 8px;"></div>
			<label style="display: block; margin-bottom: 8px;">
				<b>Jumlah yang terjual:</b>
				<input type="number" id="soldQty" class="input" min="1" placeholder="Masukkan jumlah" style="margin-top: 4px;">
			</label>
			<div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
				<button onclick="closeSoldModal()" class="button secondary">Batal</button>
				<button onclick="confirmSold()" class="button success">Konfirmasi</button>
			</div>
		</div>
	</div>

	<!-- Modal untuk notifikasi kustom -->
	<div id="customAlertModal" class="custom-modal-overlay" style="display: none;">
		<div class="custom-modal-box">
			<h3 id="customAlertTitle" style="margin: 0 0 16px 0;">Pemberitahuan</h3>
			<p id="customAlertMessage"></p>
			<div style="text-align: right; margin-top: 20px;">
				<button onclick="closeCustomAlert()" class="button">Tutup</button>
			</div>
		</div>
	</div>

	<script>
	let currentProductId = null;
	let currentProductName = '';
	let currentStock = 0;

	// --- SOLD MODAL ---
	const soldModal = document.getElementById('soldModal');

	function markAsSold(productId, productName, stock) {
		currentProductId = productId;
		currentProductName = productName;
		currentStock = stock;

		// Tampilkan modal
		document.getElementById('modalProductInfo').innerHTML = `
			<strong>${productName}</strong><br>
			Stok tersedia: ${stock} pcs
		`;
		document.getElementById('soldQty').value = '';
		document.getElementById('soldQty').max = stock;
		soldModal.style.display = 'flex';
		document.getElementById('soldQty').focus();
	}

	function closeSoldModal() {
		soldModal.style.display = 'none';
		currentProductId = null;
	}

	function confirmSold() {
		const qty = parseInt(document.getElementById('soldQty').value);
		
		if (!qty || qty <= 0) {
			showCustomAlert('Jumlah yang dimasukkan tidak valid.', 'Error');
			return;
		}
		
		if (qty > currentStock) {
			showCustomAlert('Jumlah melebihi stok yang tersedia.', 'Error');
			return;
		}
		
		// Kirim ke server
		fetch('mark_sold.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: 'product_id=' + currentProductId + '&qty=' + qty
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				showCustomAlert(`Berhasil! ${currentProductName} telah ditandai terjual sebanyak ${qty} pcs. Halaman akan dimuat ulang.`, 'Sukses');
				setTimeout(() => location.reload(), 2000);
			} else {
				showCustomAlert(data.message || 'Gagal memperbarui data penjualan.', 'Error');
			}
		})
		.catch(error => {
			showCustomAlert('Terjadi kesalahan koneksi. Silakan coba lagi.', 'Error');
		});

		closeSoldModal();
	}

	// Tutup modal saat klik di luar
	document.getElementById('soldModal').addEventListener('click', function(e) {
		if (e.target === this) {
			closeSoldModal();
		}
	});

	// --- CUSTOM ALERT ---
	const customAlertModal = document.getElementById('customAlertModal');

	function showCustomAlert(message, title = 'Pemberitahuan') {
		document.getElementById('customAlertTitle').innerText = title;
		document.getElementById('customAlertMessage').innerText = message;
		customAlertModal.style.display = 'flex';
	}

	function closeCustomAlert() {
		customAlertModal.style.display = 'none';
	}

	// Enter key untuk konfirmasi
	document.getElementById('soldQty').addEventListener('keypress', function(e) {
		if (e.key === 'Enter') {
			confirmSold();
		}
	});

	// --- SEARCH FUNCTIONALITY ---
	function setupSearch(inputId, gridId) {
		const searchInput = document.getElementById(inputId);
		const grid = document.getElementById(gridId);
		if (!searchInput) return;

		searchInput.addEventListener('input', function() {
			const searchTerm = this.value.toLowerCase().trim();

			// Search in grid cards
			if (grid) {
				const cards = grid.querySelectorAll('.product-card');
				cards.forEach(card => {
					const productName = card.getAttribute('data-name').toLowerCase();
					if (productName.includes(searchTerm)) {
						card.style.display = '';
					} else {
						card.style.display = 'none';
					}
				});
			}
		});
	}

	// Inisialisasi fungsi pencarian
	setupSearch('salesSearchInput', 'salesGrid');
	</script>
</body>
</html>
