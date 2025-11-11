<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	header('Location: login.php');
	exit;
}

require __DIR__ . '/../inc/db.php';
$config = require __DIR__ . '/../inc/config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$price = (int)($_POST['price'] ?? 0);
	$cost = (int)($_POST['cost'] ?? 0);
	$stock = (int)($_POST['stock'] ?? 0);
	$active = isset($_POST['active']) ? 1 : 0;
	$discount_percentage = (int)($_POST['discount_percentage'] ?? 0);
	$discount_active = isset($_POST['discount_active']) ? 1 : 0;
	$image = '';
	
	// Handle image upload
	if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
		$uploadDir = $config['uploads_dir'];
		$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		$maxSize = 5 * 1024 * 1024; // 5MB
		
		if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
			$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
			$filename = time() . '_' . rand(1000, 9999) . '.' . $extension;
			$uploadPath = $uploadDir . $filename;
			
			if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
				$image = $filename;
			} else {
				$error = 'Gagal mengupload gambar';
			}
		} else {
			$error = 'Format gambar tidak didukung atau ukuran terlalu besar (max 5MB)';
		}
	}
	
	if (empty($error)) {
		if (empty($name)) {
			$error = 'Nama produk harus diisi';
		} elseif ($price <= 0) {
			$error = 'Harga harus lebih dari 0';
		} else {
			$product = [
				'name' => $name,
				'price' => $price,
				'cost' => $cost,
				'stock' => $stock,
				'image' => $image,
				'active' => $active,
				'discount_percentage' => $discount_percentage,
				'discount_active' => $discount_active
			];
			
			add_product($product);
			$success = 'Produk berhasil ditambahkan';
		}
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tambah Produk</title>
<link rel="stylesheet" href="../assets/style.css">
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>Tambah Produk</h2>
		</div>
		<div class="card" style="max-width: 600px;">
			<?php if ($error): ?>
				<div style="color: #b91c1c; margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>
			
			<?php if ($success): ?>
				<div style="color: #16a34a; margin-bottom: 16px;"><?php echo htmlspecialchars($success); ?></div>
			<?php endif; ?>
			
			<form method="post" enctype="multipart/form-data" id="productForm">
				<label>Nama Produk *
					<input class="input" type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
				</label>
				
				<div class="mt-2"></div>
				<label>Harga Jual *
					<input class="input" type="number" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" min="1" required>
				</label>
				
				<div class="mt-2"></div>
				<label>Harga Beli
					<input class="input" type="number" name="cost" value="<?php echo htmlspecialchars($_POST['cost'] ?? '0'); ?>" min="0">
				</label>
				
				<div class="mt-2"></div>
				<label>Stok
					<input class="input" type="number" name="stock" value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>" min="0">
				</label>
				
				<div class="mt-2"></div>
				<label>Gambar Produk
					<input class="input" type="file" id="imageInput" accept="image/*">
					<small style="color: #666;">Format: JPG, PNG, GIF, WebP (max 5MB)</small>
				</label>

				<!-- Image Cropper Container -->
				<div id="cropperContainer" style="display: none; margin-top: 16px;">
					<div style="max-width: 100%; margin-bottom: 8px;">
						<img id="imageToCrop" style="max-width: 100%;">
					</div>
					<div style="display: flex; gap: 8px;">
						<button type="button" id="cropBtn" class="button">Crop & Upload</button>
						<button type="button" id="cancelCropBtn" class="button secondary">Batal</button>
					</div>
				</div>
				
				<div class="mt-2"></div>
				<label class="flex">
					<input type="checkbox" name="active" <?php echo isset($_POST['active']) ? 'checked' : 'checked'; ?>>
					<span>Aktif</span>
				</label>

				<div class="mt-2"></div>
				<label>Diskon (%)
					<input class="input" type="number" name="discount_percentage" value="<?php echo htmlspecialchars($_POST['discount_percentage'] ?? ''); ?>" min="0" max="100">
				</label>

				<div class="mt-2"></div>
				<label class="flex" for="discount_active">
					<input type="checkbox" id="discount_active" name="discount_active" <?php echo isset($_POST['discount_active']) ? 'checked' : ''; ?>>
					<span>Aktifkan Diskon</span>
				</label>

				<div class="mt-4"></div>
				<button class="button" type="submit">Simpan Produk</button>
			</form>

		</div>
		<div class="mt-4">
			<a href="./" class="button secondary">Kembali ke Dashboard</a>
		</div>
	</div>

<!-- Cropper.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script>
let cropper;
const imageInput = document.getElementById('imageInput');
const cropperContainer = document.getElementById('cropperContainer');
const imageToCrop = document.getElementById('imageToCrop');
const cropBtn = document.getElementById('cropBtn');
const cancelCropBtn = document.getElementById('cancelCropBtn');
const productForm = document.getElementById('productForm');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            imageToCrop.src = event.target.result;
            cropperContainer.style.display = 'block';
            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(imageToCrop, {
                aspectRatio: NaN, // Free crop for products
                viewMode: 1,
                responsive: true,
                restore: false,
                checkCrossOrigin: false,
                checkOrientation: false,
                modal: true,
                guides: true,
                center: true,
                highlight: false,
                background: false,
                autoCrop: true,
                autoCropArea: 0.8,
                movable: true,
                rotatable: true,
                scalable: true,
                zoomable: true,
                zoomOnTouch: true,
                zoomOnWheel: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: true,
            });
        };
        reader.readAsDataURL(file);
    }
});

cropBtn.addEventListener('click', function() {
    if (cropper) {
        cropper.getCroppedCanvas().toBlob(function(blob) {
            // Create a new FormData and append the cropped image
            const formData = new FormData(productForm);
            formData.set('image', blob, 'cropped_image.jpg'); // Replace the file input with cropped blob
            
            // Submit the form with cropped image
            fetch(productForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Reload the page to show success/error
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupload gambar.');
            });
        });
    }
});

cancelCropBtn.addEventListener('click', function() {
    cropperContainer.style.display = 'none';
    imageInput.value = '';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

// Prevent default form submission if cropping is active
productForm.addEventListener('submit', function(e) {
    if (cropperContainer.style.display === 'block') {
        e.preventDefault();
        alert('Silakan crop gambar terlebih dahulu atau batalkan cropping.');
    }
});
</script>
</body>
</html>
