<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
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
        ];
    }

    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function addresses(): HasMany { return $this->hasMany(UserAddress::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }

    public function canBeManagedBy(User $actor): bool
    {
        if ($actor->hasRole('admin')) {
            return true;
        }

        if ($this->hasRole('admin')) {
            return false;
        }

        return $this->getAllPermissions()->pluck('name')
            ->diff($actor->getAllPermissions()->pluck('name'))
            ->isEmpty();
    }
}
