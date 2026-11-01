# Status publikasi Insights

Daftar `/admin/insights` dan formulir tambah/edit hanya memakai Draf dan Tayang.

- Draf menyimpan isi dan tidak ditampilkan di halaman Insights maupun publikasi di beranda. Menyimpan konten tayang sebagai Draf langsung menyembunyikannya.
- Tayang menyimpan dan langsung menampilkan isi terbaru, tanpa pengajuan review terpisah.
- Administrator dan Super Administrator dapat menayangkan serta menyembunyikan publikasi. Editor dapat menyimpan draf miliknya.
- Riwayat revisi dan penggunaan media tetap dicatat.

Jalankan migration berikut saat menerapkan kode ini. Status lama `review` dan `archived` dipindahkan menjadi `draft`; publikasi yang sudah tayang dan riwayat revisinya dipertahankan.

```sh
php artisan migrate --path=database/migrations/2026_11_02_000001_simplify_insight_statuses.php
```
