<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FalAIController;
use App\Http\Controllers\SupabaseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint for Railway
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'StyleAI Professional API',
        'laravel_version' => app()->version()
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// fal.ai API routes (public for embedded widget)
Route::prefix('fal')->group(function () {
    // Test endpoints
    Route::get('/test', [FalAIController::class, 'test']);
    Route::get('/debug-fal', [FalAIController::class, 'debugFal']);
    
    Route::post('/transform-hairstyle', [FalAIController::class, 'transformHairstyle']);
    Route::get('/check-status', [FalAIController::class, 'checkStatus']);
    Route::get('/get-result', [FalAIController::class, 'getResult']);
    
    // Direct upload to fal.ai (backup method) - REDIRECT TO BASE64 METHOD
    Route::post('/direct-transform', [App\Http\Controllers\FalAIBase64Controller::class, 'transformWithBase64']);
    
    // Base64 transformation (no storage needed)
    Route::post('/base64-transform', [App\Http\Controllers\FalAIBase64Controller::class, 'transformWithBase64']);
    
    // BULLETPROOF transformation with multiple fallbacks
    Route::post('/robust-transform', [App\Http\Controllers\FalAIRobustController::class, 'robustTransform']);
    
    // SOLUTION 1: Direct Base64 Upload (SIMPLEST - NO STORAGE NEEDED)
    Route::post('/direct-base64-transform', [App\Http\Controllers\FalAIDirectController::class, 'directTransform']);
});

// Supabase storage routes (public for embedded widget)
Route::prefix('supabase')->group(function () {
    Route::post('/upload-image', [SupabaseController::class, 'uploadImage']);
    Route::get('/get-image', [SupabaseController::class, 'getImage']);
    Route::delete('/delete-image', [SupabaseController::class, 'deleteImage']);
});

// Simple Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\SimpleAuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\SimpleAuthController::class, 'login']);
    Route::get('/profile', [App\Http\Controllers\SimpleAuthController::class, 'getProfile']);
    Route::get('/can-transform', [App\Http\Controllers\SimpleAuthController::class, 'canTransform']);
    Route::post('/consume-transformation', [App\Http\Controllers\SimpleAuthController::class, 'consumeTransformation']);
    
    // Test route to check Supabase configuration
    Route::get('/test-supabase', function() {
        return response()->json([
            'supabase_url' => env('SUPABASE_URL') ? 'configured' : 'missing',
            'supabase_anon_key' => env('SUPABASE_ANON_KEY') ? 'configured' : 'missing', 
            'supabase_service_key' => env('SUPABASE_SERVICE_KEY') ? 'configured' : 'missing',
            'timestamp' => now()
        ]);
    });
    
    // Test token update directly in Supabase
    Route::post('/test-token-update', function(Request $request) {
        try {
            $request->validate([
                'user_email' => 'required|email',
                'tokens' => 'required|integer'
            ]);
            
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseServiceKey = env('SUPABASE_SERVICE_KEY');
            
            if (!$supabaseUrl || !$supabaseServiceKey) {
                return response()->json(['error' => 'Supabase not configured'], 500);
            }
            
            // Test direct token update
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey,
                'Prefer' => 'return=minimal'
            ])->patch($supabaseUrl . '/rest/v1/user_profiles?email=eq.' . $request->user_email, [
                'tokens_remaining' => $request->tokens,
                'updated_at' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'tokens_set' => $request->tokens,
                'user_email' => $request->user_email
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token update test failed: ' . $e->getMessage()
            ], 500);
        }
    });
});

// Payment routes (public for webhooks, but most require auth)
Route::prefix('payments')->group(function () {
    Route::post('/create-checkout-session', [App\Http\Controllers\StripePaymentController::class, 'createCheckoutSession']);
    Route::get('/status', [App\Http\Controllers\StripePaymentController::class, 'checkPaymentStatus']);
    Route::post('/webhook', [App\Http\Controllers\StripePaymentController::class, 'handleWebhook']);
    
    // Get pricing packages
    Route::get('/packages', function() {
        return response()->json([
            'success' => true,
            'packages' => [
                'starter' => [
                    'id' => 'starter',
                    'stripe_product_id' => 'prod_SxnNZ076SWbgPv',
                    'name' => 'StyleAI Starter',
                    'price' => 2.49,
                    'generations' => 3,
                    'tokens' => 15,
                    'features' => ['Premium Styles', 'Premium Colors'],
                    'description' => 'Perfect for trying premium features',
                    'popular' => false
                ],
                'creator' => [
                    'id' => 'creator',
                    'stripe_product_id' => 'prod_Sxky4xnizZyXAB',
                    'name' => 'StyleAI Creator',
                    'price' => 4.49,
                    'generations' => 10,
                    'tokens' => 50,
                    'features' => ['Premium Styles', 'Premium Colors'],
                    'description' => 'Great for regular styling needs',
                    'popular' => true
                ],
                'salon' => [
                    'id' => 'salon',
                    'stripe_product_id' => 'prod_Sxkzq5isxfAN6Y',
                    'name' => 'StyleAI Salon Package',
                    'price' => 19.99,
                    'generations' => 80,
                    'tokens' => 400,
                    'features' => ['Premium Styles', 'Premium Colors', 'White Labeling', 'Custom Integration Support'],
                    'description' => 'Professional salon solution',
                    'popular' => false
                ]
            ]
        ]);
    });
    
    // Test endpoint to verify webhook configuration
    Route::get('/webhook-test', function() {
        return response()->json([
            'webhook_url' => url('/api/payments/webhook'),
            'stripe_configured' => !empty(env('STRIPE_SECRET')),
            'stripe_secret_prefix' => env('STRIPE_SECRET') ? substr(env('STRIPE_SECRET'), 0, 7) : 'missing',
            'webhook_secret_configured' => !empty(env('STRIPE_WEBHOOK_SECRET')),
            'webhook_secret_prefix' => env('STRIPE_WEBHOOK_SECRET') ? substr(env('STRIPE_WEBHOOK_SECRET'), 0, 7) : 'missing',
            'app_env' => env('APP_ENV'),
            'timestamp' => now()->toISOString()
        ]);
    });
    
    // Debug endpoint to manually test user upgrade
    Route::post('/debug-upgrade', function(Request $request) {
        try {
            $request->validate([
                'user_email' => 'required|email',
                'package' => 'required|string'
            ]);
            
            $controller = new App\Http\Controllers\StripePaymentController();
            $method = new ReflectionMethod($controller, 'processPackagePurchase');
            $method->setAccessible(true);
            
            $generations = [
                'starter' => 3,
                'creator' => 10, 
                'salon' => 80
            ][$request->package] ?? 0;
            
            $method->invoke($controller, 'debug-user-id', $request->user_email, $request->package, $generations, 'debug-session-id');
            
            return response()->json([
                'success' => true,
                'message' => 'Debug upgrade completed',
                'package' => $request->package,
                'generations' => $generations
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug upgrade failed: ' . $e->getMessage()
            ], 500);
        }
    });
});

// Style and content routes (public for embedded widget)
Route::prefix('styles')->group(function () {
    Route::get('/hairstyles', [App\Http\Controllers\StyleController::class, 'getStyles']);
    Route::get('/hairstyles/{styleId}', [App\Http\Controllers\StyleController::class, 'getStyleById']);
    Route::get('/colors', [App\Http\Controllers\StyleController::class, 'getColors']);
    Route::get('/colors/{colorId}', [App\Http\Controllers\StyleController::class, 'getColorById']);
    Route::get('/subscription-plans', [App\Http\Controllers\StyleController::class, 'getSubscriptionPlans']);
});

// Token management routes
Route::prefix('tokens')->group(function () {
    // Deduct tokens for generation (5 tokens per generation)
    Route::post('/deduct', function(Request $request) {
        try {
            $request->validate([
                'user_id' => 'required|string',
                'tokens_to_deduct' => 'integer|min:1|max:100'
            ]);

            $userId = $request->user_id;
            $tokensToDeduct = $request->tokens_to_deduct ?? 5; // Default 5 tokens per generation

            $supabaseUrl = env('SUPABASE_URL');
            $supabaseServiceKey = env('SUPABASE_SERVICE_KEY');

            if (!$supabaseUrl || !$supabaseServiceKey) {
                return response()->json(['error' => 'Supabase configuration missing'], 500);
            }

            // First, get current user profile to check available tokens
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey
            ])->get($supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId);

            if (!$userResponse->successful() || empty($userResponse->json())) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $userProfile = $userResponse->json()[0];
            $currentTokens = $userProfile['tokens_remaining'] ?? 0;
            $currentGenerations = $userProfile['generations_remaining'] ?? 0;

            // Check if user has enough tokens
            if ($currentTokens < $tokensToDeduct) {
                return response()->json([
                    'error' => 'Insufficient tokens',
                    'current_tokens' => $currentTokens,
                    'required_tokens' => $tokensToDeduct
                ], 400);
            }

            $newTokens = $currentTokens - $tokensToDeduct;
            $newGenerations = max(0, $currentGenerations - 1); // Also deduct 1 generation

            // Update user tokens in Supabase
            $updateResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey,
                'Prefer' => 'return=minimal'
            ])->patch($supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId, [
                'tokens_remaining' => $newTokens,
                'generations_remaining' => $newGenerations,
                'updated_at' => now()->toISOString()
            ]);

            if ($updateResponse->successful()) {
                Log::info('Tokens deducted successfully', [
                    'user_id' => $userId,
                    'tokens_deducted' => $tokensToDeduct,
                    'tokens_before' => $currentTokens,
                    'tokens_after' => $newTokens,
                    'generations_remaining' => $newGenerations
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tokens deducted successfully',
                    'tokens_deducted' => $tokensToDeduct,
                    'tokens_remaining' => $newTokens,
                    'generations_remaining' => $newGenerations
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to deduct tokens',
                    'details' => $updateResponse->body()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Token deduction failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'Token deduction failed',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // Debug endpoint to check user ID format
    Route::get('/debug/{userId}', function($userId) {
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseServiceKey = env('SUPABASE_SERVICE_KEY');

            if (!$supabaseUrl || !$supabaseServiceKey) {
                return response()->json(['error' => 'Supabase configuration missing'], 500);
            }

            // Try to find user by ID
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey
            ])->get($supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId);

            return response()->json([
                'debug_info' => [
                    'provided_user_id' => $userId,
                    'supabase_query' => $supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId,
                    'response_status' => $userResponse->status(),
                    'response_body' => $userResponse->json(),
                    'found_users' => count($userResponse->json() ?? [])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug failed',
                'message' => $e->getMessage(),
                'user_id' => $userId
            ], 500);
        }
    });

    // Check token balance
    Route::get('/balance/{userId}', function($userId) {
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseServiceKey = env('SUPABASE_SERVICE_KEY');

            if (!$supabaseUrl || !$supabaseServiceKey) {
                return response()->json(['error' => 'Supabase configuration missing'], 500);
            }

            // Get current user profile
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey
            ])->get($supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId);

            if (!$userResponse->successful() || empty($userResponse->json())) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $userProfile = $userResponse->json()[0];

            return response()->json([
                'success' => true,
                'tokens_remaining' => $userProfile['tokens_remaining'] ?? 0,
                'generations_remaining' => $userProfile['generations_remaining'] ?? 0,
                'current_package' => $userProfile['current_package'] ?? null,
                'is_premium' => $userProfile['is_premium'] ?? false
            ]);
        } catch (\Exception $e) {
            Log::error('Token balance check failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return response()->json([
                'error' => 'Token balance check failed',
                'message' => $e->getMessage()
            ], 500);
        }
    });
});
