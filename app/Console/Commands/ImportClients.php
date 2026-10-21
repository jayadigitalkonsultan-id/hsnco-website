<?php
namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportClients extends Command
{
    protected $signature = 'hsnco:import-clients {--publish : Publish the client copy supplied by the website owner}';
    protected $description = 'Import client profile records once without overwriting CMS edits';

    public function handle(): int
    {
        $items = json_decode(file_get_contents(database_path('seeders/data/clients.json')), true, 512, JSON_THROW_ON_ERROR);
        $count = DB::transaction(function () use ($items) {
            $count = 0;
            foreach ($items as $index => $item) {
                $slug = Str::slug($item['name']);
                if (Client::withTrashed()->where('slug', $slug)->exists()) continue;
                $logo = Media::where('disk', 'frontend')->where('path', 'images/clients/'.$slug.'.png')->where('visibility', 'public')->first();
                $published = (bool) $this->option('publish');
                $client = Client::create([
                    'name' => $item['name'], 'slug' => $slug, 'category' => $item['category'],
                    'summary' => app(\App\Services\Cms\EnglishCopy::class)->text($item['summary']), 'body' => app(\App\Services\Cms\EnglishCopy::class)->text($item['body']), 'image_id' => $logo?->id,
                    'source_note' => 'Materi klien diberikan pemilik website pada 20 Oktober 2026. Nama historis dipertahankan. Verifikasi penugasan dan pembaruan logo dikelola melalui CMS.',
                    'sort_order' => $index + 1, 'status' => $published ? 'published' : 'draft',
                    'published_at' => $published ? now() : null, 'published_revision' => $published ? 1 : null,
                ]);
                $client->revisions()->create(['version' => 1, 'status' => $client->status, 'payload' => $client->attributesToArray(), 'published_at' => $client->published_at]);
                $count++;
            }
            return $count;
        });
        $this->info($count.' klien ditambahkan; konten existing tetap dipertahankan.');
        return self::SUCCESS;
    }
}