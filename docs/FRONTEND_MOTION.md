# Animasi frontend HSNCO

## Perilaku

Judul, eyebrow, paragraf pengantar, kartu layanan, kartu klien, publikasi dan gambar editorial menggunakan fade-in serta translateY 38px selama 1100ms. Kelompok yang tampil bersama memiliki jeda bertahap 90ms, maksimal 360ms. Animasi terpicu ulang setelah elemen keluar dari area pengamatan dan masuk kembali. Kartu dianimasikan sebagai satu unit untuk menghindari transform bertumpuk pada teks di dalam kartu.

Hero memiliki zoom-out halus dari skala 1.075 ke 1 selama 9 detik. Area hero memotong gambar agar tidak menimbulkan overflow. Hover panel layanan dan klien memperjelas border serta shadow secara halus. Tidak ada plugin animasi tambahan atau loop transform terus-menerus.

Implementasi sebelumnya hanya menganimasikan blok besar satu kali selama 700ms, sehingga ritme visual kurang terlihat. Selector kini menargetkan komponen yang lebih spesifik serta kelas banner aktual inner-hero.

## File

public/assets/js/motion.js dan public/assets/css/motion.css, dimuat oleh partial head/scripts. URL khusus motion memakai versi filemtime agar browser mengunduh perubahan terbaru. Semua halaman frontend memakai mekanisme yang sama. Manifest Docker v2 diperbarui dengan node tools/update-asset-manifest.mjs setiap aset berubah.

## Aksesibilitas dan fallback

Konten tidak disembunyikan melalui CSS. Tanpa JS atau API browser terkait, konten tetap tersedia. Prefers-reduced-motion menonaktifkan animasi. Fokus keyboard menyelesaikan animasi aktif agar formulir dan tautan langsung terbaca. Perubahan preferensi selama halaman terbuka juga ditangani. Pencetakan menyelesaikan animasi dan observer diaktifkan kembali setelah pencetakan.

## Pengujian

node tools/motion-qa.mjs: 14 kombinasi dari tujuh halaman pada desktop 1440px dan mobile 390px, animasi terpicu, tidak ada overflow/error HTTP/JavaScript, reduced motion dan fallback tanpa JS lulus.

node tools/motion-detail-qa.mjs: zoom hero, opacity saat fade berjalan, opacity penuh sesudah selesai, pemicu ulang pada re-entry serta penghentian animasi melalui reduced motion lulus. Server preview pengujian menggunakan port 4182.

Validasi 68 aset melalui manifest Docker lulus. Tidak ada perubahan database, autentikasi atau source template lain. Belum di-push atau di-deploy ke production.
Banner judul halaman internal kini memakai ilustrasi per halaman, lapisan gelap untuk kontras, zoom/pan lembut 18 detik bolak-balik, garis aksen yang muncul dan fade-in teks berurutan: breadcrumb, label, judul, deskripsi. Efek latar hanya aktif ketika reduced motion tidak dipilih. Komponen bersama: resources/views/components/frontend/page-banner.blade.php.

Validasi banner: tools/banner-motion-qa.mjs lulus pada enam halaman internal di desktop 1440px/mobile 390px, gambar berhasil dimuat, animasi latar terdeteksi, reduced motion dihormati, tanpa overflow/error HTTP/JavaScript. Peta Situs kini memiliki banner fallback jika section CMS belum tersedia; pengaturan sembunyikan banner tetap dihormati.

Perbaikan khusus URL beranda port 8000: hero fallback maupun hero CMS memakai komponen hero-visual dengan tiga gambar lokal. CSS crossfade berulang 13.5 detik per siklus (sekitar 4.5 detik per gambar), ditambah zoom lembut. Gambar utama dari CMS tetap dipertahankan. Fade teks menunggu font siap dengan batas 1500ms. tools/home-slideshow-8000-qa.mjs memverifikasi pada port 8000 bahwa opacity gambar berubah dari [1,0,0] ke [0,1,0] setelah lima detik di 1440px dan 390px, tanpa overflow/error HTTP/JS; reduced motion menampilkan gambar pertama tanpa gerakan. Screenshot sebelum/sesudah tersimpan di docs/qa-motion/home-before-* dan home-after-*.
