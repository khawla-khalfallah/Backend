<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $fillable = ['question_id', 'texte', 'est_correcte'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
