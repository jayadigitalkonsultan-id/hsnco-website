# Perbaikan 403 halaman edit CMS di production

Akun Admin aktif dengan faktor email terkonfirmasi tetap ditolak saat mengedit profesional. Penyebab berhasil direproduksi lokal: jalankan `php artisan route:cache`, kemudian pengujian HTTP editor profesional menghasilkan 403 bahkan untuk Super Admin.

Sebelumnya, `Route::model` berada di loop `routes/admin.php`. Ketika cache route digunakan, file route tidak dieksekusi pada boot berikutnya. Controller CMS generik hanya menerima Request sehingga tidak memiliki implicit binding bertipe model sebagai pengganti. Parameter professional tetap berupa teks ULID; Gate tidak menerima instance Professional yang dibutuhkan policy dan menolak akses.

Pendaftaran seluruh binding modul CMS dipindahkan ke `AppServiceProvider::boot`, yang tetap dijalankan ketika route cache aktif. Definisi route tetap sama, pemeriksaan status akun, OTP dan policy tidak diubah. Tidak ada perubahan database.

File utama: app/Providers/AppServiceProvider.php, routes/admin.php, tests/Feature/ProfessionalPortfolioImportTest.php. Pengujian editor kini memakai peran Admin (sebelumnya Super Admin).

Validasi nyata: sebelum perbaikan, pengujian editor cached-routes gagal 403. Setelah perbaikan, ProfessionalPortfolioImportTest dan AboutPageTest lulus dengan cache route aktif: 10 pengujian, 144 assertion. Pengujian yang sama tanpa cache route juga lulus. Cache route lokal dibersihkan setelah pengujian. Belum diverifikasi di production.

Setelah push dan deploy ulang EasyPanel, entrypoint container otomatis membuat cache route. Perbaikan tetap bekerja dalam mode tersebut. Jika membutuhkan pemulihan sementara sebelum deploy kode baru, jalankan `php artisan route:clear` pada terminal aplikasi hosting lalu coba halaman edit kembali. Ini hanya sementara; deploy ulang kode lama dapat membuat cache yang bermasalah kembali.