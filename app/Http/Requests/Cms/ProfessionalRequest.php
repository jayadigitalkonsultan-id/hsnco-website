<?php

namespace App\Http\Requests\Cms;

use App\Models\Professional;
use Illuminate\Validation\Rule;

class ProfessionalRequest extends ContentRequest
{
    protected function prepareForValidation(): void
    {
        $data = ['practice_area_ids' => $this->input('practice_area_ids', [])];
        foreach (['qualifications', 'languages'] as $field) {
            if ($this->has($field.'_text')) {
                $value = $this->input($field.'_text');
                $data[$field] = is_string($value) ? array_values(array_filter(array_map('trim', explode("\n", $value)), fn ($line) => $line !== '')) : $value;
            }
        }
        $this->merge($data);
    }

    protected string $modelClass = Professional::class;

    protected string $routeKey = 'professional';

    public function rules(): array
    {
        return array_merge($this->editorialRules(), [
            'slug' => $this->uniqueSlug('professionals'),
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:150'],
            'level' => ['required', Rule::in(['partner', 'associate'])],
            'summary' => ['nullable', 'string', 'max:5000'],
            'biography' => ['nullable', 'string', 'max:100000'],
            'qualifications' => ['nullable', 'array', 'max:30'],
            'qualifications.*' => ['string', 'max:255'],
            'languages' => ['nullable', 'array', 'max:30'],
            'languages.*' => ['string', 'max:100'],
            'photo_id' => $this->mediaRule(),
            'email' => ['nullable', 'email', 'max:254'],
            'practice_area_ids' => ['sometimes', 'array', 'max:30'],
            'practice_area_ids.*' => ['ulid', 'distinct', Rule::exists('practice_areas', 'id')->whereNull('deleted_at')],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_leadership' => ['sometimes', 'boolean'],
        ]);
    }
}
