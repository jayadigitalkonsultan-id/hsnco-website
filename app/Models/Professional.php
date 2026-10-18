<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professional extends ContentModel
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'qualifications' => 'array',
            'languages' => 'array',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_leadership' => 'boolean',
        ]);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function practiceAreas(): BelongsToMany
    {
        return $this->belongsToMany(PracticeArea::class)->withTimestamps();
    }

    public function publishedPracticeAreas(): BelongsToMany
    {
        return $this->practiceAreas()->where('practice_areas.status', 'published')
            ->whereNotNull('practice_areas.published_at')->where('practice_areas.published_at', '<=', now());
    }

    public function publicPhoto(): BelongsTo
    {
        return $this->photo()->where('visibility', 'public');
    }

    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class, 'author_id');
    }
}
