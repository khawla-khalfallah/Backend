<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificat extends Model {
    use HasFactory;

    protected $fillable = [
        'date_obtention', 
        'apprenant_id', 
        'formation_id', 
        'formateur_id', 
        'certification_template_id',
        'titre_certification', 
        'note_examen', 
        'pdf_path'
    ];

    protected $casts = [
        'date_obtention' => 'date',
        'note_examen' => 'decimal:2'
    ];

    public function apprenant() {
        return $this->belongsTo(Apprenant::class, 'apprenant_id', 'user_id');
    }

    public function formation() {
        return $this->belongsTo(Formation::class);
    }

    public function formateur() {
        return $this->belongsTo(Formateur::class, 'formateur_id', 'user_id');
    }

    public function certificationTemplate() {
        return $this->belongsTo(CertificationTemplate::class);
    }
}
