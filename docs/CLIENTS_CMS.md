# Modul Klien HSNCO

## Hasil lokal

43 klien diimpor dari materi pemilik: 8 Bank/Jasa Keuangan, 29 Korporasi/Komersial, 6 Lembaga Negara. Dua daftar Korporasi digabung menjadi satu sektor. Angka 05 sebagai nomor halaman tidak dimasukkan. Nama historis dan keterangan penugasan dipertahankan; ejaan dan pemisahan kalimat dirapikan. Daftar ini bukan hasil verifikasi hubungan hukum melalui sumber eksternal.

Slideshow tampil pada Beranda dan Tentang Firma, membaca database secara langsung. Kartu menampilkan logo dengan object-fit contain, nama, kategori, keterangan nama sebelumnya dan detail penugasan melalui modal bila tersedia. Tidak ada tautan palsu atau detail penugasan tambahan yang dikarang. Slideshow memiliki filter sektor, navigasi, scroll-snap sentuh, slideshow otomatis, jeda saat interaksi/fokus, dukungan reduced motion dan fallback tanpa JavaScript. Autoplay setiap 6 detik ketika pengurangan gerakan tidak dipilih.

## Logo

7 logo lokal: Bank Mandiri, CIMB Niaga, Maybank Indonesia, KB Bukopin/KB Bank, Jakarta Propertindo/Jakpro, Pelindo, Sucofindo. Aset diambil dari situs resmi organisasi; sumber, URL aset dan tanggal pengambilan dicatat pada CLIENT_LOGO_SOURCES.json dan database/seeders/data/client-logos.json. SVG sumber dirasterisasi menjadi PNG; logo tidak direka. Varian logo CIMB berwarna putih menggunakan panel gelap agar terbaca. Nama entitas historis tidak otomatis diganti oleh nama logo terbaru.

36 organisasi belum memiliki logo yang identitasnya dapat dipastikan. Website menampilkan nama, bukan gambar logo palsu; daftar ada pada CLIENT_LOGOS_PENDING.json. SEKDA PROVINSI belum menyebut provinsi tertentu sehingga tidak dipasangkan dengan lambang daerah secara sembarang. Logo historis BPPN dan Bank Global juga belum tersedia. Status kelengkapan logo terlihat pada daftar admin. Jangan menyatakan seluruh 43 logo sudah tersedia.

## Backend

Buka /admin/clients, atau menu Klien. Autentikasi dan verifikasi akun existing tetap berlaku. Tambahkan/sunting Nama, Slug, Sektor, keterangan nama sebelumnya, Gambaran penugasan, logo publik, urutan serta catatan sumber internal. Unggah logo PNG/WebP/JPG melalui Pustaka Media, kemudian pilih thumbnail pada Logo klien. Logo terpilih ditandai bingkai emas dan tampil utuh; media bertanda `dark-surface` memakai latar gelap. Catatan sumber tidak ditampilkan di frontend.

Status hanya Draf dan Tayang pada filter daftar serta formulir tambah/edit. Simpan sebagai Tayang untuk langsung memperbarui logo dan informasi publik; simpan kembali sebagai Draf untuk menyembunyikan klien dari direktori, beranda, dan Tentang Firma. Administrator dan Super Administrator dapat menayangkan serta menyembunyikan konten; Editor dapat menyimpan draf miliknya. Setiap penyimpanan mencatat revisi. Tanggal publikasi mendatang tidak tampil. Media private ditolak; logo yang sedang direferensikan tidak dapat dihapus. Gunakan angka urutan untuk mengatur urutan slideshow. Impor hanya menambah slug yang belum ada dan tidak mengubah hasil editorial atau soft-delete.

Migration `2026_11_02_000003_simplify_client_statuses.php` mengalihkan status lama `review` dan `archived` menjadi `draft`, tanpa menghapus isi maupun riwayat revisi. Status klien yang sudah tayang dipertahankan. Saat deployment kode terbaru jalankan:

```sh
php artisan migrate --path=database/migrations/2026_11_02_000003_simplify_client_statuses.php
```

## Arsitektur dan deployment

Model Client memakai ContentModel, ContentPolicy, revisi, media dan ContentController existing. Tabel clients mempunyai image_id, category, summary, body, sort_order, source_note, status, publication/revision metadata dan soft delete. Tidak ada perubahan konfigurasi login, database connection atau .env.

Migration telah dijalankan hanya pada database lokal. Untuk production sesudah deployment kode dan backup normal aplikasi, jalankan secara manual:

```sh
php artisan migrate --force
php artisan hsnco:import-clients --publish
php artisan hsnco:import-client-logos
```

Tanpa --publish, impor teks menghasilkan draft yang dapat direview di admin. --publish adalah impor awal atas materi yang diberikan pemilik, bukan mekanisme publikasi perubahan editorial berikutnya. Logo hanya dipasangkan jika logo klien masih kosong; pilihan editor tidak ditimpa. Command tidak otomatis berjalan saat container start. Seluruh PNG disertakan pada public/assets serta manifest Docker v2. Production belum diubah.

## Pengujian

ClientCmsTest mencakup 43 data dan jumlah per sektor, impor idempoten, logo lokal, CRUD melalui route admin, draft/publication/archive, catatan internal, tamu, kategori tidak valid, media private, escaping dan jadwal publikasi.

Suite terarah ClientCmsTest + PracticeServiceCmsTest + ServiceProfileTest: 10 lulus / 144 assertion. Browser: 12 kombinasi halaman/viewport (Beranda dan Tentang Firma pada 1920/1440/1366/768/390/375), filter 8/29/6, detail modal, navigasi, putar/jeda, reduced motion, tanpa JS, seluruh 7 gambar serta pemindaian axe lingkup slideshow lulus tanpa error HTTP/JavaScript. Snapshot ada di docs/qa-clients. Pratinjau daftar/editor admin menggunakan mekanisme preview lokal hanya-baca existing; pengujian write memakai akun admin pada SQLite in-memory. Suite keseluruhan sebelumnya masih memiliki 9 kegagalan di bagian lain; hasil terarah ini bukan klaim semua tes aplikasi lulus.

Perintah: php artisan test --filter=ClientCmsTest --do-not-cache-result; node tools/clients-qa.mjs (preview port 4182). Docker build dan deployment production belum dijalankan.
Pembaruan: tombol Putar slideshow dihapus. Slideshow otomatis berganti setiap 6 detik; jeda sementara saat hover/fokus, lalu berjalan lagi setelah interaksi berakhir. Reduced motion menonaktifkan autoplay. Pengujian tools/clients-autoplay-qa.mjs lulus untuk penghapusan tombol, perpindahan otomatis, jeda hover, resume dan reduced motion.

## Pengelolaan klien

Buka /admin/clients setelah login dan verifikasi OTP. Tambah Klien membuka form nama, slug, sektor, keterangan, logo, urutan dan status. Logo dipilih dari Pustaka Media; pilih Tayang untuk menampilkan perubahan atau Draf untuk menyembunyikannya. Tombol Edit tersedia pada daftar. Hapus tersedia pada daftar dan halaman edit, dengan konfirmasi; Administrator dapat menghapus klien Tayang, sedangkan Editor tidak dapat menghapus klien Tayang. Penghapusan bersifat soft delete, tidak menghapus logo atau riwayat revisi dan langsung mengecualikan klien dari halaman publik serta carousel. Tidak ada perubahan skema database untuk fitur ini.

### Logo langsung dari form

Area Unggah logo baru pada form klien menerima drag-and-drop satu file atau pemilihan file perangkat, dengan preview dan tombol pembatalan. JPG/PNG/WebP maksimal 5 MB, dimensi maksimal 8000 x 8000. File disimpan hanya saat Simpan klien ditekan. Upload baru menggantikan pilihan image_id, masuk Pustaka Media publik, dan mempertahankan file lama untuk riwayat. Jika validasi atau penyimpanan klien gagal, unggahan tidak ditinggalkan sebagai file/media yatim. Pilihan Pustaka Media tetap tersedia; memilih radio membatalkan file baru. Gunakan volume /app/storage permanen di hosting.
