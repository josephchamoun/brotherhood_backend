<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}

