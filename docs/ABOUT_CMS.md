# Tentang Firma: 07.01–07.09

Di admin **Halaman website**, buka **Tentang Firma**. Sembilan bagian mengikuti urutan existing; urutan dan visibility dapat diubah pada setiap bagian. Urutan milestone, angka, dan klien mengikuti urutan item dalam array JSON.

Banner memakai judul bagian, eyebrow, intro, dan label breadcrumb. Narasi firma memiliki dua kolom terpisah. Visi dan misi masing-masing memiliki judul serta penjelasan sendiri. CTA penutup secara default menuju `/contact#enquiry`.

Kutipan hanya muncul jika `approved` aktif dan isi, atribusi, serta bukti persetujuan lengkap. Milestone hanya muncul jika `verified` aktif dengan tahun, judul, deskripsi, dan bukti. Angka firma memerlukan nilai, label, sumber/bukti, serta `verified_at` berformat `YYYY-MM-DD` yang tidak melewati hari ini; satuan bersifat opsional.

Untuk logo/tanggapan klien, isi `client_name` dan `evidence`, kemudian aktifkan `permission_confirmed` setelah izin mencakup seluruh materi pada item tersebut. `media_id` hanya menerima gambar publik; ID tersedia pada referensi media di editor. `feedback` dan `rating` opsional. Bukti internal tidak masuk HTML publik.

Pimpinan dipilih dari modul Profesional. Hanya profil berstatus published dengan tanggal publikasi yang sudah berlaku yang ditampilkan. Nama, jabatan, biografi, dan foto dibaca dari profil tersebut; foto privat atau terhapus tidak ditampilkan.

**Pratinjau** memperlihatkan revisi melalui layout `/about` dan menerapkan penyaringan persetujuan yang sama. Akses memerlukan akun berizin dan respons memakai `no-store` serta `noindex`. Simpan draft, ajukan review, lalu terbitkan melalui workflow admin untuk memperbarui halaman publik. Tanpa versi published, `/about` menampilkan pemberitahuan singkat dan tautan kontak.

Section dari seed lama diadaptasi dalam memori pada editor dan renderer, tanpa menimpa data tersimpan. Penyimpanan tetap membuat revisi draft. Seed baru memakai field terstruktur; seed ulang tidak menimpa perubahan editor. Riwayat, angka, dan klien awal kosong, sedangkan kutipan awal belum disetujui.

Pemeriksaan task: `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit tests/Feature/AboutPageTest.php`. SQLite hanya diaktifkan untuk proses pengujian; database aplikasi tidak dipakai.
