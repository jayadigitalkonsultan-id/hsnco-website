@extends('layouts.frontend')
@section('title', 'News & Insights | HSNCO')
@section('description', 'Legal perspectives, firm news and updates from HS & CO Counsellors at Law.')
@section('content')
<x-frontend.page-banner current="News & Insights" eyebrow="Perspectives & updates"><x-slot:heading>News &amp; Insights.</x-slot:heading><x-slot:intro>Legal perspectives and the latest updates from HS &amp; CO.</x-slot:intro></x-frontend.page-banner>
<section class="section-space publication-directory"><div class="container">
    <div class="publication-section-heading"><div><p class="eyebrow">From the firm</p><h2>Latest news &amp; insights.</h2></div><p>Perspectives to inform. Developments to follow.</p></div>
    <div class="row g-4 g-lg-5">@forelse($publications as $item)<div class="col-md-6 col-lg-4"><x-frontend.publication-card :item="$item" /></div>@empty<div class="col-12"><p class="empty-state">News and insights will appear here once published.</p></div>@endforelse</div>
    @if($publications->hasPages())<nav class="mt-5" aria-label="News and insights pagination">{{ $publications->links() }}</nav>@endif
</div></section>
@endsection
@push('styles')@include('frontend.publications.styles')@endpush
