# Fondasi database HSNCO — Task 01

Implementasi 16 Oktober 2026. Detail kolom dan dependensi tetap di `task.md`; file implementasi berada di `database/migrations`, `app/Models`, `app/Enums`, `app/Policies`, dan `app/Http/Requests`.

## Kondisi awal dan hasil

- MySQL lokal 8.0.30, database `hsnco`, sebelumnya kosong: tidak memiliki tabel, migration history, atau akun untuk dikonversi.
- Baseline: 17 test/634 assertion lulus. Hash 86 file route, view, komponen, aset, dan test awal tersimpan di `FOUNDATION_BASELINE.json`; semuanya tetap cocok setelah implementasi.
- Tambahan 18 migration, sehingga 21 migration total. Terdapat 26 tabel termasuk tabel infrastruktur dan repository migration.
- Model domain menggunakan ULID, relasi foreign ULID, casts JSON/datetime/enum, morph map tetap, serta soft delete sesuai rancangan. Pivot praktik/profesional menggunakan primary key gabungan.
- Isi seed: 7 halaman, 35 section, 9 praktik, 6 profil contoh, 3 pratinjau publikasi, 25 revisi draft, 19 metadata media, 19 penggunaan media, dan 7 pengaturan.
- Semua konten domain tetap draft; tidak ada user, password default, enquiry sintetis, atau email undangan yang dibuat/dikirim oleh seed.
- Frontend existing tetap membaca Blade statis. Koneksi ke CMS, layanan publikasi, middleware login/2FA, dan UI admin belum termasuk fase ini.

## Konfigurasi

`.env` lokal sudah memilih MySQL `hsnco`; kredensial yang ada dipertahankan. `.env.example` mengikuti konfigurasi pengembangan MySQL, sedangkan `docker/easypanel.env.example` menyediakan placeholder service/akun database produksi. Gunakan user database produksi khusus, bukan akun root lokal.

Waktu aplikasi/database ditangani dalam UTC. `config/cms.php` menyediakan `APP_DISPLAY_TIMEZONE=Asia/Jakarta` untuk tampilan. Connection MySQL Laravel memakai sesi `+00:00` secara eksplisit agar konversi TIMESTAMP tidak bergantung pada zona waktu server. Nama file backup memakai waktu UTC. Secret disediakan melalui environment dan tidak dimasukkan ke repository.

Session/cache tetap file dan queue sync untuk fase ini. Pada banyak instance produksi, pilih penyimpanan bersama pada fase deployment; pembatasan login tidak boleh memakai cache array produksi. Docker sudah memasang pdo_mysql dan pdo_sqlite.

PHP CLI Laragon memiliki pdo_mysql aktif. pdo_sqlite tersedia sebagai extension, tetapi tidak diaktifkan global. `tools/test.ps1` memuat extension itu hanya untuk proses pengujian, sehingga tidak mengubah php.ini Laragon atau driver aplikasi lokal. PHPUnit dipaksa menggunakan SQLite `:memory:` dan DB_URL kosong agar test tidak mengarah ke MySQL lokal.

## Menjalankan dan memeriksa

```powershell
php artisan database:preflight
php artisan database:backup
php artisan migrate --no-interaction
php artisan db:seed --no-interaction
php artisan migrate:status
powershell -NoProfile -File tools/test.ps1
```

Seed `FrontendContentSeeder` bersifat idempotent: memakai snapshot teks dari frontend existing dan tidak menimpa perubahan editor atau menghidupkan kembali konten soft deleted. Section seed merupakan data awal dengan source_text/requires_verification; field editor per tipe akan diperkaya pada fase CMS. Gambar existing memakai disk `frontend` yang menunjuk ke `public/assets`; upload mendatang menggunakan disk storage terpisah. Referensi entitas bersama menggunakan ULID, bukan duplikasi data praktik/profesional.

`DatabaseSeeder` tidak lagi membuat akun `test@example.com`. Factory akun hanya untuk pengujian; `.invited()` menghasilkan akun tanpa password, dan `.active(role)` dipakai saat menguji policy. Bootstrap Super Administrator interaktif dikerjakan pada fase autentikasi.

## Perlindungan migration dan data lama

`database:preflight` hanya membaca koneksi yang dipilih. Perintah menolak `users.id` integer, nilai ID non-ULID, atau akun existing yang belum memiliki rencana role/status/onboarding. Migration keamanan juga berhenti sebelum perubahan jika menemukan ID integer atau akun existing; default invited tidak boleh diam-diam menonaktifkan akun lama.

Untuk lingkungan yang sudah berisi user integer: inventarisasi semua foreign key, buat pemetaan integer → ULID, lakukan backfill bertahap/terverifikasi dalam migration khusus, dan tetapkan role/status secara eksplisit. Jangan menjalankan migration fondasi ini secara paksa atau menggunakan migrate:fresh pada database berisi data.

Rollback telah diuji pada database sementara. Mengembalikan nama/password menjadi NOT NULL ditolak jika masih ada akun undangan dengan nilai null. Soft delete tidak memicu cascade; hard delete parent membersihkan pivot/catatan sesuai foreign key. Media yang direferensikan dilindungi foreign key dan pemeriksaan model, termasuk referensi dari konten soft deleted. Audit log append-only dan snapshot revisi published tidak dapat diubah lewat model; operasi retensi langsung pada database memerlukan prosedur operasional terpisah.

## Batas kontrak FormRequest dan policy

FormRequest dasar mencakup halaman, section, praktik, profesional, publikasi, revisi, media, pengaturan, enquiry/catatan, dan pendaftaran email undangan. Role/status publikasi, actor ID, path file, dan metadata autentikasi yang harus ditetapkan server tidak diterima sembarangan dari input pengguna.

Policy dasar: hanya super_admin aktif dapat mengundang akun; editor aktif membuat/mengubah draft miliknya; admin/super_admin melakukan review/publish sesuai policy; enquiry/pengaturan dibatasi ke pengelola. Update langsung terhadap konten published ditolak; edit harus melalui draft revisi. Izin policy belum menggantikan middleware autentikasi dan verifikasi 2FA yang akan dibuat pada fase 03.

Validasi JSON/rich text per tipe section, sanitasi HTML, alokasi versi revisi secara atomik, transaksi publish, idempotensi submission/challenge, pencabutan sesi, log autentikasi, dan lifecycle polymorphic akan diimplementasikan bersama layanan pada fase masing-masing. Tabel security dan casts secret terenkripsi menyediakan fondasinya; bukan bukti alur 2FA sudah aktif. Pengguna sudah memilih OTP melalui email. Implementasi fase 03 memakai method=email dan hash OTP pada two_factor_challenges; secret/last_used_step pada akun tetap null, tanpa setup TOTP/QR. Batas awal: 6 digit, 5 menit, 5 kesalahan, cooldown resend 60 detik. Skema Task 01 sudah mendukung pilihan ini sehingga tidak membutuhkan perubahan tabel.

## Backup dan restore

`php artisan database:backup` memanggil mysqldump dari `DB_DUMP_BINARY` (default `mysqldump` di PATH). Password disalurkan lewat environment proses anak, bukan argumen CLI atau output. Dump memakai single transaction dan disimpan di `storage/app/private/backups` dengan nama unik serta checksum SHA-256. Jangan mempublikasikan folder ini; backup dapat berisi data pribadi. Salin backup terenkripsi ke penyimpanan terpisah sesuai retensi operasional.

Restore manual dilakukan pada database tujuan yang sudah dibuat dan diverifikasi, memakai mysql client melalui stdin dari file dump. Jangan menggunakan `--databases` saat membuat dump untuk alur ini karena target restore dipilih terpisah. Sebelum restore produksi, hentikan perubahan data, cocokkan checksum, dan gunakan prosedur pemulihan/deployment yang disetujui. APP_KEY juga harus dicadangkan secara aman; diperlukan untuk membaca secret 2FA terenkripsi.

Backup skema lokal sebelum seed dan backup lengkap setelah seed telah dibuat di folder private tersebut. Backup post-seed terakhir: `20261015_175104_nc8fy0xt.sql`, SHA-256 `84d0098b577338eec968b4ffbb08c5412e8494a4ba1d6cbd45e15e092d864106`. Tanggal nama file menggunakan UTC.

## Verifikasi MySQL terisolasi

```powershell
php tools/verify-mysql-foundation.php
```

Perintah menggunakan kredensial pengembangan untuk membuat dua schema bernama acak dengan prefix `hsnco_foundation_check_` / `hsnco_foundation_restore_`. Dibutuhkan izin create/drop database serta mysqldump/mysql client di PATH (`DB_DUMP_BINARY`/`DB_CLIENT_BINARY` dapat diatur). Jalankan pada server pengembangan, bukan VPS produksi.

Alur: migrate → rollback → migrate → seed dua kali → backup → restore pada schema kedua → cocokkan hasil. Schema sementara yang berhasil dibuat oleh proses ini dibersihkan pada akhirnya; database lokal tidak di-reset. Perintah memeriksa identitas koneksi dan membandingkan daftar/jumlah data tabel lokal sebelum/sesudah. Ringkasan fingerprint skema tersimpan di `FOUNDATION_MYSQL_VERIFICATION.json`.

Pada percobaan awal, konfigurasi koneksi uji masih membawa nama koneksi asli sehingga tabel sempat terbentuk di database lokal kosong tanpa repository migration lokal. Isolasi diperbaiki dengan membuang nama koneksi hasil salinan dan menggunakan parameter koneksi eksplisit. Setelah backup, seluruh 25 tabel lokal kosong dibandingkan kolom/index/foreign key-nya dengan skema MySQL yang diuji independen; 21 record migration direkonsiliasi tanpa menghapus tabel. Seed baru dijalankan setelah kesesuaian tersebut terverifikasi. Pengujian MySQL berikutnya berhasil tanpa mengubah data lokal.

Task deployment VPS, verifikasi staging, dan backup terjadwal produksi tetap pada fase 15.

## Hasil pengujian

- Suite lengkap melalui `tools/test.ps1`: 37 test, 759 assertion lulus, termasuk 17 test existing.
- SQLite in-memory: migrate/rollback/reapply, ULID/factory/relasi, foreign key, nullOnDelete/restrict/cascade, reservasi slug setelah arsip, duplikasi versi/submission key, policy, cast secret, seed idempotent, serta proteksi database legacy dan rollback undangan.
- MySQL 8.0.30: migrate/rollback/reapply, seed berulang, backup/restore pada dua schema sementara, dan pemeriksaan bahwa daftar/jumlah data lokal tidak berubah selama verifikasi.
- Baseline route/view/aset/test existing tetap identik. Audit kompatibilitas aset dan proxy existing ikut lulus dalam suite; belum ada perubahan UI yang memerlukan pengujian browser baru.

Ringkasan jumlah tabel/data, migration yang sudah diterapkan, serta hasil akhir tersimpan di `FOUNDATION_RESULT.json`.
