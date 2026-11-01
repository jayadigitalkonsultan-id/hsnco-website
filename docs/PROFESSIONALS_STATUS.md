# Status profil Profesional

Daftar `/admin/professionals` dan formulir tambah/edit memakai Draf dan Tayang.

- Draf menyimpan profil tanpa menampilkannya kepada publik. Mengembalikan profil Tayang ke Draf menyembunyikannya dari direktori tim, Key Contact industri, dan nama penulis Insights.
- Tayang langsung menampilkan informasi terbaru, termasuk foto, jabatan, kelompok, bidang praktik, biografi, kualifikasi, bahasa, dan urutan tampil.
- Hanya bidang praktik berstatus Tayang yang terlihat pada profil publik. Relasi bidang praktik disimpan pada setiap penyimpanan; mengosongkan pilihannya melepaskan relasi tersebut.
- Foto dipilih dari thumbnail media publik pada formulir.
- Administrator dan Super Administrator dapat menayangkan serta menyembunyikan profil; Editor dapat menyimpan draf miliknya.
- Setiap penyimpanan menyimpan riwayat revisi, termasuk pilihan bidang praktik dan foto.

Migration berikut mengalihkan status lama `review` dan `archived` ke `draft` tanpa menghapus isi, relasi, atau riwayat revisinya. Profil yang sudah tayang tetap tayang.

```sh
php artisan migrate --path=database/migrations/2026_11_02_000002_simplify_professional_statuses.php
```
