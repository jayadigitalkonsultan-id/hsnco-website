<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends DomainModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    protected $table = 'media';

    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            if ($media->usages()->exists()
                || Page::withTrashed()->where('og_media_id', $media->id)->exists()
                || PracticeArea::withTrashed()->where('image_id', $media->id)->exists()
                || Professional::withTrashed()->where('photo_id', $media->id)->exists()
                || Insight::withTrashed()->where('cover_media_id', $media->id)->exists()
                || $media->isReferencedByPublishedPageSection()
                || $media->isReferencedByPublishedRevision()) {
                throw new \LogicException('Referenced media must be detached before deletion.');
            }
        });
    }

    private function isReferencedByPublishedPageSection(): bool
    {
        foreach (PageSection::query()->whereHas('page', fn ($query) => $query->published())->cursor() as $section) {
            if ($this->containsMediaId($section->content, (string) $this->getKey())) {
                return true;
            }
        }

        return false;
    }

    private function isReferencedByPublishedRevision(): bool
    {
        foreach (ContentRevision::query()->where('status', ContentStatus::Published->value)->cursor() as $revision) {
            if ($this->containsMediaId($revision->payload, (string) $this->getKey())) {
                return true;
            }
        }

        return false;
    }

    private function containsMediaId(mixed $value, string $mediaId): bool
    {
        if ($value === $mediaId) {
            return true;
        }
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $nested) {
            if ($this->containsMediaId($nested, $mediaId)) {
                return true;
            }
        }

        return false;
    }
}
