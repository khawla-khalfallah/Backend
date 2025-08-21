<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
            // Supprimer la contrainte utilisateur
        Schema::create('evaluations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('formation_id')->constrained();
    $table->foreignId('apprenant_id')->constrained('apprenants', 'user_id'); // Correction ici
    $table->tinyInteger('note')->unsigned();
    $table->text('commentaire')->nullable();
    $table->timestamps();
    
    $table->unique(['formation_id', 'apprenant_id']);
});
    }

    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
};