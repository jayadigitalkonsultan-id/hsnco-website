# Gmail OAuth untuk HSNCO

## Status implementasi

Integrasi lokal menggunakan Gmail API, bukan SMTP password. Pengirim: jaya.digitalkonsultan@gmail.com. Client ID publik telah diisi hanya pada .env lokal; Client Secret sengaja kosong karena secret sebelumnya telah terungkap. Isi secret pengganti sendiri. File JSON kredensial tidak disalin, tidak dimasukkan ke Git/Docker, dan tidak diperlukan saat runtime.

Konfigurasi lokal sekarang MAIL_MAILER=gmail dan MAIL_FROM_ADDRESS/GMAIL_SENDER_EMAIL=jaya.digitalkonsultan@gmail.com. Sampai secret diisi dan akun Google memberikan persetujuan, pengiriman akan gagal secara jelas; tidak ada fallback diam-diam ke log. Tidak ada email nyata yang dikirim selama implementasi. Akun Google belum dihubungkan. Produksi dan Google Cloud Console tidak diubah dari sini.

## Google Cloud Console

1. Pilih project hsnco-oauth dan aktifkan Gmail API melalui APIs & Services / Library.
2. Reset secret OAuth yang pernah terungkap pada Google Auth Platform / Clients. Gunakan secret pengganti untuk aplikasi.
3. Branding: nama aplikasi HSNCO Website dan kontak pengelola. Audience External untuk akun Gmail pribadi; tambahkan jaya.digitalkonsultan@gmail.com sebagai Test user selama Testing. Penerima aktivasi tidak perlu menjadi test user OAuth: hanya akun pengirim yang memberikan izin.
4. Data Access: tambahkan openid, email, dan https://www.googleapis.com/auth/gmail.send. Izin email dipakai untuk memeriksa akun pengirim; tidak meminta izin membaca inbox.
5. Pada OAuth Web Client yang sama, Authorized redirect URIs harus mencakup URL yang benar-benar dipakai:

Lokal:
```
http://127.0.0.1:8000/auth/google/gmail/callback
```
Production:
```
https://hsncoatlaw.com/auth/google/gmail/callback
```

URI harus sama persis, termasuk skema, host, port, path dan tanpa slash tambahan. Callback sudah diimplementasikan. Ini bukan URI login admin dan tidak mengubah autentikasi pengunjung.

## Hubungkan lokal

Isi pada .env lokal (jangan commit):

```env
MAIL_MAILER=gmail
MAIL_FROM_ADDRESS=jaya.digitalkonsultan@gmail.com
MAIL_FROM_NAME="HSNCO"
GMAIL_CLIENT_ID=CLIENT_ID_OAUTH_ANDA
GMAIL_CLIENT_SECRET="CLIENT_SECRET_PENGGANTI"
GMAIL_SENDER_EMAIL=jaya.digitalkonsultan@gmail.com
GMAIL_REDIRECT_URI=http://127.0.0.1:8000/auth/google/gmail/callback
```

Jalankan dari root project:
```sh
php artisan migrate
php artisan config:clear
php artisan hsnco:gmail-connect --status
php artisan hsnco:gmail-connect
```

Perintah terakhir baru dapat membuat tautan jika konfigurasi lengkap. Buka tautan dari terminal dalam 10 menit, masuk sebagai jaya.digitalkonsultan@gmail.com, dan setujui izin pengiriman. Pastikan server lokal port 8000 tetap berjalan. Callback memverifikasi identitas email Google sebelum menyimpan koneksi. Akun lain ditolak walaupun juga termasuk email aktivasi HSNCO.

Sesudah berhasil:
```sh
php artisan hsnco:gmail-connect --status
php artisan hsnco:mail-test
php artisan hsnco:mail-test arvinz24wicaksono@gmail.com
```

mail-test tanpa penerima tidak melakukan koneksi atau mengirim pesan; ia memeriksa konfigurasi dan keberadaan koneksi. Perintah dengan penerima benar-benar mengirim email, sehingga jalankan hanya saat siap. Tujuan uji dibatasi tabel aktivasi. Respons sukses Gmail API bukan jaminan pesan masuk inbox; periksa inbox/spam.

## EasyPanel

Deploy kode, isi Environment menggunakan docker/gmail.env.example dan secret pengganti. Gunakan GMAIL_REDIRECT_URI production dan MAIL_FROM_ADDRESS yang sama dengan pengirim. Kemudian:
```sh
php artisan migrate --force
php artisan config:clear
php artisan config:cache
php artisan hsnco:gmail-connect
```

Otorisasi lokal dan production harus dilakukan pada database masing-masing. Token tersimpan dalam database menggunakan enkripsi APP_KEY yang sudah ada, bukan file sementara di container. Jangan generate ulang APP_KEY saat deployment. Token lama tidak perlu dikirim melalui chat, .env atau Git. Tidak ada migration otomatis pada entrypoint dan tidak ada queue worker tambahan untuk send langsung.

## Perilaku dan keamanan

- Laravel Mail memakai custom transport gmail. Email aktivasi, OTP, reset password dan notifikasi existing mengikuti mailer yang dipilih.
- Tautan otorisasi hanya dibuat dari console server, tidak ada endpoint publik untuk membuat tautan atau mengganti koneksi.
- OAuth state acak disimpan sebagai SHA-256 dan hanya sekali pakai selama 10 menit. PKCE verifier terenkripsi; token disimpan terenkripsi dan disembunyikan dari serialisasi model.
- Callback memakai no-store dan no-referrer; tidak mengembalikan token atau secret. OAuth ini tidak memasukkan pengguna ke admin HSNCO.
- Scope pengiriman wajib diberikan dan email Google harus terverifikasi serta sama dengan GMAIL_SENDER_EMAIL.
- Access token diperbarui otomatis dengan refresh token sebelum kedaluwarsa. Respons 401 memicu sekali refresh/retry; error lain tidak diulang untuk menghindari pengiriman ganda.
- MAIL_FROM_ADDRESS harus sama dengan pengirim yang diotorisasi. Tidak menonaktifkan verifikasi TLS.
- Mode Testing Google untuk scope Gmail dapat membuat refresh token kedaluwarsa setelah 7 hari. Untuk production, atur status publikasi aplikasi serta persyaratan verifikasi Google yang berlaku. Jika izin dicabut atau refresh token tidak berlaku, jalankan gmail-connect dan lakukan persetujuan ulang.

## Pengujian aktual

GmailOAuthTest + ActivationAllowlistTest + ManualAuthenticationTest: 25 tes lulus / 108 assertion. Meliputi PKCE/state, token terenkripsi, callback replay, email berbeda, scope kurang, state salah/kedaluwarsa, refresh token, MIME transport, 401 retry, From berbeda, missing secret dan diagnosis tanpa email. Endpoint Google di-fake; tidak ada klaim koneksi akun Google atau penerimaan inbox nyata sudah berhasil.

## Sumber resmi

- https://developers.google.com/identity/protocols/oauth2/web-server
- https://developers.google.com/workspace/gmail/api/guides/sending
- https://developers.google.com/workspace/gmail/api/auth/scopes
- https://developers.google.com/workspace/guides/configure-oauth-consent
- https://support.google.com/cloud/answer/15549945
Pengirim sementara diganti atas permintaan pemilik menjadi jaya.digitalkonsultan@gmail.com. Email pengirim berbeda dari daftar akun yang diizinkan aktivasi; perubahan ini tidak menambahkan Jaya ke allowlist atau memberi akses admin. Tetap diperlukan persetujuan OAuth dari akun pengirim, meskipun akun tersebut memiliki project Google Cloud. Tidak ada token milik email lain yang dipindahkan ke pengirim baru.
