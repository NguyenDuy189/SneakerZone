<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder {
    public function run() {
        DB::table('settings')->truncate();
        DB::table('settings')->insert([
            ['key' => 'site_name', 'value' => 'Sneaker Zone Premium', 'type' => 'text'],
            ['key' => 'hotline', 'value' => '1900 6868', 'type' => 'text'],
            ['key' => 'email', 'value' => 'support@sneakerzone.com', 'type' => 'text'],
            ['key' => 'address', 'value' => 'Tầng 5, Landmark 81, TP.HCM', 'type' => 'text'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/sneakerzone', 'type' => 'text'],
            ['key' => 'freeship_threshold', 'value' => '2000000', 'type' => 'number'], // Đơn > 2tr freeship
        ]);
    }
}