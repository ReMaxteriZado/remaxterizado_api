<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SergioSubCategory extends Model
{
    use HasFactory;

    protected $table = "sergio_subcategories";

    protected $fillable = [
        'name',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(SergioCategory::class, 'category_id');
    }

    public function books()
    {
        return $this->hasMany(SergioBook::class, 'subcategory_id');
    }
}
