<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificats', function (Blueprint $table) {
            $table->id();
            $table->date('date_obtention');
            $table->unsignedBigInteger('apprenant_id');
            $table->timestamps();

            $table->foreign('apprenant_id')->references('user_id')->on('apprenants')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('certificats');
    }
};
