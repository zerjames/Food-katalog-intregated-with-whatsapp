<?php
require __DIR__ . '/inc/db.php';
$config = require __DIR__ . '/inc/config.php';
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Checkout Pesanan | Rumah Frozen Food</title>
	<link rel="stylesheet" href="assets/style.css">
	<style>
		.checkout-container {
			max-width: 800px;
			margin: 0 auto;
		}
		.cart-table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 24px;
		}
		.cart-table th, .cart-table td {
			padding: 12px;
			text-align: left;
			border-bottom: 1px solid #eee;
		}
		.cart-table th {
			background-color: #f8f9fa;
		}
		.cart-item-info {
			display: flex;
			align-items: center;
		}
		.cart-item-info img {
			width: 60px;
			height: 60px;
			object-fit: cover;
			border-radius: 8px;
			margin-right: 16px;
		}
		.qty-controls { display: flex; align-items: center; gap: 8px; }
		.qty-btn { background: #eee; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-weight: bold; }
		.remove-item-btn { background: none; border: none; color: #b91c1c; cursor: pointer; font-size: 24px; padding: 0 8px; }
		.cart-summary {
			margin-top: 24px;
			float: right;
			width: 100%;
			max-width: 300px;
		}
		.cart-summary .row {
			display: flex;
			justify-content: space-between;
			padding: 8px 0;
		}
		.cart-summary .total {
			font-weight: bold;
			font-size: 1.2em;
			border-top: 2px solid #333;
			margin-top: 8px;
		}
		.checkout-actions {
			margin-top: 24px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			clear: both;
		}
		
		/* Responsive Table Styles for Mobile */
		@media (max-width: 768px) {
			.table-responsive {
				overflow-x: hidden; /* Matikan scroll horizontal */
			}
			.cart-table thead {
				display: none; /* Sembunyikan header tabel */
			}
			.cart-table, .cart-table tbody, .cart-table tr, .cart-table td {
				display: block;
				width: 100%;
			}
			.cart-table tr {
				margin-bottom: 16px;
				border: 1px solid #eee;
				border-radius: 12px;
				padding: 12px;
			}
			.cart-table td {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 10px 0;
				border-bottom: 1px solid #f1f5f9;
			}
			.cart-table td:last-child {
				border-bottom: none;
				padding-top: 16px;
				justify-content: flex-end;
			}
			.cart-table td::before { content: attr(data-label); font-weight: 600; }
		}

		/* Custom Alert Modal Style */
		.custom-alert-overlay {
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
		.custom-alert-box {
			background: white;
			padding: 24px;
			border-radius: 12px;
			width: 95vw;
			max-width: 400px;
		}
	</style>
</head>
<body>
	<header class="header">
        <div class="container">
            <h1>RUMAH <span class="frozen">FROZEN</span> MAMA SANTI</h1>
        </div>
    </header>

	<div class="container checkout-container">
		<div class="header">
			<h2>Keranjang Belanja Anda</h2>
		</div>

		<div id="cartContent">
			<!-- Konten keranjang akan dirender oleh JavaScript di sini -->
		</div>

		<div class="checkout-actions">
			<a href="index.php" class="button secondary">&larr; Kembali Belanja</a>
			<button id="sendOrderBtn" class="button">Pesan Sekarang via WhatsApp</button>
		</div>
	</div>

	<!-- Custom Alert Modal -->
	<div id="customAlertModal" class="custom-alert-overlay" style="display: none;">
		<div class="custom-alert-box">
			<h3 id="customAlertTitle" style="margin: 0 0 16px 0;">Pemberitahuan</h3>
			<p id="customAlertMessage" style="line-height: 1.6;"></p>
			<div style="text-align: right; margin-top: 20px;">
				<button onclick="closeCustomAlert()" class="button">Tutup</button>
			</div>
		</div>
	</div>



	<!-- Custom Confirmation Modal -->
	<div id="confirmDeleteModal" class="custom-alert-overlay" style="display: none;">
		<div class="custom-alert-box">
			<h3 style="margin: 0 0 16px 0;">Konfirmasi</h3>
			<p id="confirmDeleteMessage">Hapus pesanan dari keranjang?</p>
			<div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px;">
				<button onclick="closeConfirmDeleteModal()" class="button secondary">Tidak</button>
				<button onclick="confirmDeletion()" class="button danger">Ya</button>
			</div>
		</div>
	</div>


	<script>
	let cart = [];

	function formatRupiah(x) {
		return new Intl.NumberFormat('id-ID').format(x);
	}

	function saveCart() {
		localStorage.setItem('frozenCart', JSON.stringify(cart));
	}

	function loadCart() {
		cart = JSON.parse(localStorage.getItem('frozenCart')) || [];
	}

	function renderCheckoutCart() {
		const container = document.getElementById('cartContent');
		if (cart.length === 0) {
			container.innerHTML = '<div class="card center" style="padding: 40px;">Keranjang Anda kosong.</div>';
			document.getElementById('sendOrderBtn').style.display = 'none';
			return;
		}

		let subTotal = 0;
		let tableHTML = `
			<div class="card">
			<div class="table-responsive">
			<table class="cart-table">
				<thead>
					<tr>
						<th>Produk</th>
						<th>Jumlah</th>
						<th style="text-align:right;">Subtotal</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
		`;

		cart.forEach(item => {
			const itemSubtotal = item.price * item.qty;
			subTotal += itemSubtotal;
			tableHTML += `
				<tr>
					<td data-label="Produk">
						<div class="cart-item-info">
							<img src="${item.img}" alt="${item.name}">
							<div>
								<div style="font-weight:600;">${item.name}</div>
								<div style="font-size: 14px; color: var(--text-muted);">Rp ${formatRupiah(item.price)}</div>
							</div>
						</div>
					</td>
					<td data-label="Jumlah">
						<div class="qty-controls">
							<button class="qty-btn" onclick="updateQuantity('${item.id}', -1)">-</button>
							<span>${item.qty}</span>
							<button class="qty-btn" onclick="updateQuantity('${item.id}', 1)">+</button>
						</div>
					</td>
					<td data-label="Subtotal">Rp ${formatRupiah(itemSubtotal)}</td>
					<td data-label=""><button class="button danger" style="padding: 8px 12px; font-size: 12px;" onclick="openConfirmDeleteModal('${item.id}')">Hapus</button></td>
				</tr>
			`;
		});

		tableHTML += `</tbody></table></div></div>`;
		tableHTML += `
			<div class="cart-summary">
				<div class="row total"><span>Total</span><span>Rp ${formatRupiah(subTotal)}</span></div>
			</div>
		`;
		container.innerHTML = tableHTML;
	}

	function updateQuantity(productId, change) {
		const item = cart.find(i => i.id === productId);
		if (item) {
			const newQty = item.qty + change;
			if (newQty > 0 && newQty <= item.stock) {
				item.qty = newQty;
			} else if (newQty > item.stock) {
				showCustomAlert('Stok tidak mencukupi!', 'Gagal');
			} else {
				openConfirmDeleteModal(productId); // Minta konfirmasi sebelum menghapus jika qty jadi 0
			}
		}
		saveCart();
		renderCheckoutCart();
	}

	function removeFromCart(productId) {
		cart = cart.filter(item => item.id !== productId);
		saveCart();
		renderCheckoutCart();
	}

	// --- Delete Confirmation Modal Functions ---
	let itemToDeleteId = null;

	function openConfirmDeleteModal(productId) {
		itemToDeleteId = productId;
		document.getElementById('confirmDeleteModal').style.display = 'flex';
	}

	function closeConfirmDeleteModal() {
		itemToDeleteId = null;
		document.getElementById('confirmDeleteModal').style.display = 'none';
	}

	function confirmDeletion() {
		if (itemToDeleteId) {
			removeFromCart(itemToDeleteId);
		}
		closeConfirmDeleteModal();
	}


	// --- Custom Alert Functions ---
	function showCustomAlert(message, title = 'Pemberitahuan') {
		document.getElementById('customAlertTitle').innerText = title;
		document.getElementById('customAlertMessage').innerText = message;
		document.getElementById('customAlertModal').style.display = 'flex';
	}

	function closeCustomAlert() {
		document.getElementById('customAlertModal').style.display = 'none';
	}

	document.getElementById('customAlertModal').addEventListener('click', function(e) {
		if (e.target === this) closeCustomAlert();
	});

	document.getElementById('confirmDeleteModal').addEventListener('click', function(e) {
		if (e.target === this) closeConfirmDeleteModal();
	});

	document.getElementById('sendOrderBtn').addEventListener('click', function() {
		if (cart.length === 0) {
			showCustomAlert('Keranjang Anda kosong.', 'Peringatan');
			return;
		}

		let itemLines = [];
		let total = 0;
		cart.forEach(item => {
			const sub = item.qty * item.price;
			total += sub;
			// Menggunakan format tebal (bold) untuk nama produk dan jumlah
			itemLines.push(`- *${item.name}* (x${item.qty}) = Rp ${formatRupiah(sub)}`);
		});

		// Membangun pesan dengan format yang lebih rapi
		const messageParts = [
			`*-- PESANAN BARU --*`,
			`Halo Mama Santi, saya mau pesan:`,
			``, // Baris kosong sebagai spasi
			...itemLines,
			``,
			`-----------------------------------`,
			`*Total Pesanan: Rp ${formatRupiah(total)}*`
		];
		const msg = encodeURIComponent(messageParts.join('\n'));
		const phone = '<?php echo $config['admin_whatsapp']; ?>';
		const url = 'https://wa.me/' + phone + '?text=' + msg;
		window.open(url, '_blank');
	});

	// Initial Load
	loadCart();
	renderCheckoutCart();
	</script>
</body>
</html>