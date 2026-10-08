<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SurveyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'kost_id',
        'pencari_id',
        'owner_id',
        'survey_date',
        'status', // PENDING, CONFIRMED, REJECTED, COMPLETED, CANCELLED
        'notes',
        'owner_notes',
    ];

    protected function casts(): array
    {
        return [
            'survey_date' => 'datetime',
        ];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    public function pencari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencari_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
