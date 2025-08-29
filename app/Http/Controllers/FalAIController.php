<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SupabaseController;

class FalAIController extends Controller
{
    private $falKey;
    private $falBaseUrl = 'https://fal.run';
    private $supabaseController;

    public function __construct()
    {
        $this->falKey = config('app.fal_key') ?: env('FAL_KEY');
        $this->supabaseController = new SupabaseController();
    }

    /**
     * Test API connectivity and key validity
     */
    public function test()
    {
        Log::info('FalAI: Testing API connectivity');
        
        if (!$this->falKey) {
            Log::error('FalAI: No API key found');
            return response()->json([
                'success' => false,
                'error' => 'FAL_KEY not configured'
            ], 500);
        }

        // Test with a simple request to see if our key works
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->timeout(10)->get($this->falBaseUrl . '/fal-ai/nano-banana/edit');

            Log::info('FalAI: API test response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAL API key is valid',
                'status' => $response->status()
            ]);

        } catch (\Exception $e) {
            Log::error('FalAI: API test failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to FAL API: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform hairstyle using fal.ai nano-banana/edit model
     * SIMPLIFIED VERSION FOR TESTING - Uses test images first
     */
    public function transformHairstyle(Request $request)
    {
        Log::info('FalAI: Starting hairstyle transformation', [
            'request_data' => $request->all()
        ]);

        try {
            // Validate required fields
            $request->validate([
                'gender' => 'required|string|in:male,female',
                'hairstyle' => 'required|string',
                'color' => 'required|string',
                'image_url' => 'nullable|string|url'  // Optional uploaded image URL
            ]);

            Log::info('FalAI: Request validation passed');

            // Use uploaded image if provided, otherwise fallback to test image
            $imageUrl = $request->input('image_url');
            if (!$imageUrl) {
                $imageUrl = 'https://storage.googleapis.com/falserverless/example_inputs/nano-banana-edit-input.png';
                Log::info('FalAI: Using test image for development', ['url' => $imageUrl]);
            } else {
                Log::info('FalAI: Using uploaded image', ['url' => $imageUrl]);
            }

            // Step 1: Create prompt based on user selections
            $prompt = $this->createHairstylePrompt(
                $request->gender,
                $request->hairstyle,
                $request->color
            );

            Log::info('FalAI: Generated prompt', ['prompt' => $prompt]);

            // Step 2: Call fal.ai API directly
            $result = $this->callFalAIDirectly($imageUrl, $prompt);

            if ($result['success']) {
                Log::info('FalAI: Transformation completed successfully');
                return response()->json([
                    'success' => true,
                    'result_url' => $result['images'][0]['url'],
                    'description' => $result['description'],
                    'original_url' => $imageUrl
                ]);
            } else {
                Log::error('FalAI: Transformation failed', ['error' => $result['error']]);
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('FalAI: Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('FalAI: Unexpected error in transformation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to transform image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call fal.ai API directly (synchronous)
     */
    private function callFalAIDirectly($imageUrl, $prompt)
    {
        Log::info('FalAI: Making direct API call', [
            'endpoint' => $this->falBaseUrl . '/fal-ai/nano-banana/edit',
            'image_url' => $imageUrl,
            'prompt' => $prompt
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->timeout(60)->post($this->falBaseUrl . '/fal-ai/nano-banana/edit', [
                'prompt' => $prompt,
                'image_urls' => [$imageUrl],
                'num_images' => 1,
                'output_format' => 'jpeg',
                'sync_mode' => true  // Return data URIs instead of URLs for faster response
            ]);

            Log::info('FalAI: API response received', [
                'status' => $response->status(),
                'response_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('FalAI: API call successful', [
                    'images_count' => count($data['images'] ?? []),
                    'has_description' => isset($data['description'])
                ]);

                return [
                    'success' => true,
                    'images' => $data['images'],
                    'description' => $data['description'] ?? 'Image transformation completed'
                ];
            } else {
                $errorBody = $response->body();
                Log::error('FalAI: API call failed', [
                    'status' => $response->status(),
                    'error_body' => $errorBody
                ]);

                return [
                    'success' => false,
                    'error' => 'API call failed with status ' . $response->status() . ': ' . $errorBody
                ];
            }

        } catch (\Exception $e) {
            Log::error('FalAI: Exception during API call', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'API call exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a predetermined prompt for hairstyle transformation
     * Using predetermined prompts for consistency and performance
     */
    private function createHairstylePrompt($gender, $hairstyle, $color)
    {
        // Predetermined prompts for different hairstyles
        $prompts = [
            'male' => [
                'short' => [
                    'black' => 'Transform this person to have a short, modern black haircut with clean sides and styled top',
                    'brown' => 'Transform this person to have a short, modern brown haircut with clean sides and styled top',
                    'blonde' => 'Transform this person to have a short, modern blonde haircut with clean sides and styled top',
                    'red' => 'Transform this person to have a short, modern red haircut with clean sides and styled top',
                ],
                'medium' => [
                    'black' => 'Transform this person to have a medium-length black hairstyle with layered texture',
                    'brown' => 'Transform this person to have a medium-length brown hairstyle with layered texture',
                    'blonde' => 'Transform this person to have a medium-length blonde hairstyle with layered texture',
                    'red' => 'Transform this person to have a medium-length red hairstyle with layered texture',
                ],
                'long' => [
                    'black' => 'Transform this person to have long, flowing black hair with natural waves',
                    'brown' => 'Transform this person to have long, flowing brown hair with natural waves',
                    'blonde' => 'Transform this person to have long, flowing blonde hair with natural waves',
                    'red' => 'Transform this person to have long, flowing red hair with natural waves',
                ]
            ],
            'female' => [
                'short' => [
                    'black' => 'Transform this person to have a stylish short black bob haircut with sleek finish',
                    'brown' => 'Transform this person to have a stylish short brown bob haircut with sleek finish',
                    'blonde' => 'Transform this person to have a stylish short blonde bob haircut with sleek finish',
                    'red' => 'Transform this person to have a stylish short red bob haircut with sleek finish',
                ],
                'medium' => [
                    'black' => 'Transform this person to have shoulder-length black hair with soft waves and layers',
                    'brown' => 'Transform this person to have shoulder-length brown hair with soft waves and layers',
                    'blonde' => 'Transform this person to have shoulder-length blonde hair with soft waves and layers',
                    'red' => 'Transform this person to have shoulder-length red hair with soft waves and layers',
                ],
                'long' => [
                    'black' => 'Transform this person to have long, voluminous black hair with elegant curls',
                    'brown' => 'Transform this person to have long, voluminous brown hair with elegant curls',
                    'blonde' => 'Transform this person to have long, voluminous blonde hair with elegant curls',
                    'red' => 'Transform this person to have long, voluminous red hair with elegant curls',
                ]
            ]
        ];

        // Get the specific prompt or fallback to a generic one
        $prompt = $prompts[$gender][$hairstyle][$color] ?? 
                  "Transform this person to have a {$hairstyle} {$color} hairstyle suitable for {$gender}";

        Log::info('FalAI: Created prompt', [
            'gender' => $gender,
            'hairstyle' => $hairstyle,
            'color' => $color,
            'prompt' => $prompt
        ]);

        return $prompt;
    }

    /**
     * Debug endpoint for testing fal.ai connectivity
     */
    public function debugFal()
    {
        Log::info('FalAI: Debug endpoint called');
        
        $debug_info = [
            'fal_key_configured' => !empty($this->falKey),
            'fal_key_length' => $this->falKey ? strlen($this->falKey) : 0,
            'base_url' => $this->falBaseUrl,
            'endpoint' => $this->falBaseUrl . '/fal-ai/nano-banana/edit'
        ];

        Log::info('FalAI: Debug info', $debug_info);

        return response()->json([
            'success' => true,
            'debug_info' => $debug_info
        ]);
    }

    /**
     * Legacy methods for backward compatibility (deprecated)
     */
    public function checkStatus(Request $request)
    {
        Log::warning('FalAI: checkStatus called - this method is deprecated for direct API calls');
        
        return response()->json([
            'success' => false,
            'error' => 'Status checking not needed for direct API calls'
        ], 400);
    }

    public function getResult(Request $request)
    {
        Log::warning('FalAI: getResult called - this method is deprecated for direct API calls');
        
        return response()->json([
            'success' => false,
            'error' => 'Result retrieval not needed for direct API calls'
        ], 400);
    }
}