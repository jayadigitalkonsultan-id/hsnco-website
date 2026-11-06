<?php

namespace App\Http\Requests\Cms;

use App\Models\News;
use Illuminate\Validation\Rule;

class NewsRequest extends ContentRequest
{
    protected string $modelClass = News::class;

    protected string $routeKey = 'news';

    public function rules(): array
    {
        return array_merge($this->editorialRules(), [
            'status' => ['required', Rule::in(['draft', 'published'])],
            'slug' => $this->uniqueSlug('berita'),
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
