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
                
                // Get user profile (created by Supabase trigger) and ensure correct token allocation
                $profile = $this->ensureUserProfileHasTokens($userId);
                
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
                
                // Get user profile and ensure correct token allocation
                $profile = $this->ensureUserProfileHasTokens($user['id']);
                
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
     * Helper: Consume user transformation (subtract 1 from generations_remaining)
     */
    private function consumeUserTransformation(string $userId)
    {
        try {
            // Get current profile
            $profile = $this->getUserProfile($userId);
            
            if (!$profile) {
                Log::error('User profile not found for transformation consumption', ['user_id' => $userId]);
                return false;
            }
            
            $currentGenerations = $profile['generations_remaining'] ?? 0;
            
            // Check if user has generations left
            if ($currentGenerations <= 0) {
                Log::info('User has no generations remaining', [
                    'user_id' => $userId,
                    'current_generations' => $currentGenerations
                ]);
                return false;
            }
            
            // Subtract 1 generation
            $newGenerations = $currentGenerations - 1;
            
            // Update user profile in Supabase
            $response = Http::withHeaders([
                'apikey' => $this->supabaseServiceKey,
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->patch($this->supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId, [
                'generations_remaining' => $newGenerations,
                'updated_at' => now()->toISOString()
            ]);

            if ($response->successful()) {
                Log::info('Generation consumed successfully', [
                    'user_id' => $userId,
                    'generations_before' => $currentGenerations,
                    'generations_after' => $newGenerations
                ]);
                
                // Return updated profile
                $updatedProfile = array_merge($profile, [
                    'generations_remaining' => $newGenerations
                ]);
                
                return [
                    'success' => true,
                    'profile' => $updatedProfile,
                    'generations_consumed' => 1,
                    'generations_remaining' => $newGenerations
                ];
            } else {
                Log::error('Failed to update generations_remaining', [
                    'user_id' => $userId,
                    'error' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Consume transformation exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Ensure user profile has correct token allocation (relies on Supabase trigger for creation)
     */
    private function ensureUserProfileHasTokens(string $userId)
    {
        try {
            // Wait a moment for Supabase trigger to create profile
            sleep(1);
            
            // Get the profile created by Supabase trigger
            $profile = $this->getUserProfile($userId);
            
            if (!$profile) {
                Log::error('Profile not found after registration', ['user_id' => $userId]);
                // Return default structure for frontend compatibility
                return [
                    'id' => $userId,
                    'is_premium' => false,
                    'tokens_remaining' => 10,
                    'generations_remaining' => 2,
                    'current_package' => 'free'
                ];
            }
            
            // Check if profile needs token allocation update
            $needsUpdate = false;
            $updateData = [];
            
            if (!isset($profile['tokens_remaining']) || $profile['tokens_remaining'] === 0) {
                $updateData['tokens_remaining'] = 10;
                $needsUpdate = true;
            }
            
            if (!isset($profile['generations_remaining']) || $profile['generations_remaining'] === 0) {
                $updateData['generations_remaining'] = 2;
                $needsUpdate = true;
            }
            
            if (!isset($profile['current_package'])) {
                $updateData['current_package'] = 'free';
                $needsUpdate = true;
            }
            
            // Update profile if needed
            if ($needsUpdate) {
                $response = Http::withHeaders([
                    'apikey' => $this->supabaseServiceKey,
                    'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                    'Content-Type' => 'application/json'
                ])->patch($this->supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId, $updateData);
                
                if ($response->successful()) {
                    Log::info('User profile tokens updated', [
                        'user_id' => $userId,
                        'updates' => $updateData
                    ]);
                    // Merge updates into profile
                    $profile = array_merge($profile, $updateData);
                } else {
                    Log::error('Failed to update user profile tokens', [
                        'user_id' => $userId,
                        'error' => $response->body()
                    ]);
                }
            }
            
            return $profile;
            
        } catch (\Exception $e) {
            Log::error('Ensure profile tokens exception', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            // Return default structure
            return [
                'id' => $userId,
                'is_premium' => false,
                'tokens_remaining' => 10,
                'generations_remaining' => 2,
                'current_package' => 'free'
            ];
        }
    }
}
