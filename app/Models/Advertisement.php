<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type', // Banner, Pop-up, Promo Kost
        'image_url',
        'target_url',
        'kost_id',
        'start_date',
        'end_date',
        'is_active',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'impressions' => 'integer',
            'clicks' => 'integer',
        ];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(AdAnalytic::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    protected function ctr(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->impressions <= 0) {
                    return '0%';
                }
                $rate = ($this->clicks / $this->impressions) * 100;

                return round($rate, 2).'%';
            }
        );
    }
}
