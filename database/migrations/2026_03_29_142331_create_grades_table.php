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
    Schema::create('grades', function (Blueprint $table) {
        $table->id();
        // On lie la note à un étudiant (Clé étrangère)
        $table->foreignId('student_id')->constrained()->onDelete('cascade');
        $table->string('matiere');
        $table->decimal('note', 4, 2); // Ex: 18.50
        $table->string('semestre');
        $table->timestamps();
    });
}

};
