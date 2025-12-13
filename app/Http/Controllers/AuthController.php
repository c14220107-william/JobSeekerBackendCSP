<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register user baru (job seeker)
     */
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'full_name' => 'required|string|max:255'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_approved' => true, 
        ]);

        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Please complete your profile.',
            'data' => [
                'user' => $user,
                'profile' => $profile,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'next_step' => 'complete_profile',
            ]
        ], 201);
    }

    /**
     * Register company baru
     */
    public function registerCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'photo_url' => 'nullable|url',
            'avatar_url' => 'nullable|url',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'company',
            'is_approved' => false, // Company perlu approval dari admin
        ]);

        

        $company = Company::create([
            'user_id' => $user->id,
            'name' => $request->company_name,
            'description' => $request->description,
            'address' => $request->address,
            'photo_url' => $request->photo_url,
            'is_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company registered successfully. Your account is pending admin approval. You will be able to login once approved.',
            'data' => [
                'user' => $user,
                'company' => $company,
                'status' => 'pending_approval',
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        

        // Cek approval untuk company
        if ($user->role === 'company' && !$user->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Your company account is pending approval from admin. Please wait for approval.',
                'data' => [
                    'is_approved' => false,
                    'role' => 'company'
                ]
            ], 403);
        }
        

        $token = $user->createToken('auth_token')->plainTextToken;

       
        // Load relasi sesuai role
        $userData = $user;
        if ($user->role === 'user') {
            $userData = $user->load('profile');
        } elseif ($user->role === 'company') {
            $userData = $user->load('company');
        }
       

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $userData,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->role,
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Load relasi sesuai role
        if ($user->role === 'user' || $user->role === 'company') {
            $user->load('profile');
            
            if ($user->role === 'company') {
                $user->load('profile.company');
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ], 200);
    }
}
