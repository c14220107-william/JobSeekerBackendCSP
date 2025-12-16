<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\JobPosting;
use App\Models\Qualification;

class JobPostingController extends Controller
{
    /**
     * Create new job posting with qualifications
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'tenure' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'status' => 'nullable|in:open,closed',
            'qualification_ids' => 'nullable|array',
            'qualification_ids.*' => 'exists:qualifications,id',
        ], [
            'title.required' => 'Judul lowongan wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'tenure.required' => 'Tenure wajib diisi.',
            'type.required' => 'Tipe pekerjaan wajib diisi.',
            'status.in' => 'Status harus open atau closed.',
            'qualification_ids.array' => 'Qualification IDs harus berupa array.',
            'qualification_ids.*.exists' => 'Qualification ID tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Pastikan user punya company
        if (!$user->company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found. Please complete your company registration.'
            ], 404);
        }

        $company = $user->company;

        // Create job posting
        $jobPosting = JobPosting::create([
            'company_id' => $company->id,
            'title' => $request->title,
            'location' => $request->location,
            'salary' => $request->salary,
            'description' => $request->description,
            'tenure' => $request->tenure,
            'type' => $request->type,
            'status' => $request->status ?? 'open',
        ]);

        // Attach qualifications ke pivot table
        if ($request->has('qualification_ids') && is_array($request->qualification_ids)) {
            $jobPosting->qualifications()->attach($request->qualification_ids);
        }

        // Load relations
        $jobPosting->load('qualifications', 'company');

        return response()->json([
            'success' => true,
            'message' => 'Job posting created successfully',
            'data' => [
                'job_posting' => $jobPosting
            ]
        ], 201);
    }

    /**
     * Update job posting with qualifications
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'tenure' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'status' => 'nullable|in:open,closed',
            'qualification_ids' => 'nullable|array',
            'qualification_ids.*' => 'exists:qualifications,id',
        ], [
            'title.required' => 'Judul lowongan wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'tenure.required' => 'Tenure wajib diisi.',
            'type.required' => 'Tipe pekerjaan wajib diisi.',
            'status.in' => 'Status harus open atau closed.',
            'qualification_ids.array' => 'Qualification IDs harus berupa array.',
            'qualification_ids.*.exists' => 'Qualification ID tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $company = $user->company;

        // Find job posting milik company ini
        $jobPosting = JobPosting::where('company_id', $company->id)->find($id);

        if (!$jobPosting) {
            return response()->json([
                'success' => false,
                'message' => 'Job posting not found or you do not have permission to update it'
            ], 404);
        }

        // Update job posting
        $jobPosting->update([
            'title' => $request->title,
            'location' => $request->location,
            'salary' => $request->salary,
            'description' => $request->description,
            'tenure' => $request->tenure,
            'type' => $request->type,
            'status' => $request->status ?? $jobPosting->status,
        ]);

        // Sync qualifications (replace semua qualifications dengan yang baru)
        if ($request->has('qualification_ids')) {
            $jobPosting->qualifications()->sync($request->qualification_ids);
        }

        // Load relations
        $jobPosting->load('qualifications', 'company');

        return response()->json([
            'success' => true,
            'message' => 'Job posting updated successfully',
            'data' => [
                'job_posting' => $jobPosting
            ]
        ], 200);
    }

    public function updateStatusJobPosting(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,closed',
        ], [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus open atau closed.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $company = $user->company;

        $jobPosting = JobPosting::where('company_id', $company->id)->find($id);

        if (!$jobPosting) {
            return response()->json([
                'success' => false,
                'message' => 'Job posting not found or you do not have permission to update it'
            ], 404);
        }

        // Update status job posting
        $jobPosting->status = $request->status;
        $jobPosting->save();

        return response()->json([
            'success' => true,
            'message' => 'Job posting status updated successfully',
            'data' => [
                'job_posting' => $jobPosting
            ]
        ], 200);


    }

    /**
     * Delete job posting
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $company = $user->company;

        $jobPosting = JobPosting::where('company_id', $company->id)->find($id);

        if (!$jobPosting) {
            return response()->json([
                'success' => false,
                'message' => 'Job posting not found or you do not have permission to delete it'
            ], 404);
        }

        // Delete akan otomatis detach qualifications karena cascade
        $jobPosting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job posting deleted successfully'
        ], 200);
    }

    /**
     * Get all job postings milik company (authenticated company)
     */
    public function myJobPostings(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $jobPostings = JobPosting::where('company_id', $company->id)
            ->withCount('applications')
            ->with('qualifications', 'applications')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'job_postings' => $jobPostings
            ]
        ], 200);
    }

    
    public function applicant(Request $request, $id)
    {
        $applicants = JobPosting::where('id', $id)
        ->with([
            'applications.seeker',      // Load Profile (seeker)
            'applications.seeker.user'  // Load User dari Profile
        ])
        ->first();
        return response()->json([
            'success' => true,
            'data' => [
                'applicants' => $applicants
            ]
        ], 200);
    }

    /**
     * Accept applicant
     */
    public function acceptApplicant(Request $request, $applicationId)
    {
        $user = $request->user();
        $company = $user->company;

        // Find application
        $application = Application::with('jobPosting')
            ->find($applicationId);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        // Verify job posting belongs to company
        if ($application->jobPosting->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to accept this applicant'
            ], 403);
        }

        // Update status to accepted
        $application->status = 'accepted';
        $application->save();

        $application->load('seeker.user');

        return response()->json([
            'success' => true,
            'message' => 'Applicant accepted successfully',
            'data' => [
                'application' => $application
            ]
        ], 200);
    }

    /**
     * Reject applicant
     */
    public function rejectApplicant(Request $request, $applicationId)
    {
        $user = $request->user();
        $company = $user->company;

        // Find application
        $application = Application::with('jobPosting')
            ->find($applicationId);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        // Verify job posting belongs to company
        if ($application->jobPosting->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject this applicant'
            ], 403);
        }

        // Update status to rejected
        $application->status = 'rejected';
        $application->save();

        $application->load('seeker.user');

        return response()->json([
            'success' => true,
            'message' => 'Applicant rejected successfully',
            'data' => [
                'application' => $application
            ]
        ], 200);
    }

    /**
     * Get single job posting milik company
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $company = $user->company;

        $jobPosting = JobPosting::where('company_id', $company->id)
            ->withCount('applications')
            ->with('qualifications', 'applications.seeker.user')
            ->find($id);

        if (!$jobPosting) {
            return response()->json([
                'success' => false,
                'message' => 'Job posting not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_posting' => $jobPosting
            ]
        ], 200);
    }

    /**
     * Get all job postings (public - untuk job seeker)
     */
    public function index()
    {
        $jobPostings = JobPosting::where('status', 'open')
            ->withCount('applications')
            ->with('company', 'qualifications')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'job_postings' => $jobPostings
            ]
        ], 200);
    }

        /**
     * Get all job postings (public - untuk job seeker)
     */
    public function jobpostingById($id)
    {
        // Get profile ID from user_id
        $profile = \App\Models\Profile::where('user_id', $id)->first();
        $profileId = $profile ? $profile->id : null;

        $jobPostings = JobPosting::where('status', 'open')
            ->withCount('applications')
            ->with('company', 'qualifications')
            ->addSelect([
                'is_applied' => Application::select(\DB::raw('1'))
                    ->whereColumn('applications.job_id', 'job_postings.id')
                    ->where('applications.seeker_id', $profileId)
                    ->limit(1)
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($jobPosting) {
                $jobPosting->is_applied = (bool) $jobPosting->is_applied;
                return $jobPosting;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'job_postings' => $jobPostings
            ]
        ], 200);
    }

    /**
     * Get single job posting by ID (public)
     */
    public function showById($id)
    {
        $jobPosting = JobPosting::with('company', 'qualifications')
            ->find($id);

        if (!$jobPosting) {
            return response()->json([
                'success' => false,
                'message' => 'Job posting not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_posting' => $jobPosting
            ]
        ], 200);
    }

    /**
     * Get all qualifications (untuk dropdown saat create/edit job posting)
     */
    public function getQualifications()
    {
        $qualifications = Qualification::all();

        return response()->json([
            'success' => true,
            'data' => [
                'qualifications' => $qualifications
            ]
        ], 200);
    }

    /**
     * Apply for a job posting (User/Job Seeker only)
     */
    public function applyJob(Request $request, $id)
    {
        $user = $request->user();
        
        // Check if user has profile
        $profile = $user->profile;
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile before applying for jobs'
            ], 403);
        }

        // Find job posting
        $jobPosting = JobPosting::find($id);
        
        if (!$jobPosting) {
            return response()->json([
                'success' => false,
                'message' => 'Job posting not found'
            ], 404);
        }

        // Check if job posting is still open
        if ($jobPosting->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'This job posting is no longer accepting applications'
            ], 400);
        }

        // Check if user already applied
        $existingApplication = \App\Models\Application::where('job_id', $id)
            ->where('seeker_id', $profile->id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job',
                'data' => [
                    'application' => $existingApplication
                ]
            ], 400);
        }

        // Create application
        $application = Application::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'job_id' => $id,
            'seeker_id' => $profile->id,
            'status' => 'pending',
        ]);

        $application->load('jobPosting.company', 'seeker');

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => [
                'application' => $application
            ]
        ], 201);
    }

    /**
     * Get user's applications (Job Seeker only)
     */
    public function myApplications(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        $applications = \App\Models\Application::where('seeker_id', $profile->id)
            ->with('jobPosting.company', 'jobPosting.qualifications')
            ->orderBy('applied_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications,
                'total_applications' => $applications->count()
            ]
        ], 200);
    }
}
