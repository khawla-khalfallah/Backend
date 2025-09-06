<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReponseApprenant extends Model
{
    protected $table = 'reponses_apprenants';
    protected $fillable = ['examen_apprenant_id', 'question_id', 'reponse_id', 'reponse_donnee', 'est_correct'];

    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reponse()
    {
        return $this->belongsTo(Reponse::class);
    }
    public function examenApprenant() {
        return $this->belongsTo(\App\Models\ExamenApprenant::class);
    }
}
