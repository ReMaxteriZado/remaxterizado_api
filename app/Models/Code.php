<?php

namespace App;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;

    protected $fillable = [
        'link_id',
        'language',
        'comment',
        'code',
    ];

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
