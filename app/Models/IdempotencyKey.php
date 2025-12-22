<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $table = 'idempotency_keys';
    protected $fillable = ['key', 'cash_session_id'];
    public $timestamps = true;

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }
}
