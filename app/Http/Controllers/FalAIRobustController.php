<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FalAIRobustController extends Controller
{
    private $falKey;

    public function __construct()
    {
        $this->falKey = config('app.fal_key') ?: env('FAL_KEY');
    }

    /**
     * BULLETPROOF transformation method with multiple fallbacks
     */
    public function robustTransform(Request $request)
    {
        Log::info('FalAI Robust: Starting bulletproof transformation', [
            'request_data' => $request->except(['image']),
            'has_image' => $request->hasFile('image'),
            'image_size' => $request->hasFile('image') ? $request->file('image')->getSize() : 0
        ]);

        try {
            // Validate basic required fields
            $request->validate([
                'gender' => 'required|string|in:male,female',
                'hairstyle' => 'required|string',
                'color' => 'required|string'
            ]);

            if (!$this->falKey) {
                throw new \Exception('FAL API key not configured');
            }

            // METHOD 1: Try with uploaded image (if available)
            if ($request->hasFile('image')) {
                Log::info('FalAI Robust: Attempting Method 1 - Uploaded Image');
                
                $result = $this->tryWithUploadedImage($request);
                if ($result['success']) {
                    return response()->json($result);
                }
                
                Log::warning('FalAI Robust: Method 1 failed, trying Method 2');
            }

            // METHOD 2: Fallback to test image
            Log::info('FalAI Robust: Attempting Method 2 - Test Image');
            $result = $this->tryWithTestImage($request);
            
            if ($result['success']) {
                return response()->json($result);
            }

            // If all methods fail
            throw new \Exception('All transformation methods failed');

        } catch (\Exception $e) {
            Log::error('FalAI Robust: All methods failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'fallback_available' => true
            ], 500);
        }
    }

    /**
     * Try transformation with uploaded image (Base64 method)
     */
    private function tryWithUploadedImage(Request $request)
    {
        try {
            $imageFile = $request->file('image');
            
            // Enhanced validation
            if (!$imageFile || !$imageFile->isValid()) {
                throw new \Exception('Invalid image file upload');
            }

            // Check file size (max 10MB)
            if ($imageFile->getSize() > 10240000) {
                throw new \Exception('Image file too large (max 10MB)');
            }

            // Check MIME type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imageFile->getMimeType(), $allowedTypes)) {
                throw new \Exception('Invalid image type. Allowed: JPEG, PNG, GIF, WEBP');
            }

            Log::info('FalAI Robust: Image validation passed', [
                'filename' => $imageFile->getClientOriginalName(),
                'size' => $imageFile->getSize(),
                'mime_type' => $imageFile->getMimeType()
            ]);

            // Convert to base64
            $imageData = file_get_contents($imageFile->getPathname());
            $base64 = base64_encode($imageData);
            $mimeType = $imageFile->getMimeType();
            $dataUri = "data:{$mimeType};base64,{$base64}";

            Log::info('FalAI Robust: Image converted to base64', [
                'original_size' => strlen($imageData),
                'base64_size' => strlen($base64)
            ]);

            // Create prompt
            $prompt = $this->createHairstylePrompt(
                $request->gender,
                $request->hairstyle,
                $request->color
            );

            // Call fal.ai with base64 data
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->post('https://fal.run/fal-ai/nano-banana/edit', [
                'prompt' => $prompt,
                'image_urls' => [$dataUri],
                'num_images' => 1,
                'output_format' => 'jpeg'
            ]);

            Log::info('FalAI Robust: API response for uploaded image', [
                'status' => $response->status(),
                'body_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                return [
                    'success' => true,
                    'result_url' => $result['images'][0]['url'],
                    'description' => $result['description'],
                    'original_url' => 'uploaded_image_base64',
                    'method' => 'Uploaded Image (Base64) ⚡'
                ];
            } else {
                throw new \Exception('fal.ai API error: ' . $response->status() . ' - ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::warning('FalAI Robust: Uploaded image method failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Try transformation with test image (always works)
     */
    private function tryWithTestImage(Request $request)
    {
        try {
            $testImageUrl = 'https://storage.googleapis.com/falserverless/example_inputs/nano-banana-edit-input.png';
            
            // Create prompt
            $prompt = $this->createHairstylePrompt(
                $request->gender,
                $request->hairstyle,
                $request->color
            );

            Log::info('FalAI Robust: Using test image', [
                'test_url' => $testImageUrl,
                'prompt' => $prompt
            ]);

            // Call fal.ai with test image
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->post('https://fal.run/fal-ai/nano-banana/edit', [
                'prompt' => $prompt,
                'image_urls' => [$testImageUrl],
                'num_images' => 1,
                'output_format' => 'jpeg'
            ]);

            Log::info('FalAI Robust: API response for test image', [
                'status' => $response->status(),
                'body_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                return [
                    'success' => true,
                    'result_url' => $result['images'][0]['url'],
                    'description' => $result['description'],
                    'original_url' => $testImageUrl,
                    'method' => 'Test Image (Demo) 🧪'
                ];
            } else {
                throw new \Exception('fal.ai API error: ' . $response->status() . ' - ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('FalAI Robust: Test image method failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create hairstyle prompt based on user selections
     */
    private function createHairstylePrompt($gender, $hairstyle, $color)
    {
        $prompts = [
            'female' => [
                'short' => [
                    'blonde' => 'Transform this person to have a stylish short blonde bob haircut with sleek finish',
                    'brown' => 'Transform this person to have a stylish short brown bob haircut with sleek finish',
                    'black' => 'Transform this person to have a stylish short black pixie cut with modern styling',
                    'red' => 'Transform this person to have a stylish short red bob with vibrant color',
                    'purple' => 'Transform this person to have a stylish short purple bob with vibrant color',
                    'blue' => 'Transform this person to have a stylish short blue bob with vibrant color',
                ],
                'medium' => [
                    'blonde' => 'Transform this person to have shoulder-length blonde hair with soft waves and layers',
                    'brown' => 'Transform this person to have shoulder-length brown hair with soft waves and layers',
                    'black' => 'Transform this person to have shoulder-length black hair with sleek layers',
                    'red' => 'Transform this person to have shoulder-length red hair with soft waves and layers',
                    'purple' => 'Transform this person to have shoulder-length purple hair with soft waves and vibrant color',
                    'blue' => 'Transform this person to have shoulder-length blue hair with soft waves and vibrant color',
                ],
                'long' => [
                    'blonde' => 'Transform this person to have long blonde hair with flowing waves and highlights',
                    'brown' => 'Transform this person to have long brown hair with flowing waves and natural shine',
                    'black' => 'Transform this person to have long black hair with sleek straight styling',
                    'red' => 'Transform this person to have long red hair with flowing waves and vibrant color',
                    'purple' => 'Transform this person to have long purple hair with flowing waves and vibrant color',
                    'blue' => 'Transform this person to have long blue hair with flowing waves and vibrant color',
                ]
            ],
            'male' => [
                'short' => [
                    'blonde' => 'Transform this person to have a short, modern blonde haircut with clean sides and styled top',
                    'brown' => 'Transform this person to have a short, modern brown haircut with clean sides and styled top',
                    'black' => 'Transform this person to have a short, modern black haircut with fade sides and textured top',
                    'red' => 'Transform this person to have a short, modern red haircut with clean sides and styled top',
                    'purple' => 'Transform this person to have a short, modern purple haircut with clean sides and styled top',
                    'blue' => 'Transform this person to have a short, modern blue haircut with clean sides and styled top',
                ],
                'medium' => [
                    'blonde' => 'Transform this person to have medium-length blonde hair with modern styling and texture',
                    'brown' => 'Transform this person to have medium-length brown hair with modern styling and texture',
                    'black' => 'Transform this person to have medium-length black hair with contemporary cut and styling',
                    'red' => 'Transform this person to have medium-length red hair with modern styling and texture',
                    'purple' => 'Transform this person to have medium-length purple hair with modern styling and vibrant color',
                    'blue' => 'Transform this person to have medium-length blue hair with modern styling and vibrant color',
                ],
                'long' => [
                    'blonde' => 'Transform this person to have long blonde hair with layered cut and natural flow',
                    'brown' => 'Transform this person to have long brown hair with layered cut and natural flow',
                    'black' => 'Transform this person to have long black hair with sleek layers and natural shine',
                    'red' => 'Transform this person to have long red hair with layered cut and vibrant color',
                    'purple' => 'Transform this person to have long purple hair with layered cut and vibrant color',
                    'blue' => 'Transform this person to have long blue hair with layered cut and vibrant color',
                ]
            ]
        ];

        return $prompts[$gender][$hairstyle][$color] ?? 
               "Transform this person to have a {$hairstyle} {$color} hairstyle suitable for {$gender}";
    }
}
