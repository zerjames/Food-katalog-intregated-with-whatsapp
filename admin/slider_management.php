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

// Handle form submission for adding a new slide
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
	if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
		$uploadDir = $config['uploads_dir'];
		$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		$maxSize = 5 * 1024 * 1024; // 5MB

		if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
			$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
			$filename = 'slide_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
			$uploadPath = $uploadDir . $filename;

			if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
				add_slide(['image' => $filename]);
				$success = 'Slide baru berhasil ditambahkan.';
			} else {
				$error = 'Gagal mengupload gambar slide.';
			}
		} else {
			$error = 'Format gambar tidak valid atau ukuran terlalu besar (max 5MB).';
		}
	} else {
		$error = 'Terjadi kesalahan saat mengupload file.';
	}
}

// Handle slide deletion
if (isset($_GET['delete'])) {
    $slide_id = (int)$_GET['delete'];
    $slide = get_slide($slide_id);
    if ($slide) {
        // Hapus file gambar dari server
        $filePath = $config['uploads_dir'] . $slide['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        delete_slide($slide_id);
        header('Location: slider_management.php?status=deleted');
        exit;
    }
}

$slides = get_slides();
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Manajemen Slider</title>
	<link rel="stylesheet" href="../assets/style.css">
	<!-- Cropper.js CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<body>
	<div class="container">
		<div class="header">
			<h2>Manajemen Slider</h2>
		</div>

		<!-- Form Tambah Slide -->
		<div class="card mt-6" style="max-width: 600px;">
			<h3>Tambah Slide Baru</h3>
			<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
			<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
			<form method="post" enctype="multipart/form-data" id="slideForm">
				<label>Pilih Gambar Slide
					<input class="input" type="file" id="imageInput" accept="image/*" required>
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
				<small style="color: #666;">Ukuran rekomendasi: 800x300 pixel. Format: JPG, PNG, GIF, WebP (max 5MB)</small>
				<button type="submit" class="button mt-4">Upload Slide</button>
			</form>
		</div>

		<!-- Daftar Slide -->
		<div class="mt-6">
			<h3>Daftar Slide Aktif</h3>
			<div class="grid-three">
				<?php if (empty($slides)): ?>
					<p>Belum ada slide.</p>
				<?php else: ?>
					<?php foreach ($slides as $slide): ?>
						<div class="card">
							<img src="<?php echo $config['uploads_url'] . htmlspecialchars($slide['image']); ?>" alt="Slide Image">
							<a href="?delete=<?php echo $slide['id']; ?>" class="button danger" onclick="return confirm('Apakah Anda yakin ingin menghapus slide ini?')">Hapus</a>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
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
const slideForm = document.getElementById('slideForm');

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
                aspectRatio: 16 / 9, // Recommended aspect ratio for slides
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
            const formData = new FormData(slideForm);
            formData.set('image', blob, 'cropped_slide.jpg'); // Replace the file input with cropped blob
            
            // Submit the form with cropped image
            fetch(slideForm.action, {
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
slideForm.addEventListener('submit', function(e) {
    if (cropperContainer.style.display === 'block') {
        e.preventDefault();
        alert('Silakan crop gambar terlebih dahulu atau batalkan cropping.');
    }
});
</script>
</body>
</html>
