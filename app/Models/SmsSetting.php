<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'is_enabled',
        'user_id',
        'api_key',
        'sender_id',
        'default_country_code',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'provider' => 'notifylk',
            'is_enabled' => false,
        ]);
    }

    public function hasActiveCredentials(): bool
    {
        return $this->is_enabled
            && filled($this->user_id)
            && filled($this->api_key)
            && filled($this->sender_id);
    }
}
