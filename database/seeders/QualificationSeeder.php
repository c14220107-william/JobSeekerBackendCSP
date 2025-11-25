<?php

namespace Database\Seeders;

use App\Models\Qualification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QualificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Qualification::create(
            ['skill' => 'Project Management']

        );
        Qualification::create(
            ['skill' => 'Data Analysis']

        );
        Qualification::create(
            ['skill' => 'Graphic Design']
        );
        Qualification::create(
            ['skill' => 'Digital Marketing']
        );
        Qualification::create(
            ['skill' => 'Cybersecurity']
        );
        

    }
}
