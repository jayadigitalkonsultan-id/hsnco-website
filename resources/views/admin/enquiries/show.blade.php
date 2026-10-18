@extends('layouts.admin')
@section('title', 'Detail permintaan')
@section('intro', 'Informasi ini hanya untuk tim yang diberi izin.')
@section('actions')<a class="btn btn-outline-secondary" href="{{ route('admin.enquiries.index') }}">Kembali ke inbox</a>@endsection
@section('content')
<div class="row g-4"><div class="col-lg-8"><section class="panel"><div class="panel-heading"><div><h2>{{ $enquiry->first_name }} {{ $enquiry->last_name }}</h2><p>{{ $enquiry->email }} @if($enquiry->phone) · {{ $enquiry->phone }} @endif</p></div><x-admin.status :status="$enquiry->status" /></div><div class="editor-body"><p class="overline">{{ $enquiry->area_label }}</p><div class="message-body">{{ $enquiry->message }}</div><p class="small text-muted mt-3 mb-0">
    @if($enquiry->notification_sent_at)
        Notifikasi email internal tercatat terkirim {{ $enquiry->notification_sent_at->timezone(config('cms.display_timezone'))->format('d M Y, H:i') }}.
    @else
        Permintaan sudah tersimpan; belum ada catatan bahwa notifikasi email internal berhasil terkirim.
    @endif
</p></div></section><section class="panel mt-4"><div class="panel-heading"><div><h2>Catatan internal</h2><p>Tidak dikirim kepada pengirim pesan.</p></div></div><div class="editor-body">@forelse($enquiry->notes as $note)<article class="enquiry-note"><strong>{{ $note->user?->name ?? 'Pengguna tidak tersedia' }}</strong><time>{{ $note->created_at->timezone(config('cms.display_timezone'))->format('d M Y, H:i') }}</time><p>{{ $note->body }}</p></article>@empty<p class="text-muted">Belum ada catatan tindak lanjut.</p>@endforelse<form method="post" action="{{ route('admin.enquiries.notes.store',$enquiry) }}">@csrf<x-admin.field name="body" label="Tambah catatan" type="textarea" required maxlength="10000" /><button class="btn btn-dark" type="submit">Simpan catatan</button></form></div></section></div>
<div class="col-lg-4"><section class="panel"><div class="panel-heading"><h2>Tindak lanjut</h2></div><form class="editor-body" method="post" action="{{ route('admin.enquiries.update',$enquiry) }}">@csrf @method('PUT')<x-admin.field name="status" label="Status permintaan" type="select" :value="$enquiry->status" :options="['new'=>'Baru','read'=>'Dibaca','in_progress'=>'Ditindaklanjuti','closed'=>'Selesai','spam'=>'Spam']" required /><x-admin.field name="assigned_to" label="Penanggung jawab" type="select" :value="$enquiry->assigned_to" :options="$assignees->pluck('name','id')->all()" /><button class="btn btn-dark" type="submit">Simpan status</button></form></section></div></div>
@endsection

