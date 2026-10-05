<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $table = 'school_settings';

    protected $fillable = [
        'school_id',
        'academic_session_id',
        'key',
        'value',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    protected static $cache = [];

    /**
     * Get a school setting value.
     */
    public static function getSetting($schoolId, $sessionId, string $key, $default = null)
    {
        $cacheKey = "{$schoolId}_{$sessionId}_{$key}";
        
        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey] ?? $default;
        }

        $setting = static::where('school_id', $schoolId)
            ->where('academic_session_id', $sessionId)
            ->where('key', $key)
            ->first();

        self::$cache[$cacheKey] = $setting ? $setting->value : null;

        return self::$cache[$cacheKey] ?? $default;
    }

    public static function getCurrencySymbol($schoolId = null, $sessionId = null)
    {
        $schoolId = $schoolId ?? session('selected_school_id');
        $sessionId = $sessionId ?? session('selected_academic_session_id');

        if (!$sessionId) {
            $sessionId = \App\Models\AcademicSession::latest('id')->value('id');
        }

        $currency = self::getSetting($schoolId, $sessionId, 'currency', 'USD');
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
     * Set a school setting value.
     */
    public static function setSetting($schoolId, $sessionId, string $key, $value): void
    {
        static::updateOrCreate(
            ['school_id' => $schoolId, 'academic_session_id' => $sessionId, 'key' => $key],
            ['value' => $value]
        );
        
        $cacheKey = "{$schoolId}_{$sessionId}_{$key}";
        self::$cache[$cacheKey] = $value;
    }
}
