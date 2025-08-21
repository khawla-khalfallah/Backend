<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = ['formation_id', 'apprenant_id', 'note', 'commentaire'];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }
  
    public static function globalAverage()
    {
        return (float) self::avg('note') ?? 3.0;
    }
    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class, 'apprenant_id', 'user_id');
    }
  
}