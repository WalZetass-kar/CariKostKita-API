<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KostImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'kost_id',
        'file_path',
        'url',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }
}
