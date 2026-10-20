@extends('layouts.admin')
@php($titleField = $definition['title_field'])
@section('title', 'Pratinjau '.$definition['singular'])
@section('intro', 'Pratinjau privat revisi v'.$revision->version.'. Hanya pengguna yang berizin dapat membuka halaman ini.')
@section('actions')<a class="btn btn-outline-secondary" href="{{ route('admin.'.$module.'.edit', $record) }}">Kembali ke editor</a>@endsection
@section('content')
<article class="panel"><div class="panel-heading"><div><p class="overline">{{ $definition['label'] }} · {{ $payload['slug'] ?? '' }}</p><h2>{{ $payload[$titleField] ?? 'Konten tanpa judul' }}</h2><p>Revisi v{{ $revision->version }} · <x-admin.status :status="$revision->status" /></p></div></div>
    <div class="editor-body">
        @foreach(['description', 'summary', 'excerpt', 'seo_title', 'seo_description', 'body', 'biography'] as $field)
            @if(!empty($payload[$field]))<section class="mb-4"><h3>{{ \Illuminate\Support\Str::headline($field) }}</h3><p class="text-break">{{ $payload[$field] }}</p></section>@endif
        @endforeach
        @if (! empty($payload['sections']))
            <h3>Bagian halaman</h3>
            @foreach ($payload['sections'] as $section)
                <section class="border rounded p-3 my-3">
                    <p class="overline">{{ $section['type'] ?? 'section' }} | urutan {{ $section['sort_order'] ?? 0 }} | {{ ! empty($section['is_visible']) ? 'Terlihat' : 'Disembunyikan' }}</p>
                    <h4>{{ $section['heading'] ?? $section['key'] ?? 'Bagian' }}</h4>
                    @include('admin.content.section-preview', ['section' => $section])
                </section>
            @endforeach
        @endif
        <details class="mt-4"><summary>Data lengkap revisi</summary><pre class="text-break mt-3">{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre></details>
    </div>
</article>
@endsection
