<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Simple Authentication Controller
 * Handles user registration, login, and premium status
 * One-time payment model (no subscriptions)
 */
class SimpleAuthController extends Controller
{
    private string $supabaseUrl;
    private string $supabaseAnonKey;
    private string $supabaseServiceKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseAnonKey = env('SUPABASE_ANON_KEY');
        $this->supabaseServiceKey = env('SUPABASE_SERVICE_KEY');
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
                'name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('Attempting user registration', ['email' => $request->email]);

            // Register user with Supabase Auth
            $response = Http::withHeaders([
                'apikey' => $this->supabaseAnonKey,
                'Content-Type' => 'application/json'
            ])->post($this->supabaseUrl . '/auth/v1/signup', [
                'email' => $request->email,
                'password' => $request->password,
                'data' => [
                    'name' => $request->name ?? ''
                ]
            ]);

            Log::info('Supabase registration response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $userData = $response->json();
                Log::info('Supabase registration success response', ['data' => $userData]);
                
                // Supabase returns user data directly (not nested under 'user' key)
                $user = $userData; // The entire response IS the user data
                $userId = $user['id'] ?? $user['user_id'] ?? null;
                
                if (!$userId) {
                    Log::error('No user ID in registration response', ['response' => $userData]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Registration succeeded but user ID missing.'
                    ], 500);
                }
                
                // Check if email confirmation is required
                if (isset($user['confirmation_sent_at']) && !($user['email_verified'] ?? false)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Registration successful! Please check your email to verify your account before logging in.',
                        'requires_confirmation' => true,
                        'user' => $user
                    ]);
                }
                
                // Create user profile with initial free tokens
                $profile = $this->createOrGetUserProfile($userId, $request->email, $request->name);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! You have 2 free generations (10 tokens).',
                    'user' => $user,
                    'profile' => $profile
                ]);
            } else {
                $error = $response->json();
                Log::error('Registration failed', ['error' => $error]);
                
                return response()->json([
                    'success' => false,
                    'message' => $error['error_description'] ?? 'Registration failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Registration exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('Attempting user login', ['email' => $request->email]);

            // Login with Supabase Auth
            $response = Http::withHeaders([
                'apikey' => $this->supabaseAnonKey,
                'Content-Type' => 'application/json'
            ])->post($this->supabaseUrl . '/auth/v1/token?grant_type=password', [
                'email' => $request->email,
                'password' => $request->password
            ]);

            Log::info('Supabase login response', [
                'status' => $response->status()
            ]);

            if ($response->successful()) {
                $authData = $response->json();
                Log::info('Supabase login success response', ['data' => $authData]);
                
                // Handle different response structures
                $user = $authData['user'] ?? null;
                $session = $authData['session'] ?? $authData;
                
                if (!$user) {
                    Log::error('No user in login response', ['response' => $authData]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Login succeeded but user data missing.'
                    ], 500);
                }
                
                // Get or create user profile with transformation count
                $profile = $this->createOrGetUserProfile($user['id'], $user['email'], $user['user_metadata']['name'] ?? null);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user,
                    'session' => $session,
                    'profile' => $profile
                ]);
            } else {
                $error = $response->json();
                Log::warning('Login failed', ['error' => $error]);
                
                // Handle specific error cases
                $message = 'Invalid email or password';
                if (isset($error['error_code'])) {
                    switch ($error['error_code']) {
                        case 'email_not_confirmed':
                            $message = 'Please check your email and click the verification link before logging in.';
                            break;
                        case 'invalid_credentials':
                            $message = 'Invalid email or password.';
                            break;
                        case 'email_address_invalid':
                            $message = 'Please enter a valid email address.';
                            break;
                        default:
                            $message = $error['msg'] ?? $message;
                    }
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => $error['error_code'] ?? null
                ], 401);
            }

        } catch (\Exception $e) {
            Log::error('Login exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can perform transformation
     */
    public function canTransform(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $isPremiumStyle = $request->boolean('is_premium_style', false);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID required'
                ], 400);
            }

            $canTransform = $this->checkUserCanTransform($userId, $isPremiumStyle);
            $profile = $this->getUserProfile($userId);

            return response()->json([
                'success' => true,
                'can_transform' => $canTransform,
                'profile' => $profile,
                'remaining_free' => max(0, 10 - ($profile['free_transformations_used'] ?? 0))
            ]);

        } catch (\Exception $e) {
            Log::error('Can transform check exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check transformation eligibility'
            ], 500);
        }
    }

    /**
     * Consume a transformation
     */
    public function consumeTransformation(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID required'
                ], 400);
            }

            $consumed = $this->consumeUserTransformation($userId);
            $profile = $this->getUserProfile($userId);

            if ($consumed) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transformation consumed',
                    'profile' => $profile,
                    'remaining_free' => max(0, 10 - ($profile['free_transformations_used'] ?? 0))
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No transformations remaining. Upgrade to premium for unlimited access!',
                    'profile' => $profile
                ], 402); // Payment Required
            }

        } catch (\Exception $e) {
            Log::error('Transformation consumption exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to consume transformation'
            ], 500);
        }
    }

    /**
     * Get user profile info
     */
    public function getProfile(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID required'
                ], 400);
            }

            $profile = $this->getUserProfile($userId);
            
            if ($profile) {
                return response()->json([
                    'success' => true,
                    'profile' => $profile,
                    'remaining_free' => max(0, 10 - ($profile['free_transformations_used'] ?? 0))
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Get profile exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile'
            ], 500);
        }
    }

    /**
     * Helper: Get user profile from Supabase
     */
    private function getUserProfile(string $userId)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseServiceKey,
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->get($this->supabaseUrl . '/rest/v1/user_profiles', [
                'id' => 'eq.' . $userId,
                'select' => '*'
            ]);

            if ($response->successful()) {
                $profiles = $response->json();
                return $profiles[0] ?? null;
            }

            Log::error('Failed to get user profile', [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Get user profile exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Helper: Check if user can transform
     */
    private function checkUserCanTransform(string $userId, bool $isPremiumStyle = false)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseServiceKey,
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->post($this->supabaseUrl . '/rest/v1/rpc/can_user_transform', [
                'user_id' => $userId,
                'is_premium_style' => $isPremiumStyle
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Check can transform exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Helper: Consume user transformation
     */
    private function consumeUserTransformation(string $userId)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseServiceKey,
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->post($this->supabaseUrl . '/rest/v1/rpc/consume_transformation', [
                'user_id' => $userId
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Consume transformation exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create or get user profile with initial tokens
     */
    private function createOrGetUserProfile(string $userId, string $email, ?string $name = null)
    {
        try {
            // First, try to get existing profile
            $existingProfile = $this->getUserProfile($userId);
            
            if ($existingProfile) {
                Log::info('User profile already exists', ['user_id' => $userId]);
                return $existingProfile;
            }
            
            // Create new user profile with initial free allocation
            $profileData = [
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'is_premium' => false,
                'current_package' => 'free',
                'tokens_remaining' => 10, // Initial free tokens
                'generations_remaining' => 2, // Initial free generations
                'free_transformations_used' => 0, // Legacy field for compatibility
                'package_purchased_at' => null,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $response = Http::withHeaders([
                'apikey' => $this->supabaseServiceKey,
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($this->supabaseUrl . '/rest/v1/user_profiles', $profileData);

            if ($response->successful()) {
                $createdProfile = $response->json();
                Log::info('User profile created successfully', [
                    'user_id' => $userId,
                    'email' => $email,
                    'tokens_assigned' => 10,
                    'generations_assigned' => 2
                ]);
                return $createdProfile[0] ?? $profileData;
            } else {
                Log::error('Failed to create user profile', [
                    'user_id' => $userId,
                    'email' => $email,
                    'error' => $response->body()
                ]);
                // Return default profile structure even if creation failed
                return $profileData;
            }
        } catch (\Exception $e) {
            Log::error('User profile creation exception', [
                'user_id' => $userId,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            // Return default profile structure
            return [
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'is_premium' => false,
                'tokens_remaining' => 10,
                'generations_remaining' => 2,
                'free_transformations_used' => 0
            ];
        }
    }
}
