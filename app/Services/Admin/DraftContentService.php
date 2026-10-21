<?php

namespace App\Services\Admin;

use App\Enums\ContentStatus;
use App\Models\ContentModel;
use App\Models\ContentRevision;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DraftContentService
{
    public function save(ContentModel $content, array $data, User $actor): ContentModel
    {
        return DB::transaction(function () use ($content, $data, $actor) {
            if ($content->exists) {
                $content = $content->newQuery()->lockForUpdate()->findOrFail($content->getKey());
                Gate::forUser($actor)->authorize('update', $content);
            } else {
                Gate::forUser($actor)->authorize('create', $content::class);
            }

            $practiceIds = $data['practice_area_ids'] ?? null;
            unset($data['practice_area_ids']);
            $keyContactIds = $data['key_contact_ids'] ?? null;
            unset($data['key_contact_ids']);
            foreach ($practiceIds ?? [] as $practiceId) {
                Gate::forUser($actor)->authorize('view', PracticeArea::findOrFail($practiceId));
            }
            if (! empty($data['author_id'])) {
                Gate::forUser($actor)->authorize('view', Professional::findOrFail($data['author_id']));
            }

            if ($content instanceof \App\Models\Industry && $keyContactIds !== null && \App\Models\Professional::published()->whereIn('id', $keyContactIds)->count() !== count($keyContactIds)) {
                throw ValidationException::withMessages(['key_contact_ids'=>'Key Contact harus berasal dari profil profesional yang sudah terbit.']);
            }
            $published = $content->exists && $content->status === ContentStatus::Published;
            $base = $content->exists ? ($this->workingPayload($content) ?? $this->snapshot($content)) : [];
            $payload = array_replace($base, $data);
            if ($practiceIds !== null) {
                $payload['practice_area_ids'] = array_values($practiceIds);
            }
            if ($content instanceof \App\Models\Industry && $keyContactIds !== null) $payload['key_contact_ids'] = $keyContactIds;
            $payload['status'] = $data['status'] ?? ($base['status'] ?? ContentStatus::Draft->value);

            if (! $published) {
                $recordData = Arr::except($payload, ['sections', 'practice_area_ids', 'key_contact_ids']);
                $content->fill($recordData);
                if (! $content->exists) {
                    $content->created_by = $actor->id;
                }
                $content->updated_by = $actor->id;
                $content->save();

                if ($content instanceof Professional && $practiceIds !== null) {
                    $content->practiceAreas()->sync($practiceIds);
                }
                if ($content instanceof \App\Models\Industry && $keyContactIds !== null) $content->keyContacts()->sync($keyContactIds);
                $payload = array_replace($this->snapshot($content), $payload);
                if ($content instanceof Professional) {
                    $payload['practice_area_ids'] = $practiceIds ?? $content->practiceAreas()->pluck('practice_areas.id')->all();
                }
            }
            if ($content instanceof Page && isset($payload['sections'])) {
                $payload['sections'] = $this->normalizePageSections($content, $payload['sections']);
            }

            $this->createRevision($content, $payload, ContentStatus::from($payload['status']), $actor);

            return $content;
        });
    }

    /** Save page section edits into a new draft snapshot without touching live sections. */
    public function savePageSection(Page $page, ?string $sectionId, array $data, User $actor, bool $delete = false): ContentRevision
    {
        return DB::transaction(function () use ($page, $sectionId, $data, $actor, $delete) {
            $page = Page::query()->lockForUpdate()->findOrFail($page->getKey());
            Gate::forUser($actor)->authorize('update', $page);

            $payload = $this->workingPayload($page) ?? $this->snapshot($page);
            $payload['sections'] = $this->normalizePageSections($page, $payload['sections'] ?? []);
            $sections = collect($payload['sections'] ?? [])->values();
            $index = $sectionId === null ? null : $sections->search(fn ($section) => ($section['id'] ?? null) === $sectionId);
            if ($sectionId !== null && $index === false) {
                throw ValidationException::withMessages(['section' => 'Bagian halaman tidak ditemukan pada versi draft ini.']);
            }

            if ($delete) {
                $sections->forget($index);
            } else {
                $section = array_merge($index === null ? [
                    'id' => (string) Str::ulid(), 'page_id' => $page->id,
                ] : $sections->get($index), Arr::except($data, ['content_json']));
                if ($index !== null && isset($sections->get($index)['content']['source_text']) && ! isset($section['content']['source_text'])) {
                    $section['content']['source_text'] = $sections->get($index)['content']['source_text'];
                }
                if ($index === null) {
                    $sections->push($section);
                } else {
                    $sections->put($index, $section);
                }
            }

            $sections = $sections->values();
            $keys = $sections->pluck('key');
            if ($keys->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['key' => 'Setiap kunci bagian harus unik dalam satu halaman.']);
            }
            $payload['sections'] = $sections->all();
            $status = $this->workingStatus($page) ?? ($page->status === ContentStatus::Published ? ContentStatus::Draft : $page->status);

            $payload['status'] = $status->value;

            return $this->createRevision($page, $payload, $status, $actor);
        });
    }

    public function workingRevision(ContentModel $content): ?ContentRevision
    {
        return $content->revisions()->whereIn('status', [ContentStatus::Draft->value, ContentStatus::Review->value])
            ->orderByDesc('version')->first();
    }

    public function workingStatus(ContentModel $content): ?ContentStatus
    {
        return $this->workingRevision($content)?->status;
    }

    public function workingPayload(ContentModel $content): ?array
    {
        return $this->workingRevision($content)?->payload;
    }

    public function hydrateWorkingCopy(ContentModel $content): ContentModel
    {
        $payload = $this->workingPayload($content);
        if ($payload === null) {
            return $content;
        }

        $attributes = Arr::except($payload, ['sections', 'practice_area_ids', 'key_contact_ids']);
        $content->fill($attributes);
        if ($content instanceof Page) {
            $sections = collect($this->normalizePageSections($content, $payload['sections'] ?? []))->map(function (array $section) {
                $model = new PageSection;
                $model->forceFill($section);
                $model->exists = true;

                return $model;
            });
            $content->setRelation('sections', $sections);
        }

        return $content;
    }

    public function previewRevision(ContentModel $content): ?ContentRevision
    {
        return $this->workingRevision($content)
            ?? $content->revisions()->where('status', ContentStatus::Published->value)->orderByDesc('version')->first();
    }

    private function snapshot(ContentModel $content): array
    {
        $payload = $content->attributesToArray();
        if ($content instanceof \App\Models\Industry) $payload['key_contact_ids'] = $content->keyContacts()->pluck('professionals.id')->all();
        if ($content instanceof Professional) {
            $payload['practice_area_ids'] = $content->practiceAreas()->pluck('practice_areas.id')->all();
        }
        if ($content instanceof Page) {
            $payload['sections'] = $content->sections()->get()->map(fn (PageSection $section) => $section->attributesToArray())->all();
        }

        return $payload;
    }

    private function normalizePageSections(Page $page, array $sections): array
    {
        $existing = $page->sections()->get()->keyBy('key');

        return collect($sections)->map(function (array $section) use ($existing, $page) {
            $stored = $existing->get($section['key'] ?? '');
            if ($stored) {
                $section = array_merge($stored->attributesToArray(), $section);
            }
            $section['page_id'] = $page->id;
            $section['id'] ??= (string) Str::ulid();

            return $section;
        })->values()->all();
    }

    private function createRevision(ContentModel $content, array $payload, ContentStatus $status, User $actor): ContentRevision
    {
        $version = ((int) $content->revisions()->max('version')) + 1;
        $revision = $content->revisions()->create([
            'version' => $version,
            'status' => $status,
            'payload' => $payload,
            'created_by' => $actor->id,
            'submitted_at' => $status === ContentStatus::Review ? now() : null,
        ]);
        $this->recordMediaUsages($revision, $payload);

        return $revision;
    }

    private function recordMediaUsages(ContentRevision $revision, array $payload, string $path = 'payload'): void
    {
        foreach ($payload as $key => $value) {
            $fieldPath = $path.'.'.$key;
            if (in_array($key, ['image_id', 'media_id', 'photo_id', 'cover_media_id', 'og_media_id'], true)
                && is_string($value) && $value !== '') {
                $revision->mediaUsages()->create(['media_id' => $value, 'field' => $fieldPath]);

                continue;
            }
            if (is_array($value)) {
                $this->recordMediaUsages($revision, $value, $fieldPath);
            }
        }
    }
}
