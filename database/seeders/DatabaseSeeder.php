<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder for users
        $users = [
            [
                'username' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            $user['password'] = Hash::make('123456');
            DB::table('users')->insert($user);
    }
}
}