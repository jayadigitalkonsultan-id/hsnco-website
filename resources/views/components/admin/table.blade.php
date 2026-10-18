@props(['headers', 'caption'])
<div class="table-responsive" tabindex="0" role="region" aria-label="{{ $caption }}">
<table {{ $attributes->class(['table admin-table']) }}>
    <caption class="visually-hidden">{{ $caption }}</caption>
    <thead><tr>@foreach($headers as $header)<th scope="col">{{ $header }}</th>@endforeach</tr></thead>
    <tbody>{{ $slot }}</tbody>
</table>
</div>

