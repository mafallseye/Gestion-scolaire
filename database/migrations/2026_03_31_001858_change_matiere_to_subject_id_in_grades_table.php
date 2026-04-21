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
    Schema::table('grades', function (Blueprint $table) {
        $table->dropColumn('matiere'); // On supprime l'ancien texte
        $table->foreignId('subject_id')->constrained()->onDelete('cascade'); // On lie à la table subjects
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            //
        });
    }
};
