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
            // Support both old format (gender/hairstyle/color) and new simplified format (image/prompt)
            $isSimplifiedFormat = $request->has('image') && $request->has('prompt');
            
            if ($isSimplifiedFormat) {
                // New simplified format - just image and prompt
                $request->validate([
                    'image' => 'required|string',
                    'prompt' => 'required|string'
                ]);
                
                $base64Data = $request->input('image');
                $prompt = $request->input('prompt');
            } else {
                // Old format - gender, hairstyle, color
                $request->validate([
                    'gender' => 'required|string|in:male,female',
                    'hairstyle' => 'required|string',
                    'color' => 'required|string',
                    'base64_image' => 'required|string'
                ]);
                
                $base64Data = $request->input('base64_image');
                $prompt = $this->createHairstylePrompt(
                    $request->gender,
                    $request->hairstyle,
                    $request->color
                );
            }

            if (!$this->falKey) {
                throw new \Exception('FAL API key not configured');
            }

            // Extract base64 data (remove data:image/jpeg;base64, prefix if present)
            if (strpos($base64Data, 'data:image') === 0) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }

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
                    'image' => $result['images'][0]['url'],  // Changed from result_url to image for consistency
                    'result_url' => $result['images'][0]['url'],
                    'description' => $result['description'] ?? 'AI transformation complete',
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
     * Create enhanced hairstyle prompt based on user selections
     * Supports both basic and professional (accented) color transformations
     */
    private function createHairstylePrompt($gender, $hairstyle, $color)
    {
        // Check if this is a professional color transformation
        $isProfessionalColor = $this->isProfessionalColorTransformation($color);
        
        if ($isProfessionalColor) {
            return $this->createProfessionalPrompt($gender, $hairstyle, $color);
        } else {
            return $this->createBasicPrompt($gender, $hairstyle, $color);
        }
    }

    /**
     * Check if color choice involves professional techniques
     */
    private function isProfessionalColorTransformation($color)
    {
        $professionalKeywords = ['balayage', 'highlights', 'lowlights', 'ombre', 'babylights', 'color-melt', 'with'];
        
        foreach ($professionalKeywords as $keyword) {
            if (stripos($color, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Create basic prompt for simple color transformations
     */
    private function createBasicPrompt($gender, $hairstyle, $color)
    {
        $basicPrompts = [
            'female' => [
                // Short Styles
                'pixie cut' => "Transform this person to have a chic pixie cut with {$color} color, very short and stylish",
                'bob' => "Transform this person to have a classic bob haircut with {$color} color, cut at jawline",
                'lob' => "Transform this person to have a long bob (lob) with {$color} color, shoulder-length and sleek",
                
                // Medium-Length Styles  
                'layered cut' => "Transform this person to have a layered medium-length cut with {$color} color and movement",
                'shag cut' => "Transform this person to have a shag cut with {$color} color, choppy layers and texture",
                'curtain bangs' => "Transform this person to have curtain bangs with {$color} color, parted fringe framing face",
                'shoulder-length waves' => "Transform this person to have shoulder-length wavy hair with {$color} color",
                
                // Long Styles
                'straight long hair' => "Transform this person to have straight long hair with {$color} color, sleek and polished",
                'layered long hair' => "Transform this person to have long layered hair with {$color} color and flowing volume",
                'ponytail' => "Transform this person to have hair styled in a ponytail with {$color} color",
                'braids' => "Transform this person to have braided hair with {$color} color, protective styling",
                'beach waves' => "Transform this person to have long beach waves with {$color} color, natural texture",
                'hollywood waves' => "Transform this person to have glamorous Hollywood waves with {$color} color",
                
                // Curly & Textured
                'afro' => "Transform this person to have a natural afro with {$color} color, rounded and voluminous",
                'natural curls' => "Transform this person to have natural curly hair with {$color} color, free-flowing",
                'twists' => "Transform this person to have twisted hair with {$color} color, protective styling"
            ],
            'male' => [
                // Short Classics
                'buzz cut' => "Transform this person to have a buzz cut with {$color} color, ultra-short military style",
                'crew cut' => "Transform this person to have a crew cut with {$color} color, short sides and neat top",
                'french crop' => "Transform this person to have a French crop with {$color} color and straight fringe",
                
                // Medium-Length Styles
                'side part' => "Transform this person to have a side part hairstyle with {$color} color, professional look",
                'pompadour' => "Transform this person to have a pompadour with {$color} color, voluminous and slicked up",
                'textured crop' => "Transform this person to have a textured crop with {$color} color, modern messy style",
                'bro flow' => "Transform this person to have a bro flow with {$color} color, medium length flowing back",
                
                // Long Styles
                'man bun' => "Transform this person to have a man bun with {$color} color, long hair tied back",
                'shoulder-length flow' => "Transform this person to have shoulder-length flowing hair with {$color} color",
                
                // Fades & Undercuts
                'low fade' => "Transform this person to have a low fade with {$color} color, gradual taper",
                'mid fade' => "Transform this person to have a mid fade with {$color} color, clean sides",
                'high fade' => "Transform this person to have a high fade with {$color} color, sharp contrast",
                'undercut' => "Transform this person to have an undercut with {$color} color, long top and shaved sides",
                
                // Curly & Textured
                'afro' => "Transform this person to have a natural afro with {$color} color, rounded curls",
                'curly top fade' => "Transform this person to have curly top fade with {$color} color, defined curls on top",
                'dreadlocks' => "Transform this person to have dreadlocks with {$color} color, rope-like strands"
            ]
        ];

        // Look up specific style prompt
        $styleKey = strtolower($hairstyle);
        if (isset($basicPrompts[$gender][$styleKey])) {
            return $basicPrompts[$gender][$styleKey];
        }

        // Fallback for unmatched styles
        return "Transform this person to have {$hairstyle} hair with {$color} color and professional styling";
    }

    /**
     * Create professional prompt for complex color transformations
     */
    private function createProfessionalPrompt($gender, $hairstyle, $color)
    {
        // Parse professional color description
        $basePrompt = "Transform this person to have {$hairstyle} hair with professional salon coloring: {$color}";
        
        // Add gender-specific styling details
        if ($gender === 'female') {
            $basePrompt .= ", with smooth blending and natural-looking results, salon-quality finish";
        } else {
            $basePrompt .= ", with clean lines and modern styling, professional barbershop quality";
        }
        
        // Add technique-specific details
        if (stripos($color, 'balayage') !== false) {
            $basePrompt .= ", hand-painted balayage technique with natural gradients";
        } elseif (stripos($color, 'highlights') !== false) {
            $basePrompt .= ", precise highlight placement with professional foil technique";
        } elseif (stripos($color, 'ombre') !== false) {
            $basePrompt .= ", smooth ombre transition from dark to light";
        }
        
        return $basePrompt;
    }

    /**
     * NAFNet Deblur - Fix blurriness and noise in images
     * Uses fal-ai/nafnet/deblur API
     */
    public function deblur(Request $request)
    {
        Log::info('NAFNet Deblur: Starting image restoration');

        try {
            $request->validate([
                'image_url' => 'required|string',
                'seed' => 'nullable|integer'
            ]);

            if (!$this->falKey) {
                throw new \Exception('FAL API key not configured');
            }

            $imageUrl = $request->input('image_url');
            $seed = $request->input('seed');

            // Check if image_url is a base64 data URI
            if (strpos($imageUrl, 'data:image') === 0) {
                // It's already a data URI, use it directly
                Log::info('NAFNet Deblur: Using base64 data URI');
            } else if (strpos($imageUrl, 'http') !== 0) {
                // It's a base64 string without the data URI prefix
                $imageUrl = 'data:image/jpeg;base64,' . $imageUrl;
                Log::info('NAFNet Deblur: Converted base64 to data URI');
            }

            // Prepare API payload
            $payload = [
                'image_url' => $imageUrl
            ];

            if ($seed !== null) {
                $payload['seed'] = $seed;
            }

            Log::info('NAFNet Deblur: Calling fal.ai API', ['has_seed' => $seed !== null]);

            // Call fal.ai NAFNet deblur API
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->falKey,
                'Content-Type' => 'application/json'
            ])->post('https://fal.run/fal-ai/nafnet/deblur', $payload);

            Log::info('NAFNet Deblur: API Response', [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // NAFNet returns the result in 'image' field with metadata
                if (isset($result['image']['url'])) {
                    return response()->json([
                        'success' => true,
                        'image' => $result['image']['url'],
                        'image_metadata' => [
                            'width' => $result['image']['width'] ?? null,
                            'height' => $result['image']['height'] ?? null,
                            'file_size' => $result['image']['file_size'] ?? null,
                            'content_type' => $result['image']['content_type'] ?? null
                        ],
                        'method' => 'NAFNet Deblur 🔍',
                        'description' => 'Image successfully deblurred and restored'
                    ]);
                } else {
                    throw new \Exception('Invalid response format from NAFNet API');
                }
            } else {
                throw new \Exception('fal.ai NAFNet API call failed: ' . $response->body());
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('NAFNet Deblur: Validation failed', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('NAFNet Deblur: Restoration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
