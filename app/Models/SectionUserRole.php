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
        'start_date',
    'end_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

