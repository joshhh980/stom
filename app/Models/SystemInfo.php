<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemInfo extends Model
{
    protected $table = 'system_info';

    public $timestamps = false;

    protected $fillable = [
        'meta_field',
        'meta_value',
    ];

    public static function values(): array
    {
        return static::query()
            ->pluck('meta_value', 'meta_field')
            ->all();
    }

    public static function setValue(string $field, string $value): self
    {
        $setting = static::query()->firstOrNew(['meta_field' => $field]);
        $setting->meta_value = $value;
        $setting->save();

        return $setting;
    }
}
