<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnquiryNote extends DomainModel
{
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
