<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // User::create([
        //     'email' => 'nanda@gmail.com',
        //     'password' => bcrypt('nanda'),
        //     'role' => 'user',
        // ]);

        // User::create([
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('admin'),
        //     'role' => 'admin',
        // ]);

        // User::create([
        //     'email' => 'girvan@gmail.com',
        //     'password' => bcrypt('girvan'),
        //     'role' => 'company',
        // ]);
        User::create([
            'email' => 'admin123@gmail.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);    

    }
}
