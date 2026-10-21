# Industri: direktori, detail dan showcase Home

Referensi yang diminta pemilik: https://www.lawghp.com/industries. Sebelas kategori/topik mengikuti taksonomi referensi, sedangkan narasi ditulis ulang dalam bahasa Indonesia. Tidak menyalin keseluruhan teks, foto, portofolio, key contacts, lokasi kantor, klien atau klaim pengalaman GHP. Isi berupa gambaran konteks industri dan topik diskusi; bukan pernyataan bahwa HSNCO memiliki rekam jejak yang sama. Tidak menyatakan sertifikasi atau pengalaman baru.

## Halaman

- `/industries`: direktori 11 sektor dengan kartu ilustrasi hitam–emas.
- `/industries/{slug}`: halaman detail setiap sektor, ringkasan, topik diskusi, tautan konsultasi, dan sektor lain.
- Beranda: bagian Layanan unggul / Kejelasan tanpa kompromi menampilkan sektor Industri, menggantikan kartu bidang praktik sebelumnya.
- `/practice-areas` tetap menampilkan delapan kategori layanan dari CMS existing.
- Navigasi, footer, sitemap HTML dan XML mencantumkan Industri. URL canonical detail memakai path aktual, bukan placeholder parameter route.

## Kategori dan referensi topik

1. Agribisnis & Komoditas — https://www.lawghp.com/industries/agribusiness-and-commodities
2. Konstruksi — https://www.lawghp.com/industries/construction
3. Barang Konsumsi — https://www.lawghp.com/industries/consumer-goods
4. Emisi & Perdagangan Karbon — https://www.lawghp.com/industries/emissions-and-carbon-trading
5. Energi & Sumber Daya Alam — https://www.lawghp.com/industries/energy-and-resources
6. Kesehatan & Ilmu Hayati — https://www.lawghp.com/industries/health-and-life-science
7. Logistik & Transportasi — https://www.lawghp.com/industries/logistics-and-transportation
8. Media & Hiburan — https://www.lawghp.com/industries/media-and-entertainment
9. Properti & Real Estat — https://www.lawghp.com/industries/real-estate
10. Sumber Daya & Infrastruktur — https://www.lawghp.com/industries/resources-infrastructure
11. Teknologi & E-Commerce — https://www.lawghp.com/industries/technology-and-e-commerce

## Konten dan arsitektur

Konten publik kini berasal dari tabel `industries` melalui IndustryCatalog. `config/industries.php` hanya menjadi sumber impor awal. Hanya industri berstatus terbit yang tampil pada Home, direktori, detail, dan sitemap. Slug tidak dikenal atau belum terbit menghasilkan 404.

Sebelas ilustrasi SVG asli dibuat sebagai aset lokal di `public/assets/images/industries/`, dengan gambar sesuai konteks: agribisnis, konstruksi, produk, karbon, energi, kesehatan, logistik, media, properti, jembatan dan teknologi. Tidak mengambil aset gambar dari situs referensi. Generator: tools/generate-industry-illustrations.mjs. Foto proyek lokal existing dipakai sebagai banner direktori. Ilustrasi tidak menggambarkan proyek atau klien HSNCO yang nyata.

## Backend dan Key Contact

Buka `/admin/industries`, pilih Tambah atau Edit. Admin dapat mengubah nama, slug, ringkasan, gambaran sektor, topik, gambar dari Media, urutan, SEO, serta Key Contact. Pilih satu atau beberapa profesional yang sudah terbit (Ctrl/Cmd untuk beberapa pilihan), simpan sebagai review, lalu publikasikan. Draft perubahan tidak langsung mengubah konten publik.

Foto, nama, jabatan, dan modal portofolio Key Contact mengikuti data Profesional. Tidak perlu mengisi ulang profil. Profil yang diarsipkan tidak ditampilkan sebagai kontak. Tidak ada penunjukan personel otomatis saat impor.

Tabel baru: `industries` dan pivot `industry_professional`. Tidak menambah kolom pada tabel `professionals`. Migration dan impor sudah dijalankan pada database lokal; belum dijalankan pada production.

Setelah kode dideploy, jalankan di console aplikasi hosting:

```bash
php artisan migrate --path=database/migrations/2026_10_21_000001_create_industries_tables.php --force
php artisan hsnco:import-industries --publish
php artisan config:cache
```

Impor hanya menambahkan slug yang belum ada, termasuk menghormati data soft-delete; pengulangan tidak menimpa suntingan atau pilihan kontak. Tanpa `--publish`, konten diimpor sebagai draft. Jalankan dengan `--dry-run` untuk pemeriksaan tanpa menulis data.


## Validasi

HomeIndustriesTest, HomeServiceFormatsTest dan PracticeServiceCmsTest: 11 pengujian lulus, 164 assertion. Termasuk detail semua sektor, 404 slug tidak dikenal, pemisahan data praktik, visibility CMS dan sitemap.

Chrome lokal: 1920, 1440, 1366, 1024, 768, 390 dan 375 px. Beranda dan direktori menampilkan 11 kartu; detail dapat dibuka, canonical benar, gambar tampil, tidak ada horizontal overflow, HTTP error aset atau error JavaScript. Screenshot di docs/qa-industries. Manifest Docker diperbarui untuk semua aset tambahan; Docker build tidak dijalankan. Production belum diperbarui dari sesi ini.

Pengujian CMS tambahan: IndustryCmsTest memeriksa pemilihan kontak, isolasi draft, publikasi/penghapusan kontak, modal portofolio, validasi profil draft, penyembunyian profil arsip, impor berulang, dan penolakan akses tamu.

Validasi setelah integrasi backend: 15 test / 209 assertion lulus dengan route cache aktif (IndustryCmsTest, HomeIndustriesTest, PracticeServiceCmsTest, HomeServiceFormatsTest). Blade berhasil dikompilasi. QA Chrome tujuh ukuran layar diulang dan lulus; 86 aset lolos validator manifest. Belum melakukan deployment atau Docker build.
