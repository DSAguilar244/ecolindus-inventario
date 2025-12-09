<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category_id', 'brand_id', 'unit', 'stock', 'min_stock', 'price', 'tax_rate'];

    protected $casts = [
        'price' => 'decimal:4',
        'tax_rate' => 'integer',
    ];

    // Returns price including tax (PVP). Uses product tax rate (not hardcoded).
    public function getPvpAttribute()
    {
        $tax = $this->tax_rate ?? 0;

        $val = ($this->price ?? 0) * (1 + ($tax / 100));

        // Keep math precision; presentation layer formats to 2 decimals where needed
        return round($val, 4);
    }

    // Relación: un producto puede tener muchos movimientos
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
