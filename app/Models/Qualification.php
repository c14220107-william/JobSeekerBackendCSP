<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Qualification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'skill',
    ];

    /**
     * Relasi Many-to-Many ke Job Postings
     */
    public function jobPostings(): BelongsToMany
    {
        return $this->belongsToMany(JobPosting::class, 'job_qualification', 'qualification_id', 'job_id')
            ->withTimestamps();
    }
}
