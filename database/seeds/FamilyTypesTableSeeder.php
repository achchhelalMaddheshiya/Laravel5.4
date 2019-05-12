<?php
use App\FamilyType;
use Illuminate\Database\Seeder;

class FamilyTypesTableSeeder extends Seeder
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
                'name' => 'Mark as primary',
                'slug' => 'primary',
                'members_count' => 1,
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => 'Mark as guarantee',
                'slug' => 'guarantee',
                'members_count' => 1,
                'status' => 1,
                'created_at' => time(),
            ],
            [
                'name' => 'Family Member',
                'slug' => 'member',
                'members_count' => 10,
                'status' => 1,
                'created_at' => time(),
            ]
        ];
        
        foreach ($input as $val) {
            FamilyType::firstOrCreate($val);
        }
    }
}

