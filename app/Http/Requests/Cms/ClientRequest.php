<?php
namespace App\Http\Requests\Cms;

use App\Models\Client;
use Illuminate\Validation\Rule;

class ClientRequest extends ContentRequest
{
    protected string $modelClass = Client::class;
    protected string $routeKey = 'client';

    public function rules(): array
    {
        return array_merge($this->editorialRules(), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => $this->uniqueSlug('clients'),
            'category' => ['required', Rule::in(array_keys(config('clients.categories')))],
            'summary' => ['nullable', 'string', 'max:5000'],
            'body' => ['nullable', 'string', 'max:100000'],
            'source_note' => ['nullable', 'string', 'max:5000'],
            'image_id' => $this->mediaRule(),
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}