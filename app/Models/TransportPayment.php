<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportPayment extends Model
{
    protected $fillable = [
        'transport_invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    /** @return BelongsTo<TransportInvoice, self> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TransportInvoice::class, 'transport_invoice_id');
    }
}
