@extends('layouts.admin')
@section('title', 'Media library')
@section('intro', 'Kelola gambar untuk mendukung informasi dan publikasi firma.')
@section('content')
<section class="panel"><div class="panel-heading"><div><h2>Library gambar</h2><p>Gambar dan informasi penggunaan untuk website.</p></div><span class="total-pill">{{ $records->total() }} gambar</span></div>
@unless($preview)
<form class="media-upload" method="post" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" id="mediaUploadForm">
    @csrf
    <div class="media-upload-heading"><div><h3>Tambah gambar</h3><p>Siapkan gambar baru untuk library Anda.</p></div><span class="media-upload-badge">Unggah gambar</span></div>
    <label class="media-dropzone" for="mediaFile" id="mediaDropzone" tabindex="0" aria-describedby="mediaDropHint mediaFileName">
        <input class="visually-hidden" id="mediaFile" name="file" type="file" accept="image/jpeg,image/png,image/webp" required>
        <span class="media-drop-icon" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V3m-5 5 5-5 5 5M4 16v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4"/></svg></span>
        <img class="media-drop-preview" id="mediaPreview" alt="Pratinjau gambar yang akan diunggah" hidden>
        <span class="media-drop-title" id="mediaDropTitle">Seret gambar Anda ke sini</span>
        <span class="media-drop-hint" id="mediaDropHint">Satu gambar setiap kali, maksimal 5 MB</span>
        <span class="media-drop-formats" aria-hidden="true"><span>JPG</span><span>PNG</span><span>WebP</span></span>
        <span class="media-file-name" id="mediaFileName" aria-live="polite">atau klik area ini untuk memilih gambar</span>
    </label>
    <div class="media-upload-options">
        <div class="media-upload-visibility"><label for="mediaVisibility">Penggunaan gambar</label>
        <select class="form-select" id="mediaVisibility" name="visibility" aria-label="Penggunaan gambar">
            <option value="public">Gambar publik website</option>
            <option value="private">Gambar internal</option>
        </select>
        </div>
        <button class="btn btn-outline-secondary media-clear-button" type="button" id="mediaClearButton" hidden>Batalkan</button>
        <button class="btn btn-dark" type="submit" id="mediaUploadButton" disabled>Unggah gambar</button>
    </div>
</form>
@endunless
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

@push('styles')
<style>
.media-upload{margin:0 24px 24px;padding:24px;border:1px solid var(--admin-border);border-radius:16px;background:#fff;box-shadow:0 4px 20px #181b1c04}
.media-upload-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}
.media-upload-heading h3{font-size:15px;font-weight:700;margin:0 0 4px}
.media-upload-heading p{font-size:12px;color:var(--admin-muted);margin:0}
.media-upload-badge{padding:5px 11px;border-radius:100px;background:#f5f1e8;color:var(--admin-gold-dark);font-size:10px;font-weight:700;white-space:nowrap}
.media-dropzone{min-height:270px;border:1.5px dashed #cbd2cc;border-radius:12px;background:linear-gradient(145deg,#fafbf9,#f5f7f4);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;text-align:center;padding:32px 24px;cursor:pointer;transition:border-color .2s,background .2s,box-shadow .2s}
.media-dropzone:hover,.media-dropzone.is-dragging{border-color:var(--admin-gold);background:#faf7f0;box-shadow:inset 0 0 0 3px #b89a5c0d}
.media-dropzone:focus-visible,.media-dropzone:focus-within{outline:3px solid var(--admin-gold-dark);outline-offset:4px}
.media-drop-icon{width:64px;height:64px;border:1px solid #e0e6dd;border-radius:18px;background:#fff;color:#53634f;display:grid;place-items:center;margin-bottom:6px;box-shadow:0 5px 14px #26382208}
.media-drop-preview{display:block;width:auto;max-width:min(100%,480px);max-height:260px;object-fit:contain;border-radius:10px;background:#fff;box-shadow:0 4px 20px #181b1c12;margin-bottom:8px}
.media-drop-preview[hidden],.media-clear-button[hidden]{display:none}
.media-drop-title{font-size:16px;font-weight:700;color:var(--admin-ink)}
.media-drop-hint,.media-file-name{font-size:12px;color:var(--admin-muted)}
.media-file-name{max-width:100%;overflow-wrap:anywhere}
.media-drop-formats{display:flex;gap:6px;margin:3px 0}
.media-drop-formats span{padding:2px 9px;border:1px solid #e0e5df;border-radius:5px;background:#ffffffb3;color:#667260;font-size:10px;font-weight:700;letter-spacing:.04em}
.media-dropzone.has-file .media-drop-icon,.media-dropzone.has-file .media-drop-formats{display:none}
.media-dropzone.has-file{border-style:solid;border-color:#c5d5c4;background:#f6faf5}
.media-file-name.has-file{color:#31563a;font-weight:600}
.media-upload-options{display:flex;align-items:flex-end;gap:10px;margin-top:20px}
.media-upload-visibility{margin-right:auto;min-width:0;width:240px}
.media-upload-visibility label{display:block;font-size:11px;font-weight:600;color:var(--admin-muted);margin-bottom:6px}
.media-upload-options .form-select{background-color:#fafbf9}
.media-upload-options .btn{min-height:42px;border-radius:8px;white-space:nowrap}
@media(max-width:575.98px){.media-upload{margin:0 16px 18px;padding:18px}.media-upload-heading{align-items:flex-start}.media-upload-badge{display:none}.media-dropzone{min-height:240px;padding:24px 16px}.media-drop-title{font-size:14px}.media-upload-options{flex-wrap:wrap}.media-upload-visibility{width:100%;margin-bottom:4px}.media-upload-options .btn{flex:1}}
@media(prefers-reduced-motion:reduce){.media-dropzone{transition:none}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('mediaFile');
    const zone = document.getElementById('mediaDropzone');
    const fileName = document.getElementById('mediaFileName');
    const uploadButton = document.getElementById('mediaUploadButton');
    const preview = document.getElementById('mediaPreview');
    const title = document.getElementById('mediaDropTitle');
    const clearButton = document.getElementById('mediaClearButton');
    if (!input || !zone || !fileName || !uploadButton || !preview) return;
    let previewUrl = null;

    const showFile = (file) => {
        if (!file) {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            preview.removeAttribute('src');
            preview.hidden = true;
            zone.classList.remove('has-file');
            title.textContent = 'Seret gambar Anda ke sini';
            clearButton.hidden = true;
            fileName.textContent = 'atau klik area ini untuk memilih gambar';
            fileName.classList.remove('has-file');
            uploadButton.disabled = true;
            return;
        }
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
        zone.classList.add('has-file');
        title.textContent = 'Gambar siap diunggah';
        clearButton.hidden = false;
        fileName.textContent = file.name + ' · ' + (file.size / 1024 / 1024).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' MB · Klik untuk mengganti';
        fileName.classList.add('has-file');
        uploadButton.disabled = false;
    };

    input.addEventListener('change', () => showFile(input.files[0]));
    clearButton.addEventListener('click', () => {
        input.value = '';
        showFile(null);
    });
    zone.addEventListener('keydown', (event) => {
        if (event.target === zone && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            input.click();
        }
    });
    ['dragenter', 'dragover'].forEach((name) => zone.addEventListener(name, (event) => {
        event.preventDefault();
        zone.classList.add('is-dragging');
    }));
    ['dragleave', 'drop'].forEach((name) => zone.addEventListener(name, (event) => {
        event.preventDefault();
        zone.classList.remove('is-dragging');
    }));
    zone.addEventListener('drop', (event) => {
        const files = event.dataTransfer.files;
        if (files.length !== 1) {
            showFile(null);
            fileName.textContent = 'Lepaskan satu gambar setiap kali.';
            fileName.classList.remove('has-file');
            input.value = '';
            uploadButton.disabled = true;
            return;
        }
        input.files = files;
        showFile(files[0]);
    });
});
</script>
@endpush
