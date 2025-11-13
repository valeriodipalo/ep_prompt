<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FalAIDirectController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\StripePaymentController;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'AI Image Generator API',
        'laravel_version' => app()->version()
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [SimpleAuthController::class, 'register']);
    Route::post('/login', [SimpleAuthController::class, 'login']);
    Route::get('/profile', [SimpleAuthController::class, 'getProfile']);
    Route::post('/consume-transformation', [SimpleAuthController::class, 'consumeTransformation']);
});

// AI transformation routes
Route::prefix('fal')->group(function () {
    Route::post('/direct-base64-transform', [FalAIDirectController::class, 'directTransform']);
});

// Payment routes
Route::prefix('payments')->group(function () {
    Route::post('/create-checkout-session', [StripePaymentController::class, 'createCheckoutSession']);
    Route::post('/webhook', [StripePaymentController::class, 'handleWebhook']);
    
    Route::get('/packages', function() {
        return response()->json([
            'success' => true,
            'packages' => [
                'starter' => [
                    'id' => 'starter',
                    'stripe_product_id' => 'prod_SxnNZ076SWbgPv',
                    'name' => 'Starter Package',
                    'price' => 2.49,
                    'generations' => 3,
                    'tokens' => 15,
                    'features' => ['All AI Features'],
                    'description' => 'Perfect for trying out',
                    'popular' => false
                ],
                'creator' => [
                    'id' => 'creator',
                    'stripe_product_id' => 'prod_Sxky4xnizZyXAB',
                    'name' => 'Creator Package',
                    'price' => 4.49,
                    'generations' => 10,
                    'tokens' => 50,
                    'features' => ['All AI Features', 'Priority Processing'],
                    'description' => 'Great for regular use',
                    'popular' => true
                ],
                'salon' => [
                    'id' => 'salon',
                    'stripe_product_id' => 'prod_Sxkzq5isxfAN6Y',
                    'name' => 'Pro Package',
                    'price' => 19.99,
                    'generations' => 80,
                    'tokens' => 400,
                    'features' => ['All AI Features', 'Priority Processing', 'White Labeling', 'Support'],
                    'description' => 'Professional solution',
                    'popular' => false
                ]
            ]
        ]);
    });
});
