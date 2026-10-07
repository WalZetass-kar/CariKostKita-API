<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'kost_id',
        'name',
        'room_type',
        'price',
        'size',
        'facilities',
        'total_rooms',
        'available_rooms',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'facilities' => 'array',
            'total_rooms' => 'integer',
            'available_rooms' => 'integer',
        ];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }
}
