<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'moneybox_id',
        'amount',
        'type',
        'source',
        'description',
        'event_id',
        'user_id',
    ];


        public function moneybox()
    {
        return $this->belongsTo(Moneybox::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
