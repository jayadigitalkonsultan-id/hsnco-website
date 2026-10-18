<?php

namespace App\Services\Cms;

use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Professional;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;

class AboutPageContent
{
    public function fromPayload(Page $page, array $payload): Page
    {
        $copy = clone $page;
        $attributes = $payload;
        unset($attributes['sections']);
        $copy->forceFill($attributes);
        $copy->setRelation('sections', collect($payload['sections'] ?? [])->map(function (array $attributes) {
            $section = new PageSection;
            $section->forceFill($attributes);

            return $section;
        }));

        return $copy;
    }

    /** Adapt legacy section schemas in memory; saving still creates an editorial revision. */
    public function prepare(Page $page): Page
    {
        $copy = clone $page;
        $copy->setRelation('sections', $page->sections->map(function (PageSection $section) {
            $attributes = $section->attributesToArray();
            $defaults = config('about.sections.'.$section->key);
            if (! $defaults) {
                return $section;
            }
            $content = $section->content ?? [];
            if ($section->key === 'introduction' && ! isset($content['column_one']) && filled($content['body'] ?? null)) {
                $content['column_one'] = $content['body'];
                $content['column_two'] ??= $content['description'] ?? '';
            }
            if ($section->key === 'quote' && ! isset($content['quote']) && filled($content['body'] ?? null)) {
                $content['quote'] = $content['body'];
            }
            // Older structured banners/intros stored their heading inside content.
            if (in_array($section->key, ['banner', 'introduction'], true) && filled($content['heading'] ?? null)) {
                $attributes['heading'] = $content['heading'];
            }
            $attributes['heading'] ??= $defaults['heading'];
            $attributes['type'] = $defaults['type'];
            $attributes['content'] = array_replace($defaults['content'], $content);
            $model = new PageSection;
            $model->forceFill($attributes);
            $model->exists = $section->exists;

            return $model;
        })->sortBy('sort_order')->values());

        return $copy;
    }

    public function viewData(?Page $page): array
    {
        $aboutPage = $page ? $this->prepare($page) : null;
        $sections = $aboutPage?->sections->keyBy('key') ?? collect();
        $items = static fn ($key) => collect(data_get($sections->get($key), 'content.items', []))->filter(fn ($item) => is_array($item));
        $complete = static fn (array $item, array $keys) => collect($keys)->every(fn ($key) => filled($item[$key] ?? null));
        $approved = static fn ($value) => in_array($value, [true, 1, '1'], true);

        $aboutMilestones = $items('history')->filter(fn ($item) => $approved($item['verified'] ?? false)
            && $complete($item, ['year', 'title', 'description', 'evidence']))->values();
        $aboutFigures = $items('statistics')->filter(function ($item) use ($complete, $approved) {
            if (! $approved($item['verified'] ?? false) || ! $complete($item, ['value', 'title', 'evidence', 'verified_at'])) {
                return false;
            }
            $date = is_string($item['verified_at']) ? DateTimeImmutable::createFromFormat('!Y-m-d', $item['verified_at']) : false;

            return $date && $date->format('Y-m-d') === $item['verified_at'] && $date <= today();
        })->values();
        $aboutClients = $items('clients')->filter(fn ($item) => $approved($item['permission_confirmed'] ?? false)
            && $complete($item, ['client_name', 'evidence']))->values();
        $mediaIds = $aboutClients->pluck('media_id')->filter()->unique()->values();
        $media = $mediaIds->isEmpty() ? collect() : Media::query()->whereIn('id', $mediaIds)
            ->where('visibility', 'public')->get()->keyBy('id');
        $aboutClients = $aboutClients->map(function ($item) use ($media) {
            $item['media_url'] = $this->mediaUrl($media->get($item['media_id'] ?? null));

            return $item;
        })->filter(fn ($item) => $item['media_url'] || filled($item['feedback'] ?? null) || filled($item['rating'] ?? null))->values();

        $selectedIds = data_get($sections->get('leadership'), 'content.professional_ids', []);
        $selectedIds = is_array($selectedIds) ? array_slice($selectedIds, 0, 1) : [];
        $professionals = $selectedIds === [] ? collect() : Professional::query()->published()
            ->whereIn('id', $selectedIds)->with('publicPhoto')->get()->keyBy('id');
        $aboutLeadership = collect($selectedIds)->map(function ($id) use ($professionals) {
            $professional = $professionals->get($id);
            if (! $professional) {
                return null;
            }

            return [
                'name' => $professional->name, 'position' => $professional->position,
                'summary' => $professional->summary, 'biography' => $professional->biography,
                'photo_url' => $this->mediaUrl($professional->publicPhoto),
                'photo_alt' => $professional->publicPhoto?->alt_text ?: $professional->name,
            ];
        })->filter()->values();

        return compact('aboutPage', 'aboutMilestones', 'aboutFigures', 'aboutClients', 'aboutLeadership')
            + ['aboutPageIsPublished' => $aboutPage !== null];
    }

    private function mediaUrl(?Media $media): ?string
    {
        if (! $media || $media->visibility !== 'public' || ! str_starts_with($media->mime_type, 'image/')
            || ! in_array($media->disk, ['frontend', 'public', 's3'], true)) {
            return null;
        }

        return $media->disk === 'frontend' ? asset('assets/'.ltrim($media->path, '/')) : Storage::disk($media->disk)->url($media->path);
    }
}
