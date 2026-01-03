<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionUserRole extends Model
{
    protected $fillable = [
        'user_id',
        'section_id',
        'role_id',
    ];
}

