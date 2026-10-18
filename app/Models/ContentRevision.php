<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentRevision extends DomainModel
{
    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'version' => 'integer',
            'payload' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaUsage::class, 'usable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::updating(function (ContentRevision $revision) {
            if ($revision->getRawOriginal('status') === 'published') {
                throw new \LogicException('Published snapshots are immutable; create another revision.');
            }
        });
        static::deleting(function (ContentRevision $revision) {
            if ($revision->status === ContentStatus::Published || $revision->mediaUsages()->exists()) {
                throw new \LogicException('Published or referenced revisions must be retained.');
            }
        });
    }
}
