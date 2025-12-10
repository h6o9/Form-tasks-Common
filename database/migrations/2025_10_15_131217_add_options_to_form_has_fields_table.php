<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_has_fields', function (Blueprint $table) {
            // Add new column for dropdown options (JSON type is ideal)
            $table->json('options')->nullable()->after('steps');
        });
    }

    public function down(): void
    {
        Schema::table('form_has_fields', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
