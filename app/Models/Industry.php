<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Industry extends ContentModel
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), ['focus' => 'array', 'sort_order' => 'integer']);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function publicImage(): BelongsTo
    {
        return $this->image()->where('visibility', 'public');
    }

    public function keyContacts(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class, 'industry_professional')->withTimestamps()->orderBy('professionals.sort_order')->orderBy('professionals.name');
    }

    public function publishedKeyContacts(): BelongsToMany
    {
        return $this->keyContacts()->where('professionals.status', 'published')->whereNotNull('professionals.published_at')->where('professionals.published_at', '<=', now());
    }
}
