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
        Schema::create('inscrits', function (Blueprint $table) {
            $table->id('id_inscrit');
            $table->unsignedBigInteger('apprenant_id');
            $table->unsignedBigInteger('formation_id');
            $table->timestamps();

            $table->foreign('apprenant_id')->references('user_id')->on('apprenants')->onDelete('cascade');
            $table->foreign('formation_id')->references('id')->on('formations')->onDelete('cascade');

            $table->unique(['apprenant_id', 'formation_id']); // éviter les doublons
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscrits');
    }
};
