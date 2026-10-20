# Pengelolaan bidang praktik

URL admin: /admin/practice-areas. Masuk dengan akun pengelola dan selesaikan verifikasi yang berlaku.

Panel layanan frontend membaca PracticeArea berstatus published dengan tanggal publikasi yang telah berlaku, diurutkan berdasarkan sort_order lalu nama. Daftar statis pada config/services.php hanya menjadi sumber impor awal; mengarsipkan seluruh layanan tidak akan memunculkan kembali data statis. Pengantar umum tetap berada pada config/services.php. Pengaturan banner dan CTA tetap mengikuti halaman CMS yang sudah ada.

## Mengubah panel

1. Buka kategori melalui daftar Praktik Hukum.
2. Isi Nama sebagai judul panel dan Ringkasan sebagai penjelasan singkat di atas gambar.
3. Isi Daftar layanan satu butir per baris. Baris kosong diabaikan.
4. Unggah gambar melalui Pustaka Media, lalu pilih pada Gambar latar kategori. Hanya media publik yang dapat dipilih. Tanpa gambar, panel menggunakan ilustrasi kantor bawaan.
5. Atur Urutan; angka kecil tampil lebih dahulu. Panel pertama mendapat layout lebar.
6. Simpan draft atau ajukan review. Gunakan Terbitkan dengan akun berizin untuk menayangkan perubahan.
7. Arsipkan untuk menghilangkan kategori dari website. Data serta hubungan konten lama tetap tersimpan.

Draft tidak mengubah tampilan publik sampai diterbitkan. Hak akses, CSRF, workflow editorial dan verifikasi akun existing tetap digunakan.

## Impor awal dan deployment

Tidak ada migration baru. Delapan kategori dan gambar ilustrasinya telah ditambahkan ke database lokal. Konten lama tidak dihapus atau diubah. Kategori lama yang masih published juga tampil; pengelola dapat mengarsipkannya melalui alur existing bila tidak dibutuhkan.

Untuk deployment, sesudah kode diperbarui jalankan satu kali dari console aplikasi:

```sh
php artisan hsnco:import-services --publish
```

Opsi --publish ditujukan bagi delapan kategori awal yang materinya sudah diberikan pemilik. Tanpa opsi tersebut, kategori baru menjadi draft. Impor hanya menambahkan slug yang belum pernah ada, termasuk memperhitungkan soft delete; hasil edit dan status existing tidak ditimpa. Menjalankan ulang dengan --publish tidak menerbitkan draft yang sudah ada. Data berikutnya dikelola melalui admin, bukan impor. Command tidak berjalan otomatis saat container start. Production belum disentuh.

## Validasi

Pengujian PracticeServiceCmsTest mencakup impor idempoten, suntingan melalui route admin, isolasi draft, publikasi, perubahan gambar dan urutan, arsip, tidak adanya fallback statis untuk draft, dan penolakan tamu. ServiceProfileTest memverifikasi seluruh materi layanan setelah diimpor ke CMS. Database pengujian menggunakan SQLite in-memory.

Hasil pada implementasi ini: 5 pengujian integrasi layanan lulus dengan 89 assertion; browser lulus pada 1920, 1440, 1366, 768, 390, dan 375px, 8 panel/41 layanan dan semua gambar tampil tanpa overflow/error HTTP/JavaScript. Pemindaian axe pada lingkup layanan tidak menemukan pelanggaran WCAG A/AA yang diuji.

Suite keseluruhan: 55 lulus dan 9 gagal. Kegagalan tercatat pada AdminShellTest (3), ContactEnquiryWorkflowTest (1), DatabaseFoundationTest (1), FrontendIntegrationTest (2), IndonesianFrontendTest (1), MigrationLifecycleTest (1). Sebagian ekspektasi masih mengacu formulir demo kontak dan jumlah migration 21 meskipun kini 23. Hasil suite penuh belum bersih; tidak ada klaim seluruh aplikasi lulus. Tidak ada perubahan migration atau autentikasi dalam integrasi layanan ini.
