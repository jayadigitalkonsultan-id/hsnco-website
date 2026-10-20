<?php
namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportClientLogos extends Command
{
    protected $signature = 'hsnco:import-client-logos';
    protected $description = 'Register sourced local logos and attach them only to clients without a logo';

    public function handle(): int
    {
        $items = json_decode(file_get_contents(database_path('seeders/data/client-logos.json')), true, 512, JSON_THROW_ON_ERROR);
        $count = DB::transaction(function () use ($items) {
            $count = 0;
            foreach ($items as $item) {
                $file = public_path('assets/'.$item['file']);
                if (!is_file($file)) throw new \RuntimeException('Missing logo: '.$item['file']);
                $dimensions = getimagesize($file);
                $media = Media::withTrashed()->firstOrCreate(['disk' => 'frontend', 'path' => $item['file']], [
                    'original_name' => 'Logo '.$item['name'].'.png', 'mime_type' => 'image/png',
                    'size' => filesize($file), 'width' => $dimensions[0], 'height' => $dimensions[1],
                    'alt_text' => 'Logo '.$item['name'], 'visibility' => 'public',
                    'caption' => $item['name'] === 'Bank CIMB Niaga' ? 'dark-surface' : null,
                ]);
                if (!$media->trashed()) {
                    $media->forceFill(['size' => filesize($file), 'width' => $dimensions[0], 'height' => $dimensions[1]])->save();
                }
                $client = Client::where('slug', Str::slug($item['name']))->lockForUpdate()->first();
                if (!$client || $client->image_id || $media->trashed()) continue;
                $client->image_id = $media->id;
                $version = (int) $client->revisions()->max('version') + 1;
                if ($client->status === \App\Enums\ContentStatus::Published) $client->published_revision = $version;
                $client->save();
                $revision = $client->revisions()->create([
                    'version' => $version, 'status' => $client->status, 'payload' => $client->attributesToArray(),
                    'review_note' => 'Logo awal diambil dari '.$item['source'], 'published_at' => $client->published_at,
                ]);
                $revision->mediaUsages()->create(['media_id' => $media->id, 'field' => 'payload.image_id']);
                $count++;
            }
            return $count;
        });
        $this->info($count.' logo dipasangkan; pilihan logo existing tidak ditimpa.');
        return self::SUCCESS;
    }
}