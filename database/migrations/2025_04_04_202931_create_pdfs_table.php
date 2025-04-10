<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('fichier')->nullable(); // nom ou chemin du fichier PDF
            $table->unsignedBigInteger('formation_id');
            $table->timestamps();

            $table->foreign('formation_id')->references('id')->on('formations')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('pdfs');
    }
};
