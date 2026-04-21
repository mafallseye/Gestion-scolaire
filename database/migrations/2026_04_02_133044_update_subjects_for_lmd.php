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
    Schema::table('subjects', function (Blueprint $table) {
        $table->string('ue_code')->nullable()->after('name'); // Ex: UE-INF11
        $table->string('ue_nom')->nullable()->after('ue_code'); // Ex: Mathématiques et Info
        $table->integer('credits')->default(2)->after('coefficient'); 
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            //
        });
    }
};
