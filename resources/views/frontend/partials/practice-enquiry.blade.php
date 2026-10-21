<section class="section-space surface-paper" id="enquiry" aria-labelledby="practice-enquiry-title">
    <div class="container"><div class="row g-5">
        <div class="col-lg-6">
            <p class="eyebrow">Start a conversation</p>
            <h2 class="section-title" id="practice-enquiry-title">Deep expertise.<br><em>Clear direction.</em></h2>
            <p>Tell us about your legal needs.</p>
            @if($verifiedContactDetails['address'])<p>{{ $verifiedContactDetails['address'] }}</p>@endif
            @if($verifiedContactDetails['phone'])<p><a href="tel:{{ $verifiedContactDetails['phone_href'] }}">{{ $verifiedContactDetails['phone'] }}</a></p>@endif
            @if($verifiedContactDetails['email'])<p><a href="mailto:{{ $verifiedContactDetails['email'] }}">{{ $verifiedContactDetails['email'] }}</a></p>@endif
            @if(!$verifiedContactDetails['address'] && !$verifiedContactDetails['phone'] && !$verifiedContactDetails['email'])
                <p class="content-notice">Verified contact details will be made available here.</p>
            @endif
        </div>
        <div class="col-lg-6"><x-frontend.enquiry-form prefix="practice" source="contact" /></div>
    </div></div>
</section>