@props(['name', 'label', 'value' => null, 'media', 'contain' => false])
@php
    $selected = (string) old($name, $value);
    $fieldId = 'image-picker-'.\Illuminate\Support\Str::slug($name);
@endphp
<fieldset @class(['image-picker', 'mb-4', 'image-picker-contain' => $contain]) aria-describedby="{{ $fieldId }}-help{{ $errors->has($name) ? ' '.$fieldId.'-error' : '' }}">
    <legend class="form-label">{{ $label }}</legend>
    <p class="field-help" id="{{ $fieldId }}-help">Pilih gambar dari Pustaka Media. Gambar terpilih ditandai dengan bingkai emas.</p>
    <div class="image-picker-grid">
        <label class="image-picker-option">
            <input class="visually-hidden" type="radio" name="{{ $name }}" value="" @checked($selected === '')>
            <span class="image-picker-card">
                <span class="image-picker-empty" aria-hidden="true"><x-admin.icon name="image" /></span>
                <span class="image-picker-name">Tanpa gambar</span>
            </span>
        </label>
        @foreach($media->sortByDesc(fn ($image) => (string) $image->id === $selected) as $image)
            <label class="image-picker-option">
                <input class="visually-hidden" type="radio" name="{{ $name }}" value="{{ $image->id }}" @checked($selected === (string) $image->id)>
                <span @class(['image-picker-card', 'image-picker-card-dark' => $contain && $image->caption === 'dark-surface'])>
                    <img src="{{ route('admin.media.file', $image) }}" alt="" loading="lazy" width="240" height="150">
                    <span class="image-picker-name" title="{{ $image->original_name }}">{{ $image->original_name }}</span>
                </span>
            </label>
        @endforeach
    </div>
    @error($name)<div class="invalid-feedback d-block" id="{{ $fieldId }}-error">{{ $message }}</div>@enderror
</fieldset>
@once
@push('styles')
<style>
.image-picker legend{float:none;font-size:inherit;font-weight:600;margin-bottom:6px}
.image-picker-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;max-height:430px;overflow-y:auto;padding:4px;scrollbar-width:thin}
.image-picker-option{position:relative;cursor:pointer;min-width:0}
.image-picker-card{display:flex;flex-direction:column;height:100%;border:2px solid var(--admin-border);border-radius:10px;overflow:hidden;background:#fff;transition:border-color .15s,box-shadow .15s}
.image-picker-card img,.image-picker-empty{width:100%;height:100px;object-fit:cover;background:#f4f5f2}
.image-picker-contain .image-picker-card img{object-fit:contain;padding:8px;background:#fff}
.image-picker-contain .image-picker-card-dark img{background:#202622}
.image-picker-empty{display:grid;place-items:center;color:var(--admin-muted)}
.image-picker-name{padding:8px;font-size:10px;line-height:1.5;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.image-picker-option:hover .image-picker-card{border-color:#c6b482}
.image-picker-option input:checked+.image-picker-card{border-color:var(--admin-gold);box-shadow:0 0 0 2px #b89a5c26;background:#fbf8f0}
.image-picker-option input:focus-visible+.image-picker-card{outline:3px solid var(--admin-gold-dark);outline-offset:2px}
@media(prefers-reduced-motion:reduce){.image-picker-card{transition:none}}
</style>
@endpush
@endonce
