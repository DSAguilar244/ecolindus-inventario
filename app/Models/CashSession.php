<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'closing_amount',
        'total_invoiced',
        'total_cash',
        'total_transfer',
        'expected_closing',
        'reported_closing_amount',
        'difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'total_invoiced' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_transfer' => 'decimal:2',
        'expected_closing' => 'decimal:2',
        'reported_closing_amount' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }
}
