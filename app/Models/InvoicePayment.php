<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'cash_amount',
        'transfer_amount',
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTotalAmount()
    {
        return (float) $this->cash_amount + (float) $this->transfer_amount;
    }
}
