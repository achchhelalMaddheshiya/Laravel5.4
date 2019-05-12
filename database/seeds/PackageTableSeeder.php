<?php
use App\Package;
use Illuminate\Database\Seeder;

class PackageTableSeeder extends Seeder
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
                'name' => 'Free',
                'audio_limit' => 1,
                'duration' => 3,
                'video_limit' => 2,
                'document_limit' => 5,
                'image_limit' => 10,
                'members_count_limit' => 20,
                'amount' => 0,
                'size' => 2,
                'subscription_days' => 0,
                'status' => 1,
                'created_at' => time()
            ],
            [
                'name' => 'Silver',
                'audio_limit' => 10,
                'duration' => 3,
                'video_limit' => 10,
                'document_limit' => 20,
                'image_limit' => 20,
                'members_count_limit' => 20,
                'amount' => 9.99,
                'size' => 2,
                'subscription_days' => 30,
                'status' => 1,
                'created_at' => time()
            ],
            [
                'name' => 'Gold',
                'audio_limit' => 500,
                'duration' => 3,
                'video_limit' => 500,
                'document_limit' => 500,
                'image_limit' => 1000,
                'members_count_limit' => 100,
                'amount' => 15.99,
                'size' => 2,
                'subscription_days' => 30,
                'status' => 1,
                'created_at' => time()
            ]
        ];
        foreach ($input as $val) {
            Package::firstOrCreate($val);
        }
    }
}
