<?php

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Models\Client;
use App\Models\Industry;
use App\Models\Insight;
use App\Models\Page;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\SiteSetting;
use App\Services\Cms\EnglishCopy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TranslateFrontendEnglish extends Command
{
    protected $signature = 'hsnco:translate-frontend-en {--apply : Apply reviewed English copy; otherwise only inspect} {--force : Allow explicitly requested production changes}';

    protected $description = 'Apply reviewed English frontend copy while retaining IDs, URLs, roles, statuses and revision history';

    public function handle(EnglishCopy $copy): int
    {
        if ($this->option('apply') && app()->isProduction() && ! $this->option('force')) {
            $this->error('Production requires --apply --force after reviewing the dry-run and backing up the database.');

            return self::FAILURE;
        }
        $models = [Page::class => ['title', 'seo_title', 'seo_description'], PracticeArea::class => ['name', 'summary', 'body', 'seo_title', 'seo_description'], Industry::class => ['name', 'english_name', 'summary', 'body', 'focus', 'seo_title', 'seo_description'], Professional::class => ['position', 'summary', 'biography', 'qualifications', 'languages'], Insight::class => ['title', 'excerpt', 'body', 'seo_title', 'seo_description'], Client::class => ['summary', 'body']];
        $plan = [];
        foreach ($models as $class => $fields) {
            foreach ($class::query()->get() as $record) {
                $before = $record->only($fields);
                $after = $copy->structured($before);
                $sections = $record instanceof Page ? $record->sections()->get()->map(fn ($section) => ['id' => $section->id, 'before' => $section->only(['heading', 'content']), 'after' => $copy->structured($section->only(['heading', 'content']))])->filter(fn ($section) => $section['before'] !== $section['after'])->all() : [];
                $pending = $record->revisions()->whereIn('status', ['draft', 'review'])->orderByDesc('version')->first();
                $pendingAfter = $pending ? $copy->structured($pending->payload) : null;
                // Proper names of people and organisations are never translated.
                if ($pending && ($record instanceof Professional || $record instanceof Client)) {
                    $pendingAfter['name'] = $pending->payload['name'] ?? $record->name;
                }
                if ($before !== $after || $sections || ($pending && $pendingAfter !== $pending->payload)) {
                    $version = (int) $record->revisions()->max('version');
                    $plan[] = compact('class', 'record', 'before', 'after', 'sections', 'pending', 'pendingAfter', 'version');
                }
            }
        }
        $settings = SiteSetting::whereIn('key', ['navigation.main', 'seo.defaults', 'footer.content'])->get()->map(fn ($record) => ['record' => $record, 'before' => $record->value, 'after' => $copy->structured($record->value)])->filter(fn ($item) => $item['before'] !== $item['after']);
        $this->line(count($plan).' content records and '.$settings->count().' settings require English copy updates.');
        if (! $this->option('apply')) {
            $this->info('Dry run only. No database changes. Run with --apply to update reviewed copy.');

            return self::SUCCESS;
        }
        if (! $plan && $settings->isEmpty()) {
            $this->info('English copy is already up to date.');

            return self::SUCCESS;
        }
        $backup = ['created_at' => now()->toIso8601String(), 'content' => array_map(fn ($item) => ['class' => $item['class'], 'id' => $item['record']->id, 'attributes' => $item['before'], 'sections' => $item['sections'], 'pending' => $item['pending']?->attributesToArray()], $plan), 'settings' => $settings->map(fn ($item) => ['key' => $item['record']->key, 'value' => $item['before']])->values()->all()];
        $path = 'content-backups/english-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json';
        if (! Storage::disk('local')->put($path, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))) {
            throw new RuntimeException('Backup could not be written. No content updated.');
        }
        DB::transaction(function () use ($plan, $settings) {
            foreach ($plan as $item) {
                $record = $item['class']::query()->lockForUpdate()->findOrFail($item['record']->id);
                if ($record->only(array_keys($item['before'])) !== $item['before'] || (int) $record->revisions()->max('version') !== $item['version']) {
                    throw new RuntimeException('Content changed during inspection. Retry the command.');
                }
                $record->fill($item['after'])->save();
                foreach ($item['sections'] as $section) {
                    $row = $record->sections()->lockForUpdate()->findOrFail($section['id']);
                    if ($row->only(['heading', 'content']) !== $section['before']) {
                        throw new RuntimeException('Page section changed during inspection.');
                    }
                    $row->fill($section['after'])->save();
                }
                $snapshot = $record->attributesToArray();
                if ($record instanceof Page) {
                    $snapshot['sections'] = $record->sections()->get()->map(fn ($section) => $section->attributesToArray())->all();
                }
                if ($record instanceof Professional) {
                    $snapshot['practice_area_ids'] = $record->practiceAreas()->pluck('practice_areas.id')->all();
                }
                if ($record instanceof Industry) {
                    $snapshot['key_contact_ids'] = $record->keyContacts()->pluck('professionals.id')->all();
                }
                $version = (int) $record->revisions()->max('version') + 1;
                if ($record->status === ContentStatus::Published) {
                    $snapshot['published_revision'] = $version;
                    $record->revisions()->create(['version' => $version, 'status' => 'published', 'payload' => $snapshot, 'published_at' => $record->published_at, 'review_note' => 'Reviewed English copy; previous revisions retained.']);
                    $record->update(['published_revision' => $version]);
                    $version++;
                }
                if ($item['pending']) {
                    $record->revisions()->create(['version' => $version, 'status' => $item['pending']->status, 'payload' => $item['pendingAfter'], 'submitted_at' => $item['pending']->submitted_at, 'review_note' => 'English copy of existing working revision; editorial status retained.']);
                } elseif ($record->status !== ContentStatus::Published) {
                    $record->revisions()->create(['version' => $version, 'status' => $record->status, 'payload' => $snapshot, 'review_note' => 'Reviewed English copy.']);
                }
            }
            foreach ($settings as $item) {
                $record = SiteSetting::whereKey($item['record']->getKey())->lockForUpdate()->firstOrFail();
                if ($record->value !== $item['before']) {
                    throw new RuntimeException('Settings changed during inspection.');
                }
                $record->update(['value' => $item['after']]);
            }
        });
        $this->info('English frontend content updated. Backup: storage/app/private/'.$path);

        return self::SUCCESS;
    }
}
