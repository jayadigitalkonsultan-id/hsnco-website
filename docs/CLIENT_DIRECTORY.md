# Direktori Klien Kami

Halaman publik `/clients` menggunakan named route `clients` dan ClientDirectoryController. Judul Klien Kami di carousel Home/About menaut ke halaman ini. Footer dan sitemap juga mencantumkan tautan.

Semua klien terbit ditampilkan dalam grid sesuai urutan CMS, tanpa kategori atau deskripsi penugasan. Logo memakai media publik; jika belum ada logo, nama tampil sebagai pengganti, tanpa membuat logo fiktif. Draft, arsip, jadwal mendatang, soft-deleted, dan file media privat tidak diekspos.

Kelola daftar melalui `/admin/clients`; tidak ada tabel atau migration baru. Carousel tetap hanya menampilkan klien yang memiliki logo publik, sedangkan direktori mencantumkan semua klien terbit.

Validasi lokal: 9 test, 120 assertion lulus dengan route cache. Chrome memverifikasi klik judul menuju direktori, 43 entri, logo ter-load, enam viewport (1920/1440/1366/768/390/375), tanpa overflow atau error JavaScript/HTTP. Validator 100 aset lulus. Belum di-push/deploy.
## Showcase dua baris

Home dan About kini memakai dua baris logo bergerak berlawanan arah pada latar putih murni dengan aksen emas tipis. Judul Our Clients tetap menaut ke direktori, tanpa kategori, deskripsi, simbol panah, atau tombol putar. Kedua baris memakai logo publik yang sama dengan urutan awal berbeda; tidak menambahkan identitas klien atau logo fiktif. Logo yang belum tersedia tetap hanya muncul sebagai nama di direktori seluruh klien.

Animasi dijeda saat hover/fokus, dinonaktifkan untuk reduced motion, dan rail tetap bisa digeser tanpa JavaScript. Ukuran logo proporsional dengan object-fit:contain. Validasi: 9 test / 120 assertion lulus; QA Home/About pada 1440/768/390/375, gambar termuat, arah normal/reverse terverifikasi, autoplay, hover pause, reduced motion, fallback tanpa JS, tanpa overflow atau error JS/HTTP. Belum di-push/deploy.
Penyempurnaan terbaru: latar #ffffff, kartu putih dengan border abu-abu halus dan bayangan ringan, logo diperbesar, heading diapit garis emas lembut. Hover memberi aksen bingkai emas dan zoom logo ringan. Warna logo asli dipertahankan. Browser memverifikasi latar putih di desktop/mobile; QA carousel Home/About pada empat lebar layar lulus dan 102 aset lolos validator.
