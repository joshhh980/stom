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
        Schema::table('supplier_list', function (Blueprint $schema) {
            // Re-declaring columns with ->nullable()->change() flags
            $schema->text('address')->nullable()->change();
            $schema->string('cperson')->nullable()->change();
            $schema->string('contact')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_list', function (Blueprint $schema) {
            // Reverts fields back to required (NOT NULL) states if rolled back
            $schema->text('address')->nullable(false)->change();
            $schema->string('cperson')->nullable(false)->change();
            $schema->string('contact')->nullable(false)->change();
        });
    }
};
