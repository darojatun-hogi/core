# Elips — Laravel Boilerplate

Boilerplate Laravel untuk membangun aplikasi web dengan cepat, dilengkapi sistem autentikasi, role & permission, manajemen user, notifikasi, activity log, dan siap deploy via Docker.

> **Pilih mode autentikasi sebelum memulai project baru.** Core bisa dipakai dengan dua cara, diatur lewat `SSO_HUB` di `.env`:
>
> - **Auth terpusat / SSO** (`SSO_HUB=false`, direkomendasikan untuk app di `*.elips.id`): login, register, verifikasi email, reset password, serta pengaturan user/role/permission ditangani hub `auth.elips.id`. Satu kali login berlaku di semua app.
> - **Standalone** (`SSO_HUB=true`): app punya halaman login dan pengaturan user/role/permission sendiri. Cocok untuk app di luar `*.elips.id` atau yang tidak ikut login bersama.
>
> Langkah lengkap keduanya ada di [Membuat Project Baru dari Core](#membuat-project-baru-dari-core). Repo core ini juga yang dideploy sebagai hub `auth.elips.id` (`SSO_HUB=true` hanya di `.env` server hub), jadi clone tetap aman dipakai untuk project baru.

## Tech Stack

- **Backend:** Laravel 12
- **Frontend:** Blade Templating + [FlyonUI](https://flyonui.com/) (Tailwind CSS component library)
- **Package Manager (JS):** Bun
- **Authorization:** [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)
- **Audit Trail:** [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog)
- **Toast Notification:** Notyf
- **Deployment:** Docker + GitHub Actions + Cloudflare Tunnel

## Fitur yang Tersedia

### 1. Autentikasi & Role Management
- Login (email atau username), register, verifikasi email, reset password via email (Mailgun di produksi, Mailtrap/log di lokal)
- Cloudflare Turnstile di form login & register (aktif bila `TURNSTILE_SECRET_KEY` diisi)
- Auth terpusat di Supabase: satu akun untuk semua app (lihat [Auth Terpusat](#auth-terpusat-supabase))
- Single Sign-On antar app `*.elips.id`: login sekali di `auth.elips.id`, semua app ikut login (lihat [SSO](#single-sign-on-sso))
- Role default: **super-admin** (semua app), **`<app>.admin`**, **`<app>.user`** (otomatis saat user pertama kali membuka app, `ACCESS_AUTO_GRANT`)
- Permission granular per-aksi (`view`, `create`, `edit`, `delete`) untuk tiap modul
- Akses per app: hanya user dengan permission `<app>.access`; tanpa akses tampil halaman 403
- Middleware proteksi di Route, Blade (`@can`), dan Policy

### 2. User & Profile Management
- CRUD User dengan permission granular
- Multi-role assignment per user
- Pengaturan user, role & permission terpusat di hub `auth.elips.id` untuk semua app, dengan delegasi per app (`<app>.admin` hanya mengelola app miliknya). App client hanya punya tautan **Kelola Akses** ke hub; app standalone tetap punya halamannya sendiri
- Edit Profile & Reset Password

### 3. Notifikasi
- Database notification (Laravel Notification), tampil di dropdown navbar
- Trigger otomatis: user baru register, aktivitas CRUD user (created/updated/deleted)
- Halaman "Semua Notifikasi" dengan filter read/unread, mark as read, mark all as read
- Validasi target — link notifikasi tidak akan 404 meski data terkait sudah dihapus

### 4. Activity Log
- Auto-logging perubahan data (User, Role, Permission) via trait `LogsActivity`
- Mencatat causer (siapa), subject (data apa), dan detail before/after per field
- Halaman Activity Log dengan filter by model & event, modal detail before/after

### 5. Pola Reusable yang Dipakai
- **Layout & partial** — `layouts.dashboard.master` sebagai layout utama, meng-`@include` `layouts.dashboard.sidebar` dan `layouts.dashboard.navbar`. Semua halaman CRUD tinggal `@extends('layouts.dashboard.master')`.
- **Modal konfirmasi delete (1 modal per halaman, bukan per baris)** — tiap halaman index (User, Role, dll) punya **satu** modal `#confirm-delete-modal` dengan **satu** form `#confirm-delete-form`. Tombol delete di tiap baris tabel cukup punya atribut `data-delete-url="{{ route('users.destroy', $user) }}"` dan `data-overlay="#confirm-delete-modal"` — tidak perlu bikin modal baru per item. JS global di `layouts.dashboard.master` mendengarkan klik pada elemen `[data-delete-url]` dan otomatis meng-set `form.action` sebelum modal dibuka:
  ```js
  document.addEventListener('click', function(event) {
      const trigger = event.target.closest('[data-delete-url]');
      if (!trigger) return;

      const form = document.getElementById('confirm-delete-form');
      if (form) {
          form.action = trigger.getAttribute('data-delete-url');
      }
  });
  ```
- **Toast notification** — session flash (`success`, `error`, `info`) dibaca sekali di `layouts.dashboard.master`, otomatis tampil via Notyf di halaman manapun tanpa perlu setup ulang per halaman.

## Auth Terpusat (Supabase)

Semua app turunan core memakai satu database auth bersama, sedangkan data bisnis tiap app tetap di MySQL masing-masing.

| Koneksi | Database | Isi |
|---|---|---|
| `mysql` (default) | MySQL per app | Tabel bisnis app, `migrations`, cache, jobs |
| `supabase` | Postgres pusat (Supabase di produksi, `shared-postgres` di lokal) | users, roles, permissions, model_has_*, role_has_permissions, sessions, password_reset_tokens, activity_log, notifications |

Aturan yang perlu diikuti:

- **Nama app** diatur lewat `ACCESS_APP` (`config/access.php`). Semua role/permission milik app berawalan `<app>.`, `log_name` activity log dan kolom `app` di notifikasi juga memakai nama ini.
- **Permission** format `resource.action`. Milik app: `<app>.items.view`. Identitas bersama tanpa awalan: `users.*`, `roles.*`, `permissions.*`, `activity-log.view`.
- **Role**: `super-admin` berlaku di semua app (`Gate::before`, tanpa daftar permission). Tiap app punya `<app>.admin` dan `<app>.user`.
- **Akses app**: middleware `app.access` menolak user tanpa `<app>.access`. Halaman Users/Roles hanya menampilkan user dan role milik app yang sedang dikelola (`User::withAppAccess($app)`, `Role::forApp($app)`, app dari `ManagedApp::current()`).
- **Jangan hapus akun dari app**: tombol "Cabut akses" mencabut role `<app>.*` saja. Ubah role selalu lewat `$user->syncAppRoles([...])` agar role app lain tidak ikut hilang.
- **Model bisnis wajib `protected $connection = 'mysql';`**. Relasi dari model Supabase (mis. subject activity log) mewarisi koneksi induknya bila model relasi tidak menetapkan koneksi sendiri.
- **Tanpa foreign key ke `users`** di tabel MySQL (beda database): pakai `$table->foreignId('user_id')->index();`. Hindari `join`/`whereHas` antara model `supabase` dan `mysql`; pakai eager loading.
- **Validasi** ke tabel auth menyebut koneksi: `unique:supabase.users,email`, `Rule::unique('supabase.users', 'email')`. Pencarian ke tabel Supabase memakai `ilike` (Postgres membedakan huruf besar/kecil).
- **Migration tabel pusat idempoten**: dilewati bila tabel sudah ada, dan `down()` kosong supaya `migrate:rollback` di satu app tidak menghapus data pusat. RLS diaktifkan otomatis untuk menutup akses REST API publik Supabase.

## Single Sign-On (SSO)

Semua app di `*.elips.id` memakai satu session: login sekali di satu app, app lain langsung ikut login. Logout di satu app juga keluar dari semua app.

| Mode | `.env` | Peran |
|---|---|---|
| Hub | `SSO_HUB=true` | Hanya `auth.elips.id` (deploy dari repo core ini). Menangani login & register (Cloudflare Turnstile), verifikasi email, reset password, dan menampilkan daftar app (`config/sso.php`). |
| Client | `SSO_HUB=false` (default) | Semua app lain, termasuk project baru hasil clone core. Route `login`, `register`, `password.request`, `verification.notice` diarahkan ke hub lalu kembali ke halaman asal (`?redirect=`). |

Syarat agar session terbaca di semua app (nilai **wajib sama** di setiap `.env`):

```env
APP_KEY=                         # salin dari app lain, cookie session dienkripsi dengan key ini
SESSION_DRIVER=database          # tabel sessions di Supabase
SESSION_COOKIE=elips_session
SESSION_DOMAIN=.elips.id         # produksi; dev lokal: null (cookie localhost berlaku lintas port)
SESSION_LIFETIME=120
SSO_HUB_URL=https://auth.elips.id
PERMISSION_CACHE_STORE=shared    # cache permission Spatie bersama (lihat catatan di bawah)
```

Catatan:

- **Akses app**: user yang membuka app untuk pertama kali otomatis diberi role `<app>.user` (`ACCESS_AUTO_GRANT=true`). Set `false` bila akses harus diberikan admin; user tanpa akses melihat halaman 403 (tidak di-logout, karena session dipakai bersama).
- **Verifikasi email** berlaku di semua app (`users.email_verified_at`). Route yang butuh email terverifikasi memakai middleware `verified`; super-admin dan `<app>.admin` dikecualikan.
- **Redirect setelah login** hanya diizinkan ke domain `SSO_ALLOWED_DOMAIN` dan subdomainnya (default: dari `SESSION_DOMAIN`).
- **Jangan simpan data terenkripsi** (`encrypted` cast, `Crypt::`) yang bergantung pada `APP_KEY` milik satu app, karena key dipakai bersama.
- Key session bersifat global antar app: beri awalan nama app untuk data session milik app (mis. `money.tour_shown`).
- **Pengaturan akses terpusat**: halaman Users, Roles & Permissions hanya ada di hub (dan app standalone). Di hub, pilih app lewat tombol **Aplikasi** (atau `?app=money`). `super-admin` mengelola semua app; `<app>.admin` hanya app miliknya dan hanya user yang punya akses ke app itu. App client punya route `access.manage` (menu **Kelola Akses**) yang membuka `auth.elips.id/users?app=<app>`, tampil untuk `super-admin`/`<app>.admin`.
- **Syarat admin app di hub**: role `<app>.admin` wajib memiliki permission bersama (`users.*`, `roles.*`, `permissions.*`, `activity-log.view`, daftar di `config('access.shared_permissions')`). Tanpa `users.view`/`roles.view`, admin app mendapat "Akses ditolak" saat membuka **Kelola Akses**. Seeder tiap app memberikannya otomatis; jangan hapus permission ini saat mengedit role admin di hub.
- **Definisi permission tetap di tiap app** (`AppAccessSeeder` → `$appPermissions`), karena kode app yang memakainya. Hub hanya membagikan permission itu ke role. Permission baru: tambahkan di seeder app, deploy, jalankan seeder; permission langsung muncul di hub.
- **Cache permission bersama**: Spatie meng-cache pemetaan role → permission. Semua app memakai store `shared` (tabel `shared_cache` di Supabase) lewat `PERMISSION_CACHE_STORE=shared`, sehingga perubahan role/permission di hub langsung berlaku di app lain. Tanpa ini, app lain baru melihat perubahan setelah cache 24 jam habis.
- **Turnstile**: satu widget dipakai semua app dengan hostname `elips.id` (berlaku untuk semua subdomain). App baru di `*.elips.id` tidak perlu menambah hostname.

App yang sudah berjalan di VPS (`/var/www/laravel/<folder>`):

| App | Domain | `APP_PORT` | Mode |
|---|---|---|---|
| core (folder `auth`) | auth.elips.id, elips.id (landing page) | 8084 | Hub (`SSO_HUB=true`, `ACCESS_APP=auth`) |
| money | money.elips.id | 8081 | Client |
| clip | clip.elips.id | 8083 | Client |
| portofolio | portfolio.elips.id | 8080 | Client (`sso.home` = `/admin`) |

Port lain yang sudah terpakai: 8082 (movie), 5678 (n8n), 3306 (MySQL), 20128 (9router). Pilih port baru (mis. 8085) untuk app berikutnya.

## Membuat Project Baru dari Core

Pilih salah satu mode, lalu ikuti langkahnya. Detail tiap langkah server ada di [Deployment Environment Setup](#deployment-environment-setup).

| | A. Dengan SSO (direkomendasikan) | B. Tanpa SSO (standalone) |
|---|---|---|
| Domain | Subdomain `*.elips.id` | Domain apa saja |
| Halaman login/register | Di hub `auth.elips.id` | Di app itu sendiri |
| Akun | Satu akun Supabase untuk semua app | Tetap satu tabel users Supabase, tapi login terpisah |
| Session | Bersama semua app (`elips_session`) | Milik app sendiri |
| `.env` kunci | `SSO_HUB=false`, `APP_KEY` sama dengan app lain | `SSO_HUB=true`, `APP_KEY` baru |

### A. Project baru dengan SSO

1. **Clone & install**
   ```bash
   git clone https://github.com/darojatun-hogi/core.git nama-app
   cd nama-app
   rm -rf .git && git init          # repo baru, tanpa histori core
   composer install
   bun install
   cp .env.example .env
   ```
2. **`.env` lokal**
   ```env
   APP_NAME=nama-app
   APP_KEY=                 # salin APP_KEY dari .env lokal core/app lain (cookie session harus bisa dibaca)
   APP_URL=http://localhost:8001
   ACCESS_APP=namaapp       # huruf kecil, awalan role/permission

   SSO_HUB=false
   SSO_HUB_URL=http://localhost:8000   # core lokal yang dijalankan sebagai hub
   SESSION_COOKIE=elips_session
   SESSION_DOMAIN=null                 # cookie localhost berlaku lintas port
   ```
   Isi juga `DB_*` dan `SUPABASE_DB_*` seperti di [Instalasi](#instalasi-local-development).
3. **Jalankan hub lokal**: di folder core, set `SSO_HUB=true`, `APP_URL=http://localhost:8000`, `APP_KEY` dan `SESSION_COOKIE` yang sama, lalu `php artisan serve --port=8000`. Login di app baru (`php artisan serve --port=8001`) akan diarahkan ke hub ini.
   > Cara cepat tanpa hub: set sementara `SSO_HUB=true` di app baru sehingga halaman login ada di app itu sendiri. Kembalikan ke `false` sebelum commit `.env.example`/deploy.
4. **Migration & seeder**: `php artisan migrate --seed`.
5. **Sesuaikan app**:
   - `config/sso.php` → `home` bila halaman awal app bukan `/dashboard`.
   - `database/seeders/AppAccessSeeder.php` → `$appPermissions` dan `$userPermissions`.
   - `ACCESS_AUTO_GRANT=false` di `.env` bila akses app harus diberikan admin.
   - Halaman Users/Roles tidak ada di mode client; menu **Kelola Akses** mengarah ke hub.
6. **Daftarkan app di hub** (repo core): tambah entri di `config/sso.php` → `apps` (key = `ACCESS_APP`) supaya app muncul di dashboard dan bisa dipilih di halaman Users/Roles `auth.elips.id`, lalu push core.
7. **GitHub**: buat repo baru, ganti nama image di `docker-compose.prod.yml` (`ghcr.io/darojatun-hogi/<repo>-app`, `-nginx`), isi secrets `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY`, `VPS_PROJECT_PATH` (lihat [2.3](#23-isi-github-secrets-pada-directory-setting-per-project)).
8. **VPS**: buat folder `/var/www/laravel/<folder>` berisi `docker-compose.prod.yml` dan `.env` produksi (lihat [3.2](#32-update-file-env-setting-per-project)). Untuk mode SSO:
   ```env
   APP_KEY=                 # salin dari /var/www/laravel/money/.env (JANGAN key:generate)
   SSO_HUB=false
   SSO_HUB_URL=https://auth.elips.id
   SESSION_COOKIE=elips_session
   SESSION_DOMAIN=.elips.id
   SESSION_SECURE_COOKIE=true
   SESSION_LIFETIME=120
   PERMISSION_CACHE_STORE=shared
   APP_PORT=8085            # port belum terpakai
   ```
   Buat database MySQL-nya: `docker exec -it shared-mysql mysql -uroot -p -e "CREATE DATABASE nama_db"`.
9. **Tunnel & DNS**: tambah ingress `nama-app.elips.id → http://localhost:<APP_PORT>` **di atas** baris `http_status:404`, lalu `cloudflared tunnel route dns <tunnel> nama-app.elips.id` dan `sudo systemctl restart cloudflared` (lihat [4](#4-setting-cloudflared-tunnel)).
10. **Deploy**: push ke `master`. Setelah sukses, sekali saja:
    ```bash
    docker exec <app>-app php artisan db:seed --class=AppAccessSeeder --force
    ```
11. **Cek**: buka `https://nama-app.elips.id/login` → harus diarahkan ke `auth.elips.id/login?redirect=...`. Bila sudah login di app lain, app baru langsung terbuka.

### B. Project baru tanpa SSO (standalone)

Dipakai bila app di luar `*.elips.id` atau sengaja tidak ikut login bersama. App menjalankan halaman login/register/verifikasi/reset password sendiri (mode hub), tetapi akun tetap di tabel users Supabase.

1. **Clone & install**: sama dengan langkah A.1, lalu `php artisan key:generate` (APP_KEY milik app sendiri).
2. **`.env` lokal**
   ```env
   APP_NAME=nama-app
   ACCESS_APP=namaapp
   SSO_HUB=true                       # halaman auth ada di app ini
   SESSION_COOKIE=namaapp_session     # jangan elips_session
   SESSION_DOMAIN=null
   ACCESS_AUTO_GRANT=false            # opsional: akses hanya dari admin
   TURNSTILE_SITE_KEY=                # kosongkan di lokal
   TURNSTILE_SECRET_KEY=
   ```
3. **Kosongkan daftar app hub**: di `config/sso.php` set `'apps' => []` supaya dashboard tidak menampilkan kartu app Elips dan halaman Users/Roles hanya mengelola app ini sendiri.
4. **Migration & seeder, sesuaikan app**: sama dengan langkah A.4 dan A.5.
5. **GitHub**: sama dengan langkah A.7.
6. **VPS**: `.env` produksi seperti [3.2](#32-update-file-env-setting-per-project), dengan:
   ```env
   APP_KEY=                 # key baru milik app ini
   SSO_HUB=true
   SESSION_COOKIE=namaapp_session
   SESSION_DOMAIN=null      # atau domain app itu sendiri
   SESSION_SECURE_COOKIE=true
   MAIL_MAILER=mailgun      # wajib: email verifikasi & reset password dikirim dari app ini
   MAILGUN_DOMAIN=
   MAILGUN_SECRET=
   MAIL_FROM_ADDRESS=
   TURNSTILE_SITE_KEY=      # widget Turnstile dengan hostname domain app
   TURNSTILE_SECRET_KEY=
   APP_PORT=8085
   ```
7. **Tunnel, deploy, seeder**: sama dengan langkah A.9 dan A.10. Bila domain bukan `*.elips.id`, tambahkan hostname-nya di widget Turnstile.
8. **Cek**: `https://domain-app/login` menampilkan form login milik app (tidak redirect ke `auth.elips.id`).

## Instalasi (Local Development)

```bash
git clone https://github.com/darojatun-hogi/core.git nama-project-baru
cd nama-project-baru

composer install
bun install

cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env`:

```env
APP_NAME=NamaProjectBaru
ACCESS_APP=namaapp            # huruf kecil, dipakai sebagai awalan role/permission

DB_HOST=shared-mysql
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=

# Lokal: Postgres dari shared-services. Produksi: isi kredensial Supabase (session pooler) dan SUPABASE_DB_SSLMODE=require
SUPABASE_DB_HOST=shared-postgres
SUPABASE_DB_PORT=5432
SUPABASE_DB_DATABASE=postgres
SUPABASE_DB_USERNAME=postgres
SUPABASE_DB_PASSWORD=
SUPABASE_DB_SSLMODE=disable

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

Pastikan container `shared-postgres` (folder `shared-services`) berjalan, lalu jalankan migration & seeder:

```bash
php artisan migrate --seed
```

`migrate` membuat tabel bisnis di MySQL dan hanya membuat tabel pusat di Postgres bila belum ada. `--seed` menjalankan `AppAccessSeeder` (role & permission app ini) dan `UserSeeder` (akun admin, hanya bila tabel users masih kosong).

### Saat app pertama kali dipasang di produksi

Tabel users di Supabase sudah berisi akun, jadi cukup:

```bash
php artisan migrate --force
php artisan db:seed --class=AppAccessSeeder --force
```

Akun `super-admin` langsung bisa masuk. Untuk akun lain, isi `APP_ADMIN_EMAIL` sebelum seeding, atau beri role `<app>.admin`/`<app>.user` dari halaman Users app lain yang sudah punya akses. Pastikan image Docker memasang `pdo_pgsql` (sudah ada di `docker/php/Dockerfile`).

Jalankan development server:

```bash
php artisan serve
bun run dev
```

Menjalankan test (koneksi Supabase diganti SQLite in-memory di `tests/TestCase.php`, tidak menyentuh data pusat). Bila PHP lokal belum memuat `pdo_sqlite`/`gd`, jalankan PHPUnit langsung dengan flag ekstensi:

```bash
php -d extension=pdo_sqlite -d extension=gd vendor/bin/phpunit
```

**Akun default setelah seeding** (hanya dibuat bila tabel users di Postgres masih kosong, mis. dev lokal pertama kali):
| Role | Email | Password |
|---|---|---|
| super-admin | admin@example.com | admin123 |

> Ganti password ini segera. Di produksi akun ini tidak dibuat karena tabel users pusat sudah berisi.

## Struktur Permission

Permission didaftarkan oleh `database/seeders/AppAccessSeeder.php`, mengikuti pola `resource.action`:

```
<app>.access                                   # syarat login ke app ini
<app>.<modul>.view|create|edit|delete          # permission fitur app (isi $appPermissions)
users.*, roles.*, permissions.*, activity-log.view   # identitas bersama semua app
```

| Role | Isi |
|---|---|
| `super-admin` | Semua akses di semua app (lewat `Gate::before`) |
| `<app>.admin` | `<app>.access` + semua permission app + identitas bersama; di hub hanya mengelola app miliknya |
| `<app>.user` | `<app>.access` + permission di `$userPermissions`; diberikan otomatis saat user pertama kali membuka app (`ACCESS_AUTO_GRANT=true`) |

## Menambah Modul CRUD Baru

Pola CRUD di boilerplate ini mengikuti struktur standar berikut (contoh nama modul: `Item`):

1. **Migration & Model**
   ```bash
   php artisan make:model Item -mcr
   ```
   Tambahkan trait `LogsActivity` di model bila ingin modul ini tercatat di Activity Log:
   ```php
   use Spatie\Activitylog\LogOptions;
   use Spatie\Activitylog\Traits\LogsActivity;
   use Spatie\Activitylog\Models\Activity;

   class Item extends Model
   {
       use LogsActivity;

       // Wajib: data bisnis di MySQL (lihat Auth Terpusat)
       protected $connection = 'mysql';

       public function getActivitylogOptions(): LogOptions
       {
           return LogOptions::defaults()
               ->logOnly(['name']) // sesuaikan field yang dipantau
               ->logOnlyDirty()
               ->dontSubmitEmptyLogs();
       }

       public function tapActivity(Activity $activity, string $eventName): void
       {
           $activity->description = match ($eventName) {
               'created' => "Item \"{$this->name}\" was created",
               'updated' => "Item \"{$this->name}\" was updated",
               'deleted' => "Item \"{$this->name}\" was deleted",
               default   => $activity->description,
           };
       }
   }
   ```

   Di migration, kolom pemilik data cukup `$table->foreignId('user_id')->index();` tanpa `constrained()`.

2. **Form Request** — buat `ItemStoreRequest` dan `ItemUpdateRequest` untuk validasi.

3. **Controller** — implementasikan `index` (dengan search + pagination), `create`, `store`, `edit`, `update`, `destroy` mengikuti pola resource controller.

4. **Routes**
   ```php
   // Di dalam grup yang sudah ada: Route::middleware(['auth', 'app.access', 'verified'])
   Route::resource('items', ItemController::class);
   ```

5. **View** — buat `index.blade.php`, `create.blade.php`, `edit.blade.php` di `resources/views/items/`, `@extends('layouts.dashboard.master')`. Untuk tombol delete di tiap baris tabel, cukup:
   ```blade
   <button type="button" class="btn btn-error btn-xs sm:btn-sm"
       data-overlay="#confirm-delete-modal"
       data-delete-url="{{ route('items.destroy', $item) }}">
       Delete
   </button>
   ```
   Lalu tempel **satu** modal `#confirm-delete-modal` + form `#confirm-delete-form` di bagian bawah `index.blade.php` (contoh strukturnya bisa dicontek dari `resources/views/users/index.blade.php`) — form action-nya akan otomatis di-set oleh JS global di layout, tidak perlu bikin modal baru per baris data.

6. **Permission** — tambahkan ke `$appPermissions` di `AppAccessSeeder` (tanpa awalan, otomatis jadi `<app>.items.view`), lalu jalankan `php artisan db:seed --class=AppAccessSeeder`:
   ```php
   private array $appPermissions = ['items.view', 'items.create', 'items.edit', 'items.delete'];
   private array $userPermissions = ['items.view'];   // bila user biasa boleh melihat
   ```
   Di policy/Blade pakai nama lengkapnya, mis. `$user->can(config('access.app').'.items.view')` atau `@can('namaapp.items.view')`. Di app SSO, permission ini lalu dibagikan ke role lewat halaman Roles di hub (`auth.elips.id/roles?app=namaapp`).

7. **Sidebar Menu**
   ```blade
   @can(config('access.app').'.items.view')
       <li>
           <a href="{{ url('/items') }}" @class(['active' => request()->is('/items*')])>
               <span class="icon-[tabler--icon-name] size-5"></span>
               <span class="overlay-minified:hidden">Items</span>
           </a>
       </li>
   @endcan
   ```

---

# Deployment Environment Setup

Dokumentasi setup environment untuk deployment project (Local, Git, dan Server).

## Daftar Isi

- [1. Local Environment Setting](#1-local-environment-setting)
- [2. Git Environment Setting](#2-git-environment-setting)
- [3. Server Environment Setting](#3-server-environment-setting)
- [4. Setting Cloudflared Tunnel](#4-setting-cloudflared-tunnel)

---

## 1. Local Environment Setting

### 1.1 Copy File Berikut (Setting per Project)

- `.github/workflows/deploy.yml`
- `docker/nginx/default.conf`
- `docker/php/Dockerfile`
- `docker/php/php.prod.ini`
- `.dockerignore`
- `docker-compose.yml`
- `docker-compose.prod.yml`
- `vite.config.js`
- `resources/css/app.css`
- `resources/js/app.js`

### 1.2 Setup File `.env` (Setting per Project)

```env
APP_NAME=
APP_KEY=            # SSO: salin dari app lain; standalone: php artisan key:generate
ACCESS_APP=         # nama app, huruf kecil
SSO_HUB=false       # lihat "Membuat Project Baru dari Core"

DB_HOST=shared-mysql
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=root
DB_PASSWORD=
```

### 1.3 Setup `docker-compose.prod.yml`

Format `image` pada file `docker-compose.prod.yml` adalah:

```
image: ghcr.io/{username-github}/{nama-project}-app:latest
```

### 1.4 Setup `.github/workflows/deploy.yml`

- Jika server menggunakan processor **ARM**, tidak perlu diubah.
- Jika server menggunakan processor **AMD**, ubah data berikut:
  - Hapus bagian `name` dan `uses` untuk `QEMU`.
  - Ubah semua `platform` menjadi `linux/amd64`.

### 1.5 Setup Docker Container (Setting per Project)

- Pastikan ada container `shared-mysql`.
- Pastikan container `shared-mysql` berjalan di `shared-network`.

---

## 2. Git Environment Setting

### 2.1 Setup VPS Key (Setting per Server)

Tujuannya supaya GitHub bisa akses VPS.

1. Buat key baru:
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N "" -C "github-actions-deploy"
   ```

2. Tambahkan public key ke daftar yang diizinkan login:
   ```bash
   cat ~/.ssh/deploy_key.pub >> ~/.ssh/authorized_keys
   chmod 700 ~/.ssh
   chmod 600 ~/.ssh/authorized_keys ~/.ssh/deploy_key
   ```

3. Tes koneksi key (ganti `ubuntu` dengan user server kamu). Jika jawabannya `ok`, berarti koneksi berhasil:
   ```bash
   ssh -i ~/.ssh/deploy_key ubuntu@localhost echo ok
   ```

### 2.2 Setup Personal Access Token / PAT (Setting per Server)

Tujuannya supaya VPS bisa pull image dari GitHub Container Registry, karena repo GitHub bersifat private.

1. Masuk ke akun GitHub.
2. Masuk ke menu **Profile → Settings → Developer settings → Personal Access Tokens (Classic)**.
3. Klik **Generate new token → Generate new token (classic)**.
4. Verifikasi via email:
   - Copy kode yang terkirim ke email.
   - Paste kode tersebut, lalu klik **Verify**.
5. Isi **Note** dan **Expiration date** sesuai kebutuhan.
6. Pilih scope berikut:
   - `repo` (wajib jika repo berstatus private)
   - `read:packages`
7. Klik **Generate token**.
8. Simpan token yang muncul (hanya ditampilkan satu kali).

### 2.3 Isi GitHub Secrets pada Directory (Setting per Project)

1. Masuk ke directory project di GitHub.
2. Masuk ke menu **Settings**.
3. Klik **Secrets and variables → Actions**.
4. Klik **New repository secret**, lalu isi data berikut:

    | Secret | Keterangan |
    |---|---|
    | `VPS_HOST` | IP atau domain server |
    | `VPS_USER` | Username SSH di server |
    | `VPS_SSH_KEY` | Isi private key SSH (bisa dicek dengan `cat ~/.ssh/deploy_key`, lengkap dari `BEGIN` sampai `END`) |
    | `VPS_PROJECT_PATH` | Folder di server tempat `docker-compose.prod.yml` + `.env` berada |
    | `GITHUB_TOKEN` | Otomatis terisi jika step sebelumnya sudah dilakukan |

---

## 3. Server Environment Setting

### 3.1 Persiapkan Folder Project (Setting per Project)

1. Siapkan folder path project sesuai dengan yang diisi pada `VPS_PROJECT_PATH` saat mengisi GitHub secrets.
2. Isi folder tersebut dengan (copy-paste dari device lokal):
   - `docker-compose.prod.yml`
   - `.env`

### 3.2 Update File `.env` (Setting per Project)

```env
APP_NAME=
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=            # domain project
ACCESS_APP=         # nama app (awalan role/permission), sama dengan lokal

DB_HOST=shared-mysql
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=root
DB_PASSWORD=

# Auth terpusat: salin dari .env project lain di server (mis. /var/www/laravel/money/.env)
SUPABASE_DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
SUPABASE_DB_PORT=5432
SUPABASE_DB_DATABASE=postgres
SUPABASE_DB_USERNAME=
SUPABASE_DB_PASSWORD=
SUPABASE_DB_SSLMODE=require

# SSO: salin APP_KEY dari app lain dan isi nilai yang sama dengan app lain (lihat bagian SSO)
SESSION_COOKIE=elips_session
SESSION_DOMAIN=.elips.id
SESSION_SECURE_COOKIE=true
PERMISSION_CACHE_STORE=shared   # app SSO; app standalone boleh dihapus
SSO_HUB=false       # true hanya untuk hub (auth.elips.id) atau app standalone
SSO_HUB_URL=https://auth.elips.id
APP_PORT=           # port host untuk nginx, beda per app (lihat tabel di bagian SSO)

# Email & Turnstile: wajib untuk hub/standalone (SSO_HUB=true), salin dari /var/www/laravel/auth/.env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=elips.id
MAILGUN_SECRET=
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS="no-reply@elips.id"
MAIL_FROM_NAME=Elips
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
```

> Tip: cara paling aman membuat `.env` produksi baru adalah menyalin `.env` app lain di server lalu mengganti `APP_NAME`, `APP_URL`, `ACCESS_APP`, `DB_DATABASE`, dan `APP_PORT`. Pastikan file diakhiri baris kosong sebelum menambah variabel dengan `echo >>`.

Setelah deploy pertama: `docker exec <app>-app php artisan db:seed --class=AppAccessSeeder --force`.

### 3.3 Setup Docker Container (Setting per Server)

- Pastikan ada container `shared-mysql`.
- Pastikan container `shared-mysql` berjalan di `shared-network`.

### 3.4 Lanjutan Setup PAT / `GITHUB_TOKEN` (Setting per Server)

1. Jalankan perintah berikut di server kamu. **Pastikan update data di dalam `{{ }}`**:
   ```bash
   echo {{ GITHUB_TOKEN }} | docker login ghcr.io -u {{ USERNAME_GITHUB }} --password-stdin
   ```
2. Jika berhasil, responnya adalah `Login Succeeded`.

### 3.5 Cek Setelah Berhasil Setup PAT

1. Cek di directory GitHub pada tab **Actions**. Jika sukses, langkah berikut biasanya berjalan otomatis di server:
   ```bash
   docker compose -f docker-compose.prod.yml pull
   docker compose -f docker-compose.prod.yml up -d
   docker compose -f docker-compose.prod.yml ps
   ```

2. Setup awal Laravel (dilakukan sekali saja per deployment baru). `migrate` sudah dijalankan workflow deploy; yang perlu manual hanya seeder akses app:
   ```bash
   docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=AppAccessSeeder --force
   ```
   Jangan jalankan `key:generate` untuk app SSO: `APP_KEY` harus sama dengan app lain. `key:generate` hanya untuk app standalone, dan isi `APP_KEY` di `.env` sebelum deploy (bukan di dalam container, karena `.env` dibaca dari folder server).

3. Bila setelah mengubah `.env` app perlu dimuat ulang tanpa deploy:
   ```bash
   docker compose -f docker-compose.prod.yml up -d --force-recreate
   docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
   ```

4. Bila job **deploy** di GitHub Actions gagal dalam beberapa detik tanpa koneksi masuk ke VPS, periksa ulang secrets `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY` (isi ulang; GitHub tidak menampilkan nilainya), lalu push ulang atau **Re-run failed jobs**.

---

## 4. Setting Cloudflared Tunnel

1. Install `cloudflared`.
2. Login ke `cloudflared`:
   ```bash
   cloudflared tunnel login
   ```
3. Jalankan perintah untuk membuat tunnel:
   ```bash
   cloudflared tunnel create {nama-tunnel}
   ```
4. Pada path `~/.cloudflared` akan ada 2 file:
   - `{key-file}.json` (nama file berupa hash)
   - `cert.pem`
5. Buat file baru dengan nama `config.yml` yang berisi:
   ```yaml
   tunnel: {key-file} # copy dari step 3
   credentials-file: /home/{user-vps}/.cloudflared/{key-file}.json # sesuaikan user-vps & key-file dari step 3

   ingress:
     - hostname: domain-mu.com # contoh: example.com
       service: http://localhost:80
     - service: http_status:404
   ```
6. Daftarkan DNS domain dengan perintah:
   ```bash
   cloudflared tunnel route dns {nama-tunnel} {app.example.com}
   ```
7. Install `cloudflared` sebagai service (supaya tunnel bisa jalan di background):
   ```bash
   sudo cloudflared service install
   ```
8. Jalankan service `cloudflared`:
   ```bash
   sudo systemctl start cloudflared
   sudo systemctl enable cloudflared
   ```
9. Verifikasi service berjalan:
   ```bash
   sudo systemctl status cloudflared
   ```

### Catatan

1. Path pada step 5 bisa berbeda-beda tiap server. Cek lokasinya dengan:
   ```bash
   sudo systemctl status cloudflared
   ```
   Biasanya ada di salah satu lokasi berikut:
   - `/etc/cloudflared/config.yml`
   - `~/.cloudflared` atau `/home/{user-vps}/.cloudflared`

2. Port service pada step 5 (bagian kiri `service: http://localhost:80`) bisa disesuaikan dengan konfigurasi di file `docker-compose.prod.yml`.

### Menambah app baru ke tunnel yang sudah ada

Server ini sudah punya tunnel (`/etc/cloudflared/config.yml`, salinannya di `~/.cloudflared/config.yml`). Untuk app baru:

1. Backup lalu tambahkan ingress **di atas** baris terakhir `- service: http_status:404`:
   ```yaml
     - hostname: nama-app.elips.id
       service: http://localhost:8085   # APP_PORT app
   ```
2. Validasi, daftarkan DNS, dan restart:
   ```bash
   sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate
   cloudflared tunnel route dns <tunnel-id> nama-app.elips.id
   sudo systemctl restart cloudflared
   ```
3. Samakan salinan di `~/.cloudflared/config.yml` dengan `/etc/cloudflared/config.yml`.