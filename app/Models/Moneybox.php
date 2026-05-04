<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\MoneyTransaction;


class Moneybox extends Model
{
    use HasFactory;

    // Specify which fields can be mass-assigned
    protected $fillable = [
        'name',
        'amount',
        'section_id',
    ];

    // Define the relationship with Section
    public function section()
    {
        return $this->belongsTo(Section::class);
    }


    public function transactions()
{
    return $this->hasMany(MoneyTransaction::class);
}

}