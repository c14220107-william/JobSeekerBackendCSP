<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\User;
use App\Models\JobPosting;
use Illuminate\Support\Str;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get job posting dengan id tertentu (sesuaikan dengan job posting yang ada)
        $jobPostings = JobPosting::all();
        
        if ($jobPostings->isEmpty()) {
            $this->command->warn('No job postings found. Please seed job postings first.');
            return;
        }

        // Ambil job posting pertama
        $jobPosting = $jobPostings->first();
        
        // Get users dengan role 'user' yang sudah punya profile
        $profiles = \App\Models\Profile::limit(3)->get();
        
        if ($profiles->count() < 3) {
            $this->command->info('Creating job seeker users with profiles...');
            
            // Buat user + profile tambahan jika kurang dari 3
            for ($i = $profiles->count(); $i < 3; $i++) {
                $user = User::create([
                    'id' => Str::uuid(),
                    'email' => 'seeker' . ($i + 1) . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'user',
                    'is_approved' => true,
                ]);
                
                $profile = \App\Models\Profile::create([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'age' => rand(22, 35),
                    'bio' => 'Experienced professional looking for opportunities',
                ]);
                
                $profiles->push($profile);
            }
        }

        // Buat 3 applications
        foreach ($profiles->take(3) as $index => $profile) {
            Application::create([
                'id' => Str::uuid(),
                'job_id' => $jobPosting->id,
                'seeker_id' => $profile->id,
                'status' => ['pending', 'accepted', 'rejected'][$index % 3],
            ]);
        }

        $this->command->info('Created 3 applications for job posting: ' . $jobPosting->title);
    }
}
