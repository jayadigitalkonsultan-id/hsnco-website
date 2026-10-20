# Perbaikan navigasi Dashboard

Sesi autentikasi dapat masih ada ketika verifikasi OTP sudah kedaluwarsa atau status pending OTP sudah hilang. Sebelumnya /admin mengarah ke OTP, OTP mengarah ke login, lalu middleware guest mengarahkan pengguna yang masih login ke Home.

Perbaikan:
- Pengguna terautentikasi yang membuka halaman guest diarahkan ke Dashboard.
- Dashboard tetap menerima sesi OTP yang valid, dan meneruskan login yang masih pending ke formulir OTP.
- Sesi tanpa verifikasi/pending yang valid diakhiri dan diarahkan ke login ulang dengan pesan yang jelas.
- Formulir OTP tanpa sesi pending mengakhiri autentikasi lama sebelum kembali ke login.
- Perlindungan OTP dan izin backend tetap berlaku.

Validasi lokal: DashboardNavigationTest, ActivationAllowlistTest, ManualAuthenticationTest dan GmailOAuthTest: 30 pengujian lulus, 144 assertion. Termasuk Home ke Dashboard untuk pengguna terverifikasi, kedaluwarsa OTP, pending OTP dan sesi OTP tanpa pending. Belum diverifikasi pada production.