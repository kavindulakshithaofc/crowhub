<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'is_enabled',
        'label',
        'description',
        'default_body',
        'body',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function syncFromConfig(): void
    {
        $definitions = config('sms.templates', []);

        foreach ($definitions as $key => $definition) {
            $definition = static::normalizeDefinition($key, $definition);

            $template = static::query()->firstOrNew(['key' => $key]);
            $template->label = $definition['label'];
            $template->description = $definition['description'];
            $template->default_body = $definition['default'];
            $template->is_enabled = $template->is_enabled ?? true;

            if (! $template->exists || $template->body === null) {
                $template->body = $definition['default'];
            }

            $template->save();
        }
    }

    public function placeholders(): array
    {
        $definition = config("sms.templates.{$this->key}");

        if (! is_array($definition)) {
            return [];
        }

        return Arr::wrap($definition['placeholders'] ?? []);
    }

    public function resetToDefault(): void
    {
        $this->body = $this->default_body;
        $this->save();
    }

    public function content(): string
    {
        return $this->body ?? $this->default_body;
    }

    protected static function normalizeDefinition(string $key, string|array $definition): array
    {
        if (is_string($definition)) {
            return [
                'label' => Str::headline(str_replace('_', ' ', $key)),
                'description' => null,
                'default' => $definition,
            ];
        }

        return [
            'label' => $definition['label'] ?? Str::headline(str_replace('_', ' ', $key)),
            'description' => $definition['description'] ?? null,
            'default' => $definition['default'] ?? '',
        ];
    }
}
