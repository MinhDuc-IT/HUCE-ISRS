<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Scopes\NotDeletedScope;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ScopedBy([NotDeletedScope::class])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Vai trò hợp lệ */
    public const ROLE_ADMIN     = 'admin';
    public const ROLE_BO_MON    = 'bo_mon';
    public const ROLE_SINH_VIEN = 'sinh_vien';

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
        'student_code',
        'department_id',
        'is_deleted',
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
            'password'          => 'hashed',
            'department_id'     => 'integer',
            'is_deleted'        => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Role helpers
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBoMon(): bool
    {
        return $this->role === self::ROLE_BO_MON;
    }

    public function isSinhVien(): bool
    {
        return $this->role === self::ROLE_SINH_VIEN;
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function remedialRegistrations()
    {
        return $this->hasMany(RemedialRegistration::class, 'student_id');
    }
}
