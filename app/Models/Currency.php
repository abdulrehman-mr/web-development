<?php

namespace App\Models;

use App\Support\CurrencyManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['code', 'name', 'symbol', 'symbol_position', 'decimals', 'rate', 'is_enabled', 'rate_updated_at'];

    protected function casts(): array
    {
        return [
            'rate' => 'float',
            'decimals' => 'integer',
            'is_enabled' => 'boolean',
            'rate_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * All currencies keyed by code. Cached as plain arrays: the cache store only
     * unserializes allow-listed classes, so models are rebuilt after reading.
     *
     * @return Collection<string, Currency>
     */
    public static function cached()
    {
        $rows = Cache::rememberForever('currencies', fn () => static::query()->orderBy('code')->get()
            ->map(fn (Currency $currency) => $currency->getAttributes())->all());

        return static::hydrate($rows)->keyBy('code');
    }

    public static function forgetCache(): void
    {
        Cache::forget('currencies');
        app(CurrencyManager::class)->flush();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /** Format an amount that is already in this currency, e.g. "CA$1,234" or "1.234 KWD". */
    public function format(float|int|string|null $amount, ?int $decimals = null): string
    {
        $number = number_format((float) $amount, $decimals ?? $this->decimals);

        return $this->symbol_position === 'after'
            ? $number.' '.$this->symbol
            : $this->symbol.(preg_match('/\p{L}$/u', $this->symbol) ? ' ' : '').$number;
    }

    public function getLabelAttribute(): string
    {
        return $this->code.' — '.$this->name;
    }
}
