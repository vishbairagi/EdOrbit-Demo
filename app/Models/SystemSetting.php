<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static $cache = [];

    /**
     * Get a setting value by key.
     */
    public static function getSetting(string $key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }

        $setting = static::where('key', $key)->first();
        self::$cache[$key] = $setting ? $setting->value : null;

        return self::$cache[$key] ?? $default;
    }

    public static function getCurrencySymbol()
    {
        $currency = self::getSetting('currency', 'USD');
        $map = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'AUD' => '$',
            'CAD' => '$',
            'SGD' => '$',
            'JPY' => '¥',
            'NGN' => '₦',
            'ZAR' => 'R',
        ];
        return $map[$currency] ?? '$';
    }

    /**
     * Set a setting value by key.
     */
    public static function setSetting(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache[$key] = $value;
    }
}
