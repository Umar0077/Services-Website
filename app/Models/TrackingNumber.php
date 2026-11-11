<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TrackingNumber extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (TrackingNumber $number) {
            $number->number = Phone::digits($number->number);
            $number->code = strtolower(trim($number->code));
        });
        static::saved(fn () => Cache::forget('tracking-numbers'));
        static::deleted(fn () => Cache::forget('tracking-numbers'));
    }

    /** @return array<string, string> code => digits, active only */
    public static function activeMap(): array
    {
        return Cache::rememberForever('tracking-numbers', fn () => static::query()
            ->where('is_active', true)
            ->pluck('number', 'code')
            ->all());
    }
}
