<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosting;
use App\Models\Company;

class JobPostingSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        $jobs = [
            [
                'company_id' => $companies[0]->id,
                'title' => 'Senior Software Engineer',
                'location' => 'Jakarta',
                'salary' => 'Rp 10.000.000 - Rp 15.000.000',
                'type' => 'Full Time',
                'tenure' => 'Permanent',
                'status' => 'open',
                'description' => 'We are looking for a Senior Software Engineer to join our team.',
            ],
            [
                'company_id' => $companies[1]->id,
                'title' => 'Digital Marketing Specialist',
                'location' => 'Bandung',
                'salary' => 'Rp 7.000.000 - Rp 10.000.000',
                'type' => 'Full Time',
                'tenure' => 'Contract',
                'status' => 'open',
                'description' => 'Join our marketing team as a Digital Marketing Specialist.',
            ],
            [
                'company_id' => $companies[2]->id,
                'title' => 'Product Manager',
                'location' => 'Surabaya',
                'salary' => 'Negotiable',
                'type' => 'Full Time',
                'tenure' => 'Permanent',
                'status' => 'closed',
                'description' => 'Lead product development as our Product Manager.',
            ],
        ];

        foreach ($jobs as $jobData) {
            JobPosting::create($jobData);
        }
    }
}