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
            // Change titre to title if it exists
            if (Schema::hasColumn('examens', 'titre')) {
                $table->renameColumn('titre', 'title');
            } else {
                $table->string('title');
            }
            
            // Add missing columns for modern exam system
            $table->integer('duration')->default(60); // minutes
            $table->integer('total_marks')->default(0);
            $table->unsignedBigInteger('formateur_id')->nullable();
            
            // Make date_examen nullable since it can be set later
            $table->datetime('date_examen')->nullable()->change();
            
            // Add foreign key for formateur
            $table->foreign('formateur_id')->references('user_id')->on('formateurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            // Reverse the changes
            $table->dropForeign(['formateur_id']);
            $table->dropColumn(['duration', 'total_marks', 'formateur_id']);
            
            if (Schema::hasColumn('examens', 'title')) {
                $table->renameColumn('title', 'titre');
            }
            
            // Make date_examen required again
            $table->date('date_examen')->nullable(false)->change();
        });
    }
};
