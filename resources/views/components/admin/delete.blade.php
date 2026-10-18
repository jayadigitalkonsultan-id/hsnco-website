@props(['action', 'title'])
<form method="post" action="{{ $action }}" data-confirm-delete data-confirm-title="{{ $title }}">
    @csrf @method('DELETE')
    <button class="table-action delete-action" type="submit" aria-label="Arsipkan {{ $title }}"><x-admin.icon name="trash" /></button>
</form>

