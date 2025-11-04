<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'contact', 'email'];

    // Relación: un proveedor puede estar vinculado a muchos movimientos de inventario
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}