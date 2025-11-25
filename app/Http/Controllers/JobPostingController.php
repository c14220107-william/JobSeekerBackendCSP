<?php

namespace App\Http\Controllers;

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

    /**
     * Get single job posting milik company
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $company = $user->company;

        $jobPosting = JobPosting::where('company_id', $company->id)
            ->with('qualifications', 'applications.seeker')
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
}
