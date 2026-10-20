<script defer src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@if(!request()->routeIs('sitemap'))<script defer src="{{ asset('assets/js/content-data.js') }}"></script>@endif
@foreach(['components','main','motion'] as $script)<script defer src="{{ asset('assets/js/'.$script.'.js') }}{{ $script === 'motion' ? '?v='.filemtime(public_path('assets/js/motion.js')) : '' }}"></script>@endforeach
<script defer src="{{ asset('assets/plugins-init/bootstrap-init.js') }}"></script>
