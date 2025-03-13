<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasFcmTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens,
        HasFactory,
        HasProfilePhoto,
        Notifiable,
        TwoFactorAuthenticatable,
        HasFcmTokens;

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function profilePhotoDisk()
    {
        return 's3';
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'profile_photo_path',
        'roles',
        'username',
        'is_active',
        'preferred_categories'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'preferred_categories' => 'array'
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Add debugging
        \Log::info('User attempting to access panel:', [
            'user_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
            'is_active' => $this->is_active,
            'panel_id' => $panel->getId()
        ]);

        if ($panel->getId() === 'admin') {
            \Log::info('Checking admin panel access');
            return $this->roles === 'ADMIN';
        }

        \Log::warning('Unknown panel access attempt', [
            'panel_id' => $panel->getId()
        ]);

        return false;
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'owner_id');
    }

    public function courier()
    {
        return $this->hasOne(Courier::class);
    }

    public function locations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }
}
