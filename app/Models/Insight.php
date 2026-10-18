<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insight extends ContentModel
{
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function publicCoverMedia(): BelongsTo
    {
        return $this->coverMedia()->where('visibility', 'public');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'author_id');
    }

    public function publishedAuthor(): BelongsTo
    {
        return $this->author()->where('professionals.status', 'published')
            ->whereNotNull('professionals.published_at')->where('professionals.published_at', '<=', now());
    }
}
