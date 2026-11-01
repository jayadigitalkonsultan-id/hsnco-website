# Pengelolaan bidang praktik

URL admin: /admin/practice-areas. Masuk dengan akun pengelola dan selesaikan verifikasi yang berlaku.

Panel layanan frontend membaca PracticeArea berstatus published dengan tanggal publikasi yang telah berlaku, diurutkan berdasarkan sort_order lalu nama. Praktik hanya memakai status Draf dan Tayang. Daftar statis pada config/services.php hanya menjadi sumber impor awal; mengembalikan seluruh layanan ke Draf tidak akan memunculkan kembali data statis. Pengantar umum tetap berada pada config/services.php. Pengaturan banner dan CTA tetap mengikuti halaman CMS yang sudah ada.

## Mengubah panel

1. Buka kategori melalui daftar Praktik Hukum.
2. Isi Nama sebagai judul panel dan Ringkasan sebagai penjelasan singkat di atas gambar.
3. Isi Daftar layanan satu butir per baris. Baris kosong diabaikan.
4. Unggah gambar melalui Pustaka Media, lalu pilih pada Gambar latar kategori. Hanya media publik yang dapat dipilih. Tanpa gambar, panel menggunakan ilustrasi kantor bawaan.
5. Atur Urutan; angka kecil tampil lebih dahulu. Panel pertama mendapat layout lebar.
6. Pilih Draf atau Tayang, lalu klik Simpan praktik. Tayang langsung menampilkan perubahan tanpa tahapan review terpisah.
7. Kembalikan ke Draf untuk menyembunyikan kategori dari website, pilihan formulir konsultasi, dan daftar praktik di sitemap. Data serta hubungan konten lama tetap tersimpan.

Draf tidak tersedia untuk publik, termasuk ketika praktik yang sebelumnya tayang disimpan kembali sebagai Draf. Hanya Administrator yang dapat menayangkan atau menyembunyikan praktik tayang. Editor dapat membuat dan menyunting draf miliknya. Setiap penyimpanan mencatat revisi; revisi lama tidak menggantikan kembali perubahan yang sudah ditayangkan. Modul konten lain tetap memakai workflow editorial masing-masing.

## Impor awal dan deployment

Migration `2026_11_01_000001_simplify_practice_area_statuses.php` memindahkan status praktik lama `review` dan `archived` menjadi `draft`, tanpa menghapus isi atau riwayat revisi. Status `published` tetap tayang. Jalankan migration ini saat deployment kode terbaru:

```sh
php artisan migrate --path=database/migrations/2026_11_01_000001_simplify_practice_area_statuses.php
```

Delapan kategori dan gambar ilustrasinya telah ditambahkan ke database lokal pada integrasi awal. Kategori lama yang masih published juga tampil; pengelola dapat menyimpannya sebagai Draf bila tidak dibutuhkan.

Untuk deployment, sesudah kode diperbarui jalankan satu kali dari console aplikasi:

```sh
php artisan hsnco:import-services --publish
```

Opsi --publish ditujukan bagi delapan kategori awal yang materinya sudah diberikan pemilik. Tanpa opsi tersebut, kategori baru menjadi draft. Impor hanya menambahkan slug yang belum pernah ada, termasuk memperhitungkan soft delete; hasil edit dan status existing tidak ditimpa. Menjalankan ulang dengan --publish tidak menerbitkan draft yang sudah ada. Data berikutnya dikelola melalui admin, bukan impor. Command tidak berjalan otomatis saat container start. Production belum disentuh.

## Validasi

Pembaruan status 1 November 2026: migration sudah diterapkan pada database lokal. Pengujian terkait lulus, 18 pengujian dengan 232 assertion, meliputi penyimpanan Draf/Tayang, pembaruan berulang tanpa kembali ke revisi lama, migrasi status lama, pembatasan hak publikasi, daftar konsultasi/sitemap publik, serta regresi modul klien dan industri. Angka pengujian dan penjelasan workflow pada catatan implementasi awal di bawah merupakan riwayat sebelum penyederhanaan status ini.

Pengujian PracticeServiceCmsTest mencakup impor idempoten, suntingan melalui route admin, isolasi draft, publikasi, perubahan gambar dan urutan, arsip, tidak adanya fallback statis untuk draft, dan penolakan tamu. ServiceProfileTest memverifikasi seluruh materi layanan setelah diimpor ke CMS. Database pengujian menggunakan SQLite in-memory.

Hasil pada implementasi ini: 5 pengujian integrasi layanan lulus dengan 89 assertion; browser lulus pada 1920, 1440, 1366, 768, 390, dan 375px, 8 panel/41 layanan dan semua gambar tampil tanpa overflow/error HTTP/JavaScript. Pemindaian axe pada lingkup layanan tidak menemukan pelanggaran WCAG A/AA yang diuji.

Suite keseluruhan: 55 lulus dan 9 gagal. Kegagalan tercatat pada AdminShellTest (3), ContactEnquiryWorkflowTest (1), DatabaseFoundationTest (1), FrontendIntegrationTest (2), IndonesianFrontendTest (1), MigrationLifecycleTest (1). Sebagian ekspektasi masih mengacu formulir demo kontak dan jumlah migration 21 meskipun kini 23. Hasil suite penuh belum bersih; tidak ada klaim seluruh aplikasi lulus. Tidak ada perubahan migration atau autentikasi dalam integrasi layanan ini.

## Pembaruan layout direktori dan modal

Halaman `/practice-areas` mengikuti pola layout HTML yang diberikan pemilik website: hero foto besar, pengantar editorial, delapan baris direktori bernomor dengan ringkasan dan panah lingkaran, gambar ilustratif muncul saat hover/focus, dan modal dua kolom. Warna tetap hitam–emas. Header/footer Bootstrap existing tetap digunakan.

Konten contoh HTML berupa profil pengacara, nilai transaksi, dan representative matters tidak diimpor karena bukan data firma yang terverifikasi. Modal hanya menampilkan nama, ringkasan dan seluruh butir layanan dari data CMS terpublikasi, ditambah tautan ke tim serta konsultasi. Tidak ditetapkan nama practice lead tanpa relasi/data yang mendukung. Foto memakai gambar kategori CMS dan fallback office lokal.

ID slug setiap kategori tetap dipertahankan sehingga tautan dari Home/halaman lainnya tetap berfungsi. Tombol baris merupakan tombol native dengan accessible name, fokus yang terlihat, dan modal Bootstrap untuk penguncian fokus, Escape, backdrop dan pemulihan fokus. Isi layanan dirender aman oleh Blade ke template HTML lalu disalin ke modal. Tidak ada evaluasi HTML mentah dari atribut atau konten pengguna. Reduced motion menonaktifkan transisi dekoratif. Tidak ada framework, CDN atau library tambahan.

File utama: frontend/practice-areas/service-profile.blade.php, components/frontend/practice-portfolio-modal.blade.php, assets/css/practice-areas.css dan assets/js/practice-directory.js. Daftar lama panel kartu digantikan sepenuhnya; tidak ada perubahan tabel, migration, isi database atau alur editorial.

Pengujian PHP terkait: ServiceProfileTest, PracticeServiceCmsTest dan BillingFeePageTest lulus: 7 pengujian, 116 assertion. Pengujian browser/screenshot tersedia di tools/practice-directory-qa.mjs dan docs/qa-practices. Manifest aset production diperbarui. Belum di-deploy ke production.
