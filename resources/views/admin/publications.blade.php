@extends('layouts.admin')
@section('title', 'News & Insights')
@section('intro', 'Manage firm news and legal insights in one place.')
@section('actions')<a class="btn btn-dark" href="{{ route('admin.news.create') }}">Add news</a><a class="btn btn-outline-secondary" href="{{ route('admin.insights.create') }}">Add insight</a>@endsection
@section('content')
<section class="panel"><div class="panel-heading"><h2>All news &amp; insights</h2><span class="total-pill">{{ $records->total() }} articles</span></div>
<x-admin.table :headers="['Title', 'Type', 'Status', 'Action']" caption="News and insights">
@forelse($records as $record)<tr><td><a class="table-title" href="{{ route('admin.'.$record->module.'.edit', $record->id) }}">{{ $record->title }}</a></td><td>{{ $record->module === 'news' ? 'News' : 'Insight' }}</td><td>{{ ucfirst($record->status) }}</td><td><a class="table-action" href="{{ route('admin.'.$record->module.'.edit', $record->id) }}">Edit article</a></td></tr>@empty<tr><td colspan="4">No articles yet.</td></tr>@endforelse
</x-admin.table><x-admin.pagination :paginator="$records" /></section>
@endsection
