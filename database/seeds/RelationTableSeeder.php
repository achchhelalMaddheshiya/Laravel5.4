<?php

use App\Relation;
use Illuminate\Database\Seeder;

class RelationTableSeeder extends Seeder
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
                'name' => 'Father',
                'slug' => 'father',
                'status' => 1,
                'created_at' => time()
            ],
            [
                'name' => 'Mother',
                'slug' => 'mother',
                'status' => 1,
                'created_at' => time()
            ],
            [
                'name' => 'Sister',
                'slug' => 'sister',
                'status' => 1,
                'created_at' => time()
            ],
            [
                'name' => 'Brother',
                'slug' => 'brother',
                'status' => 1,
                'created_at' => time()
            ]
        ];
        foreach ($input as $val) {
            Relation::firstOrCreate($val);
        }

    }
}
