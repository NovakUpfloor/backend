<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\SubscriptionPackage;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Register new user with package selection
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'username' => 'required|string|max:32|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'package_id' => 'required|exists:subscription_packages,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get selected package
        $package = SubscriptionPackage::find($request->package_id);
        
        if (!$package || !$package->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected package is not available'
            ], 400);
        }

        // Create user
        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'package_id' => $request->package_id,
            'ads_quota' => $package->ads_quota ?? 100,
            'ads_used' => 0,
            'status' => 'pending',
            'akses_level' => 'User'
        ]);

        // Generate verification token
        $verificationToken = UserVerification::generateToken();
        
        UserVerification::create([
            'user_id' => $user->id_user,
            'token' => $verificationToken,
            'expired_at' => Carbon::now()->addHours(24)
        ]);

        // Send verification email
        try {
            Mail::to($user->email)->send(new VerificationEmail($user, $verificationToken));
        } catch (\Exception $e) {
            // Log email error but don't fail registration
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id_user,
            'action' => 'register',
            'description' => 'User registered with package: ' . $package->package_name,
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for verification.',
            'data' => [
                'user' => [
                    'id' => $user->id_user,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'package' => $package->package_name,
                    'status' => $user->status
                ]
            ]
        ], 201);
    }

    /**
     * Get available packages for registration
     */
    public function getPackages()
    {
        $packages = SubscriptionPackage::where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'package_name', 'package_code', 'ads_quota', 'price', 'description', 'features']);

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'device_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by username or email
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check user status
        if ($user->status == 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email first'
            ], 403);
        }

        if ($user->status == 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending approval from admin'
            ], 403);
        }

        if ($user->status == 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been rejected'
            ], 403);
        }

        // Update device token if provided
        if ($request->device_token) {
            $user->device_token = $request->device_token;
            $user->save();
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id_user,
            'action' => 'login',
            'description' => 'User logged in',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id_user,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'package' => $user->package->package_name ?? 'Free',
                    'ads_quota' => $user->ads_quota,
                    'ads_used' => $user->ads_used,
                    'status' => $user->status
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id_user,
            'action' => 'logout',
            'description' => 'User logged out',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}