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
        Schema::table('certificats', function (Blueprint $table) {
            $table->unsignedBigInteger('formation_id')->after('apprenant_id');
            $table->unsignedBigInteger('formateur_id')->after('formation_id');
            $table->string('titre_certification')->after('formateur_id');
            $table->decimal('note_examen', 5, 2)->nullable()->after('titre_certification');
            $table->string('pdf_path')->nullable()->after('note_examen');
            
            $table->foreign('formation_id')->references('id')->on('formations')->onDelete('cascade');
            $table->foreign('formateur_id')->references('user_id')->on('formateurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificats', function (Blueprint $table) {
            $table->dropForeign(['formation_id']);
            $table->dropForeign(['formateur_id']);
            $table->dropColumn(['formation_id', 'formateur_id', 'titre_certification', 'note_examen', 'pdf_path']);
        });
    }
};
