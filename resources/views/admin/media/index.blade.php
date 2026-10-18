@extends('layouts.admin')
@section('title', 'Media library')
@section('intro', 'Kelola gambar untuk mendukung informasi dan publikasi firma.')
@section('actions')<a class="btn btn-dark" href="{{ route('admin.media.create') }}"><x-admin.icon name="plus" /> {{ $preview ? 'Tinjau formulir unggah' : 'Unggah gambar' }}</a>@endsection
@section('content')
<section class="panel"><div class="panel-heading"><div><h2>Library gambar</h2><p>Gambar dan informasi penggunaan untuk website.</p></div><span class="total-pill">{{ $records->total() }} gambar</span></div>
<form method="get" action="{{ route('admin.media.index') }}" class="list-toolbar">
    <div class="search-field"><x-admin.icon name="search" /><label class="visually-hidden" for="mediaSearch">Cari media</label><input class="form-control" id="mediaSearch" name="q" type="search" value="{{ request('q') }}" maxlength="100" placeholder="Cari nama, alt text, atau keterangan…"></div>
    <div class="filter-fields"><label class="visually-hidden" for="mediaPerPage">Jumlah per halaman</label><select class="form-select" id="mediaPerPage" name="per_page">@foreach([10,20,50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }} per halaman</option>@endforeach</select><button class="btn btn-outline-secondary" type="submit">Cari media</button></div>
</form>
@if($records->count())<div class="row g-4 media-grid">
@foreach($records as $record)<div class="col-sm-6 col-xl-4"><article class="media-card"><a class="media-thumbnail" href="{{ route('admin.media.edit', $record) }}"><img src="{{ route('admin.media.file', $record) }}" alt="{{ $record->alt_text ?? $record->original_name }}" width="{{ $record->width ?? 480 }}" height="{{ $record->height ?? 320 }}" loading="lazy"></a><div class="media-copy"><h3><a href="{{ route('admin.media.edit', $record) }}">{{ $record->original_name }}</a></h3><p>{{ $record->width }} × {{ $record->height }} px · {{ number_format($record->size / 1024, 1, ',', '.') }} KB · {{ $record->visibility === 'private' ? 'Internal' : 'Publik' }}</p>
@if(count($record->usage_references))<p class="mb-2"><strong>Dipakai di {{ count($record->usage_references) }} referensi</strong></p><ul class="small ps-3 mb-3">@foreach(array_slice($record->usage_references, 0, 3) as $reference)<li>{{ $reference['type'] }}: {{ $reference['title'] }}</li>@endforeach</ul>@else<p class="small text-muted mb-3">Belum ada referensi pemakaian tercatat.</p>@endif
<div class="d-flex justify-content-between align-items-center"><a class="quiet-link" href="{{ route('admin.media.edit', $record) }}">Informasi gambar <x-admin.icon name="arrow" /></a>@can('delete',$record)<x-admin.delete :action="route('admin.media.destroy',$record)" :title="$record->original_name" />@endcan</div></div></article></div>@endforeach
</div>@else<x-admin.empty title="Belum ada gambar yang sesuai" description="Coba kata kunci lain atau unggah gambar baru." />@endif
<x-admin.pagination :paginator="$records" /></section>
@endsection
