@extends('layouts.frontend')
@section('title', 'Pratinjau — '.$aboutPage->title)
@section('description', 'Pratinjau privat halaman Tentang Firma.')
@section('content')
    <aside class="container section-space pb-0" role="status">
        <p>Pratinjau privat revisi v{{ $revision->version }}. <a href="{{ route('admin.pages.edit', $record) }}">Kembali ke editor</a></p>
    </aside>
    @include('frontend.about.cms-sections')
@endsection
