# Local environment setting :
## Copy file - file berikut : (Setting per project)
  a. .github/workflows/deploy.yml
  b. docker/nginx/deafult.conf
  c. docker/php/Dockerfile
  d. docker/php/php.prod.ini
  e. .dockerignore
  f. docker-compose.yml
  g. docker-compose.prod.yml

  h. vite.config.js
  i. resources/css/app.css
  j. resources/js/app.js

## Setup file .env (Setting per project)
  APP_NAME=
  APP_KEY= (php artisan key:generate)

  DB_HOST=shared-mysql
  DB_PORT=3306
  DB_DATABASE=
  DB_USERNAME=root
  DB_PASSWORD= 

## Setup docker-compose.prod.yml
  Format `image` pada file `docker-compose.prod.yml` adalah : 
  `image: ghcr.io/{username-github}/{nama-project}-app:latest`

## Setup ./github/workflows/deploy.yml
  - Jika server menggunakan processor `ARM`, maka tidak perlu diganti.
  - Jika server menggunakan processor `AMD`, maka ubah data berikut :
    - Hapus `name` dan `uses` `QEMU`
    - Ubah semua `platform` jadi `linux/amd64`

## Setup docker container (Setting per project)
  a. Pastikan ada container shared-mysql
  b. Pastikan container shared-mysql berjalan di shared-network

# Git environment setting :
## Setup VPS key (Tujuannya supaya github bisa akses VPS) (Setting per server)
  a. Buat key baru
      ```bash
        ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N "" -C "github-actions-deploy"
      ```
  b. Tambahkan public key ke daftar yang diizinkan login:
      ```bash
        cat ~/.ssh/deploy_key.pub >> ~/.ssh/authorized_keys
        chmod 700 ~/.ssh
        chmod 600 ~/.ssh/authorized_keys ~/.ssh/deploy_key
      ```
  c. Tes koneksi key, jika jawaban "ok" berarti koneksi berhasil (ganti "ubuntu" dengan user server mu)
      ```bash
        ssh -i ~/.ssh/deploy_key ubuntu@localhost echo ok
      ```
## Setup personal access token (PAT) (Tujuannya supaya VPS bisa pull image dari docker github, karena repo github bersifat private) (Setting per server)
  a. Masuk ke akun github
  b. Masuk ke menu Profile -> Credentials -> Personal Access Token (Classic)
  c. Klik generate new token -> generate new token (classic)
  d. Klik verify via email
  e. Copy code yang terkirim di email
  f. Paste kode dan klik verify
  g. Isi note dan expiration date sesuai kebutuhan
  h. isi scope
    - repo (wajib jika repo berstatus private)
    - read:packages
  i. klik generate token
  j. simpan code yang tampil

## Isi github secrets pada directory (Setting per project)
  a. Masuk ke directory project
  b. Masuk ke menu settings
  c. Klik secret & variables -> Actions
  d. Create new repository secret, data yang diinput meliputi :
    - VPS_HOST (IP atau domain server)
    - VPS_USER (username SSH di server)
    - VPS_SSH_KEY (isi private key SSH, bisa dicek dengan perintah `cat ~/.ssh/deploy_key` lengkap dari `BEGIN`/`END`)
    - VPS_PROJECT_PATH (Folder di server, tempat `docker-compose.prod.yml` + `.env` berada)
    - GITHUB_TOKEN (Otomatis terisi jika step sebelumnya sudah dilakukan)

# Server environment setting :
## Persiapkan folder project (Setting per project)
  a. Persiapkan folder path project sesuai dengan yang diisi pada VPS_PROJECT_PATH saat isi github secrets
  b. Isi folder tersebut dengan (copy - paste dari device lokal):
    - docker-compose.prod.yml
    - .env

## Update file .env (Setting per project)
  APP_NAME=
  APP_ENV=production
  APP_KEY=
  APP_DEBUG=false
  APP_URL= (domain project)

  DB_HOST=shared-mysql
  DB_PORT=3306
  DB_DATABASE=
  DB_USERNAME=root
  DB_PASSWORD= 

## Setup docker container (Setting per server)
  a. Pastikan ada container shared-mysql
  b. Pastikan container shared-mysql berjalan di shared-network

## lanjutan setup Personal Access Token (PAT) / GITHUB_TOKEN (Setting per server)
  a. Ketik perintah berikut pada server kamu. PASTIKAN UPDATE data didalam {{  }}
    ```bash
      echo {{ GITHUB_TOKEN }} | docker login ghcr.io -u {{ USERNAME_GITHUB }} --password-stdin
    ```
  b. Jika berhasil, responnya adalah Login Succeeded

## Cek setelah berhasil setup Personal Access Token (PAT) 
  a. Cek di directory github pada tab `Actions`, jika sukses, lakukan di server (defaultnya otomatis jalan sendiri):
  ```bash
    docker compose -f docker-compose.prod.yml pull
    docker compose -f docker-compose.prod.yml up -d
    docker compose -f docker-compose.prod.yml ps
  ```
  b. Setup awal Laravel (sekali saja per deployment baru):
  ```bash
    docker compose -f docker-compose.prod.yml exec app php artisan key:generate
    docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
  ```

## Setting cloudflared tunnel
  1. Install cloudflared tunnel
  2. Login cloudflared tunnel
    ```bash
      cloudflared tunnel login
    ```
  3. Jalankan perintah untuk membuat cloudflared tunnel : 
    ```bash
      cloudflared tunnel create {nama-tunnel}
    ```
  4. pada path `~/.cloudflared` akan ada 2 file, yaitu :
    - key-file.json (namanya berupa hash)
    - cert.pem
  5. buat file baru dengan nama `config.yml` yang berisi data :
    tunnel: {key-file} (copy-key file dari step 2)
    credential-file: /home/{user-vps}/.cloudflared/{key-file}.json (pastikan user-vps kamu sesuaikan, dan key-file copy dari nama file step 2)
    ingress : 
      - hostname: domain-mu (example.com)
        service: http://localhost:80
      - service: http_status:404
  6. Daftarkan dns domain dengan cara : 
    ```bash
      cloudflared tunnel route dns {nama-tunnel} {app.example.com}
    ```
  7. Install cloudflared service (supaya tunnel bisa jalan secara background)
    ```bash
      sudo cloudflared service install
    ```
  8. Jalankan service cloudflared tunnel
    ```bash
      sudo systemctl start cloudflared
      sudo systemctl enable cloudflared
    ```
  9. Verifikasi cloudflare service berjalan
    ```
      sudo systemctl status cloudflared
    ```
  
  ### Note: 
  1. Path untuk step nomor 5 bisa berubah bisa cek dengan perintah : 
    - `sudo systemctl status cloudflared`
    biasanya ada di : 
    - `/etc/cloudflared/config.yml`
    - `~/.cloudflared` atau `/home/{user-vps}/.cloudflared`
  2. port service pada step 5 bisa disesuaikan di file docker-compose.prod.yml (sebelah kiri)