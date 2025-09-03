<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('examens_apprenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id');
            $table->unsignedBigInteger('apprenant_id');
            $table->float('note')->nullable();
            $table->dateTime('date_passage')->nullable();
            $table->enum('statut', ['en_cours', 'termine'])->default('en_cours');
            $table->timestamps();

            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('apprenant_id')->references('user_id')->on('apprenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examens_apprenants');
    }
};
