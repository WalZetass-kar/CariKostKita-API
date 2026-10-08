<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'kost_id',
        'user_id',
        'survey_request_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function surveyRequest(): BelongsTo
    {
        return $this->belongsTo(SurveyRequest::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Review $review) {
            $kost = $review->kost;
            if ($kost) {
                $avg = $kost->reviews()->avg('rating') ?? 0;
                $count = $kost->reviews()->count();
                $kost->update([
                    'rating_avg' => round($avg, 2),
                    'rating_count' => $count,
                ]);
            }
        });

        static::deleted(function (Review $review) {
            $kost = $review->kost;
            if ($kost) {
                $avg = $kost->reviews()->avg('rating') ?? 0;
                $count = $kost->reviews()->count();
                $kost->update([
                    'rating_avg' => round($avg, 2),
                    'rating_count' => $count,
                ]);
            }
        });
    }
}
