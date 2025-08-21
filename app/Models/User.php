<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory,HasApiTokens;

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
}

