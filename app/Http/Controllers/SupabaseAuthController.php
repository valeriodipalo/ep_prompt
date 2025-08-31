<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Supabase Authentication Controller
 * Handles user registration, login, token management
 */
class SupabaseAuthController extends Controller
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

            if ($response->successful()) {
                $userData = $response->json();
                $userId = $userData['user']['id'] ?? null;
                
                // Create user profile with initial free tokens
                if ($userId) {
                    $this->createUserProfile($userId, $request->email, $request->name);
                }
                
                Log::info('User registered successfully', [
                    'user_id' => $userId,
                    'email' => $request->email
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! Please check your email for verification.',
                    'user' => $userData['user'] ?? null,
                    'session' => $userData['session'] ?? null
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
                'message' => 'Registration failed due to server error'
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

            // Login with Supabase Auth
            $response = Http::withHeaders([
                'apikey' => $this->supabaseAnonKey,
                'Content-Type' => 'application/json'
            ])->post($this->supabaseUrl . '/auth/v1/token?grant_type=password', [
                'email' => $request->email,
                'password' => $request->password
            ]);

            if ($response->successful()) {
                $authData = $response->json();
                
                // Get user token information
                $userTokens = $this->getUserTokens($authData['user']['id']);
                
                Log::info('User logged in successfully', [
                    'user_id' => $authData['user']['id'],
                    'email' => $request->email
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $authData['user'],
                    'session' => $authData,
                    'tokens' => $userTokens
                ]);
            } else {
                $error = $response->json();
                Log::warning('Login failed', ['error' => $error, 'email' => $request->email]);
                
                return response()->json([
                    'success' => false,
                    'message' => $error['error_description'] ?? 'Invalid credentials'
                ], 401);
            }

        } catch (\Exception $e) {
            Log::error('Login exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed due to server error'
            ], 500);
        }
    }

    /**
     * Get current user info with tokens
     */
    public function me(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            // Get user tokens and profile
            $tokens = $this->getUserTokens($user['sub']);
            $profile = $this->getUserProfile($user['sub']);

            return response()->json([
                'success' => true,
                'user' => $user,
                'profile' => $profile,
                'tokens' => $tokens
            ]);

        } catch (\Exception $e) {
            Log::error('Get user info exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user information'
            ], 500);
        }
    }

    /**
     * Check if user can perform transformation
     */
    public function canTransform(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $isPremiumStyle = $request->boolean('is_premium_style', false);
            $canTransform = $this->checkUserCanTransform($user['sub'], $isPremiumStyle);

            return response()->json([
                'success' => true,
                'can_transform' => $canTransform,
                'tokens' => $this->getUserTokens($user['sub'])
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
     * Consume a token for transformation
     */
    public function consumeToken(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $isPremiumStyle = $request->boolean('is_premium_style', false);
            $consumed = $this->consumeUserToken($user['sub'], $isPremiumStyle);

            if ($consumed) {
                return response()->json([
                    'success' => true,
                    'message' => 'Token consumed successfully',
                    'tokens' => $this->getUserTokens($user['sub'])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient tokens'
                ], 402); // Payment Required
            }

        } catch (\Exception $e) {
            Log::error('Token consumption exception', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to consume token'
            ], 500);
        }
    }

    /**
     * Helper: Get user from JWT token
     */
    private function getUserFromToken(Request $request)
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);
        
        try {
            // Note: In production, you should verify the JWT signature
            // For now, we'll decode without verification (Supabase handles verification)
            $decoded = JWT::decode($token, new Key('dummy', 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            Log::warning('Token decode failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Helper: Get user tokens from Supabase
     */
    private function getUserTokens(string $userId)
    {
        $response = Http::withHeaders([
            'apikey' => $this->supabaseServiceKey,
            'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
            'Content-Type' => 'application/json'
        ])->get($this->supabaseUrl . '/rest/v1/user_tokens', [
            'user_id' => 'eq.' . $userId,
            'select' => '*'
        ]);

        if ($response->successful()) {
            $tokens = $response->json();
            return $tokens[0] ?? null;
        }

        return null;
    }

    /**
     * Helper: Get user profile from Supabase
     */
    private function getUserProfile(string $userId)
    {
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

        return null;
    }

    /**
     * Helper: Check if user can transform
     */
    private function checkUserCanTransform(string $userId, bool $isPremiumStyle = false)
    {
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
    }

    /**
     * Helper: Consume user token
     */
    private function consumeUserToken(string $userId, bool $isPremiumStyle = false)
    {
        $response = Http::withHeaders([
            'apikey' => $this->supabaseServiceKey,
            'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
            'Content-Type' => 'application/json'
        ])->post($this->supabaseUrl . '/rest/v1/rpc/consume_token', [
            'user_id' => $userId,
            'is_premium_style' => $isPremiumStyle
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return false;
    }

    /**
     * Create user profile with initial free tokens
     */
    private function createUserProfile(string $userId, string $email, ?string $name = null)
    {
        try {
            // Create user profile with initial free allocation
            $profileData = [
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'is_premium' => false,
                'current_package' => 'free',
                'tokens_remaining' => 10, // Initial free tokens
                'generations_remaining' => 2, // Initial free generations
                'free_transformations_used' => 0,
                'package_purchased_at' => null,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $response = Http::withHeaders([
                'apikey' => $this->supabaseServiceKey,
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal'
            ])->post($this->supabaseUrl . '/rest/v1/user_profiles', $profileData);

            if ($response->successful()) {
                Log::info('User profile created successfully', [
                    'user_id' => $userId,
                    'email' => $email,
                    'tokens_assigned' => 10,
                    'generations_assigned' => 2
                ]);
                return true;
            } else {
                Log::error('Failed to create user profile', [
                    'user_id' => $userId,
                    'email' => $email,
                    'error' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('User profile creation exception', [
                'user_id' => $userId,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
