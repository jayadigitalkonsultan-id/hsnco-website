<?php

$rich = ['type' => 'rich_text', 'label' => 'Deskripsi', 'max' => 30000];
$items = static fn (array $fields): array => ['type' => 'items', 'label' => 'Daftar item (JSON)', 'items' => $fields];
$narrative = [
    'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'max' => 150],
    'heading' => ['type' => 'text', 'label' => 'Judul', 'max' => 255],
    'description' => $rich,
];
$link = [
    'button_label' => ['type' => 'text', 'label' => 'Label tombol', 'max' => 100],
    'button_url' => ['type' => 'url', 'label' => 'Tujuan tombol', 'max' => 2048],
];
$itemText = ['title' => ['type' => 'text', 'label' => 'Judul item', 'max' => 255], 'description' => $rich];
$valueItem = [
    'title' => ['type' => 'text', 'label' => 'Judul nilai', 'max' => 255, 'required' => true],
    'description' => $rich + ['required' => true],
];

$sectionFields = [
    'hero' => [
        'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'max' => 150],
        'description' => ['type' => 'text', 'label' => 'Deskripsi', 'max' => 2000],
    ] + $link + [
        'image_id' => ['type' => 'media', 'label' => 'Gambar utama'],
        'image_alt' => ['type' => 'text', 'label' => 'Alt text gambar', 'max' => 255],
    ],
    'banner' => $narrative + $link,
    'about_banner' => [
        'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'max' => 150],
        'description' => ['type' => 'text', 'label' => 'Intro banner', 'max' => 2000],
        'breadcrumb_label' => ['type' => 'text', 'label' => 'Label breadcrumb', 'max' => 100],
    ],
    'rich_text' => $narrative + [
        'subheading' => ['type' => 'text', 'label' => 'Subjudul', 'max' => 255],
        'body' => ['type' => 'rich_text', 'label' => 'Isi', 'max' => 50000],
    ] + $link,
    'about_intro' => [
        'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'max' => 150],
        'column_one' => ['type' => 'rich_text', 'label' => 'Narasi kolom pertama', 'max' => 30000],
        'column_two' => ['type' => 'rich_text', 'label' => 'Narasi kolom kedua', 'max' => 30000],
    ],
    'image' => ['media_id' => ['type' => 'media', 'label' => 'Gambar'], 'alt' => ['type' => 'text', 'label' => 'Alt text gambar', 'max' => 255], 'caption' => ['type' => 'text', 'label' => 'Keterangan singkat', 'max' => 1000]],
    'practice_selection' => $narrative,
    'values' => $narrative + [
        'image_id' => ['type' => 'media', 'label' => 'Gambar latar'],
        'items' => ['type' => 'items', 'label' => 'Daftar tiga nilai firma (JSON)', 'items' => $valueItem, 'min' => 3, 'max' => 3, 'required' => true],
    ],
    'sectors' => $narrative + ['items' => ['type' => 'items', 'label' => 'Daftar lima bentuk layanan (JSON)', 'items' => ['title' => ['type' => 'text', 'label' => 'Bentuk layanan', 'max' => 255, 'required' => true], 'description' => $rich + ['required' => true]], 'min' => 5, 'max' => 5, 'required' => true]],
    'steps' => $narrative + ['items' => ['type' => 'items', 'label' => 'Daftar tiga langkah (JSON)', 'items' => ['title' => ['type' => 'text', 'label' => 'Judul item', 'max' => 255, 'required' => true], 'description' => $rich + ['required' => true]], 'min' => 3, 'max' => 3, 'required' => true]],
    'recognitions' => $narrative + ['items' => ['type' => 'items', 'label' => 'Direktori/penghargaan (JSON)', 'items' => ['title' => ['type' => 'text', 'label' => 'Direktori/penghargaan', 'max' => 255], 'rank' => ['type' => 'text', 'label' => 'Peringkat', 'max' => 100], 'year' => ['type' => 'text', 'label' => 'Tahun', 'max' => 20], 'evidence' => ['type' => 'text', 'label' => 'Bukti internal', 'max' => 500], 'verified' => ['type' => 'boolean', 'label' => 'Terverifikasi dan disetujui untuk publikasi']], 'max' => 50]],
    'enquiry' => $narrative + $link,
    'latest_insights' => $narrative + ['limit' => ['type' => 'number', 'label' => 'Jumlah publikasi', 'min' => 1, 'max' => 12, 'required' => true], 'insight_ids' => ['type' => 'ids', 'label' => 'Publikasi pilihan (urutannya mengikuti pilihan)', 'table' => 'insights']],
    'timeline' => $narrative + ['items' => $items(['year' => ['type' => 'text', 'label' => 'Tahun', 'max' => 20], 'title' => ['type' => 'text', 'label' => 'Judul', 'max' => 255], 'description' => $rich, 'evidence' => ['type' => 'text', 'label' => 'Bukti internal', 'max' => 500], 'verified' => ['type' => 'boolean', 'label' => 'Terverifikasi dan disetujui untuk publikasi']])],
    'vision_mission' => [
        'vision_title' => ['type' => 'text', 'label' => 'Judul visi', 'max' => 255],
        'vision' => ['type' => 'rich_text', 'label' => 'Penjelasan visi', 'max' => 30000],
        'mission_title' => ['type' => 'text', 'label' => 'Judul misi', 'max' => 255],
        'mission' => ['type' => 'rich_text', 'label' => 'Penjelasan misi', 'max' => 30000],
    ],
    'quote' => ['quote' => ['type' => 'rich_text', 'label' => 'Kutipan', 'max' => 3000], 'attribution' => ['type' => 'text', 'label' => 'Atribusi', 'max' => 255], 'approved' => ['type' => 'boolean', 'label' => 'Disetujui untuk publikasi'], 'evidence' => ['type' => 'text', 'label' => 'Bukti persetujuan internal', 'max' => 500]],
    'statistics' => $narrative + ['items' => ['type' => 'items', 'label' => 'Angka firma (JSON)', 'items' => ['value' => ['type' => 'text', 'label' => 'Nilai', 'max' => 80], 'unit' => ['type' => 'text', 'label' => 'Satuan', 'max' => 80], 'title' => ['type' => 'text', 'label' => 'Label', 'max' => 255], 'evidence' => ['type' => 'text', 'label' => 'Sumber/bukti internal', 'max' => 500], 'verified_at' => ['type' => 'date', 'label' => 'Tanggal verifikasi'], 'verified' => ['type' => 'boolean', 'label' => 'Terverifikasi dan disetujui untuk publikasi']], 'max' => 50]],
    'client_logos' => $narrative + ['items' => ['type' => 'items', 'label' => 'Logo dan tanggapan klien (JSON)', 'items' => ['client_name' => ['type' => 'text', 'label' => 'Nama/identitas klien', 'max' => 255], 'media_id' => ['type' => 'media', 'label' => 'Media logo'], 'alt' => ['type' => 'text', 'label' => 'Alt text logo', 'max' => 255], 'permission_confirmed' => ['type' => 'boolean', 'label' => 'Izin penggunaan dikonfirmasi'], 'evidence' => ['type' => 'text', 'label' => 'Bukti izin/tanggapan internal', 'max' => 500], 'feedback' => ['type' => 'text', 'label' => 'Tanggapan klien yang disetujui', 'max' => 1000], 'rating' => ['type' => 'text', 'label' => 'Nilai penilaian yang disetujui', 'max' => 20]], 'max' => 50]],
    'leadership' => $narrative + ['professional_ids' => ['type' => 'ids', 'label' => 'Pilih satu profil pimpinan dari Profesional', 'table' => 'professionals', 'min' => 1, 'max' => 1, 'required' => true]],
    'faq' => $narrative + ['items' => ['type' => 'items', 'label' => 'Daftar FAQ (JSON)', 'items' => [
        'question' => ['type' => 'text', 'label' => 'Pertanyaan', 'max' => 500, 'required' => true],
        'answer' => $rich + ['required' => true],
    ], 'min' => 3, 'max' => 10, 'required' => true]],
    'cta' => $narrative + $link,
    'practice_directory' => $narrative,
    'professional_directory' => $narrative,
    'insight_directory' => $narrative,
    'sitemap' => $narrative,
];

return [
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Jakarta'),
    'page_keys' => ['home', 'about', 'practice-areas', 'team', 'insights', 'contact', 'sitemap'],
    'section_types' => array_keys($sectionFields),
    'section_fields' => $sectionFields,
    'setting_groups' => ['identity', 'contact', 'navigation', 'footer', 'seo'],
    'setting_keys' => ['identity.name', 'identity.logo', 'identity.favicon', 'contact.details', 'navigation.main', 'footer.content', 'seo.defaults'],
];
