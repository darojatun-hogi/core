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