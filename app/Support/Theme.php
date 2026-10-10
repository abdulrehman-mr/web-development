<?php

namespace App\Support;

/**
 * Site colour schemes. Tailwind's brand-* and lake-* utilities read CSS
 * variables, so a scheme only has to redefine those variables; every shade is
 * derived from one main colour with color-mix().
 */
class Theme
{
    /**
     * @var array<string, array{label: string, brand: string, accent: string}>
     */
    public const PRESETS = [
        'pakistan' => ['label' => 'Pakistan Green', 'brand' => '#097045', 'accent' => '#06aecb'],
        'indus' => ['label' => 'Indus Blue', 'brand' => '#1d4ed8', 'accent' => '#38bdf8'],
        'karakoram' => ['label' => 'Karakoram Teal', 'brand' => '#0f766e', 'accent' => '#5eead4'],
        'truck-art' => ['label' => 'Truck Art Red', 'brand' => '#be123c', 'accent' => '#facc15'],
        'royal' => ['label' => 'Royal Violet', 'brand' => '#6d28d9', 'accent' => '#f0abfc'],
        'midnight' => ['label' => 'Midnight Slate', 'brand' => '#1e293b', 'accent' => '#22d3ee'],
    ];

    /** Percentage of the main colour mixed with white (positive) or black (negative) per shade. */
    private const BRAND_SHADES = [50 => 6, 100 => 12, 200 => 25, 300 => 42, 400 => 62, 500 => 80, 600 => 90, 700 => 100, 800 => -82, 900 => -64, 950 => -42];

    private const ACCENT_SHADES = [50 => 8, 100 => 18, 200 => 34, 300 => 55, 400 => 78, 500 => 100, 600 => -86, 700 => -72, 800 => -60, 900 => -50];

    public static function isHex(?string $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /** @return array{brand: string, accent: string} */
    public static function colors(): array
    {
        $preset = setting('theme_preset', 'pakistan');

        if ($preset === 'custom' && self::isHex(setting('theme_brand')) && self::isHex(setting('theme_accent'))) {
            return ['brand' => setting('theme_brand'), 'accent' => setting('theme_accent')];
        }

        $chosen = self::PRESETS[$preset] ?? self::PRESETS['pakistan'];

        return ['brand' => $chosen['brand'], 'accent' => $chosen['accent']];
    }

    /** CSS variable overrides for the active scheme, or an empty string for the built-in default. */
    public static function css(): string
    {
        if (setting('theme_preset', 'pakistan') === 'pakistan') {
            return '';
        }

        ['brand' => $brand, 'accent' => $accent] = self::colors();

        return ':root{'.self::scale('brand', $brand, self::BRAND_SHADES).self::scale('lake', $accent, self::ACCENT_SHADES).'}';
    }

    /** @param array<int, int> $shades */
    private static function scale(string $name, string $hex, array $shades): string
    {
        $css = '';
        foreach ($shades as $shade => $mix) {
            $value = match (true) {
                $mix === 100 => $hex,
                $mix > 0 => "color-mix(in oklab, {$hex} {$mix}%, white)",
                default => "color-mix(in oklab, {$hex} ".abs($mix).'%, black)',
            };
            $css .= "--color-{$name}-{$shade}:{$value};";
        }

        return $css;
    }
}
