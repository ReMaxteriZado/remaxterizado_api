<?php

namespace App;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Name extends Model
{
    use HasFactory;

    // fillable
    protected $fillable = [
        'name',
        'lastname',
        'link_id',
    ];
}
