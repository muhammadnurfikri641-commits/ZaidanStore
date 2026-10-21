# Menjalankan ZaidanStore di lokal

Proyek ini membutuhkan PHP 8.1+, ekstensi `mysqli`, serta MySQL 8 atau MariaDB 10.4+. Pada komputer ini, Apache, MariaDB, dan PHP sudah tersedia di `D:\Games`.

1. Pastikan Apache dan MySQL aktif.
2. Impor katalog dan tabel pesanan sekali saja:

   ```powershell
   D:\Games\mysql\bin\mysql.exe -u root ygywxhgiig_servers < store-database.sql
   ```

3. Buat akun admin lokal. Ganti teks dalam tanda kutip dengan kata sandi milikmu yang panjangnya minimal 12 karakter:

   ```powershell
   D:\Games\php\php.exe tools\create-admin.php "kata-sandi-admin-yang-kuat"
   ```

   Perintah ini membuat `config.local.php`, menyimpan hash kata sandi, dan berkas tersebut tidak ikut Git atau deployment.

4. Buka [http://localhost/web-main/](http://localhost/web-main/) untuk toko, [http://localhost/web-main/admin.php](http://localhost/web-main/admin.php) untuk harga dan status pesanan, serta [http://localhost/web-main/health.php](http://localhost/web-main/health.php) untuk pemeriksaan koneksi.

`config.local.example.php` memuat semua nilai konfigurasi lokal yang bisa dioverride, termasuk database dan nomor WhatsApp. Harga katalog awal sengaja kosong dan tampil sebagai “Tanya admin” sampai diisi lewat dashboard admin.

Tabel baru toko adalah `products`, `promos`, dan `store_orders`. Tabel pengguna dan server lama tidak dihapus.
