<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $firstname
 * @property string|null $middlename
 * @property string $lastname
 * @property string $username
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $avatar
 * @property string|null $last_login
 * @property int $type 0 = standard user, 1 = admin
 * @property string $date_added Custom creation timestamp
 * @property string|null $date_updated Custom update timestamp
 * @property \Illuminate\Support\Carbon|null $created_at Original framework migration field
 * @property \Illuminate\Support\Carbon|null $updated_at Original framework migration field
 * 
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Enable automatic timestamps management.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Map Laravel's triggers to your existing custom database fields.
     */
    const UPDATED_AT = 'date_updated';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'username',
        'password',
        'avatar',
        'last_login',
        'type',
        'date_added',
        'date_updated',
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
            'last_login' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->type === \App\Enums\UserType::ADMIN;
    }
}
