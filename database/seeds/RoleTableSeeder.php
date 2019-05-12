$this->call(RoleTableSeeder::class);<?php

use App\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $input = [
            [
                'name' => 'admin',
                'slug' => 'admin',
                'status' => 1,
                'created_at' => time()
            ],
            [
                'name' => 'user',
                'slug' => 'user',
                'status' => 1,
                'created_at' => time()
            ]
        ];
        foreach ($input as $val) {
            Role::firstOrCreate($val);
        }

    }
}
