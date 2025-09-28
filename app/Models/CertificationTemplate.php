<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'examen_id',
        'formateur_id',
        'titre_certification',
        'score_minimum',
        'is_active',
        'description'
    ];

    protected $casts = [
        'score_minimum' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function formateur()
    {
        return $this->belongsTo(Formateur::class, 'formateur_id', 'user_id');
    }

    public function certificats()
    {
        return $this->hasMany(Certificat::class, 'certification_template_id');
    }
}
