<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Setting",
    description: "Setting model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "key", type: "string", example: "auto_approve_threshold"),
        new OA\Property(property: "value", type: "string", example: "90"),
        new OA\Property(property: "type", type: "string", example: "integer"),
        new OA\Property(property: "label", type: "string", example: "Auto Approval Threshold"),
    ]
)]
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'double' => (float) $setting->value,
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $label = null): void
    {
        if (is_bool($value)) {
            $type = 'boolean';
            $value = $value ? 'true' : 'false';
        } elseif (is_int($value)) {
            $type = 'integer';
        } elseif (is_float($value)) {
            $type = 'double';
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type, 'label' => $label ?? $key]
        );
    }
}
