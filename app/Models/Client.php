<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Client extends ContentModel
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), ['sort_order' => 'integer']);
    }

    public function publicImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id')->where('visibility', 'public');
    }

    public function getLogoUrlAttribute(): ?string
    {
        $media = $this->publicImage;
        if (!$media || !in_array($media->disk, ['frontend', 'public', 's3'], true)) return null;
        return $media->disk === 'frontend' ? asset('assets/' . ltrim($media->path, '/')) : Storage::disk($media->disk)->url($media->path);
    }
}
