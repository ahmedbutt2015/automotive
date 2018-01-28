<?php

use Illuminate\Database\Seeder;

class ConfigrationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('configuration')->insert([
            'C_tenant_id' => env('C_tenant_id'),
            'C_user' => env('C_user'),
            'C_password' => env('C_password'),
            'D_tenant_id' => env('D_tenant_id'),
            'D_user' => env('D_user'),
            'd_password' => env('D_password'),
        ]);
    }
}
