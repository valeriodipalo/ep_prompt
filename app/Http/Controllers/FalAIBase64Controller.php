<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FalAIBase64Controller extends Controller
{
    private $falKey;

    public function __construct()
    {
        $this->falKey = config('app.fal_key') ?: env('FAL_KEY');
    }

    /**
     * Transform image using base64 data (no storage needed)
     */
    public function transformWithBase64(Request $request)
    {
        Log::info('FalAI Base64: Starting transformation', [
            'request_data' => $request->except(['image'])
        ]);

        try {
            // Validate required fields
            $request->validate([
                'image' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
                'gender' => 'required|string|in:male,female',
                'hairstyle' => 'required|string',
                'color' => 'required|string'
            ]);

            Log::info('FalAI Base64: Request validation passed');

            if (!$this->falKey) {
                throw new \Exception('FAL API key not configured');
            }

            // Get the uploaded file
            $imageFile = $request->file('image');
            
            // Convert to base64 data URI
            $imageData = file_get_contents($imageFile->getPathname());
            $base64 = base64_encode($imageData);
            $mimeType = $imageFile->getMimeType();
            $dataUri = "data:{$mimeType};base64,{$base64}";

            Log::info('FalAI Base64: Image converted to base64', [
                'mime_type' => $mimeType,
                'size_bytes' => strlen($imageData),
                'base64_size' => strlen($base64)
            ]);

            // Create prompt
            $prompt = $this->createHairstylePrompt(
                $request->gender,
                $request->hairstyle,
                $request->color
            );

            Log::info('FalAI Base64: Generated prompt', ['prompt' => $prompt]);

            // Call fal.ai with base64 data
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->post('https://fal.run/fal-ai/nano-banana/edit', [
                'prompt' => $prompt,
                'image_urls' => [$dataUri], // Use base64 data URI
                'num_images' => 1,
                'output_format' => 'jpeg'
            ]);

            Log::info('FalAI Base64: API response', [
                'status' => $response->status(),
                'body_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('FalAI Base64: Transformation completed successfully');
                
                return response()->json([
                    'success' => true,
                    'result_url' => $result['images'][0]['url'],
                    'description' => $result['description'],
                    'original_data' => 'base64_data_uri', // Don't return the full base64
                    'method' => 'Base64 Direct ⚡'
                ]);
            } else {
                $errorBody = $response->body();
                Log::error('FalAI Base64: API call failed', [
                    'status' => $response->status(),
                    'error_body' => $errorBody
                ]);
                
                throw new \Exception('API call failed with status ' . $response->status() . ': ' . $errorBody);
            }

        } catch (\Exception $e) {
            Log::error('FalAI Base64: Process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
                ],
                'medium' => [
                    'blonde' => 'Transform this person to have shoulder-length blonde hair with soft waves and layers',
                    'brown' => 'Transform this person to have shoulder-length brown hair with soft waves and layers',
                    'black' => 'Transform this person to have shoulder-length black hair with sleek layers',
                    'red' => 'Transform this person to have shoulder-length red hair with soft waves and layers',
                ],
                'long' => [
                    'blonde' => 'Transform this person to have long blonde hair with flowing waves and highlights',
                    'brown' => 'Transform this person to have long brown hair with flowing waves and natural shine',
                    'black' => 'Transform this person to have long black hair with sleek straight styling',
                    'red' => 'Transform this person to have long red hair with flowing waves and vibrant color',
                ]
            ],
            'male' => [
                'short' => [
                    'blonde' => 'Transform this person to have a short, modern blonde haircut with clean sides and styled top',
                    'brown' => 'Transform this person to have a short, modern brown haircut with clean sides and styled top',
                    'black' => 'Transform this person to have a short, modern black haircut with fade sides and textured top',
                    'red' => 'Transform this person to have a short, modern red haircut with clean sides and styled top',
                ],
                'medium' => [
                    'blonde' => 'Transform this person to have medium-length blonde hair with modern styling and texture',
                    'brown' => 'Transform this person to have medium-length brown hair with modern styling and texture',
                    'black' => 'Transform this person to have medium-length black hair with contemporary cut and styling',
                    'red' => 'Transform this person to have medium-length red hair with modern styling and texture',
                ],
                'long' => [
                    'blonde' => 'Transform this person to have long blonde hair with layered cut and natural flow',
                    'brown' => 'Transform this person to have long brown hair with layered cut and natural flow',
                    'black' => 'Transform this person to have long black hair with sleek layers and natural shine',
                    'red' => 'Transform this person to have long red hair with layered cut and vibrant color',
                ]
            ]
        ];

        return $prompts[$gender][$hairstyle][$color] ?? 
               "Transform this person to have a {$hairstyle} {$color} hairstyle suitable for {$gender}";
    }
}
