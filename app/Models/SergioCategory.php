<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SergioCategory extends Model
{
    use HasFactory;

    protected $table = "sergio_categories";

    protected $fillable = [
        'name',
    ];

    public function books()
    {
        return $this->hasMany(SergioBook::class, 'category_id');
    }
}
