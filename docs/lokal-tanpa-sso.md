# Menjalankan app di laptop tanpa SSO

Berlaku sama di semua app Elips (core, clip, money, portfolio, stock). Saat pengembangan, login tidak lewat
auth.elips.id, dan semua data (pengguna, role, sesi, data app) ada di database lokal milik app itu sendiri, jadi laptop
tidak menyentuh database produksi. Bedanya dengan produksi hanya di `.env`.

## Isi .env lokal

```
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_LOCAL=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<app>_dev
DB_USERNAME=root
DB_PASSWORD=<kata sandi MySQL lokal>

SSO_LOCAL_LOGIN=true
SESSION_COOKIE=<app>_dev_session

SERVER_PORT=8000
VITE_PORT=5173
```

- `DB_LOCAL=true` membuat koneksi `supabase` (pengguna, role, sesi, notifikasi) ikut memakai database lokal di `DB_*`.
  Nilai `SUPABASE_DB_*` diabaikan selama saklar ini menyala.
- `SSO_LOCAL_LOGIN=true` mengganti halaman masuk hub dengan halaman masuk lokal di `/login`. Tampilan dan alurnya
  meniru auth.elips.id (email atau username + password), tapi yang diperiksa database lokal.
- `LOCAL_DEMO_PASSWORD` (opsional, bawaan `password`) adalah kata sandi semua akun demo.
- `SESSION_COOKIE` berbeda per app, supaya beberapa app yang berjalan bersamaan di localhost tidak saling menimpa sesi.
- `SERVER_PORT` dan `VITE_PORT` berbeda per app (misalnya core 8010/5181, clip 8020/5182, money 8030/5183,
  portfolio 8040/5184, stock 8050/5185), supaya `composer dev` beberapa app bisa berjalan bersamaan. `APP_URL` mengikuti
  `SERVER_PORT`.
- Kedua saklar diabaikan bila `APP_ENV` bukan `local`, jadi aman bila terbawa ke server.
- Mau SQLite: isi `DB_CONNECTION=sqlite` dan `DB_DATABASE` dengan path file. Saklar lain tetap sama.

## Pertama kali

Buat databasenya sekali di MySQL lokal (`CREATE DATABASE <app>_dev;`), lalu:

```bash
php artisan config:clear
php artisan app:local-setup
composer dev
```

`composer dev` menjalankan server di `SERVER_PORT` dan Vite di `VITE_PORT` (clip dan portfolio juga queue).
`app:local-setup` menjalankan migrasi, seeder role dari `config/local.php`, lalu `Database\Seeders\LocalDemoSeeder`
yang membuat atau memperbarui akun demo (daftar dan paketnya di `config/local.php`). Perintah ini menolak
berjalan bila koneksi utama atau `supabase` bukan database lokal (SQLite, atau MySQL di 127.0.0.1/localhost), atau
keduanya belum menunjuk database yang sama. `--fresh` menghapus semua tabel lokal lalu membuat ulang.

Buka `/login`, lalu masuk dengan username akun demo (misalnya `admin`) dan password `password`. Daftar akun demo tampil
di bawah form. Untuk menambah akun demo, tambahkan barisnya di `config/local.php`, lalu jalankan
`php artisan db:seed --class=LocalDemoSeeder`.

## Yang tidak berjalan di mode ini

Layanan luar butuh kuncinya sendiri: email, pembayaran, YouTube/TikTok, clipper, dan sejenisnya. Tautan ke hub (misalnya
Kelola akses) tidak berfungsi.

## Kembali ke konfigurasi lama

Matikan `DB_LOCAL` dan `SSO_LOCAL_LOGIN`, lalu `php artisan config:clear`. Hati-hati: dengan saklar mati, perintah artisan
yang menulis database (migrate, seed) mengenai database yang tertulis di `.env`, termasuk `SUPABASE_DB_*`.
