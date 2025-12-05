<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_type',
        'identification',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
