@props(['title' => 'Belum ada konten', 'description' => 'Konten yang Anda tambahkan akan tampil di sini.'])
<div class="empty-block"><span class="empty-symbol"><x-admin.icon name="file" /></span><strong>{{ $title }}</strong><p class="mb-0">{{ $description }}</p>{{ $slot }}</div>

