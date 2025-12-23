<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'for_month',
        'amount',
        'paid_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'for_month' => 'date',
            'paid_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(MaintenanceContract::class);
    }
}
