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
            $table->unsignedBigInteger('certification_template_id')->nullable()->after('formateur_id');
            $table->foreign('certification_template_id')->references('id')->on('certification_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificats', function (Blueprint $table) {
            $table->dropForeign(['certification_template_id']);
            $table->dropColumn('certification_template_id');
        });
    }
};
