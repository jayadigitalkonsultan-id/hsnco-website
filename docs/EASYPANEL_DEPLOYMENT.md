# Deploy HSNCO Laravel ke EasyPanel

Audit 13 Oktober 2026: Laravel **12.69.3** terkunci dalam composer.lock, composer.json meminta PHP ^8.2. Runtime Docker memakai PHP **8.3 / FrankenPHP**, Debian Bookworm, mode classic (tanpa Octane/worker). Composer production diinstal dari lock dengan --no-dev/--no-scripts; paket extension diperiksa melalui composer check-platform-reqs. Base image versi-family dapat diganti dengan digest terverifikasi melalui FRANKENPHP_IMAGE untuk pin immutable. Composer binary berasal dari official composer:2 stage.

Frontend tujuh halaman memakai **public/assets**, Bootstrap 5.3.8, CSS/JS lokal, fonts/gambar/logo, jQuery 3.7.1 dan jsTree 3.3.17 pada sitemap. Ada package/Vite/Tailwind scaffold, tetapi layout HSNCO tidak memanggil @vite. Karena itu Dockerfile tidak memasang Node/npm atau menjalankan Vite. Seluruh public/assets ikut image; aset Bootstrap tidak dimuat dua kali.

## Source dan Build

Buat App Service di EasyPanel:

- Source: GitHub, repository **jayadigitalkonsultan-id/hsnco-website**, branch **main**.
- Build: **Dockerfile**, path **Dockerfile**, build context root repository **.**.
- Final target: production (stage terakhir/default). Startup command/entrypoint override dikosongkan agar konfigurasi image dipakai.
- Commit/push file konfigurasi di project ini ke main sebelum memilih Deploy. Pekerjaan lokal ini belum melakukan push GitHub atau deployment VPS.

Dockerfile: shared PHP extension stage, Composer binary stage, production dependency/source stage, runtime non-root UID/GID **1000:1000**. Document root **/app/public**. Web process foreground FrankenPHP menjadi PID 1 melalui exec; SIGTERM diteruskan untuk shutdown.

## Port dan Domain

**Port container: 8080 TCP, protocol HTTP, bind semua interface (:8080).** EasyPanel domain:

- Public hostname: **hsncoatlaw.com**, path **/**.
- Internal protocol **HTTP**, target port **8080**.
- Enable HTTPS/certificate pada EasyPanel; DNS A record domain diarahkan ke IP VPS. Tambahkan AAAA hanya bila IPv6 VPS/proxy benar-benar tersedia.
- TLS diterminasi EasyPanel. Caddy dalam container tidak menerbitkan sertifikat atau listen 443.

Bootstrap middleware mendukung environment TRUSTED_PROXIES. Nilai * sesuai penggunaan container hanya melalui jaringan/proxy privat EasyPanel. Jika alamat/CIDR proxy dapat dipastikan, isi daftar comma-separated sebagai gantinya. Jangan expose port aplikasi langsung ke jaringan publik ketika mempercayai semua proxy. Forwarded proto/port membuat route()/asset() menghasilkan https tanpa mixed content. Host normal diteruskan oleh proxy; X-Forwarded-Host tidak dipercaya.

## Environment production

Isi di Environment EasyPanel, bukan GitHub/Dockerfile. Template non-secret tersedia di docker/easypanel.env.example; template ini tidak disalin ke image.

```dotenv
APP_NAME=HSNCO
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hsncoatlaw.com
ASSET_URL=https://hsncoatlaw.com
APP_KEY=<APP_KEY_EXISTING_YANG_VALID>
LOG_CHANNEL=stderr
LOG_LEVEL=warning
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=sync
TRUSTED_PROXIES=*
```

APP_KEY adalah secret. Pakai key production yang sudah ada, simpan aman, dan gunakan key yang sama setiap redeploy/rollback. Entrypoint menolak start jika APP_KEY kosong; tidak ada key:generate otomatis. Untuk deployment pertama yang benar-benar belum punya key, buat satu kali di terminal pribadi (misalnya php artisan key:generate --show), simpan hasilnya di EasyPanel, dan jangan ulangi pada deployment berikutnya. Jangan menyalin .env lokal ke repository atau image.

Untuk frontend statis ini, session/cache file dan queue sync tidak memerlukan DB. PDO MySQL/SQLite tersedia; file SQLite lokal tidak masuk image. Bila CMS membutuhkan database, buat service MySQL EasyPanel dan isi DB_CONNECTION=mysql, DB_HOST=<hostname service>, DB_PORT=3306, DB_DATABASE/DB_USERNAME/DB_PASSWORD lewat editor secret. Database tidak dibuat/diubah oleh startup. Migration hanya langkah manual terpisah setelah backup dan approval deployment terkait. File session tidak membutuhkan session database table.

Environment disediakan saat runtime. Jangan menambahkan ARG APP_KEY/DB_PASSWORD atau menanam secret dalam build layer. Caches config/routes/views dibuat **saat startup** setelah environment tersedia, bukan saat build. Bootstrap/cache tetap ephemeral dan tidak disimpan dalam volume agar environment/code cache tidak stale setelah redeploy. Jangan mengaktifkan pembuatan .env jika environment injection sudah dipakai; .env tidak dibutuhkan image ini.

## Storage dan permission

Tambahkan EasyPanel named volume, misalnya **hsnco-storage**, mount path **/app/storage**. Direktori ini menampung uploads, sessions, file cache dan logs. Public storage link telah dibuat di image: /app/public/storage -> /app/storage/app/public. Volume Docker baru biasanya dipopulasi dari direktori image dengan ownership UID 1000. Jika memakai bind mount atau volume lama, pastikan owner writable UID/GID 1000:1000; lakukan koreksi permission volume secara manual lewat maintenance/admin jika perlu. Entrypoint tidak menjalankan root chown dan tidak memakai chmod 777.

Jangan mount volume di /app atau /app/public/assets karena akan menutupi code/assets image. Tidak perlu volume untuk bootstrap/cache, Caddy certificates atau database SQLite frontend ini. Storage penting perlu backup; gunakan satu replica jika memakai file sessions/cache pada local volume. Untuk beberapa replica, rancang shared cache/session dan uploads terlebih dahulu.

Laravel storage serta bootstrap/cache diberi ownership writable ketika build. Runtime hanya writable pada area tersebut serta /data/caddy dan /config/caddy; code tetap root-owned. Caddy memblokir dot paths dan PHP scripts di public upload path.

## Startup dan health check

Entrypoint memeriksa APP_KEY, menyiapkan direktori writable, menjalankan config:cache, route:cache, view:cache, lalu exec FrankenPHP. **Tidak menginstal dependency, generate key, migrate, seed atau mengubah database** saat startup.

Docker health check: HTTP GET **http://127.0.0.1:8080/up**, interval 30s, timeout 5s, start period 60s, retries 3. Endpoint /up adalah Laravel health route existing; bukan pengecekan readiness database. Jika EasyPanel menawarkan health probe terpisah, pilih HTTP /up ke port 8080. Logs Laravel dan access logs menuju stderr/stdout sehingga dapat dibaca di EasyPanel Logs.

## Verifikasi di mesin dengan Docker

Dari root checkout:

```sh
docker build --pull --target production -t hsnco-website:local .
```

Simpan runtime secrets dalam file privat di luar repository (misalnya /secure/hsnco-runtime.env) atau lewat secret manager, kemudian:

```sh
docker run -d --name hsnco-web -p 127.0.0.1:8080:8080 --env-file /secure/hsnco-runtime.env -v hsnco-storage:/app/storage hsnco-website:local
docker logs hsnco-web
docker inspect --format '{{json .State.Health}}' hsnco-web
curl -f http://127.0.0.1:8080/up
curl -f -H 'Host: hsncoatlaw.com' -H 'X-Forwarded-Proto: https' -H 'X-Forwarded-Port: 443' http://127.0.0.1:8080/
docker exec hsnco-web php artisan route:list
docker exec hsnco-web id
docker exec hsnco-web php -r 'exit(is_writable("/app/storage") && is_writable("/app/bootstrap/cache") ? 0 : 1);'
```

Periksa image tidak mempunyai .env/cache lokal/SQLite:

```sh
docker run --rm --entrypoint sh hsnco-website:local -c 'test ! -e /app/.env && test ! -e /app/database/database.sqlite'
```

Setelah Deploy EasyPanel: buka https://hsncoatlaw.com/up, Home dan internal pages, cek assets/modal/mobile menu, pastikan HTTPS URLs dan session cookie Secure. Port 4180 adalah preview Laragon lama; **bukan port container production**.

## Validasi yang sudah dilakukan dan batas

Docker CLI tidak tersedia di environment Laragon ini, sehingga Docker build/run, Caddy parsing, PHP 8.3 image dan runtime volume ownership **belum diuji langsung**. Jangan anggap image telah build sukses sebelum menjalankan perintah di atas pada Docker/EasyPanel. Syntax PHP dan sh entrypoint telah diperiksa. Laravel tests: **6 passed, 114 assertions**, termasuk forwarded HTTPS/domain serta semua tests frontend existing. Tidak ada migration, database production access, APP_KEY regeneration atau push/deploy otomatis.

Rujukan: [FrankenPHP Docker](https://frankenphp.dev/docs/docker/), [EasyPanel App Service](https://easypanel.io/docs/services/app), [EasyPanel Builders](https://easypanel.io/docs/builders).
Form kontak tetap berupa validasi frontend demo; Docker deployment ini tidak menambahkan backend email/CMS. Konten placeholder dari template tetap memerlukan konten resmi sebelum publikasi.


## Perbaikan asset HTTPS

Lihat PRODUCTION_ASSET_FIX.md. Production URL generation kini mengikuti APP_URL HTTPS; ASSET_URL opsional memakai root origin tanpa /assets. Docker final stage memverifikasi manifest aset sebagai user non-root. Startup menolak origin HTTP production. Validasi terbaru: 10 tests / 562 assertions; Docker build belum dijalankan karena Docker CLI tidak tersedia.
