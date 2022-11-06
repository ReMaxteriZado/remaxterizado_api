<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SergioBook extends Model
{
    use HasFactory;

    protected $table = "sergio_books";

    protected $fillable = [
        'category_id',
        'subcategory_id',
    ];

    public function category()
    {
        return $this->belongsTo(SergioCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(SergioSubcategory::class, 'subcategory_id');
    }
}
