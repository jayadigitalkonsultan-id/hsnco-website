@props(['record'])
@php
    $legacy = array_replace(config('professionals.portrait_defaults', []), config('professionals.portrait_framing.'.$record->slug, []));
    $origin = explode(' ', str_replace('%', '', $legacy['origin'] ?? '50% 50%'));
    $framing = old('photo_framing', $record->photo_framing ?? ['zoom' => $legacy['zoom'] ?? 1, 'x' => $origin[0] ?? 50, 'y' => $origin[1] ?? 50]);
@endphp
<section class="portrait-editor mb-4" data-portrait-editor>
    <h3 class="h6">Sesuaikan bingkai foto</h3>
    <p class="field-help">Atur foto terpilih sebelum disimpan. Foto asli tetap utuh. Pengaturan ini berlaku untuk kartu direktori profesional.</p>
    <div class="portrait-preview" data-portrait-preview hidden><img alt="Pratinjau bingkai foto profesional" data-portrait-image></div>
    <p class="field-help" data-portrait-empty role="status">Pilih foto profil untuk melihat pratinjau.</p>
    @foreach(['zoom' => ['Perbesaran', 1, 3, 0.05, 1], 'x' => ['Posisi horizontal', 0, 100, 1, 50], 'y' => ['Posisi vertikal', 0, 100, 1, 50]] as $key => $setting)
    <label class="form-label d-flex justify-content-between" for="portrait-{{ $key }}">{{ $setting[0] }} <output data-portrait-output="{{ $key }}" for="portrait-{{ $key }}"></output></label>
    <input class="form-range mb-3" id="portrait-{{ $key }}" type="range" name="photo_framing[{{ $key }}]" min="{{ $setting[1] }}" max="{{ $setting[2] }}" step="{{ $setting[3] }}" value="{{ $framing[$key] ?? $setting[4] }}" data-portrait-control="{{ $key }}">
    @error('photo_framing.'.$key)<p class="text-danger small">{{ $message }}</p>@enderror
    @endforeach
    <button class="btn btn-outline-secondary btn-sm" type="button" data-portrait-reset>Atur ulang bingkai</button>
    <p class="field-help mt-2">Gunakan foto beresolusi tinggi agar tetap tajam saat diperbesar. Simpan sebagai Draf jika belum siap ditayangkan.</p>
</section>
@push('styles')
<style>
.portrait-preview{width:100%;max-width:272px;aspect-ratio:4/5;overflow:hidden;background:#111;margin:12px auto 20px;border:1px solid #c8ae71;isolation:isolate}
.portrait-preview[hidden]{display:none}
.portrait-preview img{display:block;width:100%;height:100%;object-fit:cover;max-width:none}
.portrait-editor output{font-variant-numeric:tabular-nums;color:#71571d}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-portrait-editor]').forEach(editor => {
        const form = editor.closest('form');
        const image = editor.querySelector('[data-portrait-image]');
        const preview = editor.querySelector('[data-portrait-preview]');
        const empty = editor.querySelector('[data-portrait-empty]');
        const controls = Object.fromEntries(Array.from(editor.querySelectorAll('[data-portrait-control]'), input => [input.dataset.portraitControl, input]));
        function paint() {
            const {zoom, x, y} = Object.fromEntries(Object.entries(controls).map(([key, input]) => [key, Number(input.value)]));
            image.style.transform = `scale(${zoom})`;
            image.style.transformOrigin = `${x}% ${y}%`;
            image.style.objectPosition = `${x}% ${y}%`;
            Object.entries(controls).forEach(([key, input]) => { editor.querySelector(`[data-portrait-output="${key}"]`).textContent = key === 'zoom' ? `${Number(input.value).toFixed(2)}×` : `${input.value}%`; });
        }
        function select() {
            const selected = form.querySelector('input[name="photo_id"]:checked');
            const source = selected?.closest('label')?.querySelector('img');
            preview.hidden = !source;
            empty.hidden = !!source;
            if (source) image.src = source.src;
            else image.removeAttribute('src');
            paint();
        }
        Object.values(controls).forEach(input => input.addEventListener('input', paint));
        form.querySelectorAll('input[name="photo_id"]').forEach(input => input.addEventListener('change', () => {
            controls.zoom.value = 1; controls.x.value = 50; controls.y.value = 50;
            select();
        }));
        image.addEventListener('error', () => { preview.hidden = true; empty.hidden = false; empty.textContent = 'Foto tidak dapat dimuat. Periksa file di Pustaka Media.'; });
        image.addEventListener('load', () => { preview.hidden = false; empty.hidden = true; });
        editor.querySelector('[data-portrait-reset]').addEventListener('click', () => { controls.zoom.value = 1; controls.x.value = 50; controls.y.value = 50; paint(); });
        select();
    });
});
</script>
@endpush
