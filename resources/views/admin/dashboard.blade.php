@extends('layouts.admin')
@section('title', 'Dashboard')
@section('intro', 'Kelola informasi firma, publikasi, dan komunikasi awal dalam satu ruang kerja.')
@section('actions')
<a class="btn btn-outline-secondary" href="{{ route('admin.pages.index') }}"><x-admin.icon name="file" /> Kelola halaman</a>
<a class="btn btn-dark" href="{{ route('admin.insights.create') }}"><x-admin.icon name="plus" /> {{ $preview ? 'Tinjau formulir publikasi' : 'Tambah publikasi' }}</a>
@endsection
@section('content')
<section class="welcome-panel" aria-labelledby="welcomeTitle"><div><span class="welcome-kicker">RUANG KERJA WEBSITE HSNCO</span><h2 id="welcomeTitle">Informasi yang tertata.<br><em>Langkah yang terarah.</em></h2><p>Kelola cerita firma, keahlian profesional,<br class="d-none d-md-inline"> dan publikasi dalam satu tempat.</p><a class="welcome-link" href="{{ route('admin.pages.index') }}">Jelajahi konten website <x-admin.icon name="arrow" /></a></div><div class="welcome-art" aria-hidden="true"><span class="art-ring ring-one"></span><span class="art-ring ring-two"></span><span class="art-ring ring-three"></span><div class="art-monogram">H</div><span class="art-caption">HSNCO / CONTENT WORKSPACE</span></div></section>
<section class="row g-3 g-xl-4 metric-grid mb-4" aria-label="Ringkasan website">
@foreach([['Halaman website', $counts['pages'], 'Struktur halaman utama', 'file'], ['Praktik hukum', $counts['practice-areas'], 'Keahlian yang dikelola', 'scales'], ['Publikasi', $counts['insights'], 'Draft dan artikel firma', 'news'], ['Permintaan baru', $unread ?? '—', $unread === null ? 'Tersedia bagi pengelola berizin' : 'Menunggu tindak lanjut', 'mail']] as [$label,$value,$description,$icon])
<div class="col-sm-6 col-xl-3"><article class="metric-card"><div class="metric-top"><span>{{ $label }}</span><span class="metric-icon"><x-admin.icon :name="$icon" /></span></div><strong>{{ $value }}</strong><p>{{ $description }}</p></article></div>
@endforeach
</section>
<div class="row g-4">
    <section class="col-xl-8"><div class="panel"><div class="panel-heading"><div><h2>Konten terbaru diperbarui</h2><p>Ringkasan pekerjaan editorial website.</p></div><a class="quiet-link" href="{{ route('admin.pages.index') }}">Lihat halaman <x-admin.icon name="arrow" /></a></div>
    <x-admin.table :headers="['Konten', 'Status', 'Diperbarui', 'Tindakan']" caption="Konten terbaru" class="dashboard-table">
        @forelse($recent as $item)<tr><td><a class="table-title" href="{{ $item['url'] }}">{{ $item['title'] }}</a><span class="table-subtitle">{{ $item['module'] }}</span></td><td><x-admin.status :status="$item['status']" /></td><td><time class="table-date" datetime="{{ $item['updated_at']->toIso8601String() }}">{{ $item['updated_at']->timezone(config('cms.display_timezone'))->format('d M Y') }}</time></td><td><a class="table-action" href="{{ $item['url'] }}" aria-label="Buka {{ $item['title'] }}"><x-admin.icon name="arrow" /></a></td></tr>
        @empty<tr><td colspan="4"><x-admin.empty title="Ruang untuk cerita berikutnya" description="Belum ada konten yang dapat ditampilkan." /></td></tr>@endforelse
    </x-admin.table></div></section>
    <section class="col-xl-4"><div class="panel h-100"><div class="panel-heading"><div><h2>Status editorial</h2><p>Distribusi konten yang dapat Anda akses.</p></div></div>
        <div class="editorial-canvas-wrap" id="editorialCanvasWrap" hidden><canvas id="editorialCanvas" role="img" aria-label="Distribusi status editorial"></canvas><div class="editorial-center"><strong>{{ array_sum($chart) }}</strong><span>JUMLAH KONTEN</span></div></div>
        @if(array_sum($chart) === 0)<p class="text-muted mt-3 mb-0" role="status">Belum ada data editorial untuk ditampilkan.</p>@endif
        <div class="editorial-chart">@foreach(['draft'=>'Draft','review'=>'Perlu review','published'=>'Tayang','archived'=>'Diarsipkan'] as $status => $label)<div class="chart-row"><div class="chart-label"><span>{{ $label }}</span><strong>{{ $chart[$status] }}</strong></div><div class="chart-track"><div class="chart-bar chart-{{ $status }}" style="width: {{ array_sum($chart) ? round($chart[$status] / array_sum($chart) * 100) : 0 }}%"></div></div></div>@endforeach</div>
        <div class="chart-footnote"><x-admin.icon name="file" /> Berdasarkan status konten website.</div>
    </div></section>
</div>
<section class="panel mt-4"><div class="panel-heading"><div><h2>Aktivitas terakhir</h2><p>Perubahan terbaru yang tercatat dalam ruang kerja.</p></div>@if(auth()->user()?->can('viewAny', \App\Models\AuditLog::class))<a class="quiet-link" href="{{ route('admin.audit.index') }}">Buka audit log <x-admin.icon name="arrow" /></a>@endif</div>
    @if($recentActivity->isNotEmpty())<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Waktu</th><th>Aktivitas</th><th>Akun</th><th>Hasil</th></tr></thead><tbody>@foreach($recentActivity as $activity)<tr><td>{{ $activity->created_at?->timezone(config('cms.display_timezone'))->format('d M Y H:i') }}</td><td>{{ str_replace('_',' ', $activity->event) }}</td><td>{{ $activity->user?->name ?: $activity->user?->email ?: 'Akun sistem' }}</td><td>{{ $activity->outcome }}</td></tr>@endforeach</tbody></table></div>
    @elseif($recent->isNotEmpty())<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Waktu</th><th>Aktivitas</th><th>Konten</th><th>Status</th></tr></thead><tbody>@foreach($recent as $item)<tr><td>{{ $item['updated_at']->timezone(config('cms.display_timezone'))->format('d M Y H:i') }}</td><td>Konten diperbarui</td><td><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></td><td>{{ $item['status']->value }}</td></tr>@endforeach</tbody></table></div>
    @else<div class="card-body"><p class="text-muted mb-0">Belum ada aktivitas yang dapat ditampilkan.</p></div>@endif
</section>
<script type="application/json" id="adminChartData">@json($chart)</script>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin-assets/css/plugins.css') }}">@endpush
@push('scripts')
<script defer src="{{ asset('admin-assets/vendor/chartjs/chart.umd.min.js') }}"></script>
<script defer src="{{ asset('admin-assets/plugins-init/chart-init.js') }}"></script>
@endpush

