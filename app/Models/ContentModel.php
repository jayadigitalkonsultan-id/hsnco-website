<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class ContentModel extends DomainModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime', 'published_revision' => 'integer'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisionable');
    }

    public function getPublishedRevision(): ?ContentRevision
    {
        return $this->published_revision === null ? null : $this->revisions()
            ->where('version', $this->published_revision)
            ->where('status', ContentStatus::Published->value)
            ->first();
    }

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaUsage::class, 'usable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::deleting(function (ContentModel $model) {
            if ($model->isForceDeleting() && ($model->revisions()->exists() || $model->mediaUsages()->exists())) {
                throw new \LogicException('Archive content with revision or media history instead of force deleting it.');
            }
        });
    }
}
