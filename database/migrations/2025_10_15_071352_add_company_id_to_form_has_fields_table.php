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
        Schema::table('form_has_fields', function (Blueprint $table) {
            // company_id column add karte hain (foreign key ke sath)
            $table->unsignedBigInteger('company_id')->nullable()->after('id');

            // Optional: foreign key relation
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_has_fields', function (Blueprint $table) {
            // Foreign key drop karni zaroori hai rollback se pehle
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
