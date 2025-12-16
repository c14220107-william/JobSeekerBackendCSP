<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $companies = [
            [
                'email' => 'contact@techsolutions.com',
                'name' => 'PT Tech Solutions',
                'company_name' => 'Tech Solutions Indonesia',
                'company_city' => 'Jakarta',
            ],
            [
                'email' => 'info@digitalmarketing.com',
                'name' => 'PT Digital Marketing',
                'company_name' => 'Digital Marketing Pro',
                'company_city' => 'Bandung',
            ],
            [
                'email' => 'hello@startuphub.com',
                'name' => 'PT Startup Hub',
                'company_name' => 'Startup Hub Indonesia',
                'company_city' => 'Surabaya',
            ],
        ];

        foreach ($companies as $companyData) {
            $user = User::create([
                'email' => $companyData['email'],
                'password' => Hash::make('password123'),
                'role' => 'company',
                'is_approved' => true,
            ]);

            Company::create([
                'user_id' => $user->id,
                'name' => $companyData['company_name'],
                'description' => 'Leading company in the industry',
                'address' => $companyData['company_city'],
                'is_approved' => true,
            ]);
        }
    }
}