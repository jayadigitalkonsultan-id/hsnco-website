@extends('layouts.admin')
@php
$module = request()->route('module');
$nameField = $definition['title_field'];
@endphp
@section('title', ($record->exists ? 'Tinjau ' : 'Tambah ').$definition['singular'])
@section('intro', 'Lengkapi informasi utama dan simpan sebagai draft untuk proses editorial.')
@section('actions')@if($record->exists && !$preview)<a class="btn btn-outline-secondary" href="{{ route('admin.'.$module.'.preview', $workflowRecord) }}">Pratinjau</a>@endif<a class="btn btn-outline-secondary" href="{{ route('admin.'.$module.'.index') }}">Kembali ke daftar</a>@endsection
@section('content')
@if(!$preview && !$editable)<div class="alert alert-info" role="status">Konten ini hanya dapat ditinjau. Penyuntingan konten tayang dilakukan melalui revisi editorial.</div>@endif
<form method="post" action="{{ $record->exists ? route('admin.'.$module.'.update', $record) : route('admin.'.$module.'.store') }}" class="editor-form">
@csrf @if($record->exists) @method('PUT') @endif
<div class="row g-4"><div class="col-xl-8"><section class="panel"><div class="panel-heading"><div><h2>Informasi {{ strtolower($definition['singular']) }}</h2><p>Kolom bertanda * wajib diisi.</p></div></div><div class="editor-body"><fieldset @disabled(!$editable)>
    <x-admin.field :name="$nameField" :label="$nameField === 'name' ? 'Nama' : 'Judul'" :value="$record->{$nameField}" required maxlength="255" />
    @if($module === 'pages')<x-admin.field name="description" label="Deskripsi halaman" type="textarea" :value="$record->description" maxlength="5000" />@endif
    <x-admin.field name="slug" label="Slug" :value="$record->slug" required maxlength="160" help="Gunakan huruf kecil dan tanda hubung, misalnya: profil-firma." />
    @if($module === 'pages')
        <x-admin.field name="key" label="Halaman website" type="select" :value="$record->key" :options="['home'=>'Beranda','about'=>'Tentang Firma','practice-areas'=>'Bidang Praktik','team'=>'Profesional','insights'=>'Publikasi','contact'=>'Kontak','sitemap'=>'Peta Situs']" required />
        <x-admin.field name="template" label="Tampilan halaman" type="select" :value="$record->template" :options="['home'=>'Beranda','about'=>'Tentang Firma','practice-areas'=>'Bidang Praktik','team'=>'Profesional','insights'=>'Publikasi','contact'=>'Kontak','sitemap'=>'Peta Situs']" required />
    @elseif($module === 'clients')
        <x-admin.field name="category" label="Sektor klien" type="select" :value="$record->category" :options="config('clients.categories')" required />
        <x-admin.field name="summary" label="Nama sebelumnya / keterangan singkat" type="textarea" :value="$record->summary" maxlength="5000" />
        <x-admin.field name="body" label="Gambaran penugasan" type="textarea" :value="$record->body" maxlength="100000" help="Tampil pada detail klien. Gunakan materi yang telah disetujui untuk publikasi." />
        <x-admin.field name="source_note" label="Catatan sumber internal" type="textarea" :value="$record->source_note" maxlength="5000" help="Tidak ditampilkan di website." />
    @elseif($module === 'industries')
        <x-admin.field name="english_name" label="Nama Inggris" :value="$record->english_name" maxlength="255" />
        <x-admin.field name="summary" label="Ringkasan kartu" type="textarea" :value="$record->summary" maxlength="5000" />
        <x-admin.field name="body" label="Gambaran industri" type="textarea" :value="$record->body" maxlength="100000" />
        <x-admin.field name="focus_text" label="Topik diskusi" type="textarea" :value="implode(PHP_EOL, $record->focus ?? [])" help="Satu topik per baris." />
        <x-admin.field name="key_contact_ids" label="Key Contact / Profesional" type="select" :value="$selectedKeyContacts" :options="$keyContactOptions->pluck('name','id')->all()" multiple help="Pilih satu atau beberapa profesional yang sudah terbit. Perubahan tampil setelah industri dipublikasikan." />
    @elseif($module === 'professionals')
        <div class="row"><div class="col-md-7"><x-admin.field name="position" label="Jabatan" :value="$record->position" required maxlength="150" /></div><div class="col-md-5"><x-admin.field name="level" label="Kelompok" type="select" :value="$record->level ?? 'partner'" :options="config('professionals.categories')" required /></div></div>
        <x-admin.field name="summary" label="Ringkasan keahlian" type="textarea" :value="$record->summary" maxlength="5000" />
        <x-admin.field name="biography" label="Biografi" type="textarea" :value="$record->biography" maxlength="100000" />
        <x-admin.field name="qualifications_text" label="Kualifikasi" type="textarea" :value="implode(PHP_EOL, $record->qualifications ?? [])" help="Satu kualifikasi per baris." />
        <x-admin.field name="languages_text" label="Bahasa" type="textarea" :value="implode(PHP_EOL, $record->languages ?? [])" help="Satu bahasa per baris." />
        <x-admin.field name="email" label="Email profesional" type="email" :value="$record->email" maxlength="254" />
        <x-admin.field name="practice_area_ids" label="Bidang praktik" type="select" :value="$record->exists ? $record->practiceAreas->pluck('id')->all() : []" :options="$practices->pluck('name','id')->all()" multiple help="Pilih satu atau beberapa bidang praktik." />
    @else
        <x-admin.field :name="$module === 'insights' ? 'excerpt' : 'summary'" label="Ringkasan" type="textarea" :value="$record->{$module === 'insights' ? 'excerpt' : 'summary'}" maxlength="5000" />
        <x-admin.field name="body" :label="$module === 'practice-areas' ? 'Daftar layanan' : 'Isi konten'" type="textarea" :value="$record->body" maxlength="100000" :help="$module === 'practice-areas' ? 'Satu layanan per baris. Setiap baris ditampilkan sebagai satu butir pada panel website.' : 'Gunakan teks biasa untuk draft awal.'" />
        @if($module === 'insights')<x-admin.field name="author_id" label="Penulis" type="select" :value="$record->author_id" :options="$authors->pluck('name','id')->all()" />@endif
    @endif
    @if($module !== 'professionals')<hr class="my-4"><h3 class="mb-3">Informasi pencarian</h3><x-admin.field name="seo_title" label="Judul pencarian" :value="$record->seo_title" maxlength="255" /><x-admin.field name="seo_description" label="Deskripsi pencarian" type="textarea" :value="$record->seo_description" maxlength="1000" />@endif
</fieldset></div></section></div>
<div class="col-xl-4"><section class="panel"><div class="panel-heading"><div><h2>Pengaturan konten</h2><p>Status dan tampilan di website.</p></div></div><div class="editor-body"><fieldset @disabled(!$editable)>
    <x-admin.field name="status" label="Status editorial" type="select" :value="$record->status ?? 'draft'" :options="['draft'=>'Draft','review'=>'Ajukan review']" required />
    @php($imageField = ['industries'=>'image_id','clients'=>'image_id','pages'=>'og_media_id','practice-areas'=>'image_id','professionals'=>'photo_id','insights'=>'cover_media_id'][$module])
    <x-admin.field :name="$imageField" :label="$module === 'clients' ? 'Logo klien' : ($module === 'practice-areas' ? 'Gambar latar kategori' : 'Gambar pilihan')" type="select" :value="$record->{$imageField}" :options="$media->pluck('original_name','id')->all()" />
    @if(in_array($module, ['industries','clients','practice-areas','professionals']))
        <x-admin.field name="sort_order" label="Urutan tampil" type="number" :value="$record->sort_order ?? 0" min="0" />
        @if(!in_array($module, ['clients','industries']))<input type="hidden" name="is_featured" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" @checked(old('is_featured', $record->is_featured))><label class="form-check-label" for="isFeatured">Tampilkan sebagai pilihan</label></div>@endif
        @if($module === 'professionals')<input type="hidden" name="is_leadership" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_leadership" id="isLeadership" value="1" @checked(old('is_leadership', $record->is_leadership))><label class="form-check-label" for="isLeadership">Tampilkan sebagai pimpinan</label></div>@endif
    @endif
    <button class="btn btn-dark w-100 mt-2" type="submit">Simpan draft</button>
</fieldset><p class="field-help mt-3">{{ $preview ? 'Formulir hanya dapat ditinjau pada pratinjau lokal.' : 'Penyimpanan draft tidak memublikasikan konten ke website.' }}</p></div></section></div></div>
</form>
@if($record->exists)
<section class="panel mt-4"><div class="panel-heading"><div><h2>Workflow editorial</h2><p>Status tayang: <x-admin.status :status="$workflowRecord->status" /> @if($workingRevision)<span class="ms-2">Draft terbaru: <x-admin.status :status="$workingStatus" /> (v{{ $workingRevision->version }})</span>@endif</p></div></div><div class="editor-body d-flex flex-wrap gap-2">
    @if($workingStatus === \App\Enums\ContentStatus::Review)@can('publish', $workflowRecord)<form method="post" action="{{ route('admin.'.$module.'.publish', $workflowRecord) }}">@csrf<button class="btn btn-dark" type="submit">Terbitkan revisi draft</button></form>@endcan @endif
    @can('archive', $workflowRecord)<form method="post" action="{{ route('admin.'.$module.'.archive', $workflowRecord) }}">@csrf<button class="btn btn-outline-secondary" type="submit">Arsipkan konten tayang</button></form>@endcan
    @if($workingStatus === \App\Enums\ContentStatus::Review)<span class="field-help align-self-center">Revisi menunggu pemeriksaan Administrator.</span>@elseif($workflowRecord->status === \App\Enums\ContentStatus::Published)<span class="field-help align-self-center">Versi tayang tetap tersedia sampai revisi baru diterbitkan.</span>@elseif($workflowRecord->status === \App\Enums\ContentStatus::Archived)<span class="field-help align-self-center">Konten arsip tidak tersedia untuk publik.</span>@endif
</div></section>
@endif
@if($module === 'pages' && $record->exists)
<section class="panel mt-4"><div class="panel-heading"><div><h2>Bagian halaman</h2><p>Kelola section bertipe tetap dengan field tervalidasi untuk layout website.</p></div></div>
    @forelse($record->sections as $section)
    <details class="editor-body border-top"><summary class="d-flex justify-content-between"><span><strong>{{ $section->sort_order }} · {{ $section->heading ?? \Illuminate\Support\Str::headline($section->key) }}</strong></span><span>{{ $section->is_visible ? 'Terlihat' : 'Disembunyikan' }} · {{ $section->type }}</span></summary>
        <form method="post" action="{{ route('admin.pages.sections.update', [$workflowRecord, $section->id]) }}" class="mt-3">@csrf @method('PUT')<fieldset @disabled(!$editable)>
            <div class="row"><div class="col-md-5"><x-admin.field name="key" label="Kunci bagian" :value="$section->key" required maxlength="100" /></div><div class="col-md-4"><x-admin.field name="type_display" label="Jenis section tetap" :value="\Illuminate\Support\Str::headline($section->type)" disabled /><input type="hidden" name="type" value="{{ $section->type }}"></div><div class="col-md-3"><x-admin.field name="sort_order" label="Urutan" type="number" :value="$section->sort_order" min="0" required /></div></div>
            <x-admin.field name="heading" label="Judul bagian" :value="$section->heading" maxlength="255" />
            @include('admin.content.section-fields', ['sectionType' => $section->type, 'contentData' => $section->content ?? []])
            @if(!empty($section->content['source_text']))<x-admin.field name="legacy_source" label="Catatan dari template lama" type="textarea" :value="$section->content['source_text']" disabled help="Teks sumber lama dipertahankan; lengkapi field terstruktur di atas." />@endif
            <input type="hidden" name="is_visible" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_visible" value="1" @checked($section->is_visible)><label class="form-check-label">Tampilkan bagian ini</label></div>
            @if($editable)<button class="btn btn-dark" type="submit">Simpan revisi section</button>@endif
        </fieldset></form>
        @if($editable)<form method="post" action="{{ route('admin.pages.sections.destroy', [$workflowRecord, $section->id]) }}" class="mt-2">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus dari revisi</button></form>@endif
    </details>
    @empty<div class="editor-body"><x-admin.empty title="Belum ada bagian halaman" description="Tambahkan section bertipe tetap untuk menyusun halaman." /></div>@endforelse
    @if($editable)
    <details class="editor-body border-top"><summary><strong>Tambah section halaman</strong></summary><form method="post" action="{{ route('admin.pages.sections.store', $workflowRecord) }}" class="mt-3" data-section-create>@csrf
        <div class="row"><div class="col-md-5"><x-admin.field name="key" label="Kunci bagian" required maxlength="100" /></div><div class="col-md-4"><x-admin.field name="type" label="Jenis section" type="select" :options="array_combine(config('cms.section_types'), array_map(fn($type) => \Illuminate\Support\Str::headline($type), config('cms.section_types')))" required data-section-type /></div><div class="col-md-3"><x-admin.field name="sort_order" label="Urutan" type="number" value="0" min="0" required /></div></div>
        <x-admin.field name="heading" label="Judul bagian" maxlength="255" />
        @foreach(config('cms.section_fields') as $type => $fields)<fieldset data-section-schema="{{ $type }}" @if(!$loop->first) disabled hidden @endif><legend class="h5">Field {{ \Illuminate\Support\Str::headline($type) }}</legend>@include('admin.content.section-fields', ['sectionType' => $type, 'contentData' => []])</fieldset>@endforeach
        <input type="hidden" name="is_visible" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_visible" value="1" checked><label class="form-check-label">Tampilkan section ini</label></div><button class="btn btn-dark" type="submit">Tambah section ke revisi</button>
    </form></details>
    @endif
</section>
@endif
@endsection
@if($module === 'pages')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-section-create]').forEach((form) => {
        const select = form.querySelector('[data-section-type]');
        const schemas = form.querySelectorAll('[data-section-schema]');
        const updateSchema = () => schemas.forEach((schema) => {
            const active = schema.dataset.sectionSchema === select.value;
            schema.hidden = !active;
            schema.disabled = !active;
        });
        select.addEventListener('change', updateSchema);
        updateSchema();
    });
});
</script>
@endpush
@endif



