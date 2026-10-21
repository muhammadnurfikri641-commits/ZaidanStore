# Deploy ZaidanStore PHP + MySQL

## Kebutuhan server

- PHP 8.1 atau lebih baru dengan `mysqli` dan `openssl`
- MySQL 8+ atau MariaDB 10.4+
- Web server Apache 2.4 atau Nginx
- HTTPS aktif untuk domain publik

## Langkah deploy

1. Upload proyek ke server, lalu arahkan document root domain ke folder ini.
2. Buat database dan user database khusus aplikasi dengan hak akses hanya untuk database tersebut.
3. Impor [store-database.sql](store-database.sql) ke database tujuan.
4. Atur environment variable ini pada PHP-FPM, VirtualHost Apache, atau panel hosting:

   ```text
   APP_ENV=production
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=zaidanstore
   DB_USERNAME=zaidanstore_user
   DB_PASSWORD=ganti-dengan-password-database-kuat
   SITE_URL=https://domain-kamu.com
   WHATSAPP_NUMBER=628xxxxxxxxxx
   COOKIE_SECURE=1
   STORE_ADMIN_PASSWORD_HASH=$2y$...hasil-password_hash...
   ```

   Jangan memasukkan kredensial ke berkas yang diunggah ke repository. Bila hosting tidak menyediakan environment variable, buat `config.local.php` dari `config.local.example.php`, isi nilainya, lalu pastikan berkas itu tidak masuk Git.

5. Pastikan folder `uploads/banners` dapat ditulis oleh proses PHP hanya bila fitur server lama masih digunakan. Tidak ada folder toko yang perlu ditulis oleh web server.
6. Periksa `https://domain-kamu.com/health.php`. Respons `{"status":"ok","database":"connected"}` berarti aplikasi dan database siap.
7. Buka `/admin.php`, masuk dengan kata sandi yang hash-nya disimpan dalam `STORE_ADMIN_PASSWORD_HASH`, kemudian isi harga produk.

## Apache

Berkas `.htaccess` sudah menonaktifkan directory listing dan menutup file konfigurasi, SQL, dokumentasi, tes database, serta folder internal. Pastikan konfigurasi virtual host mengizinkan `AllowOverride All` atau set aturan ekuivalen langsung pada virtual host.

## Nginx

Tambahkan penolakan akses untuk berkas dan folder internal berikut sebelum aturan PHP:

```nginx
location ~* /(config(?:\.local(?:\.example)?)?\.php|.*\.sql|.*\.md|\.env.*)$ { deny all; }
location ~* ^/(includes|tools)/ { deny all; }
```

Untuk PHP-FPM, teruskan hanya berkas PHP yang ada (`try_files $fastcgi_script_name =404;`) agar path buatan tidak diproses sebagai skrip.
