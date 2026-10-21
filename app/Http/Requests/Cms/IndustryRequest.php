<?php

namespace App\Http\Requests\Cms;

use App\Models\Industry;
use Illuminate\Validation\Rule;

class IndustryRequest extends ContentRequest
{
    protected string $modelClass = Industry::class;

    protected string $routeKey = 'industry';

    protected function prepareForValidation(): void
    {
        $data = ['key_contact_ids' => $this->input('key_contact_ids', [])];
        if ($this->has('focus_text')) {
            $data['focus'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->input('focus_text')))));
        }
        $this->merge($data);
    }

    public function rules(): array
    {
        return array_merge($this->editorialRules(), [
            'name' => ['required', 'string', 'max:255'], 'slug' => $this->uniqueSlug('industries'), 'english_name' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'], 'body' => ['nullable', 'string', 'max:100000'], 'focus' => ['nullable', 'array', 'max:50'], 'focus.*' => ['string', 'max:500'],
            'image_id' => $this->mediaRule(), 'sort_order' => ['sometimes', 'integer', 'min:0'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string', 'max:1000'],
            'key_contact_ids' => ['present', 'array', 'max:20'], 'key_contact_ids.*' => ['ulid', 'distinct', Rule::exists('professionals', 'id')->whereNull('deleted_at')->where('status', 'published')->whereNotNull('published_at')->where(fn ($q) => $q->where('published_at', '<=', now()))],
        ]);
    }
}
