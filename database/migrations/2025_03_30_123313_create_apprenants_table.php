<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('apprenants', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id')->primary(); // Supprimez $table->id()
    $table->string('niveau_etude', 50);
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->timestamps();
});
    }

    public function down()
    {
        Schema::dropIfExists('apprenants');
    }
};
