# Autentikasi manual HSNCO

Autentikasi menggunakan guard `web` Laravel (driver `session`, provider Eloquent `App\Models\User`) tanpa starter kit. Halaman admin memerlukan akun aktif, OTP email yang sudah dikonfirmasi, dan state 2FA sesi yang sesuai dengan `auth_version` akun.

## Alur akun

1. Jalankan `php artisan user:create-super-admin` untuk membuat Super Administrator pertama. Perintah meminta email, nama, dan password; tidak ada password bawaan di repository.
2. Super Administrator membuka `/admin/users/create`, mendaftarkan email dan role. Akun dibuat sebagai `invited` tanpa password.
3. Pemilik email membuka `/activate`, meminta tautan, lalu membuat nama dan password pada tautan yang berlaku 60 menit.
4. Akun menjadi `pending_2fa`. Setelah password login atau aktivasi, sistem mengirim OTP email enam digit yang berlaku lima menit.
5. OTP sukses mengaktifkan akun, mencatat login, membuat sesi penuh, dan menampilkan recovery code sekali saja.

Link aktivasi disimpan sebagai SHA-256 hash. OTP disimpan sebagai hash password Laravel, terikat pada ID sesi dan pengguna, serta dikonsumsi sekali. Resend membatalkan challenge sebelumnya.

## Konfigurasi produksi

Gunakan cache bersama (Redis atau database) untuk `CACHE_STORE`, dan session database atau Redis untuk `SESSION_DRIVER` pada lebih dari satu instance. Jangan menggunakan `array` cache. Atur `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, dan `SESSION_SAME_SITE=lax` pada HTTPS.

OTP dan aktivasi memerlukan mail transport nyata: set `MAIL_MAILER=smtp` beserta host, port, username, password, dan `MAIL_FROM_ADDRESS`. `MAIL_MAILER=log` hanya untuk pengembangan; email tidak dikirim ke pengguna pada mode itu.

Nilai batas keamanan dapat diatur melalui prefiks `SECURITY_` di `.env.example`: login 5 percobaan per menit per email+IP, lock sementara 15 menit setelah 5 kegagalan, link aktivasi 60 menit, OTP lima menit, dan cooldown pengiriman ulang 60 detik.

## Audit dan pencabutan akses

`audit_logs` hanya menerima append, tanpa password, kode OTP, token, CSRF, cookie, atau ID sesi. Pengubahan status dan unlock oleh Super Administrator menaikkan `auth_version`, sehingga state login/OTP lama tidak lagi sah pada request admin berikutnya.
