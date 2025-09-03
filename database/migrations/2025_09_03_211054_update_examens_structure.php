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
        Schema::table('examens', function (Blueprint $table) {
            // 🔴 Supprimer les colonnes inutiles
            $table->dropColumn('apprenant_id');
            $table->dropColumn('note');

            // 🟢 Ajouter des colonnes utiles
            $table->string('titre')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->unsignedBigInteger('apprenant_id');
            $table->float('note')->nullable();
            $table->dropColumn('titre');
            $table->dropColumn('description');
        });
    }
};
