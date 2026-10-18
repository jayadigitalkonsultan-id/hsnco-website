@props(['paginator'])
<div class="list-footer">
    <p role="status">Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</p>
    @if($paginator->hasPages())
    <nav aria-label="Navigasi halaman daftar"><ul class="pagination mb-0">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">@if($paginator->onFirstPage())<span class="page-link" aria-disabled="true">Sebelumnya</span>@else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif</li>
        <li class="page-item active"><span class="page-link" aria-current="page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span></li>
        <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">@if($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span class="page-link" aria-disabled="true">Berikutnya</span>@endif</li>
    </ul></nav>
    @endif
</div>

