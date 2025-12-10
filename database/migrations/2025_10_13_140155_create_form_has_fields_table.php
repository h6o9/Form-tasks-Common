<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_has_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_name_id')->constrained('form_names')->onDelete('cascade');
            $table->foreignId('form_field_id')->constrained('form_fields')->onDelete('cascade');
            $table->string('label');          // Field label
            $table->string('parameter');      // input name attribute
            $table->integer('step')->default(1); // Step number (for multi-step forms)
            $table->string('placeholder')->nullable(); // Placeholder for input
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_has_fields');
    }
};
