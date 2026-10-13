# Perbaikan Docker asset checksum lintas Windows/Linux

## Penyebab terverifikasi

EasyPanel gagal pada RUN php /usr/local/bin/hsnco-check-assets.php, asset css/components.css. File tersedia, dilacak Git, tidak dikecualikan .dockerignore, dan masuk image melalui COPY public ./public pada dependencies kemudian COPY --from=dependencies /app /app pada production.

Git melaporkan `i/lf w/mixed attr/text=auto eol=lf` untuk components.css. Working file 5505 byte dengan hash 7030a2e73c63d03803be9e76572a9394b2cf1013b46db48c3a87d4f3766cd2d8; Git HEAD 5502 byte dengan hash f3aeb2a7b54fd2808e03a8777150901bcd778127781da469c864960c3e0262fd. Setelah normalisasi CRLF -> LF, working file identik dengan Git HEAD. Manifest v1 menyimpan hash working Windows, sehingga tidak cocok dengan checkout Git Linux meski isi CSS tidak berubah.

## Perbaikan

- tools/update-asset-manifest.mjs menghasilkan manifest v2. CSS/JS/JSON/map/SVG/text/license UTF-8 dihitung setelah CRLF -> LF, tanpa menulis ulang file. Gambar/fonts/binary memakai SHA-256 byte asli.
- docker/assets-manifest.json dihasilkan ulang dengan mode lf-text atau binary per file. Tidak ada perubahan visual CSS/JS.
- docker/check-assets.php membaca v2, menerapkan aturan hash yang sama, tetap memeriksa path root, existence, nonempty, readable, UTF-8 dan checksum. Error dibedakan menjadi missing, unreadable, invalid manifest dan checksum mismatch dengan expected/actual hash. Validasi tidak dinonaktifkan.
- ProductionAssetsTest disesuaikan dengan canonical hash; AssetManifestTest menambahkan positive Windows/Linux cases dan negative content/binary/missing/old-version cases.
- tools/verify-git-assets.mjs mengekstrak byte Git HEAD ke fixture sementara, membandingkan manifest lalu menjalankan PHP checker. Source aset tidak ditulis ulang.

Dockerfile tetap memakai user 1000:1000, asset directories 755/file 644 dan validasi pada final stage sebagai non-root. Caddy document root tetap /app/public, internal HTTP port tetap 8080. Tidak ada perubahan .env, database, auth atau data production.

## Pengujian aktual

- Checker working tree: 56 assets verified, exit code 0.
- Checker dengan seluruh 56 Git HEAD blob files: exit code 0; bukti DOCKER_ASSET_CHECKSUM_REPORT.json.
- Laravel suite: 15 passed / 572 assertions. LF/CRLF diterima; isi CSS berubah, byte binary berubah, asset hilang dan manifest v1 ditolak.
- PHP syntax serta git diff --check lolos. git diff -- public/assets kosong.
- Docker CLI tidak tersedia; **Docker build final dan UID 1000 Linux runtime belum diuji langsung**. Pengecekan Git bytes mengatasi penyebab error yang ditemukan, bukan klaim deployment telah berhasil.

## Commit, push dan rebuild

Dari root project:

```sh
node tools/update-asset-manifest.mjs
php docker/check-assets.php public/assets docker/assets-manifest.json
php artisan test
git add docker/check-assets.php docker/assets-manifest.json tools/update-asset-manifest.mjs tools/audit-asset-checksum.mjs tools/verify-git-assets.mjs tests/Unit/AssetManifestTest.php tests/Feature/ProductionAssetsTest.php docs/DOCKER_ASSET_CHECKSUM_FIX.md docs/DOCKER_ASSET_CHECKSUM_REPORT.json docs/EASYPANEL_DEPLOYMENT.md
git commit -m "Fix cross-platform asset checksums for Docker builds"
git push origin main
```

Commit checker/generator/manifest bersama. Rebuild EasyPanel dari commit main terbaru. Jika public/assets berubah di masa depan, regenerate manifest dan commit aset + manifest bersamaan. Tidak perlu menonaktifkan validasi atau mengubah desain.

Pada mesin Docker atau VPS:

```sh
docker build --pull --progress=plain --target production -t hsnco-website:verify .
docker run --rm --entrypoint php hsnco-website:verify /usr/local/bin/hsnco-check-assets.php
docker run --rm --entrypoint sh hsnco-website:verify -c 'id; test -r /app/public/assets/css/components.css'
```

Build harus melewati pemeriksaan 56 aset pada final stage. Setelah deploy dengan environment existing, verifikasi https://hsncoatlaw.com/assets/css/components.css HTTP 200, https://hsncoatlaw.com/up, dan homepage/internal styling. Tidak ada migration atau APP_KEY generation dalam perbaikan ini.