<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    /**
     * Editable site content, grouped for the admin "Site content" screen.
     * Each field: [label, input type, help text].
     */
    public const SCHEMA = [
        'Brand' => [
            'site_name' => ['Site name', 'text', null],
            'site_tagline' => ['Tagline', 'text', 'Short line used in the footer and browser title.'],
            'logo' => ['Logo (optional)', 'image', 'Leave empty to use the wordmark.'],
        ],
        'Home hero' => [
            'hero_eyebrow' => ['Small line above the headline', 'text', null],
            'hero_title' => ['Headline', 'text', null],
            'hero_subtitle' => ['Intro paragraph', 'textarea', null],
            'hero_image' => ['Background image', 'image', 'Wide landscape photo, at least 2000px.'],
        ],
        'Home sections' => [
            'home_experiences_title' => ['Experiences heading', 'text', 'e.g. “Six ways to discover Pakistan”.'],
            'home_destinations_title' => ['Destinations heading', 'text', 'e.g. “From the Arabian Sea to the Karakoram”.'],
        ],
        'Home story' => [
            'story_title' => ['Story heading', 'text', null],
            'story_body' => ['Story paragraph', 'textarea', null],
            'story_image' => ['Story image', 'image', null],
            'stat_1' => ['Stat 1 (value|label)', 'text', 'e.g. 1,200+|guests hosted'],
            'stat_2' => ['Stat 2 (value|label)', 'text', null],
            'stat_3' => ['Stat 3 (value|label)', 'text', null],
        ],
        'About page' => [
            'about_title' => ['About heading', 'text', null],
            'about_body' => ['About text (blank line between paragraphs)', 'textarea', null],
            'about_image' => ['About image', 'image', null],
        ],
        'Contact' => [
            'contact_phone' => ['Phone (Pakistan)', 'text', null],
            'contact_phone_ca' => ['Phone (Canada)', 'text', null],
            'contact_whatsapp' => ['WhatsApp number (digits only, with country code)', 'text', null],
            'contact_email' => ['Email', 'text', null],
            'contact_address' => ['Office address', 'textarea', null],
            'office_hours' => ['Office hours', 'text', null],
        ],
        'Social' => [
            'social_instagram' => ['Instagram URL', 'text', null],
            'social_facebook' => ['Facebook URL', 'text', null],
            'social_youtube' => ['YouTube URL', 'text', null],
        ],
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings', fn () => static::query()->pluck('value', 'key')->all());

        return filled($all[$key] ?? null) ? $all[$key] : $default;
    }

    /** Read a value stored with putSecret(); returns null if it cannot be decrypted. */
    public static function secret(string $key): ?string
    {
        $value = static::get($key);
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    public static function putSecret(string $key, ?string $value): void
    {
        static::put([$key => blank($value) ? null : Crypt::encryptString($value)]);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('settings');
    }
}
