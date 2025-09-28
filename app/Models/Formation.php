<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'prix', 'date_debut', 'date_fin', 'formateur_id'];

    public function formateur()
    {
        return $this->belongsTo(Formateur::class, 'formateur_id');
    }
    // public function apprenants()
    // {
    //     return $this->belongsToMany(Apprenant::class, 'inscrits', 'formation_id', 'apprenant_id');
    // }
    public function apprenants()
    {
        return $this->belongsToMany(Apprenant::class, 'inscrits', 'formation_id', 'apprenant_id', 'id', 'user_id');
    }
    public function inscrits()
    {
        return $this->hasMany(Inscrit::class, 'formation_id');
    }

    public function examens()
    {
        return $this->hasMany(Examen::class, 'formation_id');
    }

    public function seances()
    {
        return $this->hasMany(Seance::class, 'formation_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'formation_id');
    }

    public function pdfs()
    {
        return $this->hasMany(Pdf::class, 'formation_id');
    }

    public function certificats()
    {
        return $this->hasMany(Certificat::class, 'formation_id');
    }

    public function certificationTemplates()
    {
        return $this->hasMany(CertificationTemplate::class, 'formation_id');
    }
    
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function getMoyenneAttribute()
    {
        return round($this->evaluations()->avg('note') ?? 0, 1);
    }
    
}
