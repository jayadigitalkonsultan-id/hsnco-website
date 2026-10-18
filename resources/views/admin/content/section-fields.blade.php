@props(['sectionType', 'contentData' => []])
@foreach(config('cms.section_fields.'.$sectionType, []) as $fieldKey => $field)
    @php
        $fieldName = 'content.'.$fieldKey;
        $fieldValue = data_get($contentData, $fieldKey);
        $inputType = match($field['type']) { 'rich_text', 'items' => 'textarea', 'media', 'ids', 'boolean' => 'select', 'url' => 'text', default => $field['type'] };
        $options = match($field['type']) {
            'media' => $media->pluck('original_name', 'id')->all(),
            'ids' => ($field['table'] ?? '') === 'practice_areas' ? $practices->pluck('name', 'id')->all() : (($field['table'] ?? '') === 'professionals' ? $authors->pluck('name', 'id')->all() : $insights->pluck('title', 'id')->all()),
            'boolean' => ['0' => 'Tidak', '1' => 'Ya'],
            default => [],
        };
        $fieldHelp = match($field['type']) {
            'rich_text' => 'Rich text dibatasi ke paragraf, judul, daftar, kutipan, tautan aman, dan penekanan.',
            'items' => 'Urutan item dalam array JSON menjadi urutan tampil. Boolean memakai true/false; tanggal memakai YYYY-MM-DD. Contoh: '.json_encode([collect($field['items'])->map(fn ($item) => match ($item['type']) { 'boolean' => false, 'media', 'date' => null, default => '' })->all()], JSON_UNESCAPED_UNICODE),
            'ids' => ($field['table'] ?? '') === 'practice_areas' ? 'Pilih tepat empat praktik. Urutan pilihan menjadi urutan kartu di Beranda.' : null,
            default => null,
        };
        if ($field['type'] === 'items' && is_array($fieldValue)) $fieldValue = json_encode($fieldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @endphp
    <x-admin.field :name="$fieldName" :label="$field['label']" :type="$inputType" :value="$fieldValue" :options="$options" :multiple="$field['type'] === 'ids'" :help="$fieldHelp" :min="$field['min'] ?? null" :max="$field['max'] ?? ($field['type'] === 'text' ? 255 : null)" />
    @if ($sectionType === 'client_logos' && $field['type'] === 'items')
        <details class="mb-3"><summary>Referensi ID media untuk logo klien</summary><p class="field-help">Gunakan gambar publik. Izin harus mencakup logo, identitas, dan tanggapan/penilaian yang diisi pada item tersebut. Bukti internal tidak ditampilkan di website.</p>
            <ul>@foreach ($media as $asset)<li>{{ $asset->original_name }}: <code>{{ $asset->id }}</code></li>@endforeach</ul>
        </details>
    @endif
@endforeach
