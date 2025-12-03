<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $all_data = array(
			array('key' => 'app_version', 'value' => '8.0.1'),
			array('key' => 'db_version', 'value' => '6.0.6'),
		);
		DB::table('settings')->truncate();
		foreach($all_data as $data){
			DB::table('settings')->insert($data);
		}
    }
}
