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
        Schema::table('receiving_list', function (Blueprint $table) {
            // Makes the field nullable AND binds an explicit blank string fallback default value
            $table->text('remarks')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receiving_list', function (Blueprint $table) {
            // Reverts back to strict constraints if rolled back
            $table->text('remarks')->nullable(false)->default('')->change();
        });
    }
};
