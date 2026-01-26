<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Event;
use App\Models\SectionUserRole;
use App\Models\Meeting;
use App\Models\Moneybox;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

public function users()
{
    return $this->belongsToMany(User::class, 'section_user_roles')
        ->withPivot('role_id')
        ->withTimestamps();
}

 public function events()
    {
        return $this->belongsToMany(Event::class, 'event_section');
    }

    public function userRoles()
    {
        return $this->hasMany(SectionUserRole::class);
    }
    public function meetings()
{
    return $this->hasMany(Meeting::class);
}

public function moneyboxes()
{
    return $this->hasMany(Moneybox::class);
}

public function elections()
{
return $this->hasMany(Election::class);
}


}

