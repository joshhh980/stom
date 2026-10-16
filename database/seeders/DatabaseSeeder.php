<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(SystemInfoSeeder::class);

        DB::table('users')->update([
            'password' => \Illuminate\Support\Facades\Hash::make('your-new-secure-password'),
            'updated_at' => now(), // Keeps the timestamps accurate
        ]);
    }
}
