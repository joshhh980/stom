<?php

namespace Database\Seeders;

use App\Models\SystemInfo;
use Illuminate\Database\Seeder;

class SystemInfoSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'name' => 'Stock Management System - PHP',
            'short_name' => 'SMS- PHP',
            'currency' => 'GHS',
            'logo' => 'uploads/logo-1635816671.png',
            'cover' => 'uploads/cover-1635816671.png',
        ];

        foreach ($defaults as $field => $value) {
            SystemInfo::firstOrCreate(
                ['meta_field' => $field],
                ['meta_value' => $value]
            );
        }
    }
}
