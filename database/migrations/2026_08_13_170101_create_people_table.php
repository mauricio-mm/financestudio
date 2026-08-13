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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_type_id')->constrained('person_types')->restrictOnDelete();
            $table->string('name');
            $table->string('document', 14);
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'document']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
