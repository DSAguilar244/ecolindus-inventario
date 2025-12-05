<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_rate' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function setLineTotalAttribute($value)
    {
        $this->attributes['line_total'] = $value ?? ($this->quantity * $this->unit_price);
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            if (is_null($item->line_total)) {
                $item->line_total = $item->quantity * $item->unit_price;
            }
        });
    }
}
