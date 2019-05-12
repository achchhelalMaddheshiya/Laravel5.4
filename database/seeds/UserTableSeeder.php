<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $users = [
            [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('admin@123'), // secret
                'remember_token' => str_random(10),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('user@123'), // secret
                'remember_token' => str_random(10),
                'role_id' => Role::where('name', 'user')->first()->id,
                'status' => 1,
                'created_at' => time(),
            ],
        ];
        foreach ($users as $val) {
            User::firstOrCreate($val);
        }
    }
}
