<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Application;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_companies' => Company::count(),
            'total_approved_companies' => Company::where('is_approved', true)->count(),
            'total_pending_companies' => Company::where('is_approved', false)->count(),
            'total_job_postings' => JobPosting::count(),
            'total_active_job_postings' => JobPosting::where('status', 'open')->count(),
            'total_applications' => Application::count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

    /**
     * Get all pending companies for approval
     */
    public function pendingCompanies()
    {
        $companies = User::where('role', 'company')
            ->where('is_approved', false)
            ->with('company')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies
            ]
        ], 200);
    }

    /**
     * Get all approved companies
     */
    public function approvedCompanies()
    {
        $companies = User::where('role', 'company')
            ->where('is_approved', true)
            ->with('company')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies
            ]
        ], 200);
    }

    /**
     * Approve company
     */
    public function approveCompany($userId)
    {
        $user = User::where('role', 'company')->find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $user->is_approved = true;
        $user->save();

        // Update company approval
        if ($user->company) {
            $company = $user->company;
            $company->is_approved = true;
            $company->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Company approved successfully',
            'data' => [
                'user' => $user->load('company')
            ]
        ], 200);
    }

    /**
     * Reject company
     */
    public function rejectCompany($userId)
    {
        $user = User::where('role', 'company')->find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $user->is_approved = false;
        $user->save();

        // Update company approval
        if ($user->company) {
            $company = $user->company;
            $company->is_approved = false;
            $company->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Company rejected',
            'data' => [
                'user' => $user->load('company')
            ]
        ], 200);
    }

    /**
     * Get all users
     */
    public function allUsers()
    {
        $users = User::where('role', 'user')
            ->with('profile')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ], 200);
    }

    /**
     * Get all job postings
     */
    public function allJobPostings()
    {
        $jobPostings = JobPosting::with('company.user', 'qualifications')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'job_postings' => $jobPostings
            ]
        ], 200);
    }

    /**
     * Get all applications
     */
    public function allApplications()
    {
        $applications = Application::with('seeker', 'jobPosting.company')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications
            ]
        ], 200);
    }
}
