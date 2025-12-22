<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Invoice;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'invoice_id', 'action', 'before', 'after', 'reason'
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
