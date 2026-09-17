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
        Schema::table('purchase_order_list', function (Blueprint $table) {
            // Fixes the column to allow NULL values natively on insert operations
            $table->text('remarks')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_list', function (Blueprint $table) {
            // Reverts the structural rule configuration back if rolled back
            $table->text('remarks')->nullable(false)->change();
        });
    }
};
