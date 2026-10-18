<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends ContentModel
{
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function publishedSections(): HasMany
    {
        return $this->sections()->where('is_visible', true)->whereHas('page', fn ($query) => $query->published());
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function publicOgMedia(): BelongsTo
    {
        return $this->ogMedia()->where('visibility', 'public');
    }
}
