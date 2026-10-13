# Panduan frontend Laravel HSNCO

Lokasi implementasi: `C:\laragon\www\hsnco-website`. Source read-only: `C:\laragon\www\hsnco-template\hsnco-bootstrap-template`. Laravel existing 12.69.3, PHP CLI yang dipakai 8.2.24. Tidak ada instalasi ulang Laravel, perubahan versi, migration atau perubahan authentication.

## Menjalankan lokal

Dari root project:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/serve-frontend.ps1
```

Buka `http://127.0.0.1:4180/`. Port dapat diatur melalui `-Port 4181`. Script memakai PHP Laragon 8.2.24 (ubah parameter -Php jika diperlukan) dan environment session/cache file khusus proses, bukan perubahan .env. Hentikan dengan Ctrl+C.

Konfigurasi awal .env memakai session/cache database sementara database.sqlite kosong. Preview di atas dan tests tetap dapat berjalan tanpa migration. Untuk host Laragon biasa, atur DocumentRoot ke `C:/laragon/www/hsnco-website/public`, serta konfigurasi session/cache yang sesuai infrastruktur sebelum memakai driver database. Database dan .env sengaja dipertahankan; tidak ada database session table yang dibuat oleh integrasi ini.

## Routing

| Named route | URL | View |
| --- | --- | --- |
| home | / | frontend.home |
| about | /about | frontend.about |
| practice-areas | /practice-areas | frontend.practice-areas |
| team | /team | frontend.team |
| insights | /insights | frontend.insights |
| contact | /contact | frontend.contact |
| sitemap | /sitemap | frontend.sitemap |

Navbar memakai route()/request()->routeIs(); footer/sitemap/fragments memakai route(). Health /up tetap tersedia. Halaman welcome scaffold dipertahankan di resources/views/welcome.blade.php; root route kini menampilkan HSNCO sesuai tujuan integrasi. Routing awal disimpan di docs/ORIGINAL_WEB_ROUTES.txt.

## Arsitektur

resources/views/layouts/frontend.blade.php menjadi master. layouts/partials/head, header, navigation, footer dan scripts mengelola shared shell. Individual views berada di resources/views/frontend. Anonymous components frontend mencakup breadcrumb, page-banner, button, section-heading, practice-card, enquiry-form dan detail-modal. Components memakai escaped props/slots; tidak ada raw output untuk data CMS yang belum dipercaya.

Semua section Home dan internal source dipertahankan. Modal hanya mempunyai satu instance tiap halaman. Form Home/contact memakai component prefix agar ID unik. Konten profesional, ranking, client/history/stats, contact details dan artikel tetap placeholder sesuai source; tidak dibuat klaim baru tentang HSNCO.

## Assets dan library

Strategi: static public/assets dengan asset() pada Blade, tanpa @vite di layout frontend. Scaffold Vite/Tailwind/npm tetap tersedia untuk pengembangan lain dan tidak dimuat bersamaan dengan Bootstrap di website ini. Tidak diperlukan npm install/build untuk menjalankan frontend website; npm build Vite tidak dijalankan karena entry tersebut tidak digunakan oleh frontend ini.

Bootstrap 5.3.8 bundle/CSS lokal dimuat sekali. Fonts Manrope, Playfair Display dan Cormorant Garamond memakai WOFF2 lokal dengan swap; gambar dan logo lokal. jQuery 3.7.1/jsTree 3.3.17 hanya dimuat pada sitemap melalui @push/@stack. Chart.js admin, logo alternatif dan authoring data JSON source yang tidak digunakan tidak disalin. CSS relative url tetap valid di public/assets; sprites jsTree dan lisensi vendor/font disertakan. ASSET_MAPPING.json mencatat file asal/tujuan dan checksum.

Gambar modal di components.js memakai `data-asset-base` yang dihasilkan Blade dari asset(), sehingga tidak bergantung pada ../assets ketika route berubah. Script jsTree membaca URL link yang sudah dihasilkan route(), sehingga sitemap menuju URL Laravel yang benar.

Untuk mengganti warna/font, edit public/assets/css/variables.css. Untuk mengganti logo, edit partial header/footer dan gunakan asset(); pertahankan CSS crop logo atau sesuaikan dengan rasio logo baru. Untuk mengganti gambar, perbarui asset() dalam view/component dan alias gambar modal pada public/assets/js/content-data.js.

## Menambah halaman atau komponen

Buat named Route::view di routes/web.php dan view yang extends layouts.frontend. Tulis directive metadata pada baris terpisah:

```blade
@extends('layouts.frontend')
@section('title')
Judul halaman — HSNCO
@endsection
@section('description')
Deskripsi unik yang sudah disetujui.
@endsection
@section('content')
<section class="section-space surface-paper">
    <div class="container">Konten halaman</div>
</section>
@endsection
```

Tambahkan link named route di partial navigation/footer jika relevan. Gunakan komponen ketika pola berulang, bukan memindahkan semua section menjadi abstraction. Vendor tambahan ditempatkan lokal dengan versi/license yang jelas dan @push styles/scripts hanya pada halaman pemakai. Hindari memuat Bootstrap kembali melalui Vite atau CDN.

## Form dan keamanan

Enquiry form mempunyai @csrf, native/whitespace/email validation dan feedback jujur bahwa data tidak dikirim/disimpan. Submit disabled jika JavaScript tidak tersedia. Tidak ada endpoint POST contact; POST /contact mengembalikan 405. Untuk delivery berikutnya, sediakan route POST bernama terpisah, controller/FormRequest validation, rate limiting, approved intake/privacy process dan delivery service; ganti demo preventDefault handler setelah server berhasil memproses. Jangan memberi konfirmasi receipt sebelum backend benar-benar berhasil.

Configuration/auth/middleware existing tidak diubah. Tidak ada API key di frontend, canonical URL atau organization schema rekaan. Domain final belum diverifikasi. CSRF middleware Laravel tetap aktif; form lokal bukan authentication atau legal engagement.

## Pengujian aktual

- `php artisan test`: 5 passed, 110 assertions termasuk tests scaffold existing.
- Tujuh pages × enam breakpoints (1920/1440/1366/768/390/375): 42 kombinasi browser lolos.
- 23 internal URLs/fragments valid; tanpa HTTP error/console/runtime error selama QA.
- 14 source/destination full-page screenshot comparisons: seluruh desktop 0 changed pixels; mobile maksimum 0.10% pixel difference (keyboard focus state), tanpa perbedaan dimensi halaman.
- Sembilan practice modals termasuk gambar, profile/insights modals, professional search/filters, form, accordion, firm values, jsTree dan reduced motion lolos.
- 14 axe WCAG 2 A/AA + 2.1 AA scans: 0 violations terdeteksi otomatis.
- SOURCE_HASHES.json dan EXISTING_FILE_HASHES.json diverifikasi: source dan protected existing files tetap identik.

Detail: docs/qa/report.json. Screenshots source/Laravel ada di docs/qa. Audit: FRONTEND_INTEGRATION_AUDIT.md. FILE_MANIFEST.txt mencatat file integrasi.

Untuk mengulang browser QA, jalankan preview di port 4180, kemudian dari tools/: `npm ci` dan `node frontend-qa.mjs`. Package QA terpisah dari package.json aplikasi, tidak dimuat website. Script Chrome path dapat disesuaikan untuk host lain. Source path dalam QA harus tetap tersedia untuk visual comparison. PHPUnit memakai session/cache array melalui phpunit.xml existing dan tidak membutuhkan database migration.

Batas: pengujian memakai Chrome desktop engine, belum Safari/Firefox/screen reader/perangkat fisik. Automated axe bukan sertifikasi aksesibilitas penuh. Tidak ada backend delivery, CMS/database binding atau verifikasi klaim firma. Tidak dilakukan Lighthouse score atau npm/Vite build untuk entry yang tidak digunakan.