<?php

namespace App;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function relatedCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('relatedCategories');
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
