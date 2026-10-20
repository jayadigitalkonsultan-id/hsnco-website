# Biaya & Kerja Sama / Billing & Fee

Halaman publik `/billing-fees`, named route `billing-fees`, menggunakan materi biaya yang diberikan pemilik website. Enam skema: Hourly Rate, Flat Fee, Success Fee, Retainer Fee, Contingency Fee dan Project-Based Fee. Tidak ditambahkan tarif, persentase tertentu, atau jaminan kemenangan. Materi dekoratif tentang bahasa dan Certified Mediator tidak dijadikan klaim sertifikasi baru pada halaman biaya.

Desain mengikuti hitam–emas: banner foto finance, gambar editorial transaksi yang diberi label ilustrasi, grid enam kartu dengan ilustrasi SVG masing-masing, dasar biaya dan tautan ke formulir konsultasi. Foto memakai aset lokal yang sudah tersedia. SVG merupakan dekorasi dan disembunyikan dari screen reader; judul kartu dan paragraf tetap menyediakan informasi tekstual lengkap. Tidak ada vendor library atau font baru.

Tautan dapat ditemukan melalui panel Biaya & Kerja Sama pada Profesional dan Bidang Praktik, footer global, sitemap HTML dan sitemap XML. Formulir tujuan tetap halaman Kontak existing.

Konten enam skema dikelola melalui `config/billing.php`; layout di `resources/views/frontend/billing-fees.blade.php`, CSS di `public/assets/css/billing.css`, ilustrasi di komponen `frontend/fee-illustration`. Setelah mengubah config di hosting, jalankan `php artisan config:cache`. Halaman ini belum memiliki editor CMS khusus; tidak ada tabel, kolom, migration atau data database baru.

Validasi lokal: BillingFeePageTest, ProfessionalPortfolioImportTest dan HomePracticeDirectoryTest: 9 pengujian lulus (100 assertion). Chrome pada 1920, 1440, 1366, 768, 390 dan 375 px: enam kartu/ilustrasi, gambar tampil, anchor skema biaya, tautan masuk dari dua halaman, tidak ada horizontal overflow, HTTP error aset atau error JavaScript. Manifest production berisi 71 aset terverifikasi. Screenshot di docs/qa-billing. Production belum di-deploy atau diverifikasi.