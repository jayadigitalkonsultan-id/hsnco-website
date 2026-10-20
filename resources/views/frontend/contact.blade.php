@extends('layouts.frontend')
@php
    $banner = $contactSections->get('banner');
    $enquirySection = $contactSections->get('enquiry');
    $faqSection = $contactSections->get('faq');
    $bannerContent = $banner?->content ?? [];
    $enquiryContent = $enquirySection?->content ?? [];
    $faqContent = $faqSection?->content ?? [];
    $faqItems = $faqContent['items'] ?? [
        ['question' => 'Apakah formulir ini mengirim permintaan?', 'answer' => 'Ya. Setelah berhasil disimpan, permintaan akan masuk ke inbox HSNCO untuk ditinjau. Konfirmasi tampil setelah penyimpanan berhasil.'],
        ['question' => 'Apa yang perlu disampaikan dalam permintaan awal?', 'answer' => 'Sertakan bidang bantuan dan uraian singkat. Jangan sertakan informasi rahasia atau dokumen sensitif pada tahap awal.'],
        ['question' => 'Apakah pengisian formulir membentuk hubungan advokat dan klien?', 'answer' => 'Tidak. Pengisian formulir tidak membentuk hubungan advokat dan klien. Penerimaan penugasan ditangani secara terpisah.'],
        ['question' => 'Bagaimana informasi kontak saya digunakan?', 'answer' => 'Informasi digunakan untuk meninjau dan menindaklanjuti permintaan. Akses inbox dibatasi kepada pengelola HSNCO yang berwenang.'],
    ];
@endphp
@section('title')
{{ $banner?->heading ?? 'Hubungi Kami' }} — HSNCO
@endsection
@section('description')
{{ $bannerContent['description'] ?? 'Sampaikan kebutuhan hukum Anda kepada HSNCO melalui formulir enquiry.' }}
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/contact.css') }}?v={{ filemtime(public_path('assets/css/contact.css')) }}">
@endpush
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Kontak" :eyebrow="$bannerContent['eyebrow'] ?? 'Mulai percakapan'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Nasihat yang jelas. Dimulai di sini.')) !!}</x-slot:heading><x-slot:intro>{!! app(\App\Services\Cms\RichTextSanitizer::class)->sanitize($bannerContent['description'] ?? 'Sampaikan kebutuhan hukum yang sedang Anda hadapi. Tim HSNCO akan meninjau permintaan awal Anda.') !!}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $enquirySection || $enquirySection->is_visible)
<section class="section-space surface-paper" id="enquiry"><div class="container"><div class="row g-5"><div class="col-lg-5">
    <p class="eyebrow">{{ $enquiryContent['eyebrow'] ?? 'Hubungi HSNCO' }}</p>
    <x-frontend.section-heading>{!! nl2br(e($enquirySection?->heading ?? 'Keahlian mendalam. Arah yang jelas.')) !!}</x-frontend.section-heading>
    <x-frontend.rich-text :html="$enquiryContent['description'] ?? 'Berikan gambaran singkat agar tim kami dapat memahami kebutuhan Anda.'" />
    @if ($verifiedContactDetails['address'])<div class="contact-detail"><h3>Kantor</h3><p>{{ $verifiedContactDetails['address'] }}</p></div>@endif
    @if ($verifiedContactDetails['phone'] || $verifiedContactDetails['email'])<div class="contact-detail"><h3>Kontak langsung</h3>
        @if ($verifiedContactDetails['phone'])<p><a href="tel:{{ $verifiedContactDetails['phone_href'] }}">{{ $verifiedContactDetails['phone'] }}</a></p>@endif
        @if ($verifiedContactDetails['email'])<p><a href="mailto:{{ $verifiedContactDetails['email'] }}">{{ $verifiedContactDetails['email'] }}</a></p>@endif
    </div>@endif
    @if (! $verifiedContactDetails['address'] && ! $verifiedContactDetails['phone'] && ! $verifiedContactDetails['email'])<p class="content-notice">Informasi kontak langsung akan ditampilkan setelah diverifikasi.</p>@endif
</div><div class="col-lg-7"><x-frontend.enquiry-form prefix="contact" /></div></div></div></section>
@endif

@include('frontend.partials.contact-map')

@if (! $faqSection || $faqSection->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4"><div class="col-lg-5"><p class="eyebrow">{{ $faqContent['eyebrow'] ?? 'Sebelum menghubungi kami' }}</p><x-frontend.section-heading>{!! nl2br(e($faqSection?->heading ?? 'Awal percakapan yang bermakna.')) !!}</x-frontend.section-heading></div><div class="col-lg-7"><div class="accordion accordion-brand" id="contactQuestions">
    @foreach ($faqItems as $index => $item)
    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer-{{ $index }}" aria-expanded="false" aria-controls="answer-{{ $index }}">{{ $item['question'] }}</button></h3><div class="accordion-collapse collapse" id="answer-{{ $index }}" data-bs-parent="#contactQuestions"><div class="accordion-body"><x-frontend.rich-text :html="$item['answer']" /></div></div></div>
    @endforeach
</div></div></div></div></section>
@endif
@endsection
