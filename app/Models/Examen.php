<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    use HasFactory;

    protected $fillable = ['date_examen', 'note', 'formation_id', 'apprenant_id'];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class);
    }
}
