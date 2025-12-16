<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Profile;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Create or update profile for authenticated user
     */
    public function createOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'age' => 'nullable|integer|min:17|max:100',
            'bio' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf|max:5120',
            'avatar' => 'nullable|file|image|mimes:jpeg,png,jpg,gif',
        ], [
            'age.integer' => 'Umur harus berupa angka.',
            'age.min' => 'Umur minimal 17 tahun.',
            'age.max' => 'Umur maksimal 100 tahun.',
            'bio.string' => 'Bio harus berupa teks.',
            'cv.file' => 'CV harus berupa file.',
            'cv.mimes' => 'CV harus berformat PDF.',
            'cv.max' => 'Ukuran CV maksimal 5MB.',
            'avatar.image' => 'Avatar harus berupa gambar.',
            'avatar.mimes' => 'Avatar harus berformat JPEG, PNG, JPG, atau GIF.',
    
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Get existing profile to preserve old URLs if no new file uploaded
        $existingProfile = Profile::where('user_id', $user->id)->first();

        // Handle file uploads
        $cvUrl = $existingProfile->cv_url ?? null;
        $avatarUrl = $existingProfile->avatar_url ?? null;

        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if ($existingProfile && $existingProfile->cv_url) {
                $oldPath = str_replace('/storage/', '', $existingProfile->cv_url);
                Storage::disk('public')->delete($oldPath);
            }
            $cvPath = $request->file('cv')->store('profiles/' . $user->id . '/cv', 'public');
            $cvUrl = '/storage/' . $cvPath;
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($existingProfile && $existingProfile->avatar_url) {
                $oldPath = str_replace('/storage/', '', $existingProfile->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }
            $avatarPath = $request->file('avatar')->store('profiles/' . $user->id . '/avatar', 'public');
            $avatarUrl = '/storage/' . $avatarPath;
        }

        // Create or update profile
        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'age' => $request->age,
                'bio' => $request->bio,
                'cv_url' => $cvUrl,
                'avatar_url' => $avatarUrl,
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
        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found. Please complete your profile.'
            ], 404);
        }

        $totalApplied = $profile ? Application::where('seeker_id', $profile->id)->count() : 0;
        $totalActive = $profile ? Application::where('seeker_id', $profile->id)->where('status', 'pending')->count() : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile,
                'total_applied' => $totalApplied,
                'total_active' => $totalActive
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
