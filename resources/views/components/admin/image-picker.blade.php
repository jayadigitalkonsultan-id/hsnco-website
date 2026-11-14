@props(['name', 'label', 'value' => null, 'media', 'contain' => false])
@php
    $selected = (string) old($name, $value);
    $fieldId = 'image-picker-'.\Illuminate\Support\Str::slug($name);
@endphp
<fieldset @class(['image-picker', 'mb-4', 'image-picker-contain' => $contain]) aria-describedby="{{ $fieldId }}-help{{ $errors->has($name) ? ' '.$fieldId.'-error' : '' }}">
    <legend class="form-label">{{ $label }}</legend>
    <p class="field-help" id="{{ $fieldId }}-help">Hanya gambar dengan penggunaan <strong>Gambar publik website</strong> yang tersedia di sini. Gambar internal tidak dapat dipakai sebagai sampul. Periksa status gambar di <a href="{{ route('admin.media.index') }}">Pustaka Media</a>. Gambar terpilih ditandai dengan bingkai emas.</p>
    <div class="image-picker-search">
        <label for="{{ $fieldId }}-search">Cari gambar berdasarkan nama file</label>
        <input class="form-control" type="search" id="{{ $fieldId }}-search" placeholder="Contoh: sidang-hery-susanto" autocomplete="off" aria-controls="{{ $fieldId }}-grid" data-image-picker-search>
        <p class="field-help" aria-live="polite" data-image-picker-count>Menampilkan {{ $media->count() }} gambar publik. Unggahan terbaru ditampilkan lebih dahulu.</p>
    </div>
    <div class="image-picker-grid" id="{{ $fieldId }}-grid" data-image-picker-grid>
        <label class="image-picker-option">
            <input class="visually-hidden" type="radio" name="{{ $name }}" value="" @checked($selected === '')>
            <span class="image-picker-card">
                <span class="image-picker-empty" aria-hidden="true"><x-admin.icon name="image" /></span>
                <span class="image-picker-name">Tanpa gambar</span>
            </span>
        </label>
        @foreach($media->sortByDesc(fn ($image) => (string) $image->id === $selected) as $image)
            <label class="image-picker-option" data-image-name="{{ $image->original_name }}">
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
.image-picker-search{margin:14px 0 10px}
.image-picker-search label{display:block;margin-bottom:6px;font-size:12px;font-weight:600}
.image-picker-search .field-help{margin:6px 0 0}
.image-picker-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;max-height:430px;overflow-y:auto;padding:4px;scrollbar-width:thin}
.image-picker-option{position:relative;cursor:pointer;min-width:0}
.image-picker-option[hidden]{display:none}
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
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.image-picker').forEach((picker) => {
        const search = picker.querySelector('[data-image-picker-search]');
        const grid = picker.querySelector('[data-image-picker-grid]');
        const count = picker.querySelector('[data-image-picker-count]');
        if (!search || !grid || !count) return;
        const choices = Array.from(grid.querySelectorAll('[data-image-name]'));
        search.addEventListener('input', () => {
            const query = search.value.trim().toLocaleLowerCase();
            let visible = 0;
            choices.forEach((choice) => {
                choice.hidden = !choice.dataset.imageName.toLocaleLowerCase().includes(query);
                if (!choice.hidden) visible++;
            });
            count.textContent = visible === 0
                ? 'Tidak ada gambar yang cocok. Coba nama file lain.'
                : `Menampilkan ${visible} dari ${choices.length} gambar publik.`;
            grid.scrollTop = 0;
        });
    });
});
</script>
@endpush
@endonce
