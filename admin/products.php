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
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Daftar Produk - Admin</title>
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

		/* Responsive Table Styles for Admin */
		@media (max-width: 768px) {
			.table-responsive {
				overflow-x: hidden;
			}
			.table thead {
				display: none;
			}
			.table, .table tbody, .table tr, .table td {
				display: block;
				width: 100%;
			}
			.table tr {
				margin-bottom: 16px;
				border: 1px solid #eee;
				border-radius: 12px;
				padding: 12px;
			}
			.table td {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 10px 0;
				border-bottom: 1px solid #f1f5f9;
			}
			.table td:last-child { border-bottom: none; }
			.table td::before { content: attr(data-label); font-weight: 600; padding-right: 16px; }
		}
	</style>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>Daftar Produk</h2>
		</div>
		<a href="index.php" class="button secondary" style="margin-top: 16px;">← Kembali ke Dashboard</a>

		<!-- Daftar Produk -->
		<div class="mt-6">
			<div class="flex admin-section-header">
				<h3>Daftar Produk</h3>
				<div class="flex" style="gap: 8px;">
					<a href="slider_management.php" class="button secondary">Kelola Slider</a>
					<a href="product_add.php" class="button">Tambah Produk</a>
				</div>
			</div>
			<div class="mt-2" style="max-width: 400px; margin-bottom: 16px;">
				<input type="text" id="productsSearchInput" class="input" placeholder="Cari produk di tabel ini...">
			</div>
			
			<?php if ($products): ?>
				<div class="card">
					<div class="table-responsive">
					<table class="table" id="productsTable">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Harga</th>
								<th>Harga Beli</th>
								<th>Diskon</th>
								<th>Stok</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($products as $p): ?>
								<tr>
									<td data-label="Nama"><?php echo htmlspecialchars($p['name']); ?></td>
									<td data-label="Harga"><?php echo rupiah($p['price']); ?></td>
									<td data-label="Harga Beli"><?php echo rupiah($p['cost']); ?></td>
									<td data-label="Diskon">
										<?php if (isset($p['discount_active']) && $p['discount_active'] == 1 && isset($p['discount_percentage']) && $p['discount_percentage'] > 0): ?>
											<?php echo $p['discount_percentage']; ?>%
										<?php else: ?>
											-
										<?php endif; ?>
									</td>
									<td data-label="Stok"><?php echo (int)$p['stock']; ?></td>
									<td data-label="Status">
										<span class="badge" style="background: <?php echo $p['active'] ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $p['active'] ? '#166534' : '#991b1b'; ?>;">
											<?php echo $p['active'] ? 'Aktif' : 'Nonaktif'; ?>
										</span>
									</td>
									<td data-label="Aksi">
										<a href="product_edit.php?id=<?php echo $p['id']; ?>" class="button secondary">Edit</a>
										<button onclick="openUpdateStockModal('<?php echo $p['id']; ?>', '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', '<?php echo (int)$p['stock']; ?>')" class="button success">Update Stok</button>
										<button onclick="openDeleteModal('<?php echo $p['id']; ?>', '<?php echo htmlspecialchars(addslashes($p['name'])); ?>')" class="button danger">Hapus</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					</div>
				</div>
			<?php else: ?>
				<div class="card">
					<p class="center">Belum ada produk</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Modal untuk konfirmasi hapus produk -->
	<div id="deleteModal" class="custom-modal-overlay" style="display: none;">
		<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 12px; width: 95vw; max-width: 420px;">
			<h3 style="margin: 0 0 16px 0;">Konfirmasi Hapus</h3>
			<p id="modalDeleteInfo" style="margin-bottom: 16px;"></p>
			<div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
				<button onclick="closeDeleteModal()" class="button secondary">Batal</button>
				<button onclick="confirmDelete()" class="button danger">Hapus</button>
			</div>
		</div>
	</div>

	<!-- Modal untuk update stok -->
	<div id="updateStockModal" class="custom-modal-overlay" style="display: none;">
		<div class="custom-modal-box">
			<h3 style="margin: 0 0 16px 0;">Update Stok Produk</h3>
			<div id="modalStockProductInfo" style="margin-bottom: 16px; padding: 12px; background: #f8f9fa; border-radius: 8px;"></div>
			<label style="display: block; margin-bottom: 8px;">
				<b>Jumlah Stok Baru:</b>
				<input type="number" id="newStockQty" class="input" min="0" placeholder="Masukkan jumlah stok baru" style="margin-top: 4px;">
			</label>
			<div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
				<button onclick="closeUpdateStockModal()" class="button secondary">Batal</button>
				<button onclick="confirmUpdateStock()" class="button success">Simpan</button>
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
	// --- DELETE MODAL ---
	const deleteModal = document.getElementById('deleteModal');
	let productToDeleteId = null;

	function openDeleteModal(productId, productName) {
		productToDeleteId = productId;
		document.getElementById('modalDeleteInfo').innerHTML = `Apakah Anda yakin ingin menghapus produk <strong>${productName}</strong>? Tindakan ini tidak dapat dibatalkan.`;
		deleteModal.style.display = 'flex';
	}

	function closeDeleteModal() {
		deleteModal.style.display = 'none';
		productToDeleteId = null;
	}

	function confirmDelete() {
		if (productToDeleteId) {
			window.location.href = 'product_delete.php?id=' + productToDeleteId;
		}
	}

	// Tutup modal hapus saat klik di luar
	document.getElementById('deleteModal').addEventListener('click', function(e) {
		if (e.target === this) {
			closeDeleteModal();
		}
	});


	// --- UPDATE STOCK MODAL ---
	const updateStockModal = document.getElementById('updateStockModal');
	let productToUpdateStockId = null;

	function openUpdateStockModal(productId, productName, currentStock) {
		productToUpdateStockId = productId;
		document.getElementById('modalStockProductInfo').innerHTML = `
			<strong>${productName}</strong><br>
			Stok saat ini: ${currentStock} pcs
		`;
		const stockInput = document.getElementById('newStockQty');
		stockInput.value = currentStock;
		updateStockModal.style.display = 'flex';
		stockInput.focus();
		stockInput.select();
	}

	function closeUpdateStockModal() {
		updateStockModal.style.display = 'none';
		productToUpdateStockId = null;
	}

	function confirmUpdateStock() {
		const newStock = document.getElementById('newStockQty').value;

		if (newStock === '' || isNaN(newStock) || parseInt(newStock) < 0) {
			showCustomAlert('Jumlah stok baru tidak valid.', 'Error');
			return;
		}

		fetch('update_stock.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: `product_id=${productToUpdateStockId}&new_stock=${parseInt(newStock)}`
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				showCustomAlert('Stok produk berhasil diperbarui. Halaman akan dimuat ulang.', 'Sukses');
				setTimeout(() => location.reload(), 2000);
			} else {
				showCustomAlert(data.message || 'Gagal memperbarui stok.', 'Error');
			}
		})
		.catch(error => {
			showCustomAlert('Terjadi kesalahan koneksi saat memperbarui stok.', 'Error');
		});

		closeUpdateStockModal();
	}


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

	// --- TABLE SEARCH FUNCTIONALITY ---
	function setupTableSearch(inputId, tableId) {
		const searchInput = document.getElementById(inputId);
		const table = document.getElementById(tableId);
		if (!searchInput || !table) return;

		const tableRows = table.querySelectorAll('tbody tr');

		searchInput.addEventListener('input', function() {
			const searchTerm = this.value.toLowerCase().trim();

			tableRows.forEach(row => {
				// Cari di sel pertama (nama produk)
				const productNameCell = row.cells[0];
				if (productNameCell) {
					const productName = productNameCell.textContent.toLowerCase();
					if (productName.includes(searchTerm)) {
						row.style.display = ''; // Tampilkan baris
					} else {
						row.style.display = 'none'; // Sembunyikan baris
					}
				}
			});
		});
	}

	// Inisialisasi fungsi pencarian
	setupTableSearch('productsSearchInput', 'productsTable');
	</script>
</body>
</html>
