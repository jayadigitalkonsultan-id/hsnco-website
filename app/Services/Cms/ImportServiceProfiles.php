<?php

namespace App\Services\Cms;

use App\Models\Media;
use App\Models\PracticeArea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportServiceProfiles
{
    /** Import approved source copy once. Existing records, drafts and deletions are preserved. */
    public function run(bool $publish = false): int
    {
        return DB::transaction(function () use ($publish) {
            $created = 0;
            foreach (config('services.groups', []) as $index => $group) {
                $slug = $group['slug'] ?? Str::slug($group['title']);
                if (PracticeArea::withTrashed()->where('slug', $slug)->exists()) {
                    continue;
                }
                $path = 'images/'.$group['image'];
                $file = public_path('assets/'.$path);
                if (!is_file($file)) {
                    throw new \RuntimeException('Image missing: '.$path);
                }
                $dimensions = getimagesize($file);
                $media = Media::withTrashed()->firstOrCreate(['disk' => 'frontend', 'path' => $path], [
                    'original_name' => $group['image'], 'mime_type' => 'image/jpeg',
                    'size' => filesize($file), 'width' => $dimensions[0], 'height' => $dimensions[1],
                    'alt_text' => 'Ilustrasi '.$group['title'], 'visibility' => 'public',
                ]);
                $practice = PracticeArea::create([
                    'slug' => $slug, 'name' => $group['title'], 'summary' => $group['caption'],
                    'body' => implode("\n", $group['items']), 'sort_order' => $index + 1,
                    'image_id' => $media->trashed() ? null : $media->id,
                    'status' => $publish ? 'published' : 'draft',
                    'published_at' => $publish ? now() : null,
                    'published_revision' => $publish ? 1 : null,
                ]);
                $revision = $practice->revisions()->create([
                    'version' => 1, 'status' => $publish ? 'published' : 'draft',
                    'payload' => $practice->attributesToArray(),
                    'review_note' => 'Impor awal materi layanan yang diberikan pemilik website.',
                    'published_at' => $publish ? now() : null,
                ]);
                if ($practice->image_id) {
                    $revision->mediaUsages()->create(['media_id' => $practice->image_id, 'field' => 'payload.image_id']);
                }
                $created++;
            }
            return $created;
        });
    }
}
