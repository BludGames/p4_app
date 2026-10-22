# Coffee Break

Aplikasi web kedai kopi sederhana dengan fitur melihat menu dan mengelola pesanan. Dibuat sebagai proyek latihan Laravel dan portofolio.

## Fitur

- Melihat menu dan harga kopi.
- Daftar akun dan login menggunakan token Laravel Sanctum.
- User membuat pesanan, melihat pesanan miliknya, dan mengubah pesanan yang masih berstatus `pending`.
- Admin melihat semua pesanan, membuat pesanan untuk user, mengubah status, dan menghapus pesanan.
- Validasi input dan pembatasan akses dilakukan di server.

## Teknologi

- PHP 8.3+
- Laravel 13
- MySQL
- Laravel Sanctum
- Blade, JavaScript `fetch`, dan Tailwind CSS CDN

## Menjalankan proyek

1. Pasang dependency PHP:

   ```bash
   composer install
   ```

2. Buat database MySQL bernama `p4_db`, lalu atur koneksi database pada `.env`. Jangan unggah `.env` ke GitHub.
3. Jalankan migrasi, isi data awal, lalu nyalakan server lokal:

   ```bash
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan serve
   ```

4. Buka alamat lokal yang ditampilkan oleh Laravel.

Seeder menyiapkan menu dan akun demo untuk pengembangan lokal. Jangan gunakan akun atau password demo di aplikasi produksi. Jika akun demo sudah ada, seeder dapat gagal karena email duplikat. Hindari `migrate:fresh` jika ingin mempertahankan data database.

## Cara kerja singkat

1. Halaman utama mengambil daftar menu yang tersedia dari `GET /api/menus`.
2. User login atau daftar melalui API. Token Sanctum disimpan di browser dan digunakan untuk request pesanan.
3. Halaman pesanan mengambil pesanan dengan `GET /api/orders` dan mengirim perubahan melalui `POST` atau `PUT`.
4. Server memvalidasi setiap input dan memastikan user hanya mengakses pesanan miliknya.
5. Penghapusan pesanan dibatasi untuk admin melalui middleware.

## Struktur utama

| Lokasi | Kegunaan |
| --- | --- |
| `resources/views/` | Halaman login, daftar, beranda, dan pesanan |
| `public/app.js` | Interaksi menu dan request daftar menu |
| `routes/web.php` | Route halaman web |
| `routes/api.php` | Route autentikasi, menu, dan pesanan |
| `app/Http/Controllers/` | Logika autentikasi dan pesanan |
| `app/Http/Middleware/AdminOnly.php` | Membatasi aksi khusus admin |
| `app/Models/` | Model dan relasi database |
| `database/migrations/` | Struktur tabel |
| `database/seeders/` | Data menu dan akun demo |

## Keamanan

- `.env` masuk dalam `.gitignore`; jangan commit secret, token, atau data pribadi.
- Role tidak dapat diisi melalui pendaftaran user biasa.
- Teks yang berasal dari data API di-escape sebelum ditampilkan sebagai HTML.
- Penyimpanan token di `localStorage` dipakai untuk demo ini. Evaluasi strategi autentikasi sebelum penggunaan produksi.
