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
        Schema::create('reponses_apprenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_apprenant_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('reponse_id')->nullable();
            $table->text('reponse_donnee')->nullable();
            $table->boolean('est_correct')->nullable();
            $table->timestamps();

            $table->foreign('examen_apprenant_id')->references('id')->on('examens_apprenants')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('reponse_id')->references('id')->on('reponses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reponses_apprenants');
    }
};
