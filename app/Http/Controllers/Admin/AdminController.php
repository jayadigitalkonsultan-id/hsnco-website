<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

abstract class AdminController extends Controller
{
    protected function preview(Request $request): bool
    {
        return $request->attributes->get('admin_preview', false);
    }

    protected function contentQuery(Request $request, string $model): Builder
    {
        $query = $model::query();
        if (! $this->preview($request) && ! $request->user()->managesContent()) {
            $query->where('created_by', $request->user()->id);
        }

        return $query;
    }

    protected function authorizeRead(Request $request, string $ability, mixed $target): void
    {
        if (! $this->preview($request)) {
            Gate::authorize($ability, $target);
        }
    }

    protected function listingInput(Request $request, array $statuses): array
    {
        if (! $request->has('per_page')) {
            $request->merge(['per_page' => data_get($request->user()?->preferences, 'page_size', 10)]);
        }
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in($statuses)],
            'per_page' => ['nullable', Rule::in([10, 20, 50])],
            'sort' => ['nullable', Rule::in(['updated_desc', 'title_asc'])],
        ]);
    }
}
