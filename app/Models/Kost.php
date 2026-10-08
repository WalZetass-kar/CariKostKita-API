<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'nama_kost',
        'description',
        'address',
        'patokan',
        'no_whatsapp',
        'province',
        'city',
        'district',
        'village',
        'latitude',
        'longitude',
        'price_min',
        'price_max',
        'deposit',
        'gender_type',
        'facilities',
        'fasilitas',
        'aturan',
        'ukuran_kamar',
        'total_kamar',
        'kamar_tersedia',
        'thumbnail_url',
        'image_urls',
        'rating_avg',
        'rating_count',
        'views_count',
        'status',
        'verification_status',
        'location_verification',
        'catatan_revisi',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'fasilitas' => 'array',
            'image_urls' => 'array',
            'aturan' => 'array',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'deposit' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'total_kamar' => 'integer',
            'kamar_tersedia' => 'integer',
            'rating_count' => 'integer',
            'views_count' => 'integer',
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

    public function surveyRequests(): HasMany
    {
        return $this->hasMany(SurveyRequest::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('verification_status', ['approved', 'Aktif']);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['available', 'Aktif']);
    }

    protected function namaKost(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->name,
            set: fn ($value) => $value
        );
    }

    protected function fasilitas(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->facilities ?? [],
            set: fn ($value) => is_array($value) ? json_encode($value) : $value
        );
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value) {
                    return $value;
                }
                if ($this->relationLoaded('primaryImage') && $this->primaryImage) {
                    return $this->primaryImage->file_path;
                }
                if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
                    return $this->images->first()->file_path;
                }

                return null;
            },
            set: fn ($value) => $value
        );
    }
}
