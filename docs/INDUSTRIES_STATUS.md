# Status Industri

Daftar `/admin/industries` dan formulir tambah/edit memakai Draf dan Tayang.

- Draf menyimpan industri tanpa menampilkannya pada direktori, beranda, sitemap XML, atau halaman detail. Detail industri Draf mengembalikan 404.
- Tayang langsung menampilkan isi terbaru, gambar, topik diskusi, urutan, dan Key Contact yang dipilih.
- Gambar industri dipilih melalui thumbnail Pustaka Media. Pilihan aktif ditandai bingkai emas. Tanpa gambar pilihan, ilustrasi industri atau gambar kantor bawaan digunakan.
- Key Contact hanya dapat dipilih dari profil Profesional yang sudah tayang. Mengosongkan pilihan melepaskan relasi. Status profil diperiksa kembali saat menerbitkan industri.
- Administrator dan Super Administrator dapat menayangkan serta menyembunyikan industri; Editor dapat menyimpan draf miliknya.
- Setiap penyimpanan mempertahankan riwayat revisi beserta gambar dan daftar kontak.

Jalankan migration berikut saat deployment. Status lama `review` dan `archived` dialihkan ke `draft`; isi, relasi, dan riwayat revisinya dipertahankan. Industri yang sudah tayang tetap tayang.

```sh
php artisan migrate --path=database/migrations/2026_11_02_000004_simplify_industry_statuses.php
```
