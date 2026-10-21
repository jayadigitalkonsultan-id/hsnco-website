# Direktori profesional dari PORTOFOLIO.pdf

Sumber: dokumen PORTOFOLIO.pdf yang diberikan pemilik website, 19 halaman (nomor cetak 11–29). Nama, ringkasan keahlian dan biografi ditranskripsikan dari halaman visual; kapitalisasi nama dinormalisasi. Tidak ada foto, alamat email atau profil fiktif yang ditambahkan. Kualifikasi pendidikan tetap tercantum dalam biografi, tidak ditafsirkan sebagai sertifikasi baru.

## Kategori yang disetujui

| Nilai level | Label | Jumlah |
| --- | --- | ---: |
| managing_partner | Managing Partner | 1 |
| partner | Partner | 2 |
| associate | Associate | 9 |
| trainee_associate | Trainee Associate | 2 |
| paralegal | Paralegal | 4 |
| professional | Profesional | 1 |

Rizky Aldila mengikuti label Partner pada judul, sementara narasi sumber yang menyebut Associate dipertahankan. dr. Hilwani tidak memiliki kategori pada judul sumber; pengguna menyetujui kategori Profesional. Raul mengikuti kategori Associate pada narasi, Aisyah mengikuti kategori Paralegal pada narasi. Arvin dan Hermalia mengikuti label Trainee Associate meskipun narasi menyebut Associate. Gelar Dr. pada heading Raden dipertahankan beserta narasi sumber mengenai studi doktor; tidak dilakukan verifikasi atau perubahan fakta di luar dokumen.

## Database

Tidak ada tabel, kolom, migration, atau indeks baru. Kategori menggunakan kolom string `professionals.level` yang sudah ada (maksimal 30 karakter). Data masuk ke tabel `professionals` dan snapshot editorial ke `content_revisions`. Relasi foto tetap menggunakan `professionals.photo_id` ke `media.id`. Tidak ada perubahan database production dari sesi ini.

Impor lokal: 18 profil baru, 1 profil Hasbullah existing dipertahankan; hanya level existing disesuaikan dari partner menjadi managing_partner. Total 19 profil. Nama/biografi/foto existing tidak ditimpa.

## Mengganti foto melalui backend

1. Login sebagai pengguna yang memiliki izin mengedit profesional.
2. Unggah potret asli melalui menu Media, dengan visibilitas publik dan alt text yang sesuai.
3. Buka `/admin/professionals`, pilih profil, lalu pilih foto pada kolom Gambar pilihan.
4. Simpan draft, ajukan review dan terbitkan melalui alur editorial yang tersedia.
5. Foto ditampilkan di kartu direktori dan modal profil setelah dipublikasikan.

Foto belum tersedia: placeholder hitam–emas dengan inisial nama. Foto orang lain tidak digunakan. Nama, kategori, jabatan dan biografi sudah terisi dan tetap dapat diedit.

## Menjalankan pada hosting setelah deployment

Database tidak ikut dalam git push. Jalankan perintah ini dari container aplikasi setelah kode terbaru di-deploy:

```bash
php artisan config:cache
php artisan hsnco:import-professionals --dry-run --publish --sync-categories
php artisan hsnco:import-professionals --publish --sync-categories
php artisan view:cache
```

Tidak perlu menjalankan migration untuk fitur ini. Perintah memerlukan tabel CMS yang sudah tersedia. Tanpa `--publish`, profil baru disimpan sebagai draft. Impor berulang melewati nama/slug existing, termasuk soft-deleted, dan tidak mengubah foto atau biografi. `--sync-categories` secara eksplisit menyelaraskan hanya level existing dan menambah snapshot revisi; profil dengan draft pending tidak disinkronkan otomatis. Atur profil tersebut melalui editor agar draft tidak tertimpa. Jangan gunakan `migrate:fresh`.

## Arsitektur dan pengujian

- `config/professionals.php`: satu sumber pilihan kategori untuk frontend/backend dan validasi.
- `database/seeders/data/professionals.json`: 19 profil serta nomor halaman sumber.
- `hsnco:import-professionals`: impor idempotent, dry-run, publikasi opsional dan sinkronisasi kategori eksplisit.
- Direktori: kelompok kategori berurutan, filter, pencarian dan modal biografi; konten tidak dipotong di database.
- `ProfessionalPortfolioImportTest` dan `AboutPageTest`: 10 pengujian lulus, 144 assertion.
- Chrome lokal: 1920, 1440, 1366, 768, 390 dan 375 px. Jumlah 19 profil, enam kategori, pencarian Arvin, biografi modal, tidak ada overflow horizontal, error HTTP aset atau error JavaScript.
- Manifest production diperbarui dan 70 aset terverifikasi. Docker build tidak dijalankan.
- Screenshot lokal: `docs/qa-professionals/team-1440.png` dan `team-390.png`.
## Modal portofolio

Desain dua kolom mengikuti contoh HTML pemilik website: panel identitas hitam–emas di kiri dan narasi pada permukaan ivory di kanan. Pada mobile kedua panel tersusun vertikal. Modal khusus profesional berada di `components/frontend/portfolio-modal.blade.php`; modal bidang praktik dan publikasi tetap terpisah.

Nama, jabatan, ringkasan, foto, biografi, qualifications, languages dan publishedPracticeAreas menggunakan data CMS existing. Jika relasi bidang praktik belum diisi, tag fokus memakai butir ringkasan sumber PDF; angka PQE dan label bahasa tidak ditafsirkan sebagai bidang praktik. Bagian kualifikasi/bahasa kosong disembunyikan. Data pendidikan, izin advokat dan pengalaman spesifik tidak dibuat atau ditebak dari nama/gelar. Seluruh narasi pengalaman dari PDF tetap dapat dibaca pada biografi.

Link kontak menuju formulir firma. Email pribadi profesional tidak dibuat. Foto kosong menggunakan inisial; foto gagal dimuat juga kembali ke placeholder. Isi dinamis ditulis dengan textContent, tanpa HTML tidak terpercaya. Tidak ada perubahan tabel.

QA tambahan `tools/portfolio-modal-qa.mjs`: enam ukuran layar, identitas/biografi yang benar, bagian kosong tersembunyi, tautan kontak valid, penguncian fokus, Escape, fokus kembali ke tombol pemicu, scroll kembali normal, dan pergantian profil tanpa isi tertinggal. Tidak ada overflow atau error HTTP/JavaScript. Screenshot: `docs/qa-professionals/portfolio-1440.png` dan `portfolio-390.png`. Tampilan dengan foto asli menunggu foto dari klien.
## Pembaruan foto besar / 21 Oktober 2026

Referensi visual yang diberikan pemilik: https://rifdaannovarazkaprabowo.com/professionals/. Pola diadaptasi menjadi direktori hitam–emas dengan foto potret 3:4 yang lebih tinggi, judul kategori serif italic besar, jabatan ringkas dan nama sebagai tombol pembuka modal. Tidak menggunakan nama, biografi atau foto dari website referensi. Tiga kolom pada desktop, dua pada tablet dan satu pada mobile. Kategori CMS, pencarian dan portofolio tetap digunakan; ringkasan lengkap dibaca pada modal, bukan memenuhi kartu. Foto asli memakai unggahan CMS, tanpa foto lain sebagai pengganti identitas. Tidak ada perubahan database.