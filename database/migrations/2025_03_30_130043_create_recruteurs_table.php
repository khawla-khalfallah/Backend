<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recruteurs', function (Blueprint $table) {
            $table->id(); // Ajouter une colonne 'id' auto-incrémentée comme clé primaire
            $table->unsignedBigInteger('user_id')->unique(); // Clé étrangère unique
            $table->string('entreprise', 100)->nullable(); // Nom de l'entreprise
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Relation avec 'users'
            $table->timestamps(); // Timestamps (created_at, updated_at)
        });
    }
    public function down()
    {
        Schema::dropIfExists('recruteurs');
    }
};
