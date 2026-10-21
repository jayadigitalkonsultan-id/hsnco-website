<?php

namespace App\Console\Commands;

use App\Models\Industry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportIndustries extends Command
{
    protected $signature = 'hsnco:import-industries {--publish : Terbitkan sebelas sektor awal} {--dry-run : Periksa tanpa menyimpan}';

    protected $description = 'Impor data industri awal tanpa menimpa edit CMS atau memilih Key Contact secara otomatis';

    public function handle(): int
    {
        $count = DB::transaction(function () {
            $count = 0;
            foreach (config('industries.items', []) as $index => $item) {
                if (Industry::withTrashed()->where('slug', $item['slug'])->exists()) {
                    continue;
                }
                $count++;
                if ($this->option('dry-run')) {
                    continue;
                }
                $published = (bool) $this->option('publish');
                $industry = Industry::create(['name' => $item['name'], 'slug' => $item['slug'], 'english_name' => $item['english'], 'summary' => $item['summary'], 'body' => $item['overview'], 'focus' => $item['focus'], 'illustration_path' => $item['image'], 'sort_order' => $index + 1, 'status' => $published ? 'published' : 'draft', 'published_at' => $published ? now() : null, 'published_revision' => $published ? 1 : null]);
                $industry->revisions()->create(['version' => 1, 'status' => $industry->status, 'payload' => array_merge($industry->attributesToArray(), ['key_contact_ids' => []]), 'published_at' => $industry->published_at]);
            }

            return $count;
        });
        $this->info($count.' industri '.($this->option('dry-run') ? 'akan ditambahkan' : 'ditambahkan').'. Data dan Key Contact existing dipertahankan.');

        return self::SUCCESS;
    }
}
