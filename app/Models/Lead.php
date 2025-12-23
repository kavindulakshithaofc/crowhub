<?php

namespace App\Models;

use App\Services\LeadSummary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'status',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'source' => 'string',
        ];
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function maintenanceContract(): HasOne
    {
        return $this->hasOne(MaintenanceContract::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function financialSummary(): array
    {
        return app(LeadSummary::class)->forLead($this);
    }
}
