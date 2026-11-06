@extends('layouts.admin')
@php($module = request()->route('module'))
@section('title', $definition['label'])
@section('intro', $module === 'practice-areas' ? 'Kelola judul, ringkasan, gambar latar, daftar layanan, dan urutan panel bidang praktik pada website.' : 'Susun dan tinjau konten firma dengan status editorial yang jelas.')
@section('actions')
@if($preview || auth()->user()?->can('create', $definition['model']))
<a class="btn btn-dark" href="{{ route('admin.'.$module.'.create') }}"><x-admin.icon name="plus" /> {{ $preview ? 'Tinjau formulir' : 'Tambah '.$definition['singular'] }}</a>
@endif
@endsection
@section('content')
@if($module === 'clients')<div class="alert alert-light border"><strong>Klien dan slideshow website.</strong> Pilih sektor dan thumbnail logo dari <a class="text-decoration-underline" href="{{ route('admin.media.index') }}">Pustaka Media</a>, lalu simpan sebagai Draf atau Tayang. Tayang langsung memperbarui daftar dan slideshow klien; Draf tidak ditampilkan. Tanpa logo, nama organisasi tampil pada direktori klien. Catatan sumber tetap internal. Gunakan Edit untuk mengubah informasi dan logo, atau Hapus untuk mengeluarkan klien dari website setelah konfirmasi.</div>@endif
@if($module === 'practice-areas')<div class="alert alert-light border mb-4"><strong>Panel layanan terhubung ke website.</strong> Isi daftar layanan satu butir per baris. Pilih Draf untuk menyimpan tanpa ditampilkan, atau Tayang agar langsung tampil di website. Angka urutan terkecil tampil lebih dahulu. Gambar dapat diunggah melalui <a href="{{ route('admin.media.index') }}" class="text-decoration-underline">Pustaka Media</a>.</div>@endif
@if($module === 'insights')<div class="alert alert-light border mb-4"><strong>Publikasi terhubung ke website.</strong> Pilih Draf untuk menyimpan tanpa ditampilkan, atau Tayang agar publikasi dan perubahannya langsung tampil di website.</div>@endif
@if($module === 'news')<div class="alert alert-light border mb-4"><strong>Berita terhubung ke website.</strong> Kelola judul, ringkasan, penulis, dan gambar berita di sini. Pilih Draf untuk menyimpan tanpa ditampilkan, atau Tayang agar berita langsung tampil.</div>@endif
@if($module === 'professionals')<div class="alert alert-light border mb-4"><strong>Profil terhubung ke website.</strong> Pilih Draf untuk menyimpan tanpa ditampilkan, atau Tayang agar profil dan perubahannya langsung tampil. Foto, bidang praktik, kualifikasi, dan bahasa mengikuti informasi yang disimpan.</div>@endif
@if($module === 'industries')<div class="alert alert-light border mb-4"><strong>Industri terhubung ke website.</strong> Pilih Draf untuk menyimpan tanpa ditampilkan, atau Tayang agar gambar, isi, topik diskusi, dan Key Contact langsung diperbarui di website. Pilih gambar melalui thumbnail pada formulir.</div>@endif
@if($module === 'pages')
<section class="panel site-structure" aria-labelledby="structureTitle"><div class="panel-heading"><div><h2 id="structureTitle">Struktur halaman</h2><p>Tinjau susunan informasi website HSNCO.</p></div></div><div class="row g-0"><div class="col-md-7"><div class="structure-tree"><label class="form-label" for="treeSearch">Cari dalam struktur</label><input id="treeSearch" class="form-control" type="search" placeholder="Nama halaman" maxlength="100"><div class="tree-tools"><button type="button" class="btn btn-outline-secondary btn-sm" id="expandTree">Buka semua</button><button type="button" class="btn btn-outline-secondary btn-sm" id="collapseTree">Tutup semua</button></div><div id="contentTree"></div><ul id="contentTreeFallback" class="tree-fallback">@foreach($tree as $node)<li><a href="{{ $node['data']['url'] }}">{{ $node['text'] }}</a></li>@endforeach</ul></div></div><div class="col-md-5"><div class="tree-selection"><p class="overline">Halaman terpilih</p><h3 id="selectedPageTitle">Informasi yang terhubung.</h3><p id="selectedPageSummary">Pilih halaman untuk meninjau judul dan susunan bagiannya.</p><a id="selectedPageEdit" class="btn btn-outline-secondary" hidden>Buka halaman <x-admin.icon name="arrow" /></a></div></div></div></section>
<script type="application/json" id="adminTreeData">@json($tree)</script>
@endif
<section class="panel content-panel">
    <div class="panel-heading"><div><h2>Daftar {{ strtolower($definition['label']) }}</h2><p>Pencarian dan filter mengikuti konten yang dapat Anda akses.</p></div><span class="total-pill">{{ $records->total() }} konten</span></div>
    <x-admin.filters :statuses="$statuses" />
    <x-admin.table :headers="[$definition['singular'], 'Status', 'Diperbarui', 'Tindakan']" :caption="'Daftar '.$definition['label']">
    @forelse($records as $record)
        <tr><td><a class="table-title" href="{{ route('admin.'.$module.'.edit', $record) }}">{{ $record->{$definition['title_field']} }}</a><span class="table-slug">{{ $record->slug }}</span>@if($module === 'clients')<small class="d-block text-muted">{{ config('clients.categories.'.$record->category) }} &middot; {{ $record->image_id ? 'Logo tersedia' : 'Logo belum dilengkapi' }}</small>@endif</td><td><x-admin.status :status="$record->status" :labels="$statuses" /></td><td><time class="table-date" datetime="{{ $record->updated_at->toIso8601String() }}">{{ $record->updated_at->timezone(config('cms.display_timezone'))->format('d M Y, H:i') }}</time></td><td><div class="row-actions"><a class="table-action" href="{{ route('admin.'.$module.'.edit', $record) }}">{{ $directPublishing ? 'Edit' : ($preview ? 'Tinjau' : 'Buka') }}</a>@can('delete', $record)<x-admin.delete :action="route('admin.'.$module.'.destroy', $record)" :title="$record->{$definition['title_field']}" :label="$module === 'clients' ? 'Hapus' : null" />@endcan</div></td></tr>
    @empty<tr><td colspan="4"><x-admin.empty title="Belum ada konten yang sesuai" description="Coba kata kunci lain atau ubah filter status." /></td></tr>@endforelse
    </x-admin.table>
    <x-admin.pagination :paginator="$records" />
</section>
<div class="workflow-note"><x-admin.icon name="file" /><span>{{ $directPublishing ? 'Hanya konten berstatus Tayang yang ditampilkan di website. Kembalikan ke Draf untuk menyembunyikannya.' : 'Draft dan konten yang diajukan untuk review disimpan terpisah dari proses publikasi.' }}</span></div>
@endsection
@if($module === 'pages')
@push('styles')<link rel="stylesheet" href="{{ asset('admin-assets/vendor/jstree/themes/default/style.min.css') }}"><link rel="stylesheet" href="{{ asset('admin-assets/css/plugins.css') }}">@endpush
@push('scripts')
<script defer src="{{ asset('admin-assets/vendor/jquery/jquery.min.js') }}"></script>
<script defer src="{{ asset('admin-assets/vendor/jstree/jstree.min.js') }}"></script>
<script defer src="{{ asset('admin-assets/plugins-init/jstree-init.js') }}"></script>
@endpush
@endif

