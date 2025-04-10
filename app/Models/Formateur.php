<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formateur extends Model
{
    use HasFactory;

   
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = ['user_id', 'specialite', 'bio'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function formations()
    {
        return $this->hasMany(Formation::class, 'formateur_id');
    }
}
