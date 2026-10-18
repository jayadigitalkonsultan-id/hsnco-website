<?php

namespace App\Http\Requests\Cms;

use App\Models\Insight;
use Illuminate\Validation\Rule;

class InsightRequest extends ContentRequest
{
    protected string $modelClass = Insight::class;

    protected string $routeKey = 'insight';

    public function rules(): array
    {
        return array_merge($this->editorialRules(), [
            'slug' => $this->uniqueSlug('insights'),
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:5000'],
            'body' => ['nullable', 'string', 'max:100000'],
            'cover_media_id' => $this->mediaRule(),
            'author_id' => ['nullable', 'ulid', Rule::exists('professionals', 'id')->whereNull('deleted_at')],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
