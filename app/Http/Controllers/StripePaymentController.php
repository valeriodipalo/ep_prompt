<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class StripePaymentController extends Controller
{
    /**
     * Create a Stripe Checkout session for premium upgrade
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|string',
                'user_email' => 'required|email',
                'success_url' => 'required|url',
                'cancel_url' => 'required|url',
                'package' => 'required|string|in:starter,creator,salon'
            ]);

            $stripeSecretKey = env('STRIPE_SECRET');
            
            if (!$stripeSecretKey) {
                return response()->json([
                    'error' => 'Stripe configuration missing'
                ], 500);
            }

            // Define pricing packages with your Stripe product IDs
            $packages = [
                'starter' => [
                    'stripe_product_id' => 'prod_SxnNZ076SWbgPv',
                    'name' => 'StyleAI Starter',
                    'description' => '3 generations + unlock premium styles and colors',
                    'price' => 249, // $2.49 in cents
                    'generations' => 3,
                    'tokens' => 15,
                    'features' => ['Premium Styles', 'Premium Colors']
                ],
                'creator' => [
                    'stripe_product_id' => 'prod_Sxky4xnizZyXAB',
                    'name' => 'StyleAI Creator',
                    'description' => '10 generations + premium styles and colors',
                    'price' => 449, // $4.49 in cents
                    'generations' => 10,
                    'tokens' => 50,
                    'features' => ['Premium Styles', 'Premium Colors']
                ],
                'salon' => [
                    'stripe_product_id' => 'prod_Sxkzq5isxfAN6Y',
                    'name' => 'StyleAI Salon Package',
                    'description' => '80 generations + premium features + white labeling + custom integration',
                    'price' => 1999, // $19.99 in cents
                    'generations' => 80,
                    'tokens' => 400,
                    'features' => ['Premium Styles', 'Premium Colors', 'White Labeling', 'Custom Integration Support']
                ]
            ];

            $selectedPackage = $packages[$request->package];
            if (!$selectedPackage) {
                return response()->json(['error' => 'Invalid package selected'], 400);
            }

            // Create Stripe Checkout Session
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $stripeSecretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types[0]' => 'card',
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][product]' => $selectedPackage['stripe_product_id'],
                'line_items[0][price_data][unit_amount]' => $selectedPackage['price'],
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => $request->success_url,
                'cancel_url' => $request->cancel_url,
                'customer_email' => $request->user_email,
                'metadata[user_id]' => $request->user_id,
                'metadata[package]' => $request->package,
                'metadata[generations]' => $selectedPackage['generations'],
                'metadata[tokens]' => $selectedPackage['tokens'],
                'metadata[stripe_product_id]' => $selectedPackage['stripe_product_id'],
            ]);

            if ($response->successful()) {
                $session = $response->json();
                
                Log::info('Stripe checkout session created', [
                    'session_id' => $session['id'],
                    'user_id' => $request->user_id,
                    'user_email' => $request->user_email
                ]);

                return response()->json([
                    'success' => true,
                    'checkout_url' => $session['url'],
                    'session_id' => $session['id']
                ]);
            } else {
                Log::error('Stripe API error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Failed to create payment session'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Payment session creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Payment processing error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Stripe webhook for payment success
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('Stripe-Signature');
            $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

            if (!$webhookSecret) {
                Log::error('Stripe webhook secret not configured');
                return response()->json(['error' => 'Webhook not configured'], 400);
            }

            // Verify webhook signature (Stripe format)
            $elements = explode(',', $signature);
            $signatureData = [];
            
            foreach ($elements as $element) {
                $parts = explode('=', $element, 2);
                if (count($parts) === 2) {
                    $signatureData[$parts[0]] = $parts[1];
                }
            }
            
            if (!isset($signatureData['v1'])) {
                Log::error('No v1 signature found');
                return response()->json(['error' => 'Invalid signature format'], 400);
            }
            
            $timestamp = $signatureData['t'] ?? '';
            $expectedSignature = $signatureData['v1'];
            $signedPayload = $timestamp . '.' . $payload;
            $computedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

            if (!hash_equals($computedSignature, $expectedSignature)) {
                Log::error('Invalid webhook signature', [
                    'expected' => $expectedSignature,
                    'computed' => $computedSignature
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $event = json_decode($payload, true);

            if ($event['type'] === 'checkout.session.completed') {
                $session = $event['data']['object'];
                $userId = $session['metadata']['user_id'] ?? null;
                $userEmail = $session['customer_email'] ?? null;
                $package = $session['metadata']['package'] ?? null;
                $generations = (int)($session['metadata']['generations'] ?? 0);

                if ($userId && $userEmail && $package) {
                    // Process package purchase
                    $this->processPackagePurchase($userId, $userEmail, $package, $generations, $session['id']);
                    
                    Log::info('Package purchase completed', [
                        'user_id' => $userId,
                        'user_email' => $userEmail,
                        'package' => $package,
                        'generations' => $generations,
                        'session_id' => $session['id']
                    ]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process package purchase and update user in Supabase
     */
    private function processPackagePurchase(string $userId, string $userEmail, string $package, int $generations, string $sessionId): void
    {
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseServiceKey = env('SUPABASE_SERVICE_KEY');

            if (!$supabaseUrl || !$supabaseServiceKey) {
                throw new \Exception('Supabase configuration missing');
            }

            // Update user profile with package data
            // All packages get premium access to styles/colors, but different generation limits
            $updateData = [
                'is_premium' => true, // All paid packages get premium access
                'current_package' => $package,
                'generations_remaining' => $generations,
                'tokens_remaining' => $this->getTokensForPackage($package),
                'package_purchased_at' => now()->toISOString()
            ];

            // Try to update by user ID first, then by email as fallback
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey,
                'Prefer' => 'return=minimal'
            ])->patch($supabaseUrl . '/rest/v1/user_profiles?id=eq.' . $userId, $updateData);

            // If user ID update failed, try email
            if (!$response->successful()) {
                Log::warning('User ID update failed, trying email', ['user_id' => $userId, 'email' => $userEmail]);
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $supabaseServiceKey,
                    'Content-Type' => 'application/json',
                    'apikey' => $supabaseServiceKey,
                    'Prefer' => 'return=minimal'
                ])->patch($supabaseUrl . '/rest/v1/user_profiles?email=eq.' . $userEmail, $updateData);
            }

            if (!$response->successful()) {
                Log::error('Both user profile update attempts failed', [
                    'user_id' => $userId,
                    'email' => $userEmail,
                    'package' => $package,
                    'response_body' => $response->body(),
                    'response_status' => $response->status()
                ]);
                throw new \Exception('Failed to update user profile: ' . $response->body());
            }

            Log::info('User profile updated successfully', [
                'user_id' => $userId,
                'email' => $userEmail,
                'package' => $package,
                'generations' => $generations,
                'tokens' => $this->getTokensForPackage($package)
            ]);

            // Get package info for payment record
            $packages = [
                'starter' => ['name' => 'StyleAI Starter', 'price' => 249, 'generations' => 3],
                'creator' => ['name' => 'StyleAI Creator', 'price' => 449, 'generations' => 10],
                'salon' => ['name' => 'StyleAI Salon Package', 'price' => 1999, 'generations' => 100]
            ];
            $packageInfo = $packages[$package] ?? ['price' => 0];

            // Record payment in payments table
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey
            ])->post($supabaseUrl . '/rest/v1/payments', [
                'user_id' => $userId,
                'stripe_session_id' => $sessionId,
                'package_type' => $package,
                'generations_purchased' => $generations,
                'amount' => $packageInfo['price'],
                'currency' => 'usd',
                'status' => 'completed',
                'created_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process package purchase', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'package' => $package,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->query('session_id');
            
            if (!$sessionId) {
                return response()->json(['error' => 'Session ID required'], 400);
            }

            $stripeSecretKey = env('STRIPE_SECRET');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $stripeSecretKey,
            ])->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

            if ($response->successful()) {
                $session = $response->json();
                
                return response()->json([
                    'success' => true,
                    'payment_status' => $session['payment_status'],
                    'status' => $session['status']
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to check payment status'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get token count for package
     */
    private function getTokensForPackage(string $package): int
    {
        $tokenMap = [
            'starter' => 15,  // 3 generations × 5 tokens
            'creator' => 50,  // 10 generations × 5 tokens  
            'salon' => 400    // 80 generations × 5 tokens
        ];
        
        return $tokenMap[$package] ?? 0;
    }
}