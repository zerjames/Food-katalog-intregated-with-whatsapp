<?php
require __DIR__ . '/inc/db.php';
$config = require __DIR__ . '/inc/config.php';

// Ambil semua produk aktif
$products = get_products(true);

// Ambil semua slide
$slides = get_slides();
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Rumah Frozen Food | Order WhatsApp</title>
	<link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>RUMAH <span class="frozen">FROZEN</span> MAMA SANTI</h1>
        </div>
    </header>
    <div class="container">
        <div class="app">
            <div class="main">
                <div class="searchbar-container">
                    <div class="searchbar">
                        <span>🔎</span>
                        <input type="text" id="searchInput" placeholder="Mau cari frozen food apa?" autocomplete="off">
                        <button type="button" class="button secondary search-btn" id="searchBtn">Cari</button>
                    </div>
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>

                <!-- Image Slider -->
                <div class="slider-container">
                    <div class="slider">
                        <?php if (empty($slides)): ?>
                            <div class="slide active">
                                <img src="https://via.placeholder.com/800x300/2563eb/ffffff?text=Selamat+Datang" alt="Default Slide">
                            </div>
                        <?php else: ?>
                            <?php foreach ($slides as $index => $slide): ?>
                                <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo $config['uploads_url'] . htmlspecialchars($slide['image']); ?>" alt="Promo Slide <?php echo $index + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button class="slider-btn prev" id="prevBtn">&#10094;</button>
                    <button class="slider-btn next" id="nextBtn">&#10095;</button>
                    <div class="slider-dots">
                        <?php foreach ($slides as $index => $slide): ?>
                            <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <form id="orderForm" onsubmit="return false;">
                <div class="grid-three mt-4 animate-fadeIn">
				<?php if (!$products): ?>
					<div class="center" style="grid-column: 1 / -1;">Belum ada produk.</div>
				<?php endif; ?>
				<?php foreach ($products as $index => $p): ?>
					<?php
					$discounted_price = $p['price'];
					$is_discounted = isset($p['discount_active']) && $p['discount_active'] == 1 && isset($p['discount_percentage']) && $p['discount_percentage'] > 0;
					if ($is_discounted) {
						$discounted_price = $p['price'] - ($p['price'] * $p['discount_percentage'] / 100);
					}
					?>
					<div class="card animate-slideInUp" style="animation-delay: <?php echo ($index * 0.1) + 0.2; ?>s;">
						<?php if (!empty($p['image'])): ?>
							<img src="<?php echo $config['uploads_url'] . htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="animate-float">
						<?php endif; ?>
						<?php if ($is_discounted): ?>
							<div class="discount-badge animate-bounceIn"><?php echo $p['discount_percentage']; ?>% OFF</div>
						<?php endif; ?>
						<?php if ((int)$p['stock'] <= 0): ?>
							<div class="stock-out-overlay">
								<span>Stok Habis</span>
							</div>
						<?php endif; ?>
						<div class="card-body">
							<div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
							<div class="product-price">
								<?php if ($is_discounted): ?>
									<span class="original-price"><?php echo rupiah($p['price']); ?></span>
									<span class="discounted-price"><?php echo rupiah($discounted_price); ?></span>
								<?php elseif ((int)$p['stock'] > 0): // Hanya tampilkan harga jika stok ada ?>
									<span><?php echo rupiah($p['price']); ?></span>
								<?php endif; ?>
							</div>
							<div class="product-stock <?php echo ((int)$p['stock'] <= 0) ? 'stock-empty' : ''; ?>">
								Stok: <?php echo (int)$p['stock']; ?>
							</div>
						</div>
						<div class="card-footer">
							<div class="add-to-cart-form">
								<?php if ((int)$p['stock'] > 0): ?>
									<button class="button add-to-cart-btn animate-pulse" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo $discounted_price; ?>" data-stock="<?php echo (int)$p['stock']; ?>" data-img="<?php echo !empty($p['image']) ? $config['uploads_url'] . htmlspecialchars($p['image']) : 'https://via.placeholder.com/100'; ?>">Tambah ke Keranjang</button>
								<?php else: ?>
									<button class="button" disabled>Stok Habis</button>
								<?php endif; ?>
							</div>
						</div>
					</div>
                <?php endforeach; ?>
                </div>

                <div class="mt-6 flex" style="justify-content: space-between;">
                    <div>
                        <!-- Total di sini tidak lagi relevan, dipindah ke panel keranjang -->
                    </div>
                </div>
                </form>
            </div>

        </div>

        <!-- Sticky order bar -->
        <div class="sticky-order-bar" id="stickyOrderBar">
			<div class="sticky-order-inner">
				<div class="sticky-summary" id="stickySummary">
					<div class="sticky-total">
						Total: <span id="stickyTotal">Rp 0</span>
					</div>
				</div>
				<div class="sticky-actions">
					<a href="checkout.php" id="checkoutBtn" class="button">Lihat Keranjang</a>
				</div>
			</div>
		</div>

		<?php 
			$displayPhone = preg_replace('/^62/', '0', $config['admin_whatsapp']);
			$waLink = 'https://wa.me/' . $config['admin_whatsapp'];
		?>

		<!-- About Us Section -->
		<div class="card mt-6 animate-slideInUp">
			<h3>Tentang Kami</h3>
			<p style="color: var(--text-muted); line-height: 1.7;">
				Rumah Frozen Mama Santi adalah tempat surganya para penikmat makanan nuget ala hokben, yang dimana menyediakan berbagai jenis nuget frozen yang enak, lezat dan praktis untuk dikonsumsi dengan harga yang terjangkau. Untuk pemesanan besar bisa langsung hubungi admin di bawah ini yaa.
			</p>
		</div>

		<footer>
			<div style="margin-bottom: 8px;">
				<b>Kontak Admin:</b>
				<a class="button" href="<?php echo $waLink; ?>" target="_blank" rel="noopener" style="margin-left: 8px;">💬 WhatsApp <?php echo htmlspecialchars($displayPhone); ?></a>
			</div>
			<p>&copy; <?php echo date('Y'); ?> Rumah Frozen Food</p>
		</footer>


	</div>



	<!-- Modal untuk input jumlah -->
	<div id="qtyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
		<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 12px; width: 95vw; max-width: 380px;">
			<h3 style="margin: 0 0 16px 0;">Masukkan Jumlah</h3>
			<div id="modalProductInfo" style="margin-bottom: 16px; padding: 12px; background: #f8f9fa; border-radius: 8px;"></div>
			<label style="display: block; margin-bottom: 8px;">
				<b>Jumlah Pesanan:</b>
				<input type="number" id="modalQtyInput" class="input" min="1" placeholder="Masukkan jumlah" style="margin-top: 4px;">
			</label>
			<div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
				<button onclick="closeQtyModal()" class="button secondary">Batal</button>
				<button onclick="confirmAddToCart()" class="button">Tambahkan</button>
			</div>
		</div>
	</div>


	<style>
		/* Style tambahan untuk kartu produk dan keranjang */
		.card { display: flex; flex-direction: column; justify-content: space-between; }
		.card-body { padding: 12px; flex-grow: 1; }
		.product-name { font-weight: 600; margin-bottom: 8px; }
		.product-price { margin-bottom: 8px; }
		.product-price .original-price { text-decoration: line-through; color: #999; font-size: 12px; margin-right: 4px; }
		.product-price .discounted-price { font-weight: bold; color: var(--primary-color); }
		.product-stock { font-size: 12px; color: var(--text-muted); }
		.card-footer { padding: 12px; border-top: 1px solid #eee; }
		.add-to-cart-form { display: flex; }
		.order-item > div:nth-child(2) { flex-grow: 1; margin: 0 12px; }

		/* Mobile Responsive Styles */
		@media (max-width: 768px) {
			.app {
				flex-direction: column;
			}
			.main {
				width: 100%;
			}
			.grid-three {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		/* Default padding-bottom untuk body, akan disesuaikan oleh JavaScript */
		body { padding-bottom: 100px; }
	</style>

	<script>
	// State management untuk keranjang
	let cart = [];
	let currentProductData = null; // Untuk menyimpan data produk yg akan dimasukkan ke modal

	// Fungsi untuk menyimpan keranjang ke localStorage
	function saveCart() {
		localStorage.setItem('frozenCart', JSON.stringify(cart));
	}

	// Fungsi untuk memuat keranjang dari localStorage
	function loadCart() {
		cart = JSON.parse(localStorage.getItem('frozenCart')) || [];
	}

	function formatRupiah(x){
		return new Intl.NumberFormat('id-ID').format(x);
	}

	// Fungsi untuk membangun pesan WhatsApp dari data keranjang
    function buildOrderMessage(){
        let lines = [];
        let total = 0;
        cart.forEach(item => {
			const sub = item.qty * item.price;
			total += sub;
			lines.push(`- ${item.name} x ${item.qty} = Rp ${formatRupiah(sub)}`);
        });
        return { lines, total };
	}

	// Fungsi untuk merender ulang tampilan keranjang
    function renderCart(){
        let subTotal = 0;

        if (cart.length === 0) {
			document.getElementById('stickyOrderBar').style.display = 'none'; // Sembunyikan sticky bar jika keranjang kosong
            return;
        }

		document.getElementById('stickyOrderBar').style.display = 'flex'; // Tampilkan sticky bar

        cart.forEach(item => {
            subTotal += item.price * item.qty;
        });

		// Update total di sticky bar
		document.getElementById('stickyTotal').innerText = 'Rp ' + formatRupiah(subTotal);
    }

	// Fungsi untuk menghapus produk dari keranjang
	function removeFromCart(productId) {
		cart = cart.filter(item => item.id !== productId);
		saveCart();
		renderCart();
	}

	// Fungsi untuk mengubah jumlah produk di keranjang
	function updateQuantity(productId, change) {
		const item = cart.find(i => i.id === productId);
		if (item) {
			const newQty = item.qty + change;
			if (newQty > 0 && newQty <= item.stock) {
				item.qty = newQty;
			} else if (newQty > item.stock) {
				alert('Stok tidak mencukupi!');
			} else {
				// Jika jumlah jadi 0 atau kurang, hapus item
				removeFromCart(productId);
			}
		}
		saveCart();
		renderCart();
		adjustBodyPadding(); // Sesuaikan padding setelah keranjang diperbarui
	}

	// Event listener untuk tombol "Pesan Sekarang"
	function sendWhatsAppOrder() {
		const { lines, total } = buildOrderMessage();
		if (lines.length === 0){
			alert('Keranjang Anda masih kosong.');
			return;
		}
		const header = '*Mama Santi, aku mau beli dong, aku mau:*%0A';
		const body = lines.map(encodeURIComponent).join('%0A');
		const totalLine = `%0A%0ATotal: Rp ${encodeURIComponent(formatRupiah(total))}`;
		const msg = header + body + totalLine;
		const phone = '<?php echo $config['admin_whatsapp']; ?>';
		const url = 'https://wa.me/' + phone + '?text=' + msg;
		window.open(url, '_blank');
	}

	// --- Modal Logic ---
	function openQtyModal(productData) {
		currentProductData = productData;
		const stock = parseInt(productData.stock, 10);
		document.getElementById('modalProductInfo').innerHTML = `
			<strong>${productData.name}</strong><br>
			Stok tersedia: ${stock} pcs
		`;
		const qtyInput = document.getElementById('modalQtyInput');
		qtyInput.value = '1';
		qtyInput.max = stock;
		document.getElementById('qtyModal').style.display = 'block';
		qtyInput.focus();
		qtyInput.select();
	}

	function closeQtyModal() {
		document.getElementById('qtyModal').style.display = 'none';
		currentProductData = null;
	}

	function confirmAddToCart() {
		const qty = parseInt(document.getElementById('modalQtyInput').value, 10);
		const stock = parseInt(currentProductData.stock, 10);

		if (!qty || qty <= 0) {
			alert('Jumlah tidak valid.');
			return;
		}
		if (qty > stock) {
			alert('Jumlah melebihi stok yang tersedia.');
			return;
		}

		const existingItem = cart.find(item => item.id === currentProductData.id);
		if (existingItem) {
			existingItem.qty += qty;
			if (existingItem.qty > stock) existingItem.qty = stock; // Pastikan tidak melebihi stok
		} else {
			cart.push({ ...currentProductData, qty: qty });
		}

		saveCart();
		renderCart();
		closeQtyModal();
	}

	// Event listener untuk semua tombol "Tambah ke Keranjang"
	document.querySelectorAll('.add-to-cart-btn').forEach(button => {
		button.addEventListener('click', (e) => {
			openQtyModal(e.currentTarget.dataset);
		});
	});

	// Tutup modal saat klik di luar area modal
	document.getElementById('qtyModal').addEventListener('click', (e) => { if (e.target === e.currentTarget) closeQtyModal(); });

	// Fungsi untuk menyesuaikan padding-bottom pada body agar konten tidak tertutup sticky bar
	function adjustBodyPadding() {
		const stickyBar = document.getElementById('stickyOrderBar');
		if (stickyBar && stickyBar.style.display !== 'none') {
			// Tambahkan sedikit ruang ekstra (misalnya, 20px) agar konten tidak terlalu mepet
			document.body.style.paddingBottom = stickyBar.offsetHeight + 20 + 'px';
		} else {
			document.body.style.paddingBottom = '20px'; // Padding default jika sticky bar tidak terlihat
		}
	}

	// Sesuaikan padding saat ukuran jendela berubah
	window.addEventListener('resize', adjustBodyPadding);

    // Product data for suggestions
    const allProducts = [
        <?php foreach ($products as $p): ?>
        {
            id: <?php echo $p['id']; ?>,
            name: "<?php echo addslashes($p['name']); ?>",
            price: <?php echo $p['price']; ?>
        },
        <?php endforeach; ?>
    ];

    // Search functionality with suggestions
    let currentFocus = -1;

    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        showSuggestions(searchTerm);
    });

    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        const suggestionsEl = document.getElementById('searchSuggestions');
        const items = suggestionsEl.querySelectorAll('.suggestion-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus = currentFocus < items.length - 1 ? currentFocus + 1 : 0;
            highlightSuggestion(items, currentFocus);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus = currentFocus > 0 ? currentFocus - 1 : items.length - 1;
            highlightSuggestion(items, currentFocus);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && items[currentFocus]) {
                selectSuggestion(items[currentFocus]);
            } else {
                performSearch();
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    document.getElementById('searchBtn').addEventListener('click', function() {
        performSearch();
    });

    document.addEventListener('click', function(e) {
        if (!document.querySelector('.searchbar').contains(e.target)) {
            hideSuggestions();
        }
    });

    function showSuggestions(searchTerm) {
        const suggestionsEl = document.getElementById('searchSuggestions');
        suggestionsEl.innerHTML = '';

        if (searchTerm.length < 1) {
            suggestionsEl.style.display = 'none';
            return;
        }

        const matches = allProducts.filter(product =>
            product.name.toLowerCase().includes(searchTerm)
        ).slice(0, 5); // Limit to 5 suggestions

        if (matches.length > 0) {
            matches.forEach((product, index) => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = product.name;
                item.addEventListener('click', function() {
                    selectSuggestion(this);
                });
                suggestionsEl.appendChild(item);
            });
            suggestionsEl.style.display = 'block';
            currentFocus = -1;
        } else {
            suggestionsEl.style.display = 'none';
        }
    }

    function highlightSuggestion(items, index) {
        items.forEach(item => item.classList.remove('highlighted'));
        if (items[index]) {
            items[index].classList.add('highlighted');
        }
    }

    function selectSuggestion(element) {
        document.getElementById('searchInput').value = element.textContent;
        hideSuggestions();
        performSearch();
    }

    function hideSuggestions() {
        const suggestionsEl = document.getElementById('searchSuggestions');
        suggestionsEl.style.display = 'none';
        currentFocus = -1;
    }

    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.card');

        if (searchTerm === '') {
            // Show all cards if search is empty
            cards.forEach(card => {
                card.style.display = 'flex';
            });
        } else {
            // Filter cards based on search term
            cards.forEach(card => {
                const productNameEl = card.querySelector('.product-name');
                if (productNameEl) {
                    const productName = productNameEl.textContent.toLowerCase();
                    if (productName.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }
        hideSuggestions();
    }

	// Render keranjang saat halaman pertama kali dimuat
	loadCart();
  renderCart();
	adjustBodyPadding(); // Penyesuaian awal setelah keranjang dirender

    // Image Slider Functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);

    // Event listeners for buttons
    document.getElementById('nextBtn').addEventListener('click', nextSlide);
    document.getElementById('prevBtn').addEventListener('click', prevSlide);

    // Event listeners for dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });
	</script>
</body>
</html>
