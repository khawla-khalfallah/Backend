<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificat extends Model {
    use HasFactory;

    protected $fillable = ['date_obtention', 'apprenant_id'];

    public function apprenant() {
        return $this->belongsTo(Apprenant::class);
    }
}
