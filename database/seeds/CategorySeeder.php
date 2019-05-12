<?php

use App\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $input = [
            [
                'name' => 'Home',
                'width' => 728,
                'height' => 90,
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => 'Login',
                'status' => 1,
                'width' => 300,
                'height' => 600,
                'created_at' => time(),
            ],
            [
                'name' => 'Forgot Password',
                'status' => 1,
                'width' => 468,
                'height' => 60,
                'created_at' => time(),
            ],
            [
                'name' => 'Contact Us',
                'status' => 1,
                'width' => 300,
                'height' => 600,
                'created_at' => time(),
            ],
            [
                'name' => 'Faq',
                'status' => 1,
                'width' => 728,
                'height' => 90,
                'created_at' => time(),
            ],
            [
                'name' => 'About Us',
                'status' => 1,
                'width' => 728,
                'height' => 90,
                'created_at' => time(),
            ],
            [
                'name' => 'Profile',
                'status' => 1,
                'width' => 300,
                'height' => 600,
                'created_at' => time(),
            ],
            [
                'name' => 'My Vault',
                'status' => 1,
                'width' => 468,
                'height' => 60,
                'created_at' => time(),
            ],
            [
                'name' => 'Vault Detail',
                'status' => 1,
                'width' => 468,
                'height' => 60,
                'created_at' => time(),
            ],
            [
                'name' => 'Links',
                'status' => 1,
                'width' => 728,
                'height' => 90,
                'created_at' => time(),
            ],
            [
                'name' => 'Passwords',
                'status' => 1,
                'width' => 728,
                'height' => 90,
                'created_at' => time(),
            ],
            [
                'name' => 'Notifications',
                'status' => 1,
                'width' => 468,
                'height' => 60,
                'created_at' => time(),
            ],
            [
                'name' => 'Location',
                'status' => 1,
                'width' => 728,
                'height' => 90,
                'created_at' => time(),
            ],
            [
                'name' => 'Forgot Pin',
                'status' => 1,
                'width' => 120,
                'height' => 600,
                'created_at' => time(),
            ],
            [
                'name' => 'Folder Detail',
                'status' => 1,
                'width' => 728,
                'height' => 90,
                'created_at' => time(),
            ],
            [
                'name' => 'Nominee',
                'status' => 1,
                'width' => 468,
                'height' => 60,
                'created_at' => time(),
            ]
        ];

        foreach ($input as $val) {
            Category::firstOrCreate($val);
        }
    }
}
