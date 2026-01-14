<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title','description','event_date','type',
        'total_spent','total_revenue','notes','drive_link','created_by'
    ];

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'event_section');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

