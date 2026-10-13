# Audit integrasi frontend HSNCO

13 Oktober 2026. Laravel terpasang: v12.69.3, require PHP >=8.2; PHP CLI yang tersedia 8.2.24. Project sudah Laravel; vendor/composer tersedia. Hanya route awal / ke welcome, model User, controller dasar dan AppServiceProvider bawaan; tidak ada auth scaffolding atau middleware custom. Health /up dari bootstrap/app.php dipertahankan. Welcome view dan test existing tetap tersedia.

Vite 6 + Tailwind 4 dan axios merupakan scaffold npm, tanpa Bootstrap npm. Frontend menggunakan public/assets lokal, tidak memanggil @vite agar tidak mencampur Tailwind/reset atau Bootstrap ganda. package.json, vite.config.js, composer dan konfigurasi existing tidak diubah.

Environment lokal menggunakan SQLite, SESSION_DRIVER=database, CACHE_STORE=database; database.sqlite kosong. Tidak ada migration dijalankan. Preview/test memakai driver file/array melalui environment proses agar frontend dapat diuji tanpa DB, tidak mengubah .env. Untuk Laragon biasa, session backend existing perlu dikonfigurasi oleh pemilik project.

Sumber read-only: C:/laragon/www/hsnco-template/hsnco-bootstrap-template. Tujuh dokumen dibaca penuh: Home, About, Practice Areas, Team, Insights, Contact, Sitemap. Home full-photo hero Playfair; internal banner Cormorant; body Manrope, charcoal/gold/paper, shared navigation/footer. Semua section, placeholders, stock images dan honest demo form dipertahankan. Bootstrap 5.3.8 lokal; jQuery 3.7.1 + jsTree 3.3.17 hanya sitemap. Chart.js admin tidak digunakan.

Seluruh CSS dan JavaScript dibaca untuk referensi dependency dan aset. Closure aset dari HTML/CSS serta data gambar modal menghasilkan 54 file yang disalin. ASSET_MAPPING.json memberi mapping/checksum. SOURCE_HASHES.json membuktikan seluruh source tetap sama setelah integrasi. Fonts WOFF2, lisensi, sprites jsTree disertakan; logo alternatif/gambar tak dipakai dan authoring JSON tidak disalin.

Path .html dikonversi ke named routes; href fragmen dipertahankan. Assets HTML memakai asset(); CSS relative url tetap valid di public/assets. Image modal JS memakai data-asset-base dari Blade sehingga aman untuk path Laravel. Shared layout/partials, banner/breadcrumb/button/section-heading/practice-card/form/modal menghindari duplikasi. Default Blade escaping dipertahankan; slot static tidak memakai raw dynamic HTML.

Tidak ada organisasi/canonical domain rekaan, auth perubahan, database migration, POST delivery handler atau klaim enquiry diterima. Form masih validasi lokal, disabled tanpa JS, disertai @csrf dan penanda demo.


## Hasil verifikasi

Seluruh tujuh halaman HTTP 200. PHPUnit 5 passed / 110 assertions, tanpa risky output-buffer warnings setelah metadata Blade dipisahkan pada baris sendiri. QA browser 42 kombinasi responsive, 14 screenshot comparisons dan 14 axe scans lolos. Desktop screenshot identik; mobile maksimum 0.10% perbedaan yang tampak pada focus indicator navbar setelah keyboard test. 23 link/fragment valid; sembilan modal praktik, profil/insights, filter/search, form, accordion, values, reduced motion dan jsTree diuji. Source dan protected existing hashes tetap sama.

Dua source maps Bootstrap turut disalin karena dirujuk oleh bundle/CSS untuk debugging. Total asset mapping: 56 file. Hanya components.js yang diadaptasi untuk base URL Laravel; source/destination hashes dicatat. Detail mesin ada di docs/qa/report.json.
