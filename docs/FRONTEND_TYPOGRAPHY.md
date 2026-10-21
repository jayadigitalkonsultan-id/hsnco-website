# Tipografi frontend HSNCO

- Lexend Deca, variable 400-700: heading dan navigasi. Bobot utama 500, tanpa italic sintetis pada judul; aksen emas dipertahankan.
- Atkinson Hyperlegible, regular/bold beserta italic asli: paragraf dan teks penjelasan. Ukuran dasar 16px, line height 1.8.
- Trebuchet MS: label, input, select dan tombol formulir; menggunakan font sistem, fallback ke Atkinson Hyperlegible jika tidak tersedia. File Trebuchet tidak didistribusikan ulang.

WOFF2 Google Fonts disimpan lokal beserta OFL masing-masing di public/assets/fonts. Tidak ada request runtime ke Google Fonts. Subset Unicode sesuai distribusi resmi dipertahankan. Sumber: https://fonts.google.com/specimen/Atkinson+Hyperlegible dan https://fonts.google.com/specimen/Lexend+Deca.

Token font di assets/css/variables.css. Penyesuaian ukuran dan hirarki di assets/css/typography.css, dimuat terakhir setelah CSS halaman khusus. Judul mengecil dibanding font serif sebelumnya agar proporsional dengan karakter sans-serif. Tidak mengubah isi, warna merek, atau data.

Perubahan ini berlaku pada frontend publik; layout admin dan email memiliki stylesheet terpisah. Setelah mengganti aset jalankan node tools/update-asset-manifest.mjs dan validator Docker. Belum dideploy dari sesi ini.
Validasi Chrome: 7 halaman pada 1440, 1024, 992, 768, 390 dan 375 px; font ter-load dan tidak ditemukan horizontal overflow halaman/navbar. Validator 100 aset lulus. Pemeriksaan HTTP menemukan satu gambar media existing yang merespons 403 (/storage/media/01M5E47N7WFVN83FF49HKDFJJP.jpg); bukan file font. Akses media tersebut belum diubah dalam pekerjaan tipografi ini.
