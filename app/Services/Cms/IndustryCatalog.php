<?php

namespace App\Services\Cms;

use App\Models\Industry;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class IndustryCatalog
{
    private function mediaUrl($media): ?string
    {
        if (! $media || ! in_array($media->disk, ['frontend', 'public', 's3'], true)) {
            return null;
        }

        return $media->disk === 'frontend' ? asset('assets/'.ltrim($media->path, '/')) : Storage::disk($media->disk)->url($media->path);
    }

    public function published()
    {
        if (! Schema::hasTable('industries')) {
            return collect();
        }

        return Industry::published()->with(['publicImage', 'publishedKeyContacts.publicPhoto', 'publishedKeyContacts.publishedPracticeAreas'])->orderBy('sort_order')->orderBy('name')->get()->map(function ($industry) {
            $contacts = $industry->publishedKeyContacts->map(function ($professional) {
                $professional->public_photo_url = $this->mediaUrl($professional->publicPhoto);

                return $professional;
            });
            $illustration = $industry->illustration_path;
            $fallback = is_string($illustration) && preg_match('~^industries/[a-z0-9-]+\.svg$~', $illustration) ? asset('assets/images/'.$illustration) : asset('assets/images/office.jpg');

            return ['name' => $industry->name, 'slug' => $industry->slug, 'english' => $industry->english_name, 'summary' => $industry->summary, 'overview' => $industry->body, 'focus' => $industry->focus ?? [], 'image_url' => $this->mediaUrl($industry->publicImage) ?: $fallback, 'key_contacts' => $contacts, 'seo_title' => $industry->seo_title, 'seo_description' => $industry->seo_description];
        });
    }
}
