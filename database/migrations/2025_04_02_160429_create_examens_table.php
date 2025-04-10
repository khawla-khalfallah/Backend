<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('examens', function (Blueprint $table) {
            $table->id();
            $table->date('date_examen');
            $table->float('note')->nullable();
            $table->unsignedBigInteger('formation_id');
            $table->unsignedBigInteger('apprenant_id');
            $table->timestamps();

            $table->foreign('formation_id')->references('id')->on('formations')->onDelete('cascade');
            $table->foreign('apprenant_id')->references('user_id')->on('apprenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examens');
    }
};
