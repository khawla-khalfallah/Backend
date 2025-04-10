<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2)->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->unsignedBigInteger('formateur_id');
            $table->timestamps();

            $table->foreign('formateur_id')->references('user_id')->on('formateurs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('formations');
    }
};
