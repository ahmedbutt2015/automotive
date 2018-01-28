<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInput extends Model
{
    protected $table = 'USERINPUT';
    public $timestamps = false;
    protected $guarded = ['id'];
}
