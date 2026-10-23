<div class="hero-visual" data-hero-slideshow aria-hidden="true">
    @foreach (['hsnco1.jpg', 'hsnco2.jpg', 'hsnco3.jpg', 'hsnco4.jpg'] as $image)
        <img class="hero-image hero-slide" src="{{ asset('assets/images/'.$image) }}" alt="" width="1920" height="1280" decoding="async" fetchpriority="{{ $loop->first ? 'high' : 'low' }}">
    @endforeach
</div>
