<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamenApprenant extends Model
{
    protected $table = 'examens_apprenants';
    protected $fillable = ['examen_id','apprenant_id','note','date_passage','statut'];

    public function examen()   
    {
         return $this->belongsTo(Examen::class);
    }
    public function apprenant() 
    {
         return $this->belongsTo(Apprenant::class, 'apprenant_id', 'user_id');
    }
}
