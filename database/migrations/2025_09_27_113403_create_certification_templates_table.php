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
        Schema::create('certification_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formation_id');
            $table->unsignedBigInteger('examen_id')->nullable(); // Optional specific exam
            $table->unsignedBigInteger('formateur_id');
            $table->string('titre_certification');
            $table->decimal('score_minimum', 5, 2); // Minimum score required (e.g., 10.00)
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('formation_id')->references('id')->on('formations')->onDelete('cascade');
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('formateur_id')->references('user_id')->on('formateurs')->onDelete('cascade');
            
            // Prevent duplicate templates for same formation/exam
            $table->unique(['formation_id', 'examen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_templates');
    }
};
