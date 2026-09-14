# Panduan Docker + CI/CD untuk Project Laravel

Panduan ini merangkum langkah lengkap untuk men-dockerize project Laravel
(stack: Laravel, MySQL eksternal, Bun.js, FlyonUI) dari kondisi project
masih jalan lokal biasa, sampai bisa di-deploy otomatis ke VPS lewat
GitHub Actions. Bisa dipakai ulang untuk project Laravel lain dengan
stack serupa.

Asumsi dasar:
- MySQL **tidak** dijalankan di dalam project ini — sudah ada container
  MySQL terpisah (`shared-mysql`) yang bisa diakses lewat Docker network
  bernama `shared-network`.
- Web server: Nginx + PHP-FPM.
- CI/CD: GitHub Actions -> build image -> push ke GitHub Container
  Registry (GHCR) -> SSH ke VPS -> jalankan `docker compose pull && up -d`.

---

## 1. File yang perlu ditambahkan ke project Laravel

Semua file ini diletakkan di **root project Laravel** (sejajar dengan
file `artisan`), bukan di folder terpisah.

```
project-laravel-kamu/
├── app/                        <- sudah ada
├── ...                         <- file Laravel lain, sudah ada
│
├── docker/                     <- BARU
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.prod.ini
│   └── nginx/
│       └── default.conf
│
├── .github/
│   └── workflows/
│       └── deploy.yml          <- BARU
│
├── docker-compose.yml          <- BARU (untuk lokal)
├── docker-compose.prod.yml     <- BARU (untuk VPS)
└── .dockerignore                <- BARU
```

Catatan: `php.dev.ini` dan Xdebug sengaja **tidak** disertakan di sini
supaya setup tetap simpel. Bisa ditambahkan belakangan kalau memang
butuh step-debugging di IDE.

---

## 2. Isi tiap file

### `docker/php/Dockerfile`

Satu Dockerfile, banyak stage (multi-stage build):

| Stage | Fungsi |
|---|---|
| `base` | Image PHP-FPM + ekstensi yang dibutuhkan Laravel (pdo_mysql, gd, intl, dll) + Composer |
| `dev` | Dipakai lokal. Kode di-mount lewat volume, jadi stage ini polos saja |
| `assets` | Build asset frontend (Vite + FlyonUI) pakai image `oven/bun` |
| `vendor` | `composer install --no-dev` + copy seluruh source code |
| `prod-app` | Image final untuk container PHP-FPM di production (gabungan `vendor` + hasil `assets`) |
| `prod-nginx` | Image Nginx berisi folder `public/` statis saja |

Poin penting di stage `prod-app`/`prod-nginx`: keduanya sama-sama
`COPY --from=vendor` / `COPY --from=assets`, supaya isi file yang
dilayani Nginx (untuk static asset) dan yang diproses PHP-FPM tetap
konsisten satu sama lain.

### `docker/php/php.prod.ini`

Konfigurasi PHP khusus production — yang paling penting: **opcache
aktif** (`opcache.enable=1`). Tanpa ini, performa Laravel di production
bisa jauh lebih lambat dari seharusnya.

### `docker/nginx/default.conf`

Konfigurasi Nginx standar Laravel: root ke `public/`, semua request
diarahkan ke `index.php`, file `.php` diteruskan ke container `app`
lewat FastCGI di port 9000.

```nginx
location ~ \.php$ {
    fastcgi_pass app:9000;
    ...
}
```

`app` di baris itu harus **sama persis** dengan nama service PHP-FPM
di `docker-compose.yml`/`docker-compose.prod.yml`.

### `docker-compose.yml` (lokal)

3 service: `app` (PHP-FPM, kode di-mount live), `nginx`, `bun` (dev
server Vite untuk hot-reload asset). Semua tersambung ke
`shared-network` (external) supaya bisa akses `shared-mysql`.

### `docker-compose.prod.yml` (VPS)

3 service: `app`, `nginx`, `queue` (worker Laravel, image sama dengan
`app`, cuma command beda). Semua pakai `image:` (bukan `build:`) —
image jadi diambil dari registry, bukan dibuild di server.

### `.github/workflows/deploy.yml`

2 job:
1. `build-and-push` — build 2 image (app, nginx) pakai Docker Buildx,
   push ke GHCR.
2. `deploy` — SSH ke VPS, `pull` image terbaru, `up -d`, lalu jalankan
   `migrate`, `config:cache`, `route:cache`, `view:cache`.

---

## 3. Setup awal di server (sekali per server, bukan per project)

### 3.1 Pastikan `shared-network` dan `shared-mysql` sudah ada

```bash
docker network ls | grep shared-network
docker ps | grep shared-mysql
```

### 3.2 Generate SSH key khusus untuk deploy

Jangan pakai SSH key pribadi kamu sehari-hari — bikin key baru khusus
untuk GitHub Actions:

```bash
ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N "" -C "github-actions-deploy"
```

- `-N ""` = tanpa passphrase (wajib, karena proses otomatis tidak bisa
  input password).
- Hasilnya 2 file: `deploy_key` (private) dan `deploy_key.pub` (public).

Tambahkan public key ke daftar yang diizinkan login:

```bash
cat ~/.ssh/deploy_key.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys ~/.ssh/deploy_key
```

> Catatan: `authorized_keys` itu **file**, bukan folder — jangan `cd`
> ke situ, cukup `cat`/edit isinya.

Tes koneksi (jawab `yes` saat ditanya host key verification):

```bash
ssh -i ~/.ssh/deploy_key ubuntu@localhost echo ok
```

Key yang sama ini **boleh dipakai ulang** untuk project-project lain di
server yang sama — tidak perlu generate baru tiap project.

### 3.3 Buat GitHub Personal Access Token (untuk `docker login` manual)

Di https://github.com/settings/tokens -> **Generate new token (classic)**,
centang scope:
- `read:packages` (wajib, untuk pull image)
- `repo` (wajib **kalau repo kamu private** — tanpa ini, pull image
  private akan gagal dengan error `denied` walau login terlihat sukses)

---

## 4. Setup per project (diulang tiap bikin project/repo baru)

### 4.1 Cek nama branch utama repo

```bash
git branch
```

Workflow di `deploy.yml` cuma trigger untuk branch tertentu (`main` atau
`master`) — sesuaikan baris `branches: [...]` di `deploy.yml` dengan
branch utama repo kamu yang sebenarnya.

### 4.2 Isi GitHub Secrets (Repository secret, bukan Environment)

Di `https://github.com/USERNAME/NAMA-REPO/settings/secrets/actions`:

| Secret | Isi |
|---|---|
| `VPS_HOST` | IP atau domain server |
| `VPS_USER` | Username SSH di server |
| `VPS_SSH_KEY` | Isi **private key** (`cat ~/.ssh/deploy_key`), lengkap dengan baris `BEGIN`/`END` |
| `VPS_PROJECT_PATH` | Folder di server tempat `docker-compose.prod.yml` + `.env` berada |

Repository secret dipakai (bukan Environment secret) karena cuma ada
1 tujuan deploy (production) — Environment secret baru relevan kalau
ada beberapa environment (staging vs production) dengan value berbeda.

### 4.3 Siapkan folder & file di server

```bash
mkdir -p /path/project-kamu
cd /path/project-kamu
```

Taruh **manual** (scp/nano) 2 file ini di folder tersebut — file ini
**tidak** ikut ter-clone/ter-update otomatis dari GitHub, jadi tiap ada
perubahan harus di-update manual juga di server:

- `docker-compose.prod.yml`
- `.env`

Isi `.env` yang wajib disesuaikan:
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=shared-mysql
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

**Penting**: di `docker-compose.prod.yml`, ganti `image:` dengan nama
repo GHCR yang sebenarnya (huruf kecil semua), jangan biarkan
placeholder generik:
```yaml
image: ghcr.io/username-github/nama-repo-app:latest
```

### 4.4 Cek arsitektur CPU server

```bash
uname -m
```
- `x86_64` -> server amd64 (standar)
- `aarch64` -> server ARM64 (misal beberapa VPS Oracle Cloud, AWS
  Graviton)

Sesuaikan baris `platforms:` di `deploy.yml`:
```yaml
platforms: linux/amd64          # server amd64 saja
platforms: linux/arm64          # server arm64 saja
platforms: linux/amd64,linux/arm64   # dua-duanya (build lebih lambat)
```

Build khusus ARM di GitHub Actions **jauh lebih lambat** dari amd64
(bisa 3-5x, karena di-emulasi lewat QEMU, bukan native) — ini normal,
bukan tanda ada yang salah.

### 4.5 Login GHCR di server (sekali per server, ulangi kalau token expired)

```bash
echo TOKEN_KAMU | docker login ghcr.io -u USERNAME_GITHUB --password-stdin
```

### 4.6 Push dan pantau

```bash
git push origin nama-branch-utama
```

Pantau di tab **Actions** repo GitHub. Setelah sukses, di server:

```bash
docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml ps
```

Setup awal Laravel (sekali saja per deployment baru):
```bash
docker compose -f docker-compose.prod.yml exec app php artisan key:generate
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

---

## 5. Pengaturan port

Port diatur di **dua tempat** yang harus selaras:

- `docker/nginx/default.conf` -> `listen PORT;` (port di **dalam**
  container, butuh rebuild image kalau diubah)
- `docker-compose.prod.yml` -> `ports: ["HOST:CONTAINER"]` (pemetaan
  dari luar ke dalam container, **tidak perlu rebuild**, cukup restart)

Contoh: mau akses lewat `http://server:8080` tapi Nginx di dalam
container tetap standar (`listen 80;`):
```yaml
ports:
  - "8080:80"
```
Ini lebih simpel — ganti port tanpa perlu trigger build ulang image.

Jangan lupa buka port yang dipakai di firewall/security group cloud
provider (AWS Security Group, GCP Firewall, Oracle Cloud Ingress Rules,
dll) — kesalahan koneksi paling sering justru dari sini, bukan dari
Docker.

---

## 6. Troubleshooting cepat

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| `pecl install xdebug` gagal saat build | Base image Alpine tidak punya compiler | Tambahkan `$PHPIZE_DEPS` sebelum `pecl install`, atau skip Xdebug sama sekali |
| `cd: Not a directory` di `authorized_keys` | `authorized_keys` itu file, bukan folder | Pakai `cat`, bukan `cd` |
| `Host key verification failed` | Prompt "yes/no" saat SSH pertama kali tidak terjawab | Jalankan ulang, ketik `yes` saat ditanya |
| Workflow tidak jalan sama sekali setelah push | Push ke branch yang beda dari yang di-trigger `deploy.yml` | Cek `git branch`, samakan dengan `branches: [...]` di workflow |
| `image ... not found` dengan nama `owner/repo` literal | Variabel `github.repository` / `GITHUB_REPOSITORY` tidak tersedia di server (cuma ada di dalam runner GitHub Actions) | Hardcode nama image asli di `docker-compose.prod.yml` |
| `error from registry: denied` saat `docker pull` | Token GHCR kurang scope `repo` untuk package private | Generate token baru dengan scope `read:packages` + `repo` |
| `no matching manifest for linux/arm64/v8` | Image dibuild cuma untuk amd64, server ternyata ARM | Tambahkan `platforms: linux/arm64` (dan `setup-qemu-action`) di workflow |
| Build ARM di GitHub Actions lambat (>15 menit) | Build ARM di runner amd64 pakai emulasi QEMU | Normal, bukan bug — build kedua & seterusnya lebih cepat karena cache layer |

---

## 7. Checklist ringkas untuk project baru

- [ ] Copy folder `docker/`, `.github/`, file `docker-compose*.yml`,
      `.dockerignore` ke root project Laravel baru
- [ ] Cek nama branch utama (`main` atau `master`), samakan dengan
      `deploy.yml`
- [ ] Hardcode nama image GHCR yang benar di `docker-compose.prod.yml`
- [ ] Cek arsitektur CPU server (`uname -m`), sesuaikan `platforms:`
      di `deploy.yml`
- [ ] Isi 4 GitHub Secrets di repo baru (`VPS_HOST`, `VPS_USER`,
      `VPS_SSH_KEY`, `VPS_PROJECT_PATH`) — boleh pakai SSH key yang
      sama dengan project sebelumnya
- [ ] Siapkan folder di server, taruh `docker-compose.prod.yml` +
      `.env` manual di situ
- [ ] Pastikan port yang dipakai project baru tidak bentrok dengan
      project lain di server yang sama, dan sudah dibuka di firewall
- [ ] Push, pantau tab Actions, verifikasi container jalan dengan
      `docker compose -f docker-compose.prod.yml ps`