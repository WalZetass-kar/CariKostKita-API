<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kost extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'address',
        'province',
        'city',
        'district',
        'village',
        'latitude',
        'longitude',
        'price_min',
        'price_max',
        'gender_type',
        'facilities',
        'status',
        'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(KostImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(KostImage::class)->where('is_primary', true);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }
}
