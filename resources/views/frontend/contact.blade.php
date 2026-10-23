@extends('layouts.frontend')
@php
    $banner = $contactSections->get('banner');
    $enquirySection = $contactSections->get('enquiry');
    $faqSection = $contactSections->get('faq');
    $bannerContent = $banner?->content ?? [];
    $enquiryContent = $enquirySection?->content ?? [];
    $faqContent = $faqSection?->content ?? [];
    $faqItems = $faqContent['items'] ?? [
        ['question' => 'Will this form submit my enquiry?', 'answer' => 'Yes. Once successfully saved, your enquiry will be available to the HSNCO team for review. A confirmation will appear after it has been saved.'],
        ['question' => 'What should I include in an initial enquiry?', 'answer' => 'Please indicate the area in which you need assistance and provide a brief overview. Do not include confidential information or sensitive documents at this stage.'],
        ['question' => 'Does submitting this form establish a lawyer-client relationship?', 'answer' => 'No. Submitting this form does not establish a lawyer-client relationship. Acceptance of an engagement is addressed separately.'],
        ['question' => 'How will my contact information be used?', 'answer' => 'Your information is used to review and follow up on your enquiry. Access is restricted to authorised HSNCO personnel.'],
    ];
@endphp
@section('title')
{{ $banner?->heading ?? 'Contact Us' }} — HSNCO
@endsection
@section('description')
{{ $bannerContent['description'] ?? 'Contact HSNCO using our enquiry form to discuss your legal needs.' }}
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/contact.css') }}?v={{ filemtime(public_path('assets/css/contact.css')) }}">
@endpush
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Contact" :eyebrow="$bannerContent['eyebrow'] ?? 'Start a conversation'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Clear advice. It starts here.')) !!}</x-slot:heading><x-slot:intro>{!! app(\App\Services\Cms\RichTextSanitizer::class)->sanitize($bannerContent['description'] ?? 'Tell us about your legal needs. Our team will review your initial enquiry.') !!}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $enquirySection || $enquirySection->is_visible)
<section class="section-space surface-paper" id="enquiry"><div class="container"><div class="row g-5"><div class="col-lg-5">
    <p class="eyebrow">{{ $enquiryContent['eyebrow'] ?? 'Contact HSNCO' }}</p>
    <x-frontend.section-heading>{!! nl2br(e($enquirySection?->heading ?? 'Deep expertise. Clear direction.')) !!}</x-frontend.section-heading>
    <x-frontend.rich-text :html="$enquiryContent['description'] ?? 'Please provide a brief overview to help us understand your needs.'" />
    @if ($verifiedContactDetails['address'])<div class="contact-detail"><h3>Our office</h3><p>{{ $verifiedContactDetails['address'] }}</p></div>@endif
    @if ($verifiedContactDetails['phone'] || $verifiedContactDetails['email'])<div class="contact-detail"><h3>Direct contact</h3>
        @if ($verifiedContactDetails['phone'])<p><a href="tel:{{ $verifiedContactDetails['phone_href'] }}">{{ $verifiedContactDetails['phone'] }}</a></p>@endif
        @if ($verifiedContactDetails['email'])<p><a href="mailto:{{ $verifiedContactDetails['email'] }}">{{ $verifiedContactDetails['email'] }}</a></p>@endif
    </div>@endif
    @if (! $verifiedContactDetails['address'] && ! $verifiedContactDetails['phone'] && ! $verifiedContactDetails['email'])<p class="content-notice">Verified contact details will be made available here.</p>@endif
</div><div class="col-lg-7"><x-frontend.enquiry-form prefix="contact" /></div></div></div></section>
@endif

@include('frontend.partials.contact-map')

@if (! $faqSection || $faqSection->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4"><div class="col-lg-5"><p class="eyebrow">{{ $faqContent['eyebrow'] ?? 'Before you contact us' }}</p><x-frontend.section-heading>{!! nl2br(e($faqSection?->heading ?? 'A considered first conversation.')) !!}</x-frontend.section-heading></div><div class="col-lg-7"><div class="accordion accordion-brand" id="contactQuestions">
    @foreach ($faqItems as $index => $item)
    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer-{{ $index }}" aria-expanded="false" aria-controls="answer-{{ $index }}">{{ $item['question'] }}</button></h3><div class="accordion-collapse collapse" id="answer-{{ $index }}" data-bs-parent="#contactQuestions"><div class="accordion-body"><x-frontend.rich-text :html="$item['answer']" /></div></div></div>
    @endforeach
    <div class="accordion-item">
        <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billingAnswer" aria-expanded="false" aria-controls="billingAnswer">How are legal fees structured?</button></h3>
        <div class="accordion-collapse collapse" id="billingAnswer" data-bs-parent="#contactQuestions"><div class="accordion-body">
            <p>HS &amp; CO offers hourly rates, flat fees, success fees, retainers, contingency fees and project-based fees. Arrangements are discussed transparently and tailored to the scope, complexity and needs of each engagement.</p>
            <a class="text-link" href="{{ route('billing-fees') }}">Explore our fee arrangements <span aria-hidden="true">&rarr;</span></a>
        </div></div>
    </div>
</div></div></div></div></section>
@endif
@endsection
