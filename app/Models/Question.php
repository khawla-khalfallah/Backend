<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['examen_id', 'enonce', 'type'];

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }
}
