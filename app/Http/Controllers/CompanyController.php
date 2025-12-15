<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Profile;

class CompanyController extends Controller
{
    /**
     * Create or update company details
     */
    public function createOrUpdate(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'photo_url' => 'nullable|url',
            'avatar_url' => 'nullable|url',
        ]);

        $user = $request->user();

        // Pastikan user adalah company
        if ($user->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only company accounts can create company details'
            ], 403);
        }

        

        // Create or update company
        $company = Company::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $request->company_name,
                'description' => $request->description,
                'address' => $request->address,
                'photo_url' => $request->photo_url,
            ]
        );

        // Update user approval status
        $user->is_approved = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Company details submitted successfully. Waiting for admin approval.',
            'data' => [
                'company' => $company
            ]
        ], 200);
    }

    /**
     * Get company details for authenticated company
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company details not found. Please complete your company details.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company
            ]
        ], 200);
    }

    /**
     * Get all approved companies (public endpoint)
     */
    public function index()
    {
        $companies = Company::where('is_approved', true)
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies
            ]
        ], 200);
    }

    /**
     * Get specific company by ID (public endpoint)
     */
    public function showById($id)
    {
        $company = Company::where('is_approved', true)
            ->with('user', 'jobPostings')
            ->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company
            ]
        ], 200);
    }
}
