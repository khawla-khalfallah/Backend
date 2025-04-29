<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['question', 'options', 'reponse_correcte', 'examen_id'];

    protected $casts = [
        'options' => 'array', // Laravel sait que c'est un tableau
    ];

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }
}
