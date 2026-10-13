@props(['current'])
<nav aria-label="Jejak navigasi"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item active" aria-current="page">{{ $current }}</li></ol></nav>
