<?php

namespace App\Models;

use App\Services\MaintenanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'start_date',
        'monthly_fee',
        'billing_day',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'monthly_fee' => 'decimal:2',
            'billing_day' => 'int',
            'status' => 'string',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MaintenancePayment::class, 'contract_id');
    }

    public function statusInfo(): array
    {
        return app(MaintenanceStatus::class)->forContract($this);
    }
}
