# Aktivasi terbatas dan email SMTP

## Tabel acuan

Migration 2026_10_20_000002_create_activation_email_allowlist_table membuat activation_email_allowlist dan tiga record awal:

| Email | Peran |
| --- | --- |
| arvinz24wicaksono@gmail.com | admin |
| hsandcoattorney@gmail.com | admin |
| hermaliaziah22@gmail.com | editor |

Email dinormalisasi menjadi huruf kecil dan dipangkas spasinya. enabled dapat dipakai untuk menonaktifkan izin aktivasi. Tabel acuan terlihat pada bagian Pengguna & undangan untuk Super Administrator sesuai izin existing. Tabel ini tidak ditampilkan ke pengunjung umum.

Hanya email yang aktif pada tabel boleh meminta tautan, menerima undangan, memakai token dan menyelesaikan enrollment OTP. Peran ditentukan kembali dari tabel saat aktivasi dan konfirmasi OTP, bukan dari input browser. Undangan admin di luar tabel atau dengan peran berbeda ditolak. Email yang diizinkan dapat meminta aktivasi langsung tanpa perlu didaftarkan ulang: akun invited dibuat saat permintaan pertama. Permintaan luar daftar tidak membuat akun dan memberikan respons generik yang sama.

Akun aktif existing tidak diubah, tidak dihapus dan tidak otomatis dicabut akses loginnya. Ini adalah pembatasan aktivasi baru. Menonaktifkan baris acuan tidak otomatis menonaktifkan akun yang sudah aktif; penonaktifan akun tetap melalui pengelolaan pengguna existing.

## Alur

Buka /activate → masukkan email yang diizinkan → terima tautan aktivasi → buat nama/kata sandi → kembali ke login → masukkan email/kata sandi → OTP dikirim → verifikasi OTP → akun aktif sesuai peran acuan. Token sekali pakai, expiry, cooldown, binding OTP ke sesi, password hashing dan autentikasi dua tahap tetap digunakan. Jika transport aktivasi gagal, token dibatalkan dan cooldown pengiriman dilepas agar dapat dicoba kembali. Kegagalan pengiriman OTP tidak mengaktifkan akun.

## Provider belum dipilih

Kode mail activation dan OTP menggunakan transport Laravel yang dikonfigurasi. MAIL_MAILER=log hanya menulis pesan ke log; itu bukan pengiriman ke inbox. Kredensial nyata belum tersedia, sehingga .env aktual tidak diubah dan tidak ada klaim email eksternal sudah terkirim.

Setelah memilih penyedia, isi Environment service aplikasi EasyPanel (atau .env lokal untuk pengujian lokal). Lihat docker/smtp.env.example:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=HOST_SMTP_DARI_PROVIDER
MAIL_PORT=587
MAIL_USERNAME=USERNAME_SMTP_DARI_PROVIDER
MAIL_PASSWORD="PASSWORD_SMTP_DARI_PROVIDER"
MAIL_FROM_ADDRESS=ALAMAT_PENGIRIM_TERVERIFIKASI
MAIL_FROM_NAME="HSNCO"
```

Port 587 memakai koneksi SMTP dengan STARTTLS sesuai server; untuk implicit TLS port 465 gunakan MAIL_SCHEME=smtps. Gunakan pengaturan asli provider. Hapus MAIL_URL lama bila menggunakan field terpisah; MAIL_URL kosong pun dapat mengganggu parser konfigurasi, sehingga lebih baik dihapus atau bernilai null. Password yang mengandung # atau spasi harus dibungkus tanda kutip. Tidak ada fallback otomatis ke log yang menyamarkan kegagalan SMTP. Pengiriman memakai send langsung, sehingga aktivasi/OTP tidak memerlukan queue worker.

Simpan environment, redeploy/restart aplikasi lalu jalankan:

```sh
php artisan migrate --force
php artisan config:clear
php artisan config:cache
php artisan hsnco:mail-test
```

Migration sudah dijalankan hanya pada database lokal. Tidak ada pembuatan ulang APP_KEY atau reset database.

Perintah mail-test tanpa email hanya memeriksa konfigurasi tanpa koneksi/pengiriman. Nilai password tidak dicetak. Untuk benar-benar mengirim email uji, jalankan sendiri salah satu tujuan yang diizinkan:

```sh
php artisan hsnco:mail-test arvinz24wicaksono@gmail.com
```

Perintah menolak mailer log dan penerima di luar daftar. Sukses SMTP berarti transport menerima pesan, bukan jaminan pesan masuk inbox; cek spam dan status provider. Sesudah uji SMTP berhasil, gunakan formulir aktivasi. Pengiriman ke inbox nyata belum diuji pada implementasi ini karena provider belum ditentukan.

## Hasil pengujian

ActivationAllowlistTest, ManualAuthenticationTest dan GmailOAuthTest: 26 pengujian lulus (126 assertion), mencakup tiga email/peran, normalisasi, penolakan email lain, penolakan eskalasi peran, OTP pertama setelah login, verifikasi sampai aktif, pencabutan izin sebelum token dan sebelum OTP, akun aktif existing tidak diubah, serta diagnostik mailer log/SMTP.

node tools/smtp-loopback-qa.mjs berhasil menerima satu pesan dari transport SMTP Laravel pada server uji loopback. Tidak ada email eksternal dikirim dan tidak ada kredensial aktual dipakai pada pengujian itu. .env.example dikosongkan pada APP_KEY dan DB_PASSWORD karena merupakan file contoh yang dilacak Git, bukan tempat menyimpan rahasia. .env aktual dipertahankan. Belum di-push atau diterapkan ke production.
Gmail melalui Google Cloud Console kini didukung melalui transport Gmail API/OAuth. Lihat docs/GMAIL_OAUTH.md. Konfigurasi .env lokal telah dialihkan ke mailer gmail; secret pengganti dan persetujuan akun pengirim tetap diperlukan sebelum email nyata dapat dikirim.


Alur aktivasi pengguna tidak membuka Google OAuth. Google OAuth hanya digunakan operator untuk menghubungkan akun pengirim. Form kata sandi mengunci email penerima tautan; server tetap memvalidasi kecocokan email, token, masa berlaku dan izin aktivasi. Setelah kata sandi disimpan pengguna tetap belum login, lalu diarahkan ke halaman login. OTP baru dikirim setelah email dan kata sandi berhasil diverifikasi saat login.

## Tambahan akun Jaya sebagai Admin

Pemilik website menyetujui `jaya.digitalkonsultan@gmail.com` sebagai Admin. Migration `2026_10_20_000004_allow_jaya_admin_activation.php` menambahkan/mengaktifkan baris acuan saja. Tidak ada perubahan struktur tabel atau akun existing. Akun yang telah dihapus dapat meminta tautan aktivasi kembali setelah baris acuan aktif. Pengguna tetap harus membuat kata sandi dan memverifikasi OTP saat login.

Setelah push dan deployment, pada terminal aplikasi EasyPanel jalankan migration tambahan secara khusus agar tidak terhalang migration lama yang statusnya belum tersinkron:

```bash
php artisan migrate --path=database/migrations/2026_10_20_000004_allow_jaya_admin_activation.php --force
```

Tabel `activation_email_allowlist` harus sudah tersedia. Jangan menjalankan `migrate:fresh`. Migration ini tidak mengirim email. Kunjungi `/activate` untuk meminta tautan baru. Rollback migration ini tidak menghapus izin; penonaktifan izin dilakukan eksplisit melalui pengelolaan acuan.