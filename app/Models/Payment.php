<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Payment extends Model
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const FAILED = 'failed';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'booking_id', 'reference', 'gateway', 'gateway_reference', 'type', 'amount', 'currency',
        'base_amount', 'status', 'failure_reason', 'payload', 'paid_at',
    ];

    protected $attributes = ['status' => self::PENDING];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Short enough for gateways with tight invoice-number limits (Authorize.net: 20 chars).
        static::creating(fn (Payment $payment) => $payment->reference ??= 'PAY-'.strtoupper(Str::random(12)));
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /** Record a successful payment. Safe to call more than once (return page and webhook can both arrive). */
    public function markPaid(?string $gatewayReference = null, array $payload = []): void
    {
        DB::transaction(function () use ($gatewayReference, $payload) {
            $fresh = static::lockForUpdate()->find($this->id);
            if ($fresh->status === self::PAID) {
                return;
            }

            $fresh->update([
                'status' => self::PAID,
                'gateway_reference' => $gatewayReference ?? $fresh->gateway_reference,
                'payload' => array_merge($fresh->payload ?? [], $payload),
                'failure_reason' => null,
                'paid_at' => now(),
            ]);
            $fresh->booking->refreshPaymentStatus();
            $this->setRawAttributes($fresh->getAttributes(), true);
        });
    }

    public function markFailed(string $reason, string $status = self::FAILED): void
    {
        if ($this->status === self::PENDING) {
            $this->update(['status' => $status, 'failure_reason' => mb_substr($reason, 0, 500)]);
        }
    }

    public function formattedAmount(): string
    {
        return format_money($this->amount, $this->currency);
    }
}
