<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apprenant extends Model
{
    use HasFactory;

      protected $primaryKey = 'user_id'; // Clé primaire = user_id
    public $incrementing = false; // Désactive l'auto-incrément

    protected $fillable = ['user_id', 'niveau_etude'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
   
    public function formations() {
        return $this->belongsToMany(Formation::class, 'inscrits', 'apprenant_id', 'formation_id');
    }

    // public function examens() {
    //     return $this->hasMany(Examen::class, 'apprenant_id', 'user_id');
    // }

    public function certificats() {
        return $this->hasMany(Certificat::class, 'apprenant_id', 'user_id');
    }
    public function inscrits()
    {
        return $this->hasMany(Inscrit::class, 'apprenant_id');
    }
    public function examens()
    {
        return $this->belongsToMany(Examen::class, 'examens_apprenants', 'apprenant_id', 'examen_id')
                    ->withPivot('note', 'statut', 'date_passage')
                    ->withTimestamps();
    }

    public function reponsesDonnees()
    {
        return $this->hasMany(ReponseApprenant::class);
    }
}