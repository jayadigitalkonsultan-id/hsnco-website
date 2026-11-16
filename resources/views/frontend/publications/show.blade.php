@extends('layouts.frontend')
@section('title', ($article->seo_title ?: $article->title).' | HSNCO')
@section('description', $article->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: $article->body), 160))
@section('content')
<header class="publication-header"><div class="container">
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('insights') }}">News &amp; Insights</a></li><li class="breadcrumb-item active" aria-current="page">{{ $article->publication_label }}</li></ol></nav>
    <p class="eyebrow">{{ $article->publication_label }}</p><h1>{{ $article->title }}</h1>
    <p class="publication-byline"><time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d F Y') }}</time>@if($article->publishedAuthor)<span>By {{ $article->publishedAuthor->name }}</span>@endif</p>
    @if($article->excerpt)<p class="publication-standfirst">{{ strip_tags($article->excerpt) }}</p>@endif
</div></header>
<section class="publication-reading"><div class="container">
    @if($article->cover_url)<figure class="publication-cover"><img src="{{ $article->cover_url }}" alt="{{ $article->publicCoverMedia?->alt_text ?: $article->title }}" width="1440" height="900" decoding="async"></figure>@endif
    <article class="publication-body" aria-label="Article content">
        @if(filled($article->body))
            @if($article->body === strip_tags($article->body))<div class="publication-plain">{{ $article->body }}</div>@else<x-frontend.rich-text :html="$article->body" />@endif
        @else<p>Further details will be published when available.</p>@endif
        <div class="publication-end"><p>This publication provides general information and does not constitute legal advice.</p><a class="text-link" href="{{ route('insights') }}">Back to News &amp; Insights <span aria-hidden="true">&rarr;</span></a></div>
    </article>
</div></section>
@include('frontend.publications.recommended')
@endsection
@push('styles')@include('frontend.publications.styles')@endpush
