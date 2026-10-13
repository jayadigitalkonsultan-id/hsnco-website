@props(['href','variant'=>'outline'])
<a href="{{ $href }}" {{ $attributes->class(['btn', $variant === 'primary' ? 'btn-brand' : 'btn-outline-brand']) }}>{{ $slot }}</a>
