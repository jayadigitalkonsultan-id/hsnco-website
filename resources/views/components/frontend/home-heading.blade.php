@props(['heading' => null])
@php
    $approvedHeading = 'Wisdom in Law. Strength in Advocacy';
    $plainHeading = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$heading)));
    $useApprovedHeading = $plainHeading === '' || in_array($plainHeading, [
        $approvedHeading, 'Wisdom in Law. Strength in Advocacy.',
        'Comprehensive legal counsel.', 'Layanan hukum yang menyeluruh.',
    ], true);
@endphp
<h1>@if($useApprovedHeading)Wisdom in Law.<br><em>Strength in Advocacy</em>@else{{ $heading }}@endif</h1>