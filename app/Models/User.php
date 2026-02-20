<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
        'role',
        'created_by',
        'avatar'
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
        ];
    }
    public function createdUsers()
{
    return $this->hasMany(User::class, 'created_by');
}

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function clientProfile()
{
    return $this->hasOne(ClientProfile::class);
}

// contracts where this user is the client
public function contracts()
{
    return $this->hasMany(Contract::class, 'client_user_id');
}

// projects managed by this user (project manager)
public function managedProjects()
{
    return $this->hasMany(Project::class, 'manager_user_id');
}

// audit logs performed by this user
public function auditLogs()
{
    return $this->hasMany(AuditLog::class);
}
public function isOwner(): bool { return $this->role === 'owner'; }
public function isAdmin(): bool { return $this->role === 'admin'; }
public function isClient(): bool { return $this->role === 'client'; }

}