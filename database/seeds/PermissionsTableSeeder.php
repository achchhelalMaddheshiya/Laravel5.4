<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Permission;
class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $input= [
            [
                'name' => 'All',
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => 'View',
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => 'Upload',
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => 'Download',
                'status' => 1,
                'created_at' => time(),
            ]
        ];
        foreach ($input as $val) {
            Permission::firstOrCreate($val);
        }
    }
}
