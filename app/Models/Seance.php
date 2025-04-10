<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = [
        'titreSeance',
        'date',
        'heureDebut',
        'heureFin',
        'lienRoom',
        'formation_id',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }
}
