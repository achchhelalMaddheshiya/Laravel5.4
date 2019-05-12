<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CategorySeeder::class);
        $this->command->info('->>> Ad Categories Created -<<<');


        $this->call(RoleTableSeeder::class);
        $this->command->info('->>> User Role created -<<<');

        $this->call(PackageTableSeeder::class);
        $this->command->info('->>> Packages Created -<<<');


        $this->call(UserTableSeeder::class);
        $this->command->info('->>> Users Created -<<<');


        $this->call(UserPackageTableSeeder::class);
        $this->command->info('->>> Package (Free) Assigned To user -<<<');


        $this->call(FamilyTypesTableSeeder::class);
        $this->command->info('->>> Family Types Created -<<<');

        $this->call(RelationTableSeeder::class);
        $this->command->info('->>> User Relation Created -<<<');
        
        $this->call(PermissionsTableSeeder::class);
        $this->command->info('->>> Folder Permissions Created -<<<');
        
    }
}
