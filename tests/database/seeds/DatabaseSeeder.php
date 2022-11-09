<?php

use Illuminate\Database\Seeder;
use SmurfWorks\ModelFinderTests\SampleModels as Models;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Seed permissions for these tests.
         *
         * @var array $permissions
         */
        $permissions = [
            'create-users' => Models\User\Permission::create(['name' => 'create-users']),
            'create-posts' => Models\User\Permission::create(['name' => 'create-posts']),
            'moderate-posts' => Models\User\Permission::create(['name' => 'moderate-posts'])
        ];

        /**
         * Create a role for the admin.
         *
         * @var Models\Role $admin
         */
        $admin = Models\User\Role::create(['name' => 'Admin']);
        $admin->permissions()->attach($permissions['create-users']);
        $admin->permissions()->attach($permissions['create-posts']);
        $admin->permissions()->attach($permissions['moderate-posts']);

        /**
         * Create a role for moderators.
         *
         * @var Models\Role $moderator
         */
        $moderator = Models\User\Role::create(['name' => 'Moderator']);
        $moderator->permissions()->attach($permissions['moderate-posts']);

        /**
         * Create a role for moderators.
         *
         * @var Models\Role $moderator
         */
        $user = Models\User\Role::create(['name' => 'User']);
        Models\User::factory()->count(10)->create(['role_id' => $user->id]);

        Models\User::first()->role()->associate($admin)->save();
        Models\User::whereIn('id', [2,3])->get()->each(
            function ($user) use ($moderator) {
                $user->role()->associate($moderator)->save();
            }
        );

        Models\User::whereIn('id', [4,5])->update(['receive_newsletter' => true]);
    }
}
