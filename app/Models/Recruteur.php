<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recruteur extends Model
{
    use HasFactory;
    
    // Définit la clé primaire sur 'id' (par défaut)
    protected $primaryKey = 'id'; // Utilise 'id' comme clé primaire
    public $incrementing = true; // Par défaut, les clés primaires sont auto-incrémentées

    protected $fillable = ['user_id', 'entreprise'];

    // Relation avec le modèle User (clé étrangère 'user_id')
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
