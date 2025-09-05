<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['nom', 'prenom', 'email', 'password', 'role'];

    public function apprenant()
    {
        return $this->hasOne(Apprenant::class, 'user_id');
    }
    public function formateur()
    {
        return $this->hasOne(Formateur::class);
    }

    public function recruteur()
    {
        return $this->hasOne(Recruteur::class);
    }
    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }
    public function formations()
    {
        return $this->hasManyThrough(
            Formation::class,       // le modèle cible
            Apprenant::class,       // le modèle intermédiaire
            'user_id',              // clé étrangère sur apprenants
            'id',                   // clé sur formations (ou adapte selon ta table pivot)
            'id',                   // clé locale sur users
            'formation_id'          // clé étrangère sur apprenants (ou table pivot)
        );
    }


}

