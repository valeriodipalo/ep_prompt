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
});

// Payment routes (public for webhooks, but most require auth)
Route::prefix('payments')->group(function () {
    Route::post('/create-checkout-session', [App\Http\Controllers\StripePaymentController::class, 'createCheckoutSession']);
    Route::get('/status', [App\Http\Controllers\StripePaymentController::class, 'checkPaymentStatus']);
    Route::post('/webhook', [App\Http\Controllers\StripePaymentController::class, 'handleWebhook']);
});

// Style and content routes (public for embedded widget)
Route::prefix('styles')->group(function () {
    Route::get('/hairstyles', [App\Http\Controllers\StyleController::class, 'getStyles']);
    Route::get('/hairstyles/{styleId}', [App\Http\Controllers\StyleController::class, 'getStyleById']);
    Route::get('/colors', [App\Http\Controllers\StyleController::class, 'getColors']);
    Route::get('/colors/{colorId}', [App\Http\Controllers\StyleController::class, 'getColorById']);
    Route::get('/subscription-plans', [App\Http\Controllers\StyleController::class, 'getSubscriptionPlans']);
});
