# Website Jualan Frozen Food

Website untuk jualan frozen food dengan fitur:
- User bisa memilih produk dengan checklist
- Input jumlah pesanan
- Tombol "Pesan Sekarang" yang mengarahkan ke WhatsApp admin
- Dashboard admin untuk CRUD produk
- Upload gambar produk langsung dari admin
- Fitur "produk yang laku" untuk mengurangi stok dan menghitung keuntungan

## Setup

1. Pastikan XAMPP sudah berjalan
2. Buka browser dan akses: `http://localhost/rumah_fro/`
3. Untuk menambahkan data sample, akses: `http://localhost/rumah_fro/sample_data.php`

## Login Admin

- URL: `http://localhost/rumah_fro/admin/login.php`
- Username: `admin`
- Password: `admin123`

## Konfigurasi

Edit file `inc/config.php` untuk:
- Mengubah nomor WhatsApp admin
- Mengubah kredensial login admin

## Fitur

### Untuk User:
- Melihat daftar produk frozen food dengan gambar
- Memilih produk dengan checklist
- Input jumlah pesanan
- Tombol "Pesan Sekarang" yang membuka WhatsApp dengan format pesanan

### Untuk Admin:
- Login/logout
- Dashboard dengan summary penjualan
- CRUD produk (tambah, edit, hapus)
- Upload gambar produk (JPG, PNG, GIF, WebP - max 5MB)
- Fitur "produk yang laku" untuk mengurangi stok
- Pencatatan keuntungan otomatis
- Tabel penjualan dengan detail produk yang terjual

## Storage

Menggunakan file JSON untuk penyimpanan data (tidak perlu database):
- File `storage/data.json`: menyimpan data produk dan penjualan
- Folder `uploads/`: menyimpan gambar produk
- Folder `storage/`: folder data (dilindungi .htaccess)

## Hosting

Website ini siap untuk di-hosting karena:
- ✅ Tidak memerlukan database eksternal
- ✅ Menggunakan file JSON untuk penyimpanan
- ✅ Upload gambar langsung ke server
- ✅ Semua data tersimpan dalam file
