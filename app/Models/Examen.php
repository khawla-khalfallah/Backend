<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'formation_id'];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function apprenants()
    {
        return $this->belongsToMany(Apprenant::class, 'examens_apprenants')
                    ->withPivot('note', 'statut', 'date_passage')
                    ->withTimestamps();
    }
    
}
