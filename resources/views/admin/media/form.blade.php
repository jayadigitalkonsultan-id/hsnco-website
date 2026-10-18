@extends('layouts.admin')
@section('title', $record->exists ? 'Informasi gambar' : 'Unggah gambar')
@section('intro', 'Gunakan gambar yang telah disetujui dan lengkapi teks alternatif untuk aksesibilitas.')
@section('actions')<a class="btn btn-outline-secondary" href="{{ route('admin.media.index') }}">Kembali ke library</a>@endsection
@section('content')
<div class="row g-4"><div class="col-lg-7"><section class="panel"><div class="panel-heading"><div><h2>{{ $record->exists ? $record->original_name : 'Gambar baru' }}</h2><p>JPG, PNG, atau WebP, maksimal 5 MB.</p></div></div><form class="editor-body" method="post" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.media.update', $record) : route('admin.media.store') }}">@csrf @if($record->exists) @method('PUT') @endif
<fieldset @disabled(!$editable)>
    @if(!$record->exists)<x-admin.field name="file" label="Pilih gambar" type="file" accept="image/jpeg,image/png,image/webp" required /><x-admin.field name="visibility" label="Penggunaan" type="select" value="public" :options="['public'=>'Gambar publik website','private'=>'Gambar internal']" required />@endif
    <x-admin.field name="alt_text" label="Teks alternatif" :value="$record->alt_text" maxlength="255" help="Jelaskan isi gambar secara singkat." />
    <x-admin.field name="caption" label="Keterangan" type="textarea" :value="$record->caption" maxlength="2000" />
    <button class="btn btn-dark" type="submit">{{ $record->exists ? 'Simpan informasi' : 'Unggah gambar' }}</button>
</fieldset>
</form></section></div><div class="col-lg-5"><section class="panel"><div class="panel-heading"><div><h2>Pratinjau gambar</h2><p>{{ $record->exists ? 'Gambar yang tersimpan dalam library.' : 'Gambar akan tersedia setelah berhasil diunggah.' }}</p></div></div>@if($record->exists)<img class="media-detail-image" src="{{ route('admin.media.file', $record) }}" alt="{{ $record->alt_text ?? $record->original_name }}" width="{{ $record->width ?? 480 }}" height="{{ $record->height ?? 320 }}">@else<x-admin.empty title="Ruang untuk gambar Anda" description="Pilih gambar dengan ukuran dan format yang sesuai." />@endif</section>
@if($record->exists)<section class="panel mt-4"><div class="panel-heading"><div><h2>Referensi pemakaian</h2><p>Lokasi konten yang tercatat menggunakan media ini.</p></div><span class="total-pill">{{ count($usageReferences) }}</span></div>@if(count($usageReferences))<ul class="mb-0 ps-3">@foreach($usageReferences as $reference)<li class="mb-2"><strong>{{ $reference['type'] }}</strong>: {{ $reference['title'] }} <span class="text-muted">({{ $reference['field'] }})</span></li>@endforeach</ul>@else<p class="mb-0 text-muted">Belum ada referensi pemakaian tercatat.</p>@endif</section>@endif
</div></div>
@endsection

