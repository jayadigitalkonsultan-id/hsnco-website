<?php

namespace App\Http\Requests\Cms;

use App\Models\PracticeArea;
use Illuminate\Validation\Rule;

class PracticeAreaRequest extends ContentRequest
{
    protected string $modelClass = PracticeArea::class;

    protected string $routeKey = 'practice_area';

    public function rules(): array
    {
        $slug = $this->uniqueSlug('practice_areas');
        $practice = $this->route('practice_area');
        $legacySlugs = [
            'corporate-commercial', 'mergers-acquisitions', 'banking-finance',
            'litigation-dispute-resolution', 'technology-intellectual-property', 'employment',
            'projects-infrastructure', 'maritime-logistics', 'restructuring-insolvency',
        ];
        if ($practice instanceof PracticeArea && in_array($practice->slug, $legacySlugs, true)) {
            $slug[] = Rule::in([$practice->slug]);
        }

        return array_merge($this->editorialRules(), [
            'slug' => $slug,
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'body' => ['nullable', 'string', 'max:100000'],
            'image_id' => $this->mediaRule(),
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
