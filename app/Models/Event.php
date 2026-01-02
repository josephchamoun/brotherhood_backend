<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'type','event_date','total_spent','total_revenue'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
