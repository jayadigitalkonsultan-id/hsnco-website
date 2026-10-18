<?php

namespace App\Http\Requests\Cms;

use App\Models\Page;
use App\Services\Cms\SectionPayloadSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PageSectionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $content = $this->input('content', []);
        if (is_array($content)) {
            $this->merge(['content' => app(SectionPayloadSchema::class)->normalize((string) $this->input('type'), $content)]);
        } else {
            $this->merge(['content' => null]);
        }
    }

    public function authorize(): bool
    {
        $section = $this->route('section');
        $page = $this->route('page');

        return $page instanceof Page && Gate::allows('update', $page);
    }

    public function rules(): array
    {
        $section = $this->route('section');
        $pageId = $this->route('page')?->id;

        $rules = [
            'key' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)],
            'type' => ['required', Rule::in(config('cms.section_types'))],
            'heading' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_visible' => ['sometimes', 'boolean'],
            'page_id' => ['prohibited'],
        ];

        $rules = array_merge($rules, app(SectionPayloadSchema::class)->rules((string) $this->input('type')));

        if ($this->route('page')?->key === 'home') {
            $homeFields = [
                'hero' => ['heading', 'content.eyebrow', 'content.description', 'content.button_label', 'content.button_url', 'content.image_id', 'content.image_alt'],
                'introduction' => ['heading', 'content.eyebrow', 'content.body', 'content.subheading', 'content.button_label', 'content.button_url'],
                'office-image' => ['content.media_id', 'content.alt', 'content.caption'],
                'services-intro' => ['heading', 'content.description', 'content.button_label', 'content.button_url'],
                'featured-practices' => ['content.practice_area_ids'],
                'values' => ['heading', 'content.image_id', 'content.items'],
                'sectors' => ['heading', 'content.items'],
                'approach' => ['heading', 'content.items'],
                'recognitions' => ['heading', 'content.items'],
                'consultation' => ['heading', 'content.description'],
                'latest-insights' => ['heading', 'content.limit'],
            ];
            $homeTypes = [
                'hero' => 'hero', 'introduction' => 'rich_text', 'office-image' => 'image',
                'services-intro' => 'rich_text', 'featured-practices' => 'practice_selection', 'values' => 'values',
                'sectors' => 'sectors', 'approach' => 'steps', 'recognitions' => 'recognitions',
                'consultation' => 'enquiry', 'latest-insights' => 'latest_insights',
            ];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($homeFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$homeTypes[$key]])];
                foreach ($homeFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            }
        }

        if ($this->route('page')?->key === 'about') {
            $rules['key'][] = Rule::in(array_keys(config('about.sections')));
            $aboutFields = [
                'banner' => ['heading', 'content.eyebrow', 'content.description', 'content.breadcrumb_label'],
                'introduction' => ['heading', 'content.column_one', 'content.column_two'],
                'quote' => ['content.quote'],
                'history' => ['heading'],
                'vision-mission' => ['content.vision_title', 'content.vision', 'content.mission_title', 'content.mission'],
                'statistics' => ['heading'],
                'clients' => ['heading'],
                'leadership' => ['heading', 'content.professional_ids'],
                'closing-cta' => ['heading', 'content.button_label', 'content.button_url'],
            ];
            $aboutTypes = [
                'banner' => 'about_banner', 'introduction' => 'about_intro', 'quote' => 'quote',
                'history' => 'timeline', 'vision-mission' => 'vision_mission', 'statistics' => 'statistics',
                'clients' => 'client_logos', 'leadership' => 'leadership', 'closing-cta' => 'cta',
            ];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($aboutFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$aboutTypes[$key]])];
                foreach ($aboutFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            }
            if ($key === 'statistics' && isset($rules['content.items.*.verified_at'])) {
                $rules['content.items.*.verified_at'][] = 'before_or_equal:today';
            }
            if ($key === 'clients' && isset($rules['content.items.*.media_id'])) {
                $rules['content.items.*.media_id'] = [
                    'nullable', 'ulid', Rule::exists('media', 'id')->whereNull('deleted_at')->where('visibility', 'public')
                        ->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')),
                ];
            }
        }

        if ($this->route('page')?->key === 'practice-areas') {
            $practiceFields = [
                'banner' => ['heading', 'content.eyebrow', 'content.description'],
                'practice-directory' => ['heading', 'content.eyebrow', 'content.description'],
                'integrated-services' => ['heading', 'content.eyebrow', 'content.description'],
                'closing-cta' => ['heading', 'content.eyebrow', 'content.button_label', 'content.button_url'],
            ];
            $practiceTypes = [
                'banner' => 'banner', 'practice-directory' => 'practice_directory',
                'integrated-services' => 'rich_text', 'closing-cta' => 'cta',
            ];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($practiceFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$practiceTypes[$key]])];
                foreach ($practiceFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            } else {
                $rules['key'][] = 'prohibited';
            }
        }

        if ($this->route('page')?->key === 'team') {
            $teamFields = [
                'banner' => ['heading', 'content.eyebrow', 'content.description'],
                'professionals' => ['heading', 'content.eyebrow', 'content.description'],
                'closing-cta' => ['heading', 'content.eyebrow', 'content.button_label', 'content.button_url'],
            ];
            $teamTypes = [
                'banner' => 'banner', 'professionals' => 'professional_directory', 'closing-cta' => 'cta',
            ];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($teamFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$teamTypes[$key]])];
                foreach ($teamFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            } else {
                $rules['key'][] = 'prohibited';
            }
        }

        if ($this->route('page')?->key === 'insights') {
            $insightFields = [
                'banner' => ['heading', 'content.eyebrow', 'content.description'],
                'publications' => ['heading', 'content.eyebrow', 'content.description'],
                'closing-cta' => ['heading', 'content.eyebrow', 'content.button_label', 'content.button_url'],
            ];
            $insightTypes = [
                'banner' => 'banner', 'publications' => 'insight_directory', 'closing-cta' => 'cta',
            ];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($insightFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$insightTypes[$key]])];
                foreach ($insightFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            } else {
                $rules['key'][] = 'prohibited';
            }
        }

        if ($this->route('page')?->key === 'sitemap') {
            $sitemapFields = [
                'banner' => ['heading', 'content.eyebrow', 'content.description'],
                'directory' => ['heading', 'content.eyebrow', 'content.description'],
            ];
            $sitemapTypes = ['banner' => 'banner', 'directory' => 'sitemap'];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($sitemapFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$sitemapTypes[$key]])];
                foreach ($sitemapFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            }
        }

        if ($this->route('page')?->key === 'contact') {
            $contactFields = [
                'banner' => ['heading', 'content.eyebrow', 'content.description'],
                'enquiry' => ['heading', 'content.eyebrow', 'content.description'],
                'faq' => ['heading', 'content.eyebrow', 'content.items'],
            ];
            $contactTypes = ['banner' => 'banner', 'enquiry' => 'enquiry', 'faq' => 'faq'];
            $existing = $section ? $this->route('page')->sections()->whereKey($section)->first() : null;
            $key = $existing?->key ?? (string) $this->input('key');
            if (isset($contactFields[$key])) {
                $rules['key'] = ['required', 'string', 'max:100', 'alpha_dash', Rule::in([$key]), Rule::unique('page_sections', 'key')->where('page_id', $pageId)->ignore($section)];
                $rules['type'] = ['required', Rule::in([$contactTypes[$key]])];
                foreach ($contactFields[$key] as $field) {
                    if (isset($rules[$field])) {
                        $rules[$field] = array_values(array_filter($rules[$field], fn ($rule) => $rule !== 'nullable'));
                        array_unshift($rules[$field], 'required');
                    }
                }
            } else {
                $rules['key'][] = 'prohibited';
            }
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($this->route('page')?->key === 'home' && $this->input('key') === 'recognitions') {
                foreach ((array) $this->input('content.items', []) as $index => $item) {
                    if (! is_array($item) || ! filter_var($item['verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                        continue;
                    }
                    foreach (['title', 'rank', 'year', 'evidence'] as $field) {
                        if (trim((string) ($item[$field] ?? '')) === '') {
                            $validator->errors()->add("content.items.$index.$field", 'Wajib diisi sebelum klaim diverifikasi dan dipublikasikan.');
                        }
                    }
                }

                return;
            }

            if ($this->route('page')?->key !== 'about') {
                return;
            }
            $key = $this->input('key');
            if ($key === 'quote' && filter_var($this->input('content.approved'), FILTER_VALIDATE_BOOLEAN)) {
                foreach (['quote', 'attribution', 'evidence'] as $field) {
                    if (trim((string) $this->input('content.'.$field)) === '') {
                        $validator->errors()->add('content.'.$field, 'Wajib diisi untuk kutipan yang disetujui.');
                    }
                }
            }
            foreach ((array) $this->input('content.items', []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $verified = filter_var($item['verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $permitted = filter_var($item['permission_confirmed'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $required = match ($key) {
                    'history' => $verified ? ['year', 'title', 'description', 'evidence'] : [],
                    'statistics' => $verified ? ['value', 'title', 'evidence', 'verified_at'] : [],
                    'clients' => $permitted ? ['client_name', 'evidence'] : [],
                    default => [],
                };
                foreach ($required as $field) {
                    if (trim((string) ($item[$field] ?? '')) === '') {
                        $validator->errors()->add("content.items.$index.$field", 'Wajib diisi sebelum konten disetujui untuk publikasi.');
                    }
                }
            }
        });
    }
}
