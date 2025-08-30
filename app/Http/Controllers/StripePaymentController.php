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
                'cancel_url' => 'required|url'
            ]);

            $stripeSecretKey = env('STRIPE_SECRET');
            
            if (!$stripeSecretKey) {
                return response()->json([
                    'error' => 'Stripe configuration missing'
                ], 500);
            }

            // Create Stripe Checkout Session
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $stripeSecretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types[0]' => 'card',
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][product_data][name]' => 'StyleAI Premium',
                'line_items[0][price_data][product_data][description]' => 'Unlimited hairstyle transformations and exclusive styles',
                'line_items[0][price_data][unit_amount]' => 999, // $9.99 in cents
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => $request->success_url,
                'cancel_url' => $request->cancel_url,
                'customer_email' => $request->user_email,
                'metadata[user_id]' => $request->user_id,
                'metadata[product]' => 'styleai_premium',
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

                if ($userId && $userEmail) {
                    // Update user to premium in Supabase
                    $this->upgradeToPremium($userId, $userEmail, $session['id']);
                    
                    Log::info('Premium upgrade completed', [
                        'user_id' => $userId,
                        'user_email' => $userEmail,
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
     * Upgrade user to premium in Supabase
     */
    private function upgradeToPremium(string $userId, string $userEmail, string $sessionId): void
    {
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseServiceKey = env('SUPABASE_SERVICE_KEY');

            if (!$supabaseUrl || !$supabaseServiceKey) {
                throw new \Exception('Supabase configuration missing');
            }

            // Update user profile to premium
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey
            ])->patch($supabaseUrl . '/rest/v1/user_profiles', [
                'is_premium' => true,
                'upgraded_at' => now()->toISOString()
            ], [
                'email' => 'eq.' . $userEmail
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to update user profile: ' . $response->body());
            }

            // Record payment in payments table
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseServiceKey,
                'Content-Type' => 'application/json',
                'apikey' => $supabaseServiceKey
            ])->post($supabaseUrl . '/rest/v1/payments', [
                'user_id' => $userId,
                'stripe_session_id' => $sessionId,
                'amount' => 999,
                'currency' => 'usd',
                'status' => 'completed',
                'created_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to upgrade user to premium', [
                'user_id' => $userId,
                'user_email' => $userEmail,
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
}