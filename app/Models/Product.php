<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'category', 'unit', 'stock', 'min_stock'];

    // Relación: un producto puede tener muchos movimientos
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}