<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('timetables', function (Blueprint $table) {
        $table->id();
        // On lie le cours à une classe (L3, M1, etc.)
        $table->foreignId('classe_id')->constrained()->onDelete('cascade');
        // On lie le cours à une matière (Maths, Info, etc.)
        $table->foreignId('subject_id')->constrained()->onDelete('cascade');

        $table->string('day'); // Lundi, Mardi...
        $table->time('start_time'); // Heure de début
        $table->time('end_time');   // Heure de fin
        $table->string('room')->nullable(); // Salle de classe
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
