# Direktori Klien Kami

Halaman publik `/clients` menggunakan named route `clients` dan ClientDirectoryController. Judul Klien Kami di carousel Home/About menaut ke halaman ini. Footer dan sitemap juga mencantumkan tautan.

Semua klien terbit ditampilkan dalam grid sesuai urutan CMS, tanpa kategori atau deskripsi penugasan. Logo memakai media publik; jika belum ada logo, nama tampil sebagai pengganti, tanpa membuat logo fiktif. Draft, arsip, jadwal mendatang, soft-deleted, dan file media privat tidak diekspos.

Kelola daftar melalui `/admin/clients`; tidak ada tabel atau migration baru. Carousel tetap hanya menampilkan klien yang memiliki logo publik, sedangkan direktori mencantumkan semua klien terbit.

Validasi lokal: 9 test, 120 assertion lulus dengan route cache. Chrome memverifikasi klik judul menuju direktori, 43 entri, logo ter-load, enam viewport (1920/1440/1366/768/390/375), tanpa overflow atau error JavaScript/HTTP. Validator 100 aset lulus. Belum di-push/deploy.