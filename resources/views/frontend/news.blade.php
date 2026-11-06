@extends('layouts.frontend')

@section('title', 'Berita HSNCO')
@section('description', 'Berita dan informasi terbaru dari HSNCO.')

@section('content')
<x-frontend.page-banner current="Berita" eyebrow="Informasi HSNCO">
    <x-slot:heading>Berita dan informasi.</x-slot:heading>
    <x-slot:intro>Ikuti berita terbaru dan informasi dari HSNCO.</x-slot:intro>
</x-frontend.page-banner>

<section class="section-space surface-paper">
    <div class="container">
        @if ($news->isNotEmpty())
            <div class="row g-4">
                @foreach ($news as $item)
                    <article class="col-md-6 col-lg-4">
                        <div class="insight-story h-100" id="{{ $item->slug }}">
                            @if ($item->cover_url)
                                <img class="insight-story-image" src="{{ $item->cover_url }}" alt="{{ $item->publicCoverMedia?->alt_text ?: 'Gambar berita: '.$item->title }}" width="900" height="600" loading="lazy" decoding="async">
                            @endif
                            <p class="eyebrow">Berita{{ $item->published_author_name ? ' · '.$item->published_author_name : '' }}</p>
                            <h2>{{ $item->title }}</h2>
                            @if ($item->excerpt)<p>{{ $item->excerpt }}</p>@endif
                            <p class="insight-date">{{ $item->published_at?->timezone(config('cms.display_timezone'))->format('d/m/Y') }}</p>
                            @if ($item->body)
                                <details><summary class="text-link">Baca berita</summary><div class="mt-3">{!! nl2br(e($item->body)) !!}</div></details>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($news->hasPages())
                <nav class="mt-5" aria-label="Navigasi halaman berita">{{ $news->links() }}</nav>
            @endif
        @else
            <p class="empty-state">Belum ada berita yang tersedia.</p>
        @endif
    </div>
</section>
@endsection
