<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'no_hp',
        'avatar',
        'avatar_url',
        'bio',
        'verification_status',
        'catatan_revisi',
        'is_active',
        'ktp_path',
        'selfie_path',
        'kepemilikan_path',
        'pengajuan_at',
        'auth_provider',
        'google_id',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'pengajuan_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function kosts(): HasMany
    {
        return $this->hasMany(Kost::class, 'owner_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteKosts(): BelongsToMany
    {
        return $this->belongsToMany(Kost::class, 'favorites');
    }

    public function chatsAsUser(): HasMany
    {
        return $this->hasMany(Chat::class, 'user_id');
    }

    public function chatsAsOwner(): HasMany
    {
        return $this->hasMany(Chat::class, 'owner_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function surveyRequests(): HasMany
    {
        return $this->hasMany(SurveyRequest::class, 'pencari_id');
    }

    public function incomingSurveys(): HasMany
    {
        return $this->hasMany(SurveyRequest::class, 'owner_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'admin_id');
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'developer'], true);
    }

    public function isPemilik(): bool
    {
        return in_array($this->role, ['pemilik', 'owner', 'pemilik_kost'], true);
    }

    public function isPencari(): bool
    {
        return in_array($this->role, ['pencari', 'user'], true);
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Provide seamless fallback for no_hp / phone.
     */
    protected function noHp(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->phone,
            set: fn ($value) => $value
        );
    }

    /**
     * Provide seamless fallback for avatar_url / avatar.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->avatar,
            set: fn ($value) => $value
        );
    }
}
