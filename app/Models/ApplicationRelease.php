<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_name',
        'version_code',
        'apk_url',
        'file_size',
        'min_android',
        'is_force_update',
        'is_latest',
        'changelog',
        'release_date',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'version_code' => 'integer',
            'is_force_update' => 'boolean',
            'is_latest' => 'boolean',
            'release_date' => 'date',
            'download_count' => 'integer',
        ];
    }

    public static function getLatest(): ?self
    {
        return static::where('is_latest', true)->latest('version_code')->first()
            ?? static::latest('version_code')->first();
    }
}
