<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_names', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // Form ka naam
            $table->string('type'); // Single / Multi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_names');
    }
};
