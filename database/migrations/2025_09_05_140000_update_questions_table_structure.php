<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Drop old columns that conflict with new structure
            $table->dropColumn(['options', 'reponse_correcte']);
            
            // Add new columns to match the controller expectations
            $table->text('enonce')->after('id');
            $table->enum('type', ['qcm', 'vrai-faux', 'texte'])->after('enonce');
            $table->integer('points')->default(1)->after('type');
        });
        
        // Rename the old 'question' column to 'enonce' if it exists
        if (Schema::hasColumn('questions', 'question')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('question');
            });
        }
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['enonce', 'type', 'points']);
            $table->string('question');
            $table->json('options');
            $table->string('reponse_correcte');
        });
    }
};
