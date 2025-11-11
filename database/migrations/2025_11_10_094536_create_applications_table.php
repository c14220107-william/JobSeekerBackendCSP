<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_id')->nullable();
            $table->uuid('seeker_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('applied_at')->useCurrent();
            
            $table->foreign('job_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->foreign('seeker_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
