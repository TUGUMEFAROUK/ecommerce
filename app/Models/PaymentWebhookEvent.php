<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Idempotency ledger for payment webhook deliveries. Full provider
 * payloads are deliberately not stored — they can carry masked card and
 * device metadata that has no business being persisted.
 */
class PaymentWebhookEvent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}
