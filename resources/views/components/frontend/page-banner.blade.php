@props(['current','eyebrow'])
<section class="inner-hero"><div class="container"><x-frontend.breadcrumb :current="$current" /><div class="row g-4 g-lg-5 align-items-end"><div class="col-lg-6"><p class="eyebrow">{{ $eyebrow }}</p><h1>{{ $heading }}</h1></div><div class="col-lg-6"><p class="banner-intro">{{ $intro }}</p></div></div></div></section>
