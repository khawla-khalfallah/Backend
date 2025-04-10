<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pdf extends Model {
    use HasFactory;

    protected $fillable = ['titre', 'fichier', 'formation_id'];

    public function formation() {
        return $this->belongsTo(Formation::class);
    }
}
