<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = array(
        'nim', 'name', 'email', 'phone',
        'address', 'major', 'birth_date', 'status'
    );

    public $timestamps = true;
}