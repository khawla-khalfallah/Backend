<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'url',
        'description',
        'formation_id',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }
}
