@props(['status'])
@php
$value = $status instanceof \BackedEnum ? $status->value : (string) $status;
$labels = ['draft' => 'Draft', 'review' => 'Perlu review', 'published' => 'Tayang', 'archived' => 'Diarsipkan', 'new' => 'Baru', 'read' => 'Dibaca', 'in_progress' => 'Ditindaklanjuti', 'closed' => 'Selesai', 'spam' => 'Spam'];
$labels += ['invited' => 'Diundang', 'pending_2fa' => 'Menunggu 2FA', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
@endphp
<span class="status-badge status-{{ array_key_exists($value, $labels) ? $value : 'draft' }}">{{ $labels[$value] ?? $value }}</span>

