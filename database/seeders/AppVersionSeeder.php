<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_versions')->insert([
            'version_name' => '1.0.0',
            'version_code' => 1,
            'force_update' => 0,
            'changelog' => 'Initial release',
            'download_url' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}