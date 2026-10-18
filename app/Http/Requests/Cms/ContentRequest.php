<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

abstract class ContentRequest extends FormRequest
{
    protected string $modelClass;

    protected string $routeKey;

    public function authorize(): bool
    {
        $target = $this->route($this->routeKey);
        if ($this->isMethod('POST') && $target === null) {
            return Gate::allows('create', $this->modelClass);
        }

        return $target instanceof $this->modelClass && Gate::allows('update', $target);
    }

    protected function uniqueSlug(string $table): array
    {
        $target = $this->route($this->routeKey);

        return ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique($table, 'slug')->ignore($target instanceof $this->modelClass ? $target->getKey() : null)];
    }

    protected function editorialRules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(['draft', 'review'])],
            'created_by' => ['prohibited'], 'updated_by' => ['prohibited'],
            'published_by' => ['prohibited'], 'published_at' => ['prohibited'],
            'published_revision' => ['prohibited'], 'id' => ['prohibited'],
        ];
    }

    protected function mediaRule(): array
    {
        return ['nullable', 'ulid', Rule::exists('media', 'id')->whereNull('deleted_at')->where('visibility', 'public')];
    }
}
