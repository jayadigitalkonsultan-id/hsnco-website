<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeArea extends ContentModel
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
        ]);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class)->withTimestamps();
    }

    public function publishedProfessionals(): BelongsToMany
    {
        return $this->professionals()->where('professionals.status', 'published')
            ->whereNotNull('professionals.published_at')->where('professionals.published_at', '<=', now());
    }

    public function publicImage(): BelongsTo
    {
        return $this->image()->where('visibility', 'public');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }
}
