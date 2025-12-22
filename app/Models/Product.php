<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category_id', 'brand_id', 'unit', 'stock', 'min_stock', 'price', 'tax'];

    protected $casts = [
        'price' => 'decimal:2',
        'tax' => 'integer',
    ];

    // Returns price including tax (PVP)
    public function getPvpAttribute()
    {
        $tax = $this->tax ?? 0;

        return round(($this->price ?? 0) * (1 + ($tax / 100)), 2);
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
