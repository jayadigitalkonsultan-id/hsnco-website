# Production asset loading: penyebab dan perbaikan

Audit 13 Oktober 2026, read-only terhadap https://hsncoatlaw.com.

## Temuan terverifikasi

Homepage live merespons HTTP 200 tetapi menghasilkan **24 URL aset unik memakai HTTP**. Halaman utama memakai HTTPS. Bootstrap CSS dan style.css langsung merespons HTTP 200 dengan Content-Type text/css ketika diminta melalui HTTPS. Pemeriksaan seluruh **56 aset HTTPS** menghasilkan HTTP 200 dan bukan HTML error page. Bukti ada di PRODUCTION_ASSET_REPORT.json. Ini menunjukkan mixed content dalam URL generation; tidak ada bukti asset fisik hilang. Nilai environment/container VPS tidak dapat diinspeksi dari lingkungan lokal, jadi penyebab konfigurasi proxy/environment spesifik tidak diklaim pasti.

Dockerfile sudah menyalin public/ pada dependencies stage dan menyalin /app lengkap ke production stage. Document root Caddy adalah /app/public. Blade head/scripts/images memakai asset(), navigation memakai route(), dan CSS font/background references valid relatif terhadap file CSS. Tidak ada @vite pada layout frontend HSNCO atau duplikasi Bootstrap. Caddy/root dan desain tidak perlu diganti.

## Source yang diperbaiki

- AppServiceProvider: ketika APP_ENV=production dan APP_URL valid HTTPS, generated route URLs memakai origin tersebut dan HTTPS, sekalipun request dari proxy ke container berupa HTTP tanpa forwarded headers. Asset origin memakai ASSET_URL bila tersedia, atau APP_URL sebagai fallback.
- config/app.php: menambahkan app.asset_url dari ASSET_URL agar asset() menerima konfigurasi eksplisit, termasuk domain CDN jika diperlukan.
- docker/easypanel.env.example: menambahkan ASSET_URL=https://hsncoatlaw.com. ASSET_URL adalah root origin, **bukan https://hsncoatlaw.com/assets**, karena asset() menambahkan assets/... sendiri.
- docker/entrypoint.sh: fail-fast pada APP_URL/ASSET_URL production yang bukan valid HTTPS. Cache config/routes/views tetap dibuat setelah environment runtime masuk. Tidak menjalankan migration, key generation, cache data clear, atau DB commands.
- Dockerfile: read permissions direktori asset 755 dan file 644; manifest hash diperiksa dalam **final stage sebagai UID 1000**. Tidak memakai 777 atau write permission pada asset publik.
- docker/check-assets.php + assets-manifest.json: memeriksa semua aset final tidak hilang, kosong, berubah atau tidak terbaca.
- tools/update-asset-manifest.mjs: regenerasi manifest ketika isi public/assets memang diubah.
- tests/Feature/ProductionAssetsTest.php: generated HTTPS URLs seluruh halaman tanpa proxy headers, ASSET_URL terpisah, localhost HTTP tetap berfungsi, file/hash dan CSS URL references, satu Bootstrap per halaman.

Tidak ada perubahan .env lokal, konfigurasi DB/auth, data production, tampilan HTML/CSS, atau paket frontend. APP_KEY lokal tidak disalin ke file deployment.

## Push dan redeploy

Dari C:\laragon\www\hsnco-website, tinjau diff lalu jalankan:

```sh
git add Dockerfile app/Providers/AppServiceProvider.php config/app.php docker/entrypoint.sh docker/easypanel.env.example docker/check-assets.php docker/assets-manifest.json tests/Feature/ProductionAssetsTest.php tools/update-asset-manifest.mjs docs/PRODUCTION_ASSET_FIX.md docs/PRODUCTION_ASSET_REPORT.json docs/EASYPANEL_DEPLOYMENT.md
git commit -m "Fix production HTTPS asset loading and validate final image assets"
git push origin main
```

Di EasyPanel App Service, tetapkan sebelum rebuild/redeploy:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hsncoatlaw.com
ASSET_URL=https://hsncoatlaw.com
TRUSTED_PROXIES=*
```

Gunakan APP_KEY production existing; jangan menggantinya. TRUSTED_PROXIES=* digunakan hanya bila service berada di jaringan privat/proxy EasyPanel. Pertahankan environment DB/auth yang sudah digunakan production. Domain -> internal HTTP port 8080. Pastikan tidak ada volume yang menutupi /app, /app/public atau /app/public/assets. Deploy commit main terbaru; entrypoint otomatis membangun ulang **configuration caches**, tanpa migration. Environment file local yang aktif di IDE tidak menggantikan Environment editor EasyPanel.

## Verifikasi setelah deployment

```sh
curl -fI https://hsncoatlaw.com/assets/vendor/bootstrap/css/bootstrap.min.css
curl -fI https://hsncoatlaw.com/assets/css/style.css
curl -fI https://hsncoatlaw.com/assets/js/main.js
curl -fI https://hsncoatlaw.com/assets/fonts/manrope.woff2
curl -fI https://hsncoatlaw.com/assets/images/hero.jpg
```

Periksa View Source/Network homepage, /about, /practice-areas, /team, /insights, /contact dan /sitemap: seluruh src/href aset memakai https, tidak ada mixed-content console error, assets HTTP 200 dan CSS/JS Content-Type benar. Header/frame Home dan modal/menus harus tetap sesuai template. Hard refresh browser setelah deploy.

Di terminal container EasyPanel:

```sh
php /usr/local/bin/hsnco-check-assets.php
ls -ld /app/public /app/public/assets /app/storage /app/bootstrap/cache
php artisan route:list
```

Jika asset manifest gagal build setelah perubahan CSS/images yang sah, jalankan `node tools/update-asset-manifest.mjs` dari root project lalu commit public/assets yang berubah dan docker/assets-manifest.json bersama. Jangan mematikan check untuk mengatasi asset yang belum committed.

## Hasil dan batas

Laravel tests: **10 passed / 562 assertions**. Check manifest lokal: **56 assets verified**. PHP/shell syntax dan git diff --check diperiksa. Production direct HTTPS asset checks: **56/56 HTTP 200**. Homepage production sebelum redeploy masih menghasilkan HTTP URLs, jadi **production belum dinyatakan diperbaiki**. Tidak ada push/deployment yang dilakukan dari sesi ini. Docker CLI tidak tersedia; Docker build/run final-stage checker belum dapat diuji di mesin ini.