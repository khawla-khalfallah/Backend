<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apprenant extends Model
{
    use HasFactory;

    // Définir la clé primaire à 'id' (par défaut)
    protected $primaryKey = 'id'; // Utilise 'id' comme clé primaire
    public $incrementing = true; // Indique que la clé primaire est auto-incrémentée

    protected $fillable = ['user_id', 'niveau_etude']; // Liste des attributs assignables

    // Relation avec le modèle User (clé étrangère 'user_id')
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'inscrits', 'apprenant_id', 'formation_id');
    }

    public function examens()
    {
        return $this->hasMany(Examen::class, 'apprenant_id');
    }

    public function certificats()
    {
        return $this->hasMany(Certificat::class, 'apprenant_id');
    }
}
