<?php

namespace App\Services\Admin;

use App\Enums\ContentStatus;
use App\Models\ContentModel;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ContentWorkflowService
{
    public function publish(ContentModel $content, User $actor): ContentModel
    {
        return DB::transaction(function () use ($content, $actor) {
            $content = $content->newQuery()->lockForUpdate()->findOrFail($content->getKey());
            Gate::forUser($actor)->authorize('publish', $content);

            $revision = $content->revisions()->orderByDesc('version')->lockForUpdate()->first();
            if (! $revision || $revision->status !== ContentStatus::Review) {
                throw ValidationException::withMessages([
                    'status' => 'Konten harus tersimpan dan diajukan untuk review sebelum dapat diterbitkan.',
                ]);
            }

            $publishedAt = now();
            $payload = $revision->payload;
            if (isset($payload['slug']) && $content->newQuery()->where('slug', $payload['slug'])->where($content->getKeyName(), '!=', $content->getKey())->exists()) {
                throw ValidationException::withMessages(['slug' => 'Slug sudah dipakai konten lain. Perbarui draft sebelum menerbitkannya.']);
            }
            if ($content instanceof Page && isset($payload['key']) && Page::query()->where('key', $payload['key'])->where('id', '!=', $content->getKey())->exists()) {
                throw ValidationException::withMessages(['key' => 'Kunci halaman sudah dipakai halaman lain.']);
            }
            $practiceIds = $payload['practice_area_ids'] ?? null;
            $keyContactIds = $payload['key_contact_ids'] ?? null;
            if ($content instanceof \App\Models\Industry && $keyContactIds !== null) {
                if (\App\Models\Professional::published()->whereIn('id', $keyContactIds)->lockForUpdate()->get()->count() !== count($keyContactIds)) {
                    throw ValidationException::withMessages(['key_contact_ids'=>'Salah satu Key Contact tidak lagi terbit. Perbarui pilihan sebelum publikasi.']);
                }
            }
            $sections = $payload['sections'] ?? null;
            $content->fill(Arr::except($payload, [
                'id', 'status', 'created_by', 'updated_by', 'published_at', 'published_revision', 'deleted_at',
                'practice_area_ids', 'key_contact_ids', 'sections', 'created_at', 'updated_at',
            ]));
            if ($content instanceof Professional && $practiceIds !== null) {
                $content->practiceAreas()->sync($practiceIds);
            }
            if ($content instanceof \App\Models\Industry && $keyContactIds !== null) $content->keyContacts()->sync($keyContactIds);
            if ($content instanceof Page && $sections !== null) {
                $this->syncPageSections($content, $sections);
            }
            $revision->forceFill([
                'status' => ContentStatus::Published,
                'payload' => array_replace($payload, ['status' => ContentStatus::Published->value]),
                'published_by' => $actor->id,
                'published_at' => $publishedAt,
                'reviewed_by' => $actor->id,
                'reviewed_at' => $publishedAt,
            ])->save();

            $content->forceFill([
                'status' => ContentStatus::Published,
                'published_at' => $publishedAt,
                'published_revision' => $revision->version,
                'updated_by' => $actor->id,
            ])->save();

            return $content;
        });
    }

    public function archive(ContentModel $content, User $actor): ContentModel
    {
        return DB::transaction(function () use ($content, $actor) {
            $content = $content->newQuery()->lockForUpdate()->findOrFail($content->getKey());
            Gate::forUser($actor)->authorize('archive', $content);

            $content->forceFill(['status' => ContentStatus::Archived, 'updated_by' => $actor->id])->save();

            return $content;
        });
    }

    private function syncPageSections(Page $page, array $sections): void
    {
        $retained = [];
        foreach ($sections as $attributes) {
            $id = $attributes['id'] ?? null;
            if (! $id) {
                continue;
            }
            $section = PageSection::query()->where('page_id', $page->id)->whereKey($id)->first();
            if (! $section) {
                $section = new PageSection;
                $section->setAttribute('id', $id);
                $section->page_id = $page->id;
            }
            $section->fill(Arr::only($attributes, ['key', 'type', 'heading', 'content', 'sort_order', 'is_visible']));
            $section->save();
            $retained[] = $section->id;
        }

        $removed = $page->sections()->whereNotIn('id', $retained)->get();
        foreach ($removed as $section) {
            $section->mediaUsages()->delete();
            $section->delete();
        }
    }
}
