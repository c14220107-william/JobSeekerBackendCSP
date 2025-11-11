<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory, HasUuids;

    const CREATED_AT = 'applied_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'job_id',
        'seeker_id',
        'status',
    ];

    /**
     * Relasi ke Job Posting
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    /**
     * Relasi ke Profile (Job Seeker)
     */
    public function seeker(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'seeker_id');
    }
}
