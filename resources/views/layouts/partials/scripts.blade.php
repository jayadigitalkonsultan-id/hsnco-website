<script defer src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@foreach(['components','main','motion'] as $script)<script defer src="{{ asset('assets/js/'.$script.'.js') }}?v={{ filemtime(public_path('assets/js/'.$script.'.js')) }}"></script>@endforeach
<script defer src="{{ asset('assets/plugins-init/bootstrap-init.js') }}"></script>
