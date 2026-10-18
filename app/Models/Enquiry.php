<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enquiry extends DomainModel
{
    use SoftDeletes;

    protected $hidden = ['ip_address', 'user_agent'];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'read_at' => 'datetime',
            'closed_at' => 'datetime',
            'notification_sent_at' => 'datetime',
        ];
    }

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(EnquiryNote::class)->orderBy('created_at');
    }
}
