<?php

namespace App\Http\Requests\Cms;

use App\Models\Page;
use Illuminate\Validation\Rule;

class PageRequest extends ContentRequest
{
    protected string $modelClass = Page::class;

    protected string $routeKey = 'page';

    public function rules(): array
    {
        return array_merge($this->editorialRules(), [
            'slug' => $this->uniqueSlug('pages'),
            'key' => ['required', Rule::in(config('cms.page_keys')), Rule::unique('pages', 'key')->ignore($this->route('page') instanceof Page ? $this->route('page')->id : null)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'template' => ['required', Rule::in(config('cms.page_keys'))],
            'og_media_id' => $this->mediaRule(),
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
