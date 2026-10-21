# ZaidanStore
File toko lah pokoknya


Website katalog dan pemesanan top up game berbasis PHP dan MySQL. Pengunjung dapat melihat produk, membuat pesanan, lalu mengonfirmasikannya melalui WhatsApp. Admin dapat mengelola harga produk dan status pesanan.

## Fitur

- Katalog top up untuk Mobile Legends, Free Fire, Genshin Impact, PUBG Mobile, dan Call of Duty Mobile
- Pemesanan dengan ID game dan kode pelacakan
- Konfirmasi pesanan melalui WhatsApp
- Panel admin untuk mengelola harga dan status pesanan
- Pengelolaan server game dan akun pengguna
- Pengaturan keamanan dasar, termasuk token CSRF dan konfigurasi cookie

## Persyaratan

- PHP 8.1 atau lebih baru
- MySQL 8+ atau MariaDB 10.4+
- Ekstensi PHP `mysqli` dan `openssl`
- Apache 2.4 atau Nginx

## Menjalankan secara lokal

1. Clone repository dan salin folder proyek ke document root web server, misalnya `htdocs`.
2. Buat database MySQL.
3. Impor `store-database.sql` untuk membuat tabel toko.
4. Jika menggunakan fitur server dan akun lama, impor juga `database.sql`. Jalankan file `update-*.sql` yang diperlukan untuk memperbarui database lama.
5. Salin `config.local.example.php` menjadi `config.local.php`, lalu sesuaikan pengaturan database dan situs.
6. Buat akun admin:

   ```bash
   php tools/create-admin.php "kata-sandi-admin-yang-kuat"
