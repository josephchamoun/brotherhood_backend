<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt; 


class DriveAccount extends Model
{
     use HasFactory;

    protected $fillable = [
        'email',
        'title',
        'password',
    ];

    // 🔓 Accessor: get plain password
    public function getPlainPasswordAttribute()
    {
        return Crypt::decryptString($this->password);
    }

    // 🔐 Mutator: auto-encrypt when setting password
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }
}
