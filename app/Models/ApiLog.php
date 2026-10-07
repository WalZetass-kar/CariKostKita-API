<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'method',
        'path',
        'status',
        'duration_ms',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'float',
            'status' => 'integer',
        ];
    }
}
