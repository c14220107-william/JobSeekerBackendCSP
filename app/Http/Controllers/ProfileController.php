<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Create or update profile for authenticated user
     */
    public function createOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string|max:255',
            'age' => 'nullable|integer',
            'bio' => 'nullable|string',
            'cv_url' => 'nullable|url',
            'avatar_url' => 'nullable|url',
        ], [
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'age.integer' => 'Umur harus berupa angka.',
            'bio.string' => 'Bio harus berupa teks.',
            'cv_url.url' => 'Format URL CV tidak valid.',
            'avatar_url.url' => 'Format URL avatar tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        

        $user = $request->user();

        // Cek apakah profile sudah ada
        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $request->full_name,
                'age' => $request->age,
                'bio' => $request->bio,
                'cv_url' => $request->cv_url,
                'avatar_url' => $request->avatar_url,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile saved successfully',
            'data' => [
                'profile' => $profile
            ]
        ], 200);
    }

    /**
     * Get profile for authenticated user
     */
    public function show(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found. Please complete your profile.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile
            ]
        ], 200);
    }

    /**
     * Delete profile
     */
    public function destroy(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully'
        ], 200);
    }
}
