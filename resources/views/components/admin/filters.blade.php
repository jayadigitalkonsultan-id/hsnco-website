@props(['statuses' => ['draft' => 'Draft', 'review' => 'Perlu review', 'published' => 'Tayang', 'archived' => 'Diarsipkan']])
<form method="get" action="{{ url()->current() }}" class="list-toolbar">
    <div class="search-field"><x-admin.icon name="search" /><label class="visually-hidden" for="listSearch">Cari konten</label><input class="form-control" id="listSearch" name="q" type="search" value="{{ request('q') }}" maxlength="100" placeholder="Cari dalam daftar…"></div>
    <div class="filter-fields">
        @if(count($statuses))<label class="visually-hidden" for="listStatus">Status</label><select class="form-select" id="listStatus" name="status"><option value="">Semua status</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>@endif
        <label class="visually-hidden" for="listPerPage">Jumlah per halaman</label><select class="form-select" id="listPerPage" name="per_page">@foreach([10,20,50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }} per halaman</option>@endforeach</select>
        <button class="btn btn-outline-secondary" type="submit">Terapkan</button>
    </div>
</form>

