<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JobPostingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (tidak perlu autentikasi)
Route::post('/register/user', [AuthController::class, 'registerUser']);
Route::post('/register/company', [AuthController::class, 'registerCompany']);
Route::post('/login', [AuthController::class, 'login']);

// Public company listing
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/companies/{id}', [CompanyController::class, 'showById']);

// Public job postings listing
Route::get('/job-postings', [JobPostingController::class, 'index']);
Route::get('/job-postings/{id}', [JobPostingController::class, 'showById']);

// Public qualifications list (untuk dropdown)
Route::get('/qualifications', [JobPostingController::class, 'getQualifications']);

// Protected routes - All authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Protected routes - User role only (job seeker)
Route::middleware(['auth:sanctum', 'role:user'])->prefix('user')->group(function () {
    Route::post('/profile', [ProfileController::class, 'createOrUpdate']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Job application
    Route::post('/job-postings/{id}/apply', [JobPostingController::class, 'applyJob']);
    Route::get('/applications', [JobPostingController::class, 'myApplications']);
});

// Protected routes - Company role only (must be approved)
Route::middleware(['auth:sanctum', 'role:company', 'company.approved'])->prefix('company')->group(function () {
    Route::get('/details', [CompanyController::class, 'show']);
    Route::put('/profile', [CompanyController::class, 'createOrUpdate']);
    Route::get('/view/{id}', [CompanyController::class, 'showById']);

    // Job posting management
    Route::post('/job-postings', [JobPostingController::class, 'store']);
    Route::get('/job-postings', [JobPostingController::class, 'myJobPostings']);
    Route::get('/job-postings/{id}', [JobPostingController::class, 'show']);
    Route::put('/job-postings/{id}', [JobPostingController::class, 'update']);
    Route::put('/job-postings/{id}/status', [JobPostingController::class, 'updateStatusJobPosting']);
    Route::delete('/job-postings/{id}', [JobPostingController::class, 'destroy']);

    // Applicants management
    Route::get('/job-postings/{id}/applicants', [JobPostingController::class, 'applicant']);
    Route::post('/applicants/{applicationId}/accept', [JobPostingController::class, 'acceptApplicant']);
    Route::post('/applicants/{applicationId}/reject', [JobPostingController::class, 'rejectApplicant']);
});

// Protected routes - Admin role only
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // Company management
    Route::get('/companies/pending', [AdminController::class, 'pendingCompanies']);
    Route::get('/companies/approved', [AdminController::class, 'approvedCompanies']);
    Route::post('/companies/{userId}/approve', [AdminController::class, 'approveCompany']);
    Route::post('/companies/{userId}/reject', [AdminController::class, 'rejectCompany']);

    // Data management
    Route::get('/users', [AdminController::class, 'allUsers']);
    Route::get('/job-postings', [AdminController::class, 'allJobPostings']);
    Route::get('/applications', [AdminController::class, 'allApplications']);
});
