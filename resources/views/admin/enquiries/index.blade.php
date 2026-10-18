@extends('layouts.admin')
@section('title', 'Permintaan konsultasi')
@section('intro', 'Tinjau komunikasi awal dan catat tindak lanjut dari tim.')
@section('content')
<section class="panel"><div class="panel-heading"><div><h2>Inbox permintaan</h2><p>Pesan yang dikirim melalui formulir website.</p></div></div>
@if($restricted)<x-admin.empty title="Pesan dilindungi" description="Masuk dengan akun pengelola dan verifikasi OTP email untuk membaca permintaan konsultasi." />
@else
<x-admin.filters :statuses="['new'=>'Baru','read'=>'Dibaca','in_progress'=>'Ditindaklanjuti','closed'=>'Selesai','spam'=>'Spam']" />
<x-admin.table :headers="['Pengirim','Bidang bantuan','Status','Diterima','Tindakan']" caption="Inbox permintaan konsultasi">
@forelse($records as $record)<tr><td><a class="table-title" href="{{ route('admin.enquiries.show', $record) }}">{{ $record->first_name }} {{ $record->last_name }}</a><span class="table-subtitle">{{ $record->email }}</span></td><td>{{ $record->area_label }}</td><td><x-admin.status :status="$record->status" /></td><td><time class="table-date" datetime="{{ $record->created_at->toIso8601String() }}">{{ $record->created_at->timezone(config('cms.display_timezone'))->format('d M Y') }}</time></td><td><a class="table-action" href="{{ route('admin.enquiries.show',$record) }}">Baca pesan</a></td></tr>
@empty<tr><td colspan="5"><x-admin.empty title="Belum ada permintaan" description="Pesan dari website akan tampil setelah formulir konsultasi diaktifkan." /></td></tr>@endforelse
</x-admin.table><x-admin.pagination :paginator="$records" />
@endif</section>
@endsection

