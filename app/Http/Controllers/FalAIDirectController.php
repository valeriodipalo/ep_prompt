<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SIMPLEST SOLUTION: Direct Base64 to fal.ai
 * No external storage needed - images go directly to fal.ai
 */
class FalAIDirectController extends Controller
{
    private $falKey;

    public function __construct()
    {
        $this->falKey = config('app.fal_key') ?: env('FAL_KEY');
    }

    /**
     * Transform image directly using base64 data
     * This is the SIMPLEST and most RELIABLE method
     */
    public function directTransform(Request $request)
    {
        Log::info('FalAI Direct: Starting base64 transformation');

        try {
            // Validate required fields
            $request->validate([
                'gender' => 'required|string|in:male,female',
                'hairstyle' => 'required|string',
                'color' => 'required|string',
                'base64_image' => 'required|string' // Base64 image data
            ]);

            if (!$this->falKey) {
                throw new \Exception('FAL API key not configured');
            }

            // Extract base64 data (remove data:image/jpeg;base64, prefix if present)
            $base64Data = $request->input('base64_image');
            if (strpos($base64Data, 'data:image') === 0) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }

            // Create prompt based on user selections
            $prompt = $this->createHairstylePrompt(
                $request->gender,
                $request->hairstyle,
                $request->color
            );

            Log::info('FalAI Direct: Generated prompt', ['prompt' => $prompt]);

            // Call fal.ai API with base64 image
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->post('https://fal.run/fal-ai/nano-banana/edit', [
                'prompt' => $prompt,
                'image_urls' => ['data:image/jpeg;base64,' . $base64Data], // Send as data URI
                'num_images' => 1,
                'output_format' => 'jpeg'
            ]);

            Log::info('FalAI Direct: API Response', [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                return response()->json([
                    'success' => true,
                    'result_url' => $result['images'][0]['url'],
                    'description' => $result['description'],
                    'method' => 'Base64 Direct Upload 🚀',
                    'prompt_used' => $prompt
                ]);
            } else {
                throw new \Exception('fal.ai API call failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('FalAI Direct: Transformation failed', [
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
                    'blonde' => 'Transform this person to have a short blonde bob haircut with modern styling',
                    'brown' => 'Transform this person to have a short brown pixie cut with contemporary styling',
                    'black' => 'Transform this person to have a short black bob with sleek styling',
                    'red' => 'Transform this person to have a short red bob with vibrant color and modern cut',
                    'purple' => 'Transform this person to have a short purple bob with vibrant color',
                    'blue' => 'Transform this person to have a short blue bob with vibrant color',
                ],
                'medium' => [
                    'blonde' => 'Transform this person to have medium-length blonde hair with layers and highlights',
                    'brown' => 'Transform this person to have medium-length brown hair with soft waves and natural color',
                    'black' => 'Transform this person to have medium-length black hair with sleek styling',
                    'red' => 'Transform this person to have medium-length red hair with vibrant color and waves',
                    'purple' => 'Transform this person to have medium-length purple hair with vibrant color',
                    'blue' => 'Transform this person to have medium-length blue hair with vibrant color',
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
                    'purple' => 'Transform this person to have medium-length purple hair with modern styling and texture',
                    'blue' => 'Transform this person to have medium-length blue hair with modern styling and texture',
                ],
                'long' => [
                    'blonde' => 'Transform this person to have long blonde hair with flowing style',
                    'brown' => 'Transform this person to have long brown hair with natural flowing style',
                    'black' => 'Transform this person to have long black hair with sleek flowing style',
                    'red' => 'Transform this person to have long red hair with flowing vibrant style',
                    'purple' => 'Transform this person to have long purple hair with flowing vibrant style',
                    'blue' => 'Transform this person to have long blue hair with flowing vibrant style',
                ]
            ]
        ];

        return $prompts[$gender][$hairstyle][$color] ?? 
               "Transform this person to have {$hairstyle} {$color} hair with modern styling";
    }
}
