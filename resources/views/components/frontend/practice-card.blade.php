@props(['href'])
<a href="{{ $href }}" {{ $attributes->class(['practice-tile']) }}>{{ $slot }}</a>
