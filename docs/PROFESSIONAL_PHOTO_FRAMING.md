# Pengaturan bingkai foto profesional

Form /admin/professionals/create dan edit menyediakan pratinjau kartu 4:5, zoom 1–3×, posisi horizontal dan vertikal, serta tombol atur ulang. Pilih gambar di Pustaka Media, sesuaikan pengaturan, lalu simpan Draf atau Tayang sesuai izin. Mengganti foto mengembalikan bingkai ke tengah dengan zoom 1×. Foto asli tidak dipotong atau ditimpa. Pembesaran tidak menambah resolusi foto.

## Database dan deployment

Migration `2026_11_14_000001_add_photo_framing_to_professionals.php` menambahkan kolom JSON nullable `professionals.photo_framing` berisi `zoom`, `x`, `y`. Jalankan migration sebelum memakai form baru:

```sh
php artisan migrate --path=database/migrations/2026_11_14_000001_add_photo_framing_to_professionals.php --force
php artisan view:clear
```

Nilai lama yang belum disunting tetap mengikuti config/professionals.php. Setelah disimpan lewat admin, pengaturan database diprioritaskan. Pengaturan berlaku di kartu direktori /team; foto modal tetap memakai tampilan aslinya. Tidak ada pengubahan file media, akun, atau data produksi secara otomatis.
