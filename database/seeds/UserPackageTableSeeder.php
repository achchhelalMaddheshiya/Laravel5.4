<?php
use App\User;
use App\Role;
use App\Package;
use App\UserPackage;
use Illuminate\Database\Seeder;

class UserPackageTableSeeder extends Seeder
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
                'user_id' =>  User::where('role_id',Role::where('name', 'user')->first()->id)->first()->id,
                'package_id' =>  Package::where(['subscription_days' => 0, 'amount' => 0])->first()->id,
                'type' => 1,
                'status' => 1,
                'created_at' => time()
            ]
        ];
        foreach ($input as $val) {
            UserPackage::firstOrCreate($val);
        }
    }
}
