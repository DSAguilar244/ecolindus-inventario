<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_EMITIDA = 'emitida';

    public const STATUS_ANULADA = 'anulada';

    public const STATUS_PENDIENTE = 'pendiente';

    protected $fillable = [
        'customer_id',
        'user_id',
        'invoice_number',
        'date',
        'subtotal',
        'tax_total',
        'total',
        'status',
        'cancelled_by',
        'cancelled_at',
        'notes',
        'payment_method',
    ];

    protected $dates = ['date'];

    protected $casts = [
        'date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_ANULADA);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payment()
    {
        return $this->hasOne(InvoicePayment::class);
    }

    public function calculateTotals()
    {
        $this->load('items');

        $subtotal = $this->items->sum(function ($i) {
            return $i->quantity * $i->unit_price;
        });

        $taxTotal = $this->items->sum(function ($i) {
            return ($i->tax_rate / 100) * ($i->quantity * $i->unit_price);
        });

        $this->subtotal = $subtotal;
        $this->tax_total = $taxTotal;
        $this->total = $subtotal + $taxTotal;

        return $this;
    }
}
