<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SergioBookPage extends Model
{
    use HasFactory;

    protected $table = "sergio_book_pages";

    protected $fillable = [
        'image',
        'book_id',
    ];

    public function book()
    {
        return $this->belongsTo(SergioBook::class, 'book_id');
    }
}
