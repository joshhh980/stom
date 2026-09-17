<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Converts column type safely to support modern Bcrypt hashes
            $table->string('password', 255)->change();

            // Adds Laravel's native session-tracking token column if missing
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
        });
    }

    public function down(): void
    {
        // No rollback needed to preserve integrity
    }
};
