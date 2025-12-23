<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    protected $keyType = 'int';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'role',
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'role' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
