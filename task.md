# Task Pengerjaan Website HSNCO

Tanggal penyusunan: 16 Oktober 2026. Kerjakan fase secara berurutan; checklist belum dicentang berarti pekerjaan belum selesai.

## Acuan dan kondisi awal

- Aplikasi: Laravel 12 di `D:\laragon\www\hsnco-website`.
- Template admin: `D:\laragon\www\hsnco-bootstrap-admin`, sebagai referensi tampilan dan interaksi.
- Frontend: tujuh halaman Blade di `resources/views/frontend`, dengan route statis di `routes/web.php`.
- Admin referensi masih HTML statis dengan CRUD demo melalui localStorage; belum memiliki autentikasi, database, atau publikasi ke website.
- `users.id` sudah ULID, `User` sudah memakai `HasUlids`, dan `sessions.user_id` sudah `foreignUlid`. Pertahankan konsistensi ini.
- Form konsultasi masih demonstrasi; `POST /contact` belum tersedia. Konten profil, penghargaan, angka firma, kontak, dan publikasi masih mengandung placeholder.
- Tujuan: admin Laravel dengan autentikasi session manual dan CMS yang memasok konten ke frontend yang sudah ada.
- Tambahan kebutuhan: akun berbasis undangan Super Administrator. Email harus didaftarkan lebih dulu; pengguna meminta aktivasi, menerima tautan membuat password, lalu menyelesaikan 2FA sebelum mengakses dashboard dan membuat konten.
- Metode 2FA yang dipilih pengguna: kode OTP melalui email terdaftar, setelah password benar. Rancangan awal kode 6 digit, berlaku 5 menit, maksimal 5 kesalahan, dan jeda kirim ulang 60 detik.

## 01. Fondasi proyek dan database

- [x] 01.01 Catat baseline tujuh halaman, komponen, route, aset, dan pengujian frontend sebelum perubahan.
- [x] 01.02 Tentukan database lokal/produksi dan konfigurasi `.env`, koneksi, zona waktu penyimpanan, serta backup. Rahasia tetap di environment.
- [x] 01.03 Pastikan ekstensi database PHP tersedia. Jika menggunakan SQLite untuk pengujian, aktifkan `pdo_sqlite` pada lingkungan pengujian.
- [x] 01.04 Tetapkan ULID untuk primary key model domain baru dan `foreignUlid` untuk referensinya. ID sesi tetap string sesuai mekanisme session Laravel.
- [x] 01.05 Periksa apakah migration lama pernah dijalankan. Untuk database berisi data, siapkan migration lanjutan dan pemetaan ID/relasi; jangan memakai `migrate:fresh` pada data yang perlu dipertahankan.
- [x] 01.06 Implementasikan skema pada bagian “Rancangan tabel dan urutan migration” di bawah: users, audit_logs, site_settings, pages, page_sections, media, practice_areas, professionals, insights, content_revisions, enquiries, enquiry_notes, serta tabel relasinya.
- [x] 01.07 Pisahkan konten halaman dari entitas bersama. Bidang praktik, profesional, publikasi, dan informasi kontak memakai sumber data yang sama di semua halaman.
- [x] 01.08 Tetapkan validasi dasar, FormRequest, policy, status publikasi, urutan tampil, dan aturan penghapusan/arsip untuk setiap modul. Validasi payload editor terperinci dan layanan publish diselesaikan pada fase 04.
- [x] 01.09 Siapkan factory dan seed konten awal dari frontend. Simpan placeholder sebagai draft atau konten yang ditandai jelas.

Hasil implementasi Task 01: MySQL lokal `hsnco`, 18 migration baru (21 total), model/relasi/casts, enum status/role, FormRequest/policy dasar, serta seed draft tanpa akun/password default. Bukti baseline, hasil pemeriksaan, dan cara backup/pengujian tersedia di [docs/DATABASE_FOUNDATION.md](docs/DATABASE_FOUNDATION.md). Frontend masih membaca Blade statis sampai integrasi CMS pada fase berikutnya.

Kriteria selesai: database dapat dimigrasikan dan diuji tanpa menghapus data existing; hubungan ULID konsisten.

### Rancangan tabel dan urutan migration

Status: 18 file migration berikut sudah dibuat di `database/migrations` dan diterapkan ke MySQL lokal `hsnco` pada Task 01. Skema juga diuji pada SQLite in-memory dan MySQL sementara. Penambahan tabel undangan/2FA belum berarti alur autentikasinya sudah diimplementasikan.

#### Konvensi skema

- Primary key tabel domain menggunakan `$table->ulid('id')->primary()`; model menggunakan `HasUlids`. Kolom relasi memakai `foreignUlid()->constrained(...)` dengan aturan penghapusan eksplisit.
- `string(n)` berarti VARCHAR dengan panjang n; ULID disimpan sebagai CHAR(26). `timestamps` berarti `created_at` dan `updated_at`; waktu disimpan UTC dan ditampilkan sesuai zona waktu aplikasi.
- Kolom yang ditandai nullable boleh kosong; kolom lainnya wajib terisi kecuali memiliki default. `timestamps`/`softDeletes` mengikuti perilaku nullable bawaan Laravel.
- Status dan role menggunakan string dengan daftar nilai yang divalidasi pada aplikasi, bukan ENUM database. Role: `super_admin`, `admin`, dan `editor`; belum membutuhkan tabel roles/permissions tersendiri. Hanya Super Administrator yang mendaftarkan email, mengundang akun, dan menetapkan role.
- Gunakan index pada foreign key dan kolom filter utama; jangan menambahkan index pada isi JSON/teks panjang tanpa kebutuhan query yang jelas.
- Tabel infrastruktur Laravel mengikuti kontrak driver: session ID, cache key, password reset, jobs, job_batches, dan failed_jobs tidak otomatis diubah menjadi ULID.
- Penghapusan akun menggunakan penonaktifan sebagai tindakan utama. Soft delete konten tidak memicu cascade foreign key; penanganan publikasi dan arsip tetap dilakukan aplikasi.

#### Daftar file migration

Nama file di bawah sesuai migration Task 01 yang sudah dibuat dan diuji.

| Urutan | Nama migration | Tabel/tujuan |
| --- | --- | --- |
| 01 | `2026_10_16_000001_add_security_fields_to_users_table.php` | Status akun, role, counter, temporary lock, login terakhir |
| 02 | `2026_10_16_000002_create_audit_logs_table.php` | Audit autentikasi dan aktivitas admin |
| 03 | `2026_10_16_000003_create_media_table.php` | Metadata file dan gambar |
| 04 | `2026_10_16_000004_create_site_settings_table.php` | Identitas firma, kontak, navigasi, SEO umum |
| 05 | `2026_10_16_000005_create_pages_table.php` | Metadata tujuh halaman frontend |
| 06 | `2026_10_16_000006_create_page_sections_table.php` | Section halaman sesuai urutan tampilan |
| 07 | `2026_10_16_000007_create_practice_areas_table.php` | Direktori praktik hukum |
| 08 | `2026_10_16_000008_create_professionals_table.php` | Direktori profesional |
| 09 | `2026_10_16_000009_create_practice_area_professional_table.php` | Relasi banyak-ke-banyak praktik dan profesional |
| 10 | `2026_10_16_000010_create_insights_table.php` | Publikasi/artikel |
| 11 | `2026_10_16_000011_create_content_revisions_table.php` | Draft, review, snapshot publikasi, riwayat revisi |
| 12 | `2026_10_16_000012_create_media_usages_table.php` | Referensi media dari section, setting, dan revisi |
| 13 | `2026_10_16_000013_create_enquiries_table.php` | Permintaan konsultasi dari kedua form publik |
| 14 | `2026_10_16_000014_create_enquiry_notes_table.php` | Catatan internal tindak lanjut |
| 15 | `2026_10_16_000015_create_user_invitations_table.php` | Izin onboarding dan token tautan membuat password |
| 16 | `2026_10_16_000016_create_user_two_factor_authentications_table.php` | Metode, secret terenkripsi, dan status aktivasi 2FA |
| 17 | `2026_10_16_000017_create_two_factor_recovery_codes_table.php` | Kode pemulihan sekali pakai yang di-hash |
| 18 | `2026_10_16_000018_create_two_factor_challenges_table.php` | Challenge login dengan batas waktu dan percobaan |

Migration `users`, `sessions`, `password_reset_tokens`, cache, dan jobs sudah tersedia. Tambahkan kolom keamanan melalui migration baru; jangan mengubah migration lama yang sudah diterapkan pada database berisi data. Sebelum implementasi, periksa tipe ID aktual pada database karena perubahan file migration ke ULID tidak otomatis mengubah tabel yang pernah dibuat.

#### A. `users` — perluasan autentikasi

Kolom existing: `id` ULID PK, `name`, `email` unique, `email_verified_at` nullable, `password`, `remember_token` nullable, timestamps. Pada migration perluasan, ubah `name` dan `password` menjadi nullable agar Super Administrator dapat mendaftarkan email sebelum pengguna mengisi nama/password. Akun tanpa password tidak boleh melewati pemeriksaan kredensial.

| Kolom baru | Tipe/default | Kegunaan |
| --- | --- | --- |
| `status` | string(20), default `invited`, index | invited / pending_2fa / active / inactive |
| `role` | string(20), default `editor` | super_admin / admin / editor; ditetapkan server |
| `failed_login_attempts` | unsignedInteger, default 0 | Counter kegagalan dalam jendela waktu login |
| `last_failed_login_at` | timestamp nullable | Menentukan jendela counter |
| `locked_until` | timestamp nullable, index | Batas waktu temporary lock |
| `last_login_at` | timestamp nullable | Login berhasil terakhir |
| `last_login_ip` | string(45) nullable | Mendukung IPv4 dan IPv6 |
| `password_changed_at` | timestamp nullable | Mendukung kebijakan pencabutan sesi |
| `invited_by` | foreignUlid nullable → users, nullOnDelete | Super Administrator yang mendaftarkan akun |
| `activated_at` | timestamp nullable | Aktivasi selesai setelah password dan 2FA terkonfirmasi |
| `auth_version` | unsignedInteger, default 0 | Dibandingkan dengan versi pada sesi untuk pencabutan sesi lama |

Super Administrator pertama dibuat melalui perintah interaktif yang aman, lalu menyelesaikan enrollment 2FA sebelum memperoleh akses penuh. Akun existing harus memiliki kebijakan backfill role/status dan enrollment 2FA sebelum proteksi diberlakukan. Cast datetime dan integer di model; field role/status/counter tidak boleh diisi dari input publik tanpa izin. Status inactive merupakan pencabutan izin oleh Super Administrator; login gagal sementara memakai `locked_until`, bukan mengubah inactive menjadi active.

#### B. `audit_logs`

- Kolom: `id` ULID PK; `user_id` foreignUlid nullable → users, nullOnDelete; `event` string(100); `outcome` string(20); `reason` string(100) nullable; `ip_address` string(45) nullable; `user_agent` text nullable; `device` string(255) nullable; `metadata` JSON nullable; `created_at` timestamp.
- Target aktivitas opsional: `subject_type` string(100) nullable dan `subject_id` ULID nullable. Keduanya merupakan referensi historis, bukan foreign key lintas tabel.
- Index: `(event, created_at)`, `(user_id, created_at)`, `(ip_address, created_at)`, dan `(subject_type, subject_id)`.
- Log bersifat append-only pada layanan aplikasi; tidak menggunakan `updated_at` atau soft delete. Hapus hanya lewat proses retensi berizin. Jangan menyimpan rahasia autentikasi atau isi pesan enquiry di metadata.

#### C. `media`

- Kolom: `id` ULID PK; `disk` string(50); `path` string(255); `original_name` string(255); `mime_type` string(100); `size` unsignedBigInteger; `width`/`height` unsignedInteger nullable; `alt_text` string(255) nullable; `caption` text nullable; `visibility` string(20), default `public`; `uploaded_by` foreignUlid nullable → users, nullOnDelete; timestamps; softDeletes.
- Constraint: unique `(disk, path)`; index `(visibility, created_at)`.
- Simpan path storage, bukan URL absolut atau file base64. Dokumen internal memakai visibility `private` dan akses terotorisasi.

#### D. `site_settings`

- Kolom: `id` ULID PK; `group` string(50); `key` string(100) unique; `value` JSON; `updated_by` foreignUlid nullable → users, nullOnDelete; timestamps.
- Kelompok awal: `identity`, `contact`, `navigation`, `footer`, `seo`. Contoh key: `identity.name`, `contact.office_address`, `navigation.main`.
- Index: `group`. Validasi tipe/struktur JSON berdasarkan key; key yang dapat diubah memakai allowlist.
- Kontak menyertakan status verifikasi pada struktur nilainya. Rahasia mail, storage, dan password tidak disimpan di sini. Referensi gambar dicatat pula di `media_usages`.

#### E. `pages`

- Kolom: `id` ULID PK; `key` string(50) unique; `slug` string(160) unique; `title` string(255); `template` string(50); `status` string(20), default `draft`; `seo_title` string(255) nullable; `seo_description` text nullable; `og_media_id` foreignUlid nullable → media, restrictOnDelete; `published_at` timestamp nullable; `published_revision` unsignedInteger nullable; `created_by`/`updated_by` foreignUlid nullable → users, nullOnDelete; timestamps; softDeletes.
- Index: `(status, published_at)`. `key` awal: home, about, practice-areas, team, insights, contact, sitemap.
- `template` berupa pilihan tetap yang diizinkan server; bukan path Blade yang bebas dikirim pengguna. Named route existing tetap menjadi sumber URL.

#### F. `page_sections`

- Kolom: `id` ULID PK; `page_id` foreignUlid → pages, cascadeOnDelete; `key` string(100); `type` string(50); `heading` string(255) nullable; `content` JSON; `sort_order` unsignedInteger, default 0; `is_visible` boolean, default true; timestamps.
- Constraint: unique `(page_id, key)`; index `(page_id, is_visible, sort_order)`.
- `type` menentukan struktur field: hero, rich_text, image, practice_selection, values, sectors, steps, recognitions, enquiry, latest_insights, timeline, vision_mission, statistics, client_logos, leadership, faq, atau cta.
- Nilai firma, sektor, timeline, FAQ, dan langkah kerja disimpan sebagai daftar terstruktur di JSON. Pilihan praktik/profesional memakai ULID referensi, divalidasi server dan disaring berdasarkan publikasi saat dibaca.
- Section milik versi halaman yang sudah diterbitkan tidak diubah langsung saat editor menyimpan draft; perubahan disimpan melalui `content_revisions`.

#### G. `practice_areas`

- Kolom: `id` ULID PK; `name` string(255); `slug` string(160) unique; `summary` text nullable; `body` longText nullable; `image_id` foreignUlid nullable → media, restrictOnDelete; `sort_order` unsignedInteger, default 0; `is_featured` boolean, default false; `status` string(20), default `draft`; `seo_title` string(255) nullable; `seo_description` text nullable; `published_at` timestamp nullable; `published_revision` unsignedInteger nullable; `created_by`/`updated_by` foreignUlid nullable → users, nullOnDelete; timestamps; softDeletes.
- Index: `(status, sort_order)` dan `(is_featured, status)`. Slug awal mengikuti anchor praktik existing agar tautan tidak putus.

#### H. `professionals`

- Kolom: `id` ULID PK; `name` string(255); `slug` string(160) unique; `position` string(150); `level` string(30); `summary` text nullable; `biography` longText nullable; `qualifications`/`languages` JSON nullable; `photo_id` foreignUlid nullable → media, restrictOnDelete; `email` string(254) nullable; `sort_order` unsignedInteger, default 0; `is_featured` boolean, default false; `is_leadership` boolean, default false; `status` string(20), default `draft`; `published_at` timestamp nullable; `published_revision` unsignedInteger nullable; `created_by`/`updated_by` foreignUlid nullable → users, nullOnDelete; timestamps; softDeletes.
- Index: `(status, level, sort_order)`. `level` awal: partner/associate; `position` menampung jabatan lebih rinci seperti asosiat senior.
- Profil profesional merupakan konten direktori, bukan akun login; tidak otomatis membuat user.

#### I. `practice_area_professional`

- Kolom: `practice_area_id` foreignUlid → practice_areas, cascadeOnDelete; `professional_id` foreignUlid → professionals, cascadeOnDelete; timestamps.
- Primary key gabungan `(practice_area_id, professional_id)` dan index `professional_id`; tidak memerlukan ID auto increment atau ULID tambahan.
- Pivot mendukung satu profesional memiliki beberapa bidang praktik dan sebaliknya.

#### J. `insights`

- Kolom: `id` ULID PK; `title` string(255); `slug` string(160) unique; `excerpt` text nullable; `body` longText nullable; `cover_media_id` foreignUlid nullable → media, restrictOnDelete; `author_id` foreignUlid nullable → professionals, nullOnDelete; `status` string(20), default `draft`; `published_at` timestamp nullable; `published_revision` unsignedInteger nullable; `seo_title` string(255) nullable; `seo_description` text nullable; `created_by`/`updated_by` foreignUlid nullable → users, nullOnDelete; timestamps; softDeletes.
- Index: `(status, published_at)` dan `author_id`. Penulis publik (`author_id`) berbeda dari akun editor (`created_by`/`updated_by`).
- Jadwal publikasi hanya tampil jika status published dan `published_at` sudah tercapai. Isi rich text disanitasi sebelum dipublikasikan.

#### K. `content_revisions`

- Kolom: `id` ULID PK; `revisionable_type` string(50); `revisionable_id` ULID; `version` unsignedInteger; `status` string(20), default `draft`; `payload` JSON; `review_note` text nullable; `created_by`/`reviewed_by`/`published_by` foreignUlid nullable → users, nullOnDelete; `submitted_at`/`reviewed_at`/`published_at` timestamp nullable; timestamps.
- Constraint: unique `(revisionable_type, revisionable_id, version)`; index `(revisionable_type, revisionable_id, status)`.
- Gunakan morph map tetap: page, practice_area, professional, insight. Referensi polymorphic tidak memiliki foreign key database lintas tabel; layanan aplikasi wajib memastikan target ada dan mengatur pembersihan/arsipnya.
- Payload menyimpan snapshot lengkap, termasuk section halaman atau daftar relasi praktik profesional. Counter version harus dialokasikan secara aman dalam transaksi untuk request bersamaan.
- Menyimpan draft tidak mengubah kolom publik pada tabel induk. Saat publish, transaksi menyalin snapshot tervalidasi ke induk/section/pivot dan mengisi `published_revision`; revisi yang sudah published tidak diedit kembali.
- Status revisi: draft/review/published/archived. Unpublish induk mengubah status publik tanpa menghapus riwayat revisi.

#### L. `media_usages`

- Kolom: `id` ULID PK; `media_id` foreignUlid → media, restrictOnDelete; `usable_type` string(50); `usable_id` ULID; `field` string(100); timestamps.
- Constraint: unique `(media_id, usable_type, usable_id, field)` dengan nama index singkat eksplisit; index `(usable_type, usable_id)`.
- Melacak referensi gambar yang tersimpan di JSON/section, setting, dan revisi sehingga tidak hilang dari inventaris penggunaan. Kolom gambar langsung tetap memiliki foreign key sendiri.
- Referensi polymorphic memakai morph map/validasi server. Penghapusan media melalui aplikasi memeriksa usage dan soft delete; foreign key hanya melindungi hard delete.

#### M. `enquiries`

- Kolom: `id` ULID PK; `submission_key` ULID unique; `first_name`/`last_name` string(100); `email` string(254); `phone` string(40) nullable; `practice_area_id` foreignUlid nullable → practice_areas, nullOnDelete; `area_label` string(255); `message` text; `source` string(20); `status` string(20), default `new`; `assigned_to` foreignUlid nullable → users, nullOnDelete; `ip_address` string(45) nullable; `user_agent` text nullable; `read_at`/`closed_at`/`notification_sent_at` timestamp nullable; timestamps; softDeletes.
- Index: `(status, created_at)` dan `(assigned_to, status)`. Status: new/read/in_progress/closed/spam; source: home/contact.
- `area_label` menyimpan pilihan saat pengiriman walaupun nama praktik berubah/dihapus; pilihan Lainnya memakai `practice_area_id` null.
- `submission_key` adalah kunci idempotensi per pengisian form, berbeda dari token CSRF. Pengiriman ulang kunci yang sama tidak membuat enquiry baru; penerbitan/pengikatan kunci ke sesi ditangani aplikasi.
- `notification_sent_at` hanya diisi setelah pengiriman notifikasi berhasil; kegagalan mail tidak menghapus enquiry yang sudah tersimpan.

#### N. `enquiry_notes`

- Kolom: `id` ULID PK; `enquiry_id` foreignUlid → enquiries, cascadeOnDelete; `user_id` foreignUlid nullable → users, nullOnDelete; `body` text; timestamps.
- Index: `(enquiry_id, created_at)`. Catatan hanya untuk admin/editor yang diberi izin; tidak dikirim ke frontend publik.

#### Pemetaan data ke bagian frontend

| Bagian frontend | Sumber data |
| --- | --- |
| Header, menu, kontak bersama, footer, SEO umum | site_settings + media/media_usages |
| Banner, hero, profil singkat, gambar, kutipan, nilai firma | pages + page_sections + media |
| Sektor, langkah kerja, timeline, visi/misi, statistik, pengakuan, logo klien, FAQ | page_sections.content dengan struktur sesuai type |
| Kartu/daftar praktik dan modal praktik | practice_areas + pilihan ULID pada section |
| Direktori dan modal profesional, profil pimpinan | professionals + practice_area_professional |
| Publikasi terbaru, daftar/detail publikasi | insights + professionals sebagai penulis opsional |
| Form konsultasi Beranda/Kontak dan inbox admin | enquiries + enquiry_notes |
| Draft, review, preview dan riwayat publikasi | content_revisions; frontend membaca versi publik pada tabel induk |
| Login dan keamanan admin | users + sessions + cache/rate limiter + audit_logs |
| Peta Situs | pages published dan named route yang diizinkan |

#### Checklist implementasi migration

- [x] DB.01 Buat 18 file migration di atas dengan urutan dependensi yang benar, termasuk tambahan undangan dan 2FA.
- [x] DB.02 Periksa status migration dan tipe ID database existing; database lokal sebelumnya kosong, sehingga tidak membutuhkan konversi ID. Tambahkan preflight yang menolak ID legacy atau akun yang belum memiliki rencana backfill.
- [x] DB.03 Terapkan constraint unique, foreign key, index bernama singkat, nullable, dan default sesuai rancangan.
- [x] DB.04 Pastikan `down()` membalikkan perubahan; rollback dilakukan dari child ke parent, termasuk pivot, usage, dan catatan enquiry. Rollback nullable credential ditolak jika masih ada undangan tanpa password agar tidak kehilangan data.
- [x] DB.05 Tambahkan model, `HasUlids`, casts, relasi, morph map, factory, dan seed setelah migration tersedia.
- [x] DB.06 Uji migrate/rollback, ULID, duplikasi slug/version/submission_key, foreign key, nullOnDelete/restrict/cascade, serta pemisahan draft dari konten published pada database pengujian.
- [x] DB.07 Verifikasi skema pada engine MySQL 8.0.30 yang dipilih untuk deployment; migrate/rollback/seed/backup/restore diuji pada skema MySQL sementara. Verifikasi VPS dan deployment produksi tetap fase 15.
- [x] DB.08 Perbarui checklist hanya setelah file dan hasil pengujian tersedia. Skema selesai; fitur login, 2FA, dan editor CMS tetap pada fase berikutnya.

#### O. `user_invitations` — email yang sudah diizinkan dan tautan aktivasi

- Kolom: `id` ULID PK; `user_id` foreignUlid unique → users, cascadeOnDelete; `email` string(254); `invited_by` foreignUlid nullable → users, nullOnDelete; `token_hash` char(64) nullable; `issued_at`/`expires_at`/`last_sent_at`/`accepted_at`/`revoked_at` timestamp nullable; timestamps.
- Index: `(expires_at, accepted_at)`. Satu record onboarding per akun; penerbitan ulang mengganti hash token lama secara atomik. Riwayat penerbitan/pencabutan disimpan di audit log.
- Email merupakan salinan email yang diizinkan saat undangan dibuat. Token terikat pada akun, email, dan tujuan aktivasi; perubahan email mencabut token lama.
- Token berupa random kriptografis berentropi tinggi; ULID hanya selector record, bukan token rahasia. Simpan SHA-256 token, kirim nilai mentah hanya melalui tautan email; jangan simpan URL/token mentah di audit log.
- Record boleh dibuat saat Super Administrator mendaftarkan email tanpa langsung mengirim tautan. Pengguna yang meminta aktivasi memicu penerbitan token; email yang belum terdaftar tidak membuat akun atau undangan.
- Konsumsi token pada POST simpan password dengan row lock/transaksi, cek expiry/revoked/accepted/status akun. Membuka tautan GET oleh pemindai email tidak menghabiskan token.

#### P. `user_two_factor_authentications`

- Kolom: `id` ULID PK; `user_id` foreignUlid unique → users, cascadeOnDelete; `method` string(20); `secret` text nullable; `confirmed_at` timestamp nullable; `last_used_step` unsignedBigInteger nullable; timestamps.
- Metode final: `method=email`. `secret` dan `last_used_step` tetap null pada OTP email; kolom tersebut dipertahankan sebagai cadangan skema dan tidak mengaktifkan TOTP atau QR pada implementasi sekarang.
- OTP setiap percobaan disimpan sebagai hash pada `two_factor_challenges`, bukan pada secret akun. Jangan menyimpan OTP mentah di database, audit, atau log email.
- `confirmed_at` diisi setelah OTP pertama benar pada onboarding. Login berikutnya tetap membutuhkan OTP baru; email_verified_at atau confirmed_at bukan penanda bahwa sesi saat ini sudah lolos 2FA.
- Reset faktor menghapus secret/konfirmasi dan recovery code lama, menaikkan `auth_version`, serta memerlukan enrollment ulang. Mengubah metode memerlukan verifikasi ulang yang sesuai.

#### Q. `two_factor_recovery_codes`

- Kolom: `id` ULID PK; `user_id` foreignUlid → users, cascadeOnDelete; `code_hash` string(255); `used_at` timestamp nullable; `created_at` timestamp.
- Index: `(user_id, used_at)`. Buat kode acak berentropi tinggi; tampilkan nilai mentah sekali setelah enrollment terkonfirmasi, simpan hanya hash.
- Penggunaan satu kode harus atomik agar request bersamaan tidak menerima kode yang sama dua kali. Regenerasi menghapus seluruh kode sebelumnya dan membutuhkan password serta faktor yang masih valid.

#### R. `two_factor_challenges`

- Kolom: `id` ULID PK; `user_id` foreignUlid → users, cascadeOnDelete; `purpose` string(30); `method` string(20); `session_binding_hash` char(64); `code_hash` string(255) nullable; `attempts` unsignedSmallInteger default 0; `expires_at` timestamp; `consumed_at` timestamp nullable; `created_at` timestamp.
- Index: `(user_id, purpose, expires_at)`. Purpose: login/enrollment/recovery. Ikat challenge pada sesi terbatas tertentu, bukan hanya pada user ID dari request.
- Untuk metode email yang dipilih, `code_hash` wajib diisi pada challenge OTP. Validasi kedaluwarsa, batas kesalahan, ikatan sesi, serta konsumsi sekali pakai dilakukan atomik.
- OTP email berentropi rendah harus dibatasi ketat: usulan kode 6 digit, berlaku 5 menit, maksimal 5 percobaan, cooldown kirim ulang 60 detik. Hash dilindungi dengan secret aplikasi/HMAC dan pembatasan percobaan; jangan menyimpan kode mentah di database/log.
- Bersihkan challenge kedaluwarsa melalui scheduler. Satu challenge tidak boleh dipakai lintas tujuan, sesi, atau akun.

## 02. Integrasi kerangka admin Bootstrap

- [ ] 02.01 Petakan `index.html`, `pages/*.html`, dan `components/*` dari template admin ke layout, partial, komponen, dan view Blade admin.
- [ ] 02.02 Tempatkan aset admin di namespace terpisah, misalnya `public/admin/assets`, agar CSS/JS tidak bertabrakan dengan frontend.
- [ ] 02.03 Buat `layouts/admin.blade.php`, sidebar, topbar, footer, breadcrumb, notifikasi, tabel, pagination, dan dialog konfirmasi.
- [ ] 02.04 Siapkan route bernama dengan prefix `/admin`. Setelah fase autentikasi selesai, seluruh route privat wajib memakai middleware autentikasi, status akun, dan authorization.
- [ ] 02.05 Ubah tautan template menjadi `route()`/`asset()`; tautan “Lihat website” menuju route `home` aplikasi Laravel.
- [ ] 02.06 Ganti adapter localStorage demo dengan controller, validasi server, dan database per modul. Hapus aksi reset data demo dari admin produksi.
- [ ] 02.07 Pertahankan pemuatan vendor per halaman: Bootstrap sekali, jsTree untuk struktur halaman, Chart.js untuk dashboard.
- [ ] 02.08 Tambahkan tampilan kosong, error validasi, akses ditolak, dan tampilan responsif mengikuti branding template.

Kriteria selesai: kerangka admin Blade berfungsi; data produksi tidak bergantung pada localStorage.

## 03. Autentikasi manual menggunakan fasilitas bawaan Laravel

Implementasi controller, request, view, dan alur login dibuat sendiri. Gunakan Session Guard, Eloquent provider, `Auth::attempt()`, `Hash`, dan middleware CSRF bawaan Laravel. Tidak memasang paket atau starter kit autentikasi.

### 03A. Akun dan konfigurasi autentikasi

- [x] 03.01 Gunakan guard `web` dengan driver `session` dan provider `users` yang mengarah ke `App\Models\User`. Gunakan guard yang sama secara konsisten pada login, middleware, dan logout.
- [x] 03.02 Tambahkan kolom akun sesuai rancangan A, termasuk status invited/pending_2fa/active/inactive, role, counter, lock, data login terakhir, dan auth_version.
- [x] 03.03 Terapkan role super_admin/admin/editor dan Gate/Policy. Pendaftaran email, undangan, role, serta pemulihan 2FA berizin dikelola Super Administrator; editor membuat draft konten sesuai izin, publikasi memerlukan hak terpisah.
- [x] 03.04 Buat perintah interaktif untuk membuat Super Administrator pertama dengan password yang di-hash dan enrollment 2FA wajib sebelum dashboard. Jangan menanam password default di repository atau seed produksi.
- [ ] 03.05 Buat halaman login dan “Aktivasi akun” sesuai branding admin. Form aktivasi hanya melayani email yang sudah didaftarkan Super Administrator; email lain tidak membuat akun.
- [ ] 03.06 Siapkan route GET login, POST login, dan POST logout; `guest` pada login dan `auth` pada halaman privat. Daftarkan middleware khusus melalui `bootstrap/app.php` Laravel 12.

### 03B. Alur login aman

- [ ] 03.07 Buat LoginRequest: validasi email/password, normalisasi email secara konsisten, dan tampilkan pesan gagal yang generik.
- [ ] 03.08 Terapkan `RateLimiter` sebelum pemeriksaan kredensial. Usulan awal: 5 percobaan per menit per kombinasi email+IP serta batas terpisah per IP; simpan limit pada konfigurasi yang dapat disetel.
- [ ] 03.09 Gunakan cache bersama yang mendukung limiter pada deployment banyak instance; jangan memakai cache `array` untuk pembatasan login produksi.
- [ ] 03.10 Periksa status akun dan temporary lock, lalu jalankan `Auth::attempt()` hanya untuk akun yang memenuhi syarat. Pastikan akun inactive/terkunci tidak mendapatkan sesi autentikasi.
- [ ] 03.11 Gunakan `Hash::make()` untuk membuat/mengubah password dan mekanisme pengecekan password provider Laravel; jangan membandingkan password mentah dengan hash.
- [ ] 03.12 Saat kredensial salah, naikkan failed login counter akun yang ditemukan secara atomik. Usulan awal: 5 kegagalan dalam jendela 15 menit memicu lock 15 menit; kegagalan lama di luar jendela tidak terus terakumulasi.
- [ ] 03.13 Perhitungkan request paralel dan risiko penguncian akun oleh pihak lain. Percobaan saat terkunci tidak memperpanjang lock tanpa batas; sediakan unlock oleh admin yang berizin dan tercatat.
- [ ] 03.14 Gunakan respons generik yang konsisten untuk akun tidak ditemukan, password salah, akun inactive, dan temporary lock; batasi perbedaan waktu respons yang dapat mengungkap keberadaan akun.
- [ ] 03.15 Setelah password benar, regenerasi session ID dan masuk tahap `2fa_pending`; keberhasilan password belum berarti login penuh. Pisahkan counter password dan 2FA; simpan last_login_at/IP serta audit login penuh hanya setelah faktor kedua berhasil.
- [ ] 03.16 Redirect ke dashboard atau tujuan internal yang diizinkan hanya setelah 2FA terverifikasi untuk sesi dan auth_version saat ini; cegah redirect ke domain luar dan tujuan onboarding di luar izin.
- [ ] 03.17 Saat logout, jalankan logout guard, invalidasi session, dan regenerasi token CSRF.
- [ ] 03.18 Aktifkan CSRF pada login, logout, dan semua operasi perubahan data. Terapkan cookie HttpOnly, Secure pada HTTPS produksi, SameSite, serta batas idle session.
- [ ] 03.19 Periksa status akun pada setiap request privat sehingga penonaktifan berlaku pada sesi yang sudah aktif. Perubahan password/role harus memiliki kebijakan pencabutan sesi.
- [ ] 03.20 Pilih penyimpanan session produksi. Jika memakai database, migrasikan tabel sessions dan uji `user_id` ULID; sesuaikan mekanisme pencabutan sesi dengan driver yang dipilih.

### 03C. Audit log dan device/IP logging

- [ ] 03.21 Buat `audit_logs`: ULID, `user_id` nullable, jenis event, hasil/alasan internal, IP, user agent, metadata yang diizinkan, dan timestamp.
- [ ] 03.22 Catat login berhasil/gagal, rate limit, temporary lock/unlock, logout, perubahan password/status/role, serta aktivitas admin penting.
- [ ] 03.23 Catat user agent dan ringkasan browser/perangkat sebagai informasi perkiraan. Jangan menjadikan IP atau user agent sebagai bukti identitas/perangkat yang tepercaya.
- [ ] 03.24 Ambil IP melalui request Laravel dengan trusted proxy yang dikonfigurasi sesuai infrastruktur; jangan mempercayai header IP sembarang.
- [ ] 03.25 Jangan mencatat password, token CSRF, token reset, cookie, session ID mentah, atau isi sensitif enquiry ke audit log. Samarkan identitas percobaan login yang tidak memiliki akun.
- [ ] 03.26 Buat halaman audit log dengan filter event, akun, hasil, waktu, dan IP; batasi akses dan tetapkan retensi/pembersihan log.

### 03D. Pengelolaan akun dan pemulihan

- [ ] 03.27 Buat pengelolaan email/undangan, akun, status, role, dan unlock khusus Super Administrator dengan policy serta audit log; pengguna tidak dapat menaikkan role sendiri.
- [ ] 03.28 Cegah penonaktifan/penghapusan/penurunan role Super Administrator aktif terakhir; perubahan role dan tindakan sensitif memerlukan autentikasi ulang serta 2FA baru.
- [ ] 03.29 Buat ubah password dengan konfirmasi password saat ini dan kebijakan password yang ditetapkan.
- [ ] 03.30 Buat lupa/reset password manual dengan password broker bawaan untuk akun yang sudah aktif. Pisahkan dari token aktivasi akun, cabut sesi lama setelah reset, dan tetap wajibkan 2FA; reset password tidak mereset faktor kedua.

### 03E. Aktivasi akun berbasis email yang didaftarkan Super Administrator

Alur yang diminta:

`Super Administrator mendaftarkan email + role → pengguna membuka Aktivasi akun dan memasukkan email → sistem mengirim tautan hanya ke email yang sudah diizinkan → pengguna membuka tautan dan membuat password → setup/konfirmasi 2FA → dashboard sesuai role → membuat draft konten`.

Tautan email digunakan untuk membuat password dan membuktikan akses ke email. Tautan tersebut bukan pengganti verifikasi 2FA pada login berikutnya. Tahap setelah penyimpanan password diarahkan otomatis ke setup/konfirmasi 2FA; dashboard terbuka setelah tahap itu selesai.

- [ ] 03.31 Buat form Super Administrator untuk mendaftarkan email dan menetapkan role. Buat users dengan status invited, password null, serta user_invitations; email harus unik dan dinormalisasi secara konsisten.
- [ ] 03.32 Buat GET/POST halaman Aktivasi akun. Pengguna hanya memasukkan email; nama dan password diisi setelah tautan diverifikasi. Jangan mengubah profil atau role berdasarkan request aktivasi yang belum terverifikasi.
- [ ] 03.33 Untuk email diizinkan yang belum diaktifkan, kirim tautan membuat password. Email tidak terdaftar, undangan dicabut, inactive, atau sudah aktif menerima pesan umum yang sama; akun aktif diarahkan secara informatif ke login/lupa password tanpa membocorkan status lewat respons aktivasi.
- [ ] 03.34 Terapkan rate limit per IP dan identitas email serta cooldown penerbitan ulang. Usulan link berlaku 60 menit; resend membuat token baru dan membatalkan token lama secara atomik.
- [ ] 03.35 Buat template email aktivasi dengan domain HTTPS yang dikonfigurasi, masa berlaku, dan tujuan tautan; jangan mengirim password. Queue/notifikasi berjalan setelah transaksi tersimpan dan tidak mencatat URL bertoken di log.
- [ ] 03.36 Buat halaman nama, password, dan konfirmasi password pada tautan valid. Validasi token, email, expiry, status undangan/akun, serta CSRF pada POST; gunakan Referrer-Policy no-referrer dan hindari aset/analytics pihak ketiga pada halaman bertoken.
- [ ] 03.37 Simpan nama dan Hash::make(password), isi email_verified_at/password_changed_at, konsumsi token, dan ubah status menjadi pending_2fa dalam transaksi. Request paralel atas token yang sama hanya boleh berhasil sekali.
- [ ] 03.38 Buat sesi onboarding terbatas dengan waktu kedaluwarsa setelah token berhasil dipakai. Sesi ini hanya boleh mengakses setup/konfirmasi 2FA dan logout, belum boleh membuka dashboard, upload, enquiry, atau endpoint CRUD.
- [ ] 03.39 Jika onboarding terputus setelah password dibuat, pengguna masuk kembali dengan password untuk melanjutkan enrollment 2FA. Tautan lama yang sudah dikonsumsi tidak dapat dipakai ulang.
- [ ] 03.40 Izinkan Super Administrator mencabut/menerbitkan ulang undangan. Cek ulang status akun sebelum konsumsi token dan aktivasi akhir; pencabutan harus menggagalkan token dan sesi onboarding yang masih berjalan.

### 03F. Enrollment dan login 2FA

Metode final yang dipilih pengguna adalah OTP melalui email, dengan recovery code sebagai jalur pemulihan terkontrol. Setelah password dibuat atau login password berhasil, kirim OTP baru ke email akun yang sudah didaftarkan Super Administrator. Semua alur tetap dibuat manual menggunakan guard/provider/session Laravel tanpa starter kit autentikasi. Tidak diperlukan setup aplikasi autentikator atau QR.

- [x] 03.41 Metode final dikonfirmasi pengguna: OTP email. Tautan membuat password dan challenge OTP adalah dua tahap dengan token/tujuan berbeda; pilihan metode selesai, implementasi challenge belum selesai.
- [ ] 03.42 Buat layar masukkan OTP email pada sesi onboarding/login terbatas: tampilkan alamat email tersamarkan, waktu berlaku, feedback, tombol kirim ulang dengan cooldown, dan tombol kembali/logout. Alamat tujuan berasal dari akun di server, bukan input bebas pada layar OTP.
- [ ] 03.43 Buat OTP acak kriptografis 6 digit, termasuk dukungan nol di depan, dan kirim melalui email. Terapkan expiry 5 menit, maksimal 5 kesalahan, cooldown resend 60 detik, serta pembatasan per akun/IP/sesi. Resend membatalkan kode lama secara atomik dan tidak menghapus akumulasi batas percobaan/rate limit.
- [ ] 03.44 Setelah konfirmasi faktor berhasil, simpan confirmed_at, ubah akun pending_2fa menjadi active, isi activated_at, dan terbitkan recovery code. Tampilkan kode sekali sebelum tombol lanjut ke dashboard; verifikasi akun masih diizinkan Super Administrator.
- [ ] 03.45 Buat sesi login penuh yang baru setelah onboarding sukses, regenerasi session/CSRF, dan arahkan pengguna ke dashboard sesuai role. Catat aktivasi dan login penuh tanpa menyertakan secret/OTP/password.
- [ ] 03.46 Untuk login berikutnya, Auth::attempt() memeriksa password/status akun; hanya akun active atau pending_2fa yang memenuhi jalurnya yang dapat maju. Setelah password benar, sesi tetap terbatas sampai challenge faktor kedua lolos; pending_2fa diarahkan ke enrollment.
- [ ] 03.47 Buat middleware `EnsureTwoFactorVerified` dan pemeriksaan onboarding/status/auth_version. Semua route privat—dashboard, API admin, upload, ekspor, dan CRUD konten—harus menggunakannya selain auth/policy; middleware auth saja tidak cukup untuk sesi yang baru lolos password.
- [ ] 03.48 Ikat hasil 2FA pada user, session, challenge, dan auth_version di server. Regenerasi sesi pada transisi; hapus state pending setelah berhasil, logout, expiry, atau perubahan akun.
- [ ] 03.49 Usulan masa berlaku challenge login 5 menit dan maksimal 5 kesalahan; gunakan rate limiter terpisah untuk password, challenge 2FA, recovery, dan resend. Setelah batas tercapai, minta ulang login password tanpa mengungkap detail faktor.
- [ ] 03.50 Simpan hash/HMAC OTP dengan secret aplikasi dan ikat challenge pada akun, sesi, serta purpose. Verifikasi dan konsumsi kode dalam transaksi; tolak replay, kode lama setelah resend, dan challenge milik akun/sesi lain. Queue email harus menangani kode yang sudah kedaluwarsa/diganti agar retry tidak mengirim kode lama sebagai kode aktif.
- [ ] 03.51 Recovery code hanya menggantikan tahap kedua setelah password benar; satu kode dipakai sekali secara atomik. Catat dan kirim notifikasi penggunaan recovery code.
- [ ] 03.52 Perubahan password/email/role, penonaktifan akun, reset faktor, dan logout harus mencabut state challenge/2FA yang relevan. Naikkan auth_version saat perlu mencabut semua sesi dan periksa pada setiap request privat.
- [ ] 03.53 Wajibkan password dan verifikasi faktor yang masih valid untuk mengganti 2FA atau meregenerasi recovery code. Kehilangan seluruh faktor memakai proses pemulihan identitas oleh Super Administrator yang diautentikasi ulang; kirim pemberitahuan ke pengguna dan audit seluruh tindakan.
- [ ] 03.54 Buat prosedur pemulihan Super Administrator terakhir melalui akses operasional terverifikasi, tanpa endpoint bypass publik atau password default. Reset password lewat email tidak otomatis mematikan 2FA.
- [ ] 03.55 Tambahkan audit invitation_created/requested/sent/revoked/accepted, enrollment_started/confirmed, challenge_failed/expired/passed, recovery_used, dan factor_reset. Redaksi rahasia pada log aplikasi, email delivery, error tracking, dan URL log.
- [ ] 03.56 Konfigurasikan mail transport untuk OTP, penanganan gagal kirim, dan retry terbatas. Jangan memakai mailer log untuk email OTP produksi; jika email diantrikan, lindungi payload queue yang memuat kode mentah dan jangan tampilkan isinya pada failed job/log. Gagal kirim tidak boleh memberi akses dashboard.
- [ ] 03.57 Perubahan alamat email harus melalui Super Administrator dan verifikasi identitas/autentikasi ulang yang ditetapkan; cabut challenge/sesi lama dan verifikasi alamat baru sebelum dipakai untuk login OTP. Akun yang kehilangan akses email mengikuti prosedur recovery, tanpa penggantian tujuan OTP secara bebas.

### 03G. Hak akses setelah onboarding

| Role | Pengelolaan email/akun dan faktor | Konten | Dashboard |
| --- | --- | --- | --- |
| Super Administrator | Mendaftarkan email, undangan, role, status, dan pemulihan faktor sesuai prosedur | Kelola/review/publish sesuai policy | Setelah password + 2FA |
| Administrator | Tidak mengundang atau menaikkan role; kelola faktor milik sendiri | Kelola/review/publish sesuai policy | Setelah password + 2FA |
| Editor | Tidak mengundang atau mengubah role; kelola faktor milik sendiri | Buat/edit draft sesuai kepemilikan/penugasan dan ajukan review | Setelah password + 2FA |

Pendaftaran akun, aktivasi, dan 2FA tidak otomatis memberikan izin publikasi. Policy untuk membuat/mengubah konten diperiksa kembali pada server.

Rujukan implementasi: [Laravel 12 SessionGuard](https://api.laravel.com/docs/12.x/Illuminate/Auth/SessionGuard.html), [OWASP MFA](https://cheatsheetseries.owasp.org/cheatsheets/Multifactor_Authentication_Cheat_Sheet.html), dan [OWASP token pemulihan password](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html). Nilai masa berlaku dan batas percobaan di atas merupakan usulan konfigurasi proyek.

Kriteria selesai: hanya email yang sudah didaftarkan Super Administrator dapat menyelesaikan aktivasi; dashboard/CRUD memerlukan password dan faktor kedua, status akun valid, serta policy yang sesuai. Undangan, pemulihan, lock, dan seluruh transisi sesi lolos pengujian.

## 04. Fondasi CMS, media, dan enquiry

Fase ini menyediakan dependensi agar implementasi halaman berikutnya dapat dikerjakan dari bagian paling atas hingga bawah.

- [x] 04.01 Buat modul Halaman Website berdasarkan `pages/content.html`: data halaman, section bertipe tetap, urutan, visibility, judul, deskripsi, dan SEO.
- [x] 04.02 Tetapkan workflow draft → review → published → archived dengan policy publikasi. Frontend hanya membaca konten published dan relasi yang juga boleh dipublikasikan.
- [x] 04.03 Pisahkan versi draft dari versi published agar menyimpan edit belum disetujui tidak langsung mengubah konten publik. Preview hanya dapat diakses akun berizin dan tidak masuk cache publik.
- [x] 04.04 Gunakan field/section terstruktur untuk layout existing. Escape output Blade dan sanitasi rich text server-side dengan daftar elemen/atribut yang diizinkan.
- [x] 04.05 Buat Media Library berdasarkan `pages/media.html`: upload, pencarian, alt text, dimensi, referensi pemakaian, dan penghapusan berizin.
- [x] 04.06 Validasi tipe file berdasarkan isi/MIME, ukuran, dan dimensi; gunakan nama file buatan server. Batasi tipe gambar dan tangani SVG hanya dengan sanitasi yang memadai atau tolak upload SVG.
- [x] 04.07 Pisahkan media publik dari dokumen internal; cegah eksekusi file upload dan penghapusan file yang masih dipakai konten published.
- [x] 04.08 Siapkan modul Praktik, Profesional, dan Publikasi beserta relasi dan seed draft. Detail antarmuka dikerjakan pada fase halaman masing-masing.
- [x] 04.09 Siapkan backend enquiry bersama: POST bernama `enquiries.store`, FormRequest, CSRF, rate limit, penyimpanan, dan pencegahan submit ganda.
- [x] 04.10 Siapkan inbox berdasarkan `pages/enquiries.html`: status new/read/in_progress/closed/spam, catatan internal, pencarian, filter, dan pembatasan akses.
- [x] 04.11 Tetapkan penerima notifikasi, mail/queue, penanganan kegagalan pengiriman, serta retensi enquiry. Konfirmasi tersimpan dibedakan dari keberhasilan pengiriman email.

Kriteria selesai: konten, media, publikasi, dan enquiry memiliki penyimpanan serta aturan akses nyata.

## 05. Frontend global: header dan navigasi

- [x] 05.01 Hubungkan identitas firma, logo, favicon, dan metadata umum ke Site Settings.
- [x] 05.02 Kelola menu sesuai urutan existing: Beranda, Tentang Firma, Bidang Praktik, Profesional, Publikasi, Kontak.
- [x] 05.03 Hubungkan CTA “Jadwalkan konsultasi” ke bagian enquiry; pertahankan named route dan anchor yang sudah dipakai.
- [x] 05.04 Uji menu desktop/mobile, offcanvas, active state, skip link, keyboard, dan fokus.

## 06. Beranda `/` — urutan dari atas ke bawah

Acuan: `resources/views/frontend/home.blade.php`. Setiap item mencakup field admin, validasi, penyimpanan, preview, publikasi, dan pembacaan di Blade.

- [x] 06.01 Hero: gambar utama, eyebrow, heading, deskripsi, dan tautan “Kenali HSNCO”.
- [x] 06.02 Profil singkat: “Layanan hukum menyeluruh dari profesional tepercaya”, paragraf firma, subjudul, dan CTA.
- [x] 06.03 Gambar pemisah kantor: gambar, alt text, dan caption pendekatan.
- [x] 06.04 Pengantar layanan: “Layanan unggul. Kejelasan tanpa kompromi”, deskripsi, dan tautan bidang praktik.
- [x] 06.05 Empat kartu praktik pilihan: pilih dari data Praktik, urutan, gambar, label, serta tautan anchor yang benar. Selaraskan kartu Perbankan & Restrukturisasi dengan tujuan praktiknya.
- [x] 06.06 Nilai firma: gambar latar dan daftar nilai, judul, deskripsi; hubungkan kontrol slider dengan data CMS, termasuk data yang sebelumnya berada di JavaScript.
- [x] 06.07 Sektor usaha: lima entri existing, ringkasan, dan urutan tampil.
- [x] 06.08 Pendekatan kerja: Memahami → Menyusun strategi → Melaksanakan; kelola judul dan deskripsi tiap langkah.
- [x] 06.09 Pengakuan pasar: direktori, peringkat, tahun, bukti, dan status verifikasi. Hanya terbitkan klaim yang sudah diverifikasi dan disetujui.
- [x] 06.10 Bagian konsultasi: judul, deskripsi, informasi kontak bersama, dan form enquiry ke backend fase 04.
- [x] 06.11 Publikasi terbaru: ambil artikel published dari modul Publikasi; kelola jumlah/urutan dan pastikan tautan menuju konten yang tepat.

Kriteria selesai: seluruh section Beranda dapat dikelola admin tanpa mengedit Blade dan tetap mengikuti susunan existing.

## 07. Tentang Firma `/about` — urutan dari atas ke bawah

- [x] 07.01 Banner halaman: eyebrow, heading, intro, dan breadcrumb.
- [x] 07.02 Tentang kami: heading dan narasi firma dua kolom.
- [x] 07.03 Kutipan firma: isi kutipan dan atribusi yang disetujui.
- [x] 07.04 Perjalanan firma: milestone, tahun, judul, deskripsi, urutan, dan status verifikasi.
- [x] 07.05 Visi dan misi: judul dan penjelasan masing-masing.
- [x] 07.06 Firma dalam angka: nilai, satuan, label, sumber, dan tanggal verifikasi; jangan menerbitkan angka rekaan.
- [x] 07.07 Hubungan tepercaya: logo klien serta tanggapan/penilaian hanya jika memiliki persetujuan; kelola urutan dan bukti internal.
- [x] 07.08 Profil pimpinan: pilih dari modul Profesional agar foto, nama, dan biografi konsisten.
- [x] 07.09 CTA penutup “Wawasan yang mendorong kemajuan usaha” dan tautan konsultasi.

## 08. Bidang Praktik `/practice-areas` — urutan dari atas ke bawah

- [x] 08.01 Banner halaman dan intro bidang praktik.
- [x] 08.02 Pengantar “Keahlian terfokus. Nasihat terpadu”.
- [x] 08.03 Selesaikan CRUD Praktik berdasarkan `pages/practice-areas.html`: nama, slug unik, ringkasan, deskripsi, gambar, status, urutan, dan pilihan tampil di Beranda.
- [x] 08.04 Hubungkan sembilan praktik existing: Korporasi; Merger & Akuisisi; Perbankan; Litigasi; Teknologi & KI; Ketenagakerjaan; Proyek; Maritim; Restrukturisasi.
- [x] 08.05 Hubungkan tombol Jelajahi dan detail modal ke data database; pertahankan slug/anchor existing atau sediakan pengalihan jika berubah.
- [x] 08.06 Kelola bagian “Keahlian tanpa batas” dan narasi layanan terpadu.
- [x] 08.07 Kelola CTA “Keahlian yang tepat. Di saat yang tepat”.
- [x] 08.08 Sinkronkan pilihan praktik pada Beranda, form enquiry, profil profesional, dan footer.

## 09. Profesional `/team` — urutan dari atas ke bawah

- [x] 09.01 Banner halaman dan pengantar direktori profesional.
- [x] 09.02 Selesaikan CRUD Profesional berdasarkan `pages/team.html`: nama, slug, jabatan/level, foto, bio, kualifikasi, bahasa, bidang praktik, status, dan urutan.
- [x] 09.03 Hubungkan pencarian, filter Mitra/Asosiat, jumlah hasil, serta empty state ke data yang boleh dipublikasikan.
- [x] 09.04 Hubungkan profil pimpinan unggulan di bagian teratas ke pilihan admin.
- [x] 09.05 Hubungkan grid profesional dan detail modal; ganti data hardcoded JavaScript dengan data published yang di-escape dengan benar.
- [x] 09.06 Kelola CTA penutup dan tautan konsultasi.
- [x] 09.07 Pastikan profil draft/arsip tidak bocor lewat hasil pencarian, modal, atau payload JavaScript; foto ilustrasi tidak dianggap identitas asli.

## 10. Publikasi `/insights` — urutan dari atas ke bawah

- [x] 10.01 Banner halaman dan intro publikasi.
- [x] 10.02 Selesaikan CRUD Publikasi berdasarkan `pages/insights.html`: judul, slug, ringkasan, isi, gambar, penulis, tanggal publikasi, SEO, dan status editorial.
- [x] 10.03 Hubungkan daftar artikel, gambar, urutan tanggal, dan pagination ke database.
- [x] 10.04 Hubungkan detail modal existing ke isi publikasi. Jika diperlukan URL artikel mandiri, tambahkan route detail dan alihkan tautan kartu secara konsisten.
- [x] 10.05 Pastikan hanya artikel published dengan waktu terbit yang sudah tercapai tampil pada daftar dan Beranda.
- [x] 10.06 Kelola CTA “Wawasan untuk pasar yang berkembang”.

## 11. Kontak `/contact` — urutan dari atas ke bawah

- [x] 11.01 Banner halaman dan intro kontak.
- [x] 11.02 Hubungkan bagian “Keahlian mendalam. Arah yang jelas” ke pengelolaan konten.
- [x] 11.03 Hubungkan alamat kantor, telepon, email, dan tautan kontak ke Site Settings; tampilkan hanya data terverifikasi.
- [x] 11.04 Hubungkan komponen form bersama untuk Beranda dan Kontak: nama depan/belakang, email, telepon opsional, bidang bantuan, dan pesan.
- [x] 11.05 Setelah backend siap, ganti `data-demo-form`, endpoint demo, teks demo, dan handler JavaScript; sediakan submit yang tetap bekerja tanpa JavaScript.
- [x] 11.06 Tampilkan error per field, pertahankan input aman, dan tampilkan konfirmasi hanya setelah penyimpanan berhasil. Cegah XSS pada pesan dan formula injection pada ekspor CSV enquiry.
- [x] 11.07 Pertahankan pemberitahuan bahwa pengisian form belum membentuk hubungan advokat-klien; sediakan penjelasan penggunaan data kontak.
- [x] 11.08 Kelola FAQ accordion di bagian bawah; perbarui jawaban tentang form demonstrasi setelah pengiriman nyata aktif.
- [ ] 11.09 Uji alur dari kedua form ke inbox admin, perubahan status, catatan internal, dan notifikasi.

## 12. Peta Situs `/sitemap` — urutan dari atas ke bawah

- [x] 12.01 Kelola banner dan intro Peta Situs.
- [x] 12.02 Bangun struktur jsTree dari halaman/tautan publik yang valid; jangan menyertakan admin atau konten draft.
- [x] 12.03 Pertahankan pencarian, buka/tutup semua, detail tujuan, dan tombol menuju halaman.
- [x] 12.04 Pertahankan fallback daftar tautan dan navigasi keyboard tanpa ketergantungan penuh pada JavaScript.

## 13. Frontend global: footer dan SEO

- [x] 13.01 Hubungkan logo dan tagline footer ke identitas firma.
- [x] 13.02 Kelola kolom Firma dan Keahlian dengan route/anchor yang valid.
- [x] 13.03 Gunakan sumber informasi kontak yang sama dengan Beranda dan halaman Kontak.
- [x] 13.04 Atur copyright/tahun dan teks legal; perbarui label template hanya setelah status konten aktual sesuai.
- [x] 13.05 Lengkapi title, meta description, canonical memakai domain final, Open Graph, robots, dan sitemap XML untuk halaman publik yang relevan.
- [x] 13.06 Hindari index pada login/admin dan preview; pastikan proteksi akses tetap melalui auth/policy.
- [x] 13.07 Invalidasi cache konten saat publish/unpublish dan perubahan pengaturan agar publik melihat versi yang benar.

## 14. Penyelesaian dashboard dan pengaturan admin

- [x] 14.01 Dashboard dari `index.html`: jumlah halaman/konten, status editorial, enquiry baru, dan aktivitas terakhir berdasarkan data nyata.
- [x] 14.02 Hubungkan Chart.js ke distribusi status konten; gunakan label yang jelas dan tampilan kosong jika belum ada data.
- [x] 14.03 Selesaikan pencarian, filter, pagination server-side, validasi, dan pesan keberhasilan/gagal pada semua modul.
- [x] 14.04 Kembangkan `pages/settings.html` menjadi pengaturan identitas, kontak, SEO default, serta preferensi admin dengan policy. Kredensial mail/storage tetap di environment.
- [x] 14.05 Tambahkan menu Pengguna/Undangan khusus Super Administrator, Audit Log sesuai izin, serta Keamanan Akun untuk 2FA/recovery code milik sendiri; tetap validasi semua izin di server.
- [x] 14.06 Audit perubahan, penghapusan, dan publikasi konten; batasi ekspor enquiry dan data akun kepada role yang berizin.

## 15. Pengujian, verifikasi konten, dan deployment

- [ ] 15.01 Uji autentikasi: kredensial valid/salah, akun tidak ada/inactive, rate limit, counter, lock kedaluwarsa, unlock, request paralel, regenerasi session, logout, serta CSRF.
- [ ] 15.02 Uji authorization: akses langsung endpoint, perubahan role, penonaktifan sesi aktif, akses audit/enquiry, dan perlindungan admin terakhir.
- [ ] 15.03 Uji migration, factory, seed, penyimpanan User ULID, session database ULID jika dipakai, dan relasi seluruh modul.
- [ ] 15.04 Uji CRUD, media upload, draft/review/publish/arsip, preview privat, versi konten, dan cache publik.
- [ ] 15.05 Perbarui test frontend yang sebelumnya mengharapkan form demo dan `POST /contact` 405 sesuai kontrak endpoint baru; tetap verifikasi CSRF pada request HTTP nyata.
- [ ] 15.06 Uji enquiry tersimpan, submit ganda, spam/rate limit, kegagalan mail, retry queue, dan akses data kontak.
- [ ] 15.07 Uji tujuh halaman desktop/mobile, keyboard, fokus modal, slider nilai, filter profesional, FAQ, jsTree, gambar, link, dan console browser.
- [ ] 15.08 Jalankan regresi yang relevan dan pemeriksaan aset produksi/proxy existing; admin tidak boleh merusak frontend.
- [ ] 15.09 Verifikasi konten firma, profil, penghargaan, statistik, logo klien, kontak, dan artikel bersama pemilik konten sebelum status published.
- [ ] 15.10 Siapkan environment produksi: `APP_KEY`, `APP_DEBUG=false`, HTTPS/cookie, trusted proxy, database, session/cache bersama jika diperlukan, mail, storage, queue worker, dan scheduler.
- [ ] 15.11 Uji backup/restore serta rencana rollback kode dan migration; deploy ke staging sebelum produksi.
- [ ] 15.12 Buat panduan admin, pengelolaan akun/lock, publikasi konten, enquiry, backup, dan operasional deployment.
- [ ] 15.13 Uji onboarding: email terdaftar/tidak, duplikasi normalisasi email, email aktif/inactive, undangan dicabut, link kedaluwarsa, resend, token sekali pakai/request paralel, GET pemindai email, serta kegagalan mail dan retry.
- [ ] 15.14 Uji OTP email: onboarding, kode salah/benar/kedaluwarsa/nol di depan, replay, cooldown resend, pembatalan kode lama, batas percobaan yang tidak ter-reset lewat resend, email gagal/tertunda, retry queue, recovery code sekali pakai, challenge lintas akun/sesi, onboarding terputus, dan redirect dashboard setelah faktor terverifikasi.
- [ ] 15.15 Uji bypass: sesi baru lolos password, cookie remember-me jika fitur itu kelak diaktifkan, direct URL dashboard/CRUD/API/upload, reset password, reset faktor, role escalation, serta pencabutan akun saat onboarding/challenge berlangsung. Remember-me dinonaktifkan pada rancangan awal.
- [ ] 15.16 Uji pencabutan semua sesi melalui auth_version, penggantian faktor, pemulihan Super Administrator terakhir, dan redaksi token/QR/secret/recovery code pada seluruh log serta respons aplikasi.

Kriteria akhir: admin memakai autentikasi session manual yang teruji; konten published tampil konsisten dari atas ke bawah pada frontend; enquiry nyata dapat diproses; data demo tidak dianggap data produksi.

## Urutan eksekusi ringkas

`01 Fondasi → 02 Kerangka admin → 03 Auth manual → 04 Fondasi CMS/media/enquiry → 05 Header → 06 Beranda → 07 Tentang → 08 Praktik → 09 Profesional → 10 Publikasi → 11 Kontak → 12 Peta Situs → 13 Footer/SEO → 14 Dashboard/pengaturan → 15 QA/deployment`.

Untuk setiap section frontend: siapkan field admin → validasi/policy → simpan draft → preview → publish → tampilkan di Blade → uji. Kebutuhan data bersama disiapkan pada fase 04, sehingga urutan halaman tetap dapat mengikuti tampilan existing.



