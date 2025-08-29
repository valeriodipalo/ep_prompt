<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FalAIDirectUploadController extends Controller
{
    private $falKey;

    public function __construct()
    {
        $this->falKey = config('app.fal_key') ?: env('FAL_KEY');
    }

    /**
     * Upload image directly to fal.ai storage and transform in one step
     * This bypasses Supabase entirely
     */
    public function uploadAndTransform(Request $request)
    {
        Log::info('FalAI Direct: Starting upload and transform', [
            'request_data' => $request->except(['image'])
        ]);

        try {
            // Debug file upload
            $imageFile = $request->file('image');
            Log::info('FalAI Direct: File upload debug', [
                'has_file' => $request->hasFile('image'),
                'file_valid' => $imageFile ? $imageFile->isValid() : false,
                'file_name' => $imageFile ? $imageFile->getClientOriginalName() : null,
                'file_size' => $imageFile ? $imageFile->getSize() : null,
                'mime_type' => $imageFile ? $imageFile->getMimeType() : null,
                'extension' => $imageFile ? $imageFile->getClientOriginalExtension() : null
            ]);

            // Validate required fields with more flexible image validation
            $request->validate([
                'image' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240', // More specific MIME types
                'gender' => 'required|string|in:male,female',
                'hairstyle' => 'required|string',
                'color' => 'required|string'
            ]);

            Log::info('FalAI Direct: Request validation passed');

            if (!$this->falKey) {
                throw new \Exception('FAL API key not configured');
            }

            // Step 1: Upload image directly to fal.ai storage
            $imageFile = $request->file('image');
            $uploadResult = $this->uploadImageToFal($imageFile);
            
            if (!$uploadResult['success']) {
                throw new \Exception('Failed to upload image to fal.ai: ' . $uploadResult['error']);
            }

            $imageUrl = $uploadResult['url'];
            Log::info('FalAI Direct: Image uploaded successfully', ['url' => $imageUrl]);

            // Step 2: Create prompt
            $prompt = $this->createHairstylePrompt(
                $request->gender,
                $request->hairstyle,
                $request->color
            );

            Log::info('FalAI Direct: Generated prompt', ['prompt' => $prompt]);

            // Step 3: Transform the image
            $transformResult = $this->transformImage($imageUrl, $prompt);

            if ($transformResult['success']) {
                Log::info('FalAI Direct: Transformation completed successfully');
                return response()->json([
                    'success' => true,
                    'result_url' => $transformResult['images'][0]['url'],
                    'description' => $transformResult['description'],
                    'original_url' => $imageUrl,
                    'method' => 'fal_direct_upload'
                ]);
            } else {
                throw new \Exception('Transformation failed: ' . $transformResult['error']);
            }

        } catch (\Exception $e) {
            Log::error('FalAI Direct: Process failed', [
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
     * Upload image directly to fal.ai storage
     */
    private function uploadImageToFal($imageFile)
    {
        try {
            Log::info('FalAI Direct: Uploading to fal.ai storage', [
                'filename' => $imageFile->getClientOriginalName(),
                'size' => $imageFile->getSize()
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
            ])->attach(
                'file', 
                file_get_contents($imageFile->getPathname()), 
                $imageFile->getClientOriginalName()
            )->post('https://fal.run/storage/upload');

            Log::info('FalAI Direct: Upload response', [
                'status' => $response->status(),
                'body_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'url' => $result['url'] ?? $result['file_url'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Upload failed with status ' . $response->status() . ': ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('FalAI Direct: Upload failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transform image using fal.ai
     */
    private function transformImage($imageUrl, $prompt)
    {
        try {
            Log::info('FalAI Direct: Starting transformation', [
                'image_url' => $imageUrl,
                'prompt' => $prompt
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->post('https://fal.run/fal-ai/nano-banana/edit', [
                'prompt' => $prompt,
                'image_urls' => [$imageUrl],
                'num_images' => 1,
                'output_format' => 'jpeg'
            ]);

            Log::info('FalAI Direct: Transform response', [
                'status' => $response->status(),
                'body_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'images' => $result['images'],
                    'description' => $result['description']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Transform failed with status ' . $response->status() . ': ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('FalAI Direct: Transform failed', ['error' => $e->getMessage()]);
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
