# Elips — Laravel Boilerplate

Boilerplate Laravel untuk membangun aplikasi web dengan cepat, dilengkapi sistem autentikasi, role & permission, manajemen user, notifikasi, activity log, dan siap deploy via Docker.

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
- Login, register, reset password (via email/Mailtrap)
- 3 role default: **Super Admin**, **Admin**, **User**
- Permission granular per-aksi (`view`, `create`, `edit`, `delete`) untuk tiap modul
- Middleware proteksi di Route, Blade (`@can`), dan Policy
- Proteksi privilege escalation untuk Super Admin

### 2. User & Profile Management
- CRUD User dengan permission granular
- Multi-role assignment per user
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

DB_HOST=shared-mysql
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

Jalankan migration & seeder:

```bash
php artisan migrate --seed
```

Jalankan development server:

```bash
php artisan serve
bun run dev
```

**Akun default setelah seeding:**
| Role | Email | Password |
|---|---|---|
| Super Admin | admin@example.com | admin123 |

> Ganti password ini segera setelah setup project baru.

## Struktur Permission

Permission didefinisikan di `database/seeders/RolePermissionSeeder.php`, mengikuti pola `<modul>.<aksi>`:

```
users.view, users.create, users.edit, users.delete
roles.manage
activity-log.view
```

Role **Admin** dan **Super Admin** memiliki seluruh permission secara default. Role **User** tidak memiliki permission khusus (hanya akses dasar).

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

2. **Form Request** — buat `ItemStoreRequest` dan `ItemUpdateRequest` untuk validasi.

3. **Controller** — implementasikan `index` (dengan search + pagination), `create`, `store`, `edit`, `update`, `destroy` mengikuti pola resource controller.

4. **Routes**
   ```php
   Route::middleware('auth')->group(function () {
       Route::resource('items', ItemController::class);
   });
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

6. **Permission** — tambahkan ke `RolePermissionSeeder`:
   ```php
   'items.view', 'items.create', 'items.edit', 'items.delete',
   ```

7. **Sidebar Menu**
   ```blade
   @can('items.view')
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
APP_KEY=            # php artisan key:generate

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

DB_HOST=shared-mysql
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=root
DB_PASSWORD=
```

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

2. Setup awal Laravel (dilakukan sekali saja per deployment baru):
   ```bash
   docker compose -f docker-compose.prod.yml exec app php artisan key:generate
   docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
   ```

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
   credential-file: /home/{user-vps}/.cloudflared/{key-file}.json # sesuaikan user-vps & key-file dari step 3

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