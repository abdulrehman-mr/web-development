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
     * Editable site-wide content, grouped for the admin "Site content" screen.
     * Page content lives in page sections (Admin → Pages).
     * Each field: [label, input type, help text].
     */
    public const SCHEMA = [
        'Brand' => [
            'site_name' => ['Site name', 'text', null],
            'site_tagline' => ['Tagline', 'text', 'Short line used in the footer and browser title.'],
            'logo' => ['Logo (optional)', 'image', 'Leave empty to use the wordmark.'],
            'site_description' => ['Search-engine description', 'textarea', 'Used by Google and social shares when a page has no description of its own.'],
        ],
        'Header & menu' => [
            'menu_items' => ['Menu links', 'textarea', 'One per line as Label|link, e.g. “Journeys|/journeys”. Leave empty for the standard menu. Pages ticked “Show in menu” are added after these.'],
            'menu_experiences' => ['Experiences dropdown title', 'text', 'Leave empty to hide the dropdown.'],
            'header_button_label' => ['Header button text', 'text', 'e.g. “Plan my trip”. Leave empty to hide.'],
            'header_button_url' => ['Header button link', 'text', null],
        ],
        'Footer' => [
            'footer_about' => ['Text under the logo', 'textarea', null],
            'footer_links_title' => ['Links column title', 'text', null],
            'footer_contact_title' => ['Contact column title', 'text', null],
            'footer_note' => ['Small print', 'text', 'Shown at the very bottom, e.g. how prices are quoted.'],
            'whatsapp_label' => ['WhatsApp button text', 'text', null],
        ],
        'Journey pages' => [
            'journey_book_button' => ['Booking button text', 'text', null],
            'journey_book_note' => ['Note under the booking button', 'text', null],
            'journey_call_text' => ['Phone line beside the price', 'text', 'Shown before your second phone number. Leave empty to hide.'],
            'booking_default_country' => ['Guests usually travel from', 'text', 'Pre-selected country on the booking form, e.g. “Canada”.'],
        ],
        'Contact' => [
            'contact_phone' => ['Main phone', 'text', null],
            'label_phone_primary' => ['Main phone label', 'text', 'e.g. “Pakistan”.'],
            'contact_phone_ca' => ['Second phone', 'text', 'Shown in the header on large screens.'],
            'label_phone_secondary' => ['Second phone label', 'text', 'e.g. “Canada”.'],
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
