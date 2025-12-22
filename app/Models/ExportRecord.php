<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportRecord extends Model
{
    protected $table = 'exports';
    protected $fillable = ['token', 'user_id', 'type', 'path', 'status', 'meta', 'finished_at'];
    protected $casts = [
        'meta' => 'array',
        'finished_at' => 'datetime',
    ];
}
