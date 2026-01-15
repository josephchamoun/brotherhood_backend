<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
 protected $fillable = [
    'name',
    'email',
    'phone',
    'password',
    'created_by',
    'is_global_admin',
    'is_super_admin',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
public function sections()
{
    return $this->belongsToMany(
        Section::class,
        'section_user_roles'
    )
    ->withPivot('role_id', 'start_date', 'end_date')
    ->withTimestamps();
}


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function chabibaRoles()
    {
        return $this->hasMany(SectionUserRole::class)
            ->where('section_id', 1)
            ->orderBy('start_date', 'desc');
    }

    public function sectionRoles()
    {
        return $this->hasMany(SectionUserRole::class);
    }

    public function activeSectionRoles()
    {
        return $this->sectionRoles()->whereNull('end_date');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'section_user_roles')
            ->withPivot('section_id', 'start_date', 'end_date')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
{
    return $this->is_super_admin === true;
}

public function isGlobalAdmin(): bool
{
    return $this->is_global_admin === true || $this->isSuperAdmin();
}



}