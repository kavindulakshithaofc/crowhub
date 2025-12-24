<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'recipient_name',
        'recipient_number',
        'message',
        'template',
        'status',
        'provider_response',
        'sent_at',
        'context_type',
        'context_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function context(): MorphTo
    {
        return $this->morphTo();
    }
}
