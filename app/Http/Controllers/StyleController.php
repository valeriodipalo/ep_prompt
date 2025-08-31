<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * StyleAI Controller - Freemium Hairstyle & Color Catalog
 * 
 * Features:
 * - 7 Free styles (Anchor): Fade variants, Crew Cut, Pompadour, Undercut, Bald
 * - 12 Premium styles (Try-on): Advanced and specialized cuts
 * - Categorized by: Short, Medium, Long, Professional, Trendy, Cultural_Identity, No_Hair
 * - Comprehensive color palette: Natural, Modern, Creative
 */
class StyleController extends Controller
{
    /**
     * Get available hairstyles with gender-specific categorized model
     */
    public function getStyles(Request $request): JsonResponse
    {
        $isPremium = $request->boolean('is_premium', false);
        $category = $request->query('category');
        $gender = $request->query('gender', 'male'); // Default to male for backward compatibility

        // Gender-specific categorized styles
        $allStyles = [
            'female' => [
                // ========== SHORT STYLES ==========
                [
                    'id' => 'pixie_cut',
                    'name' => 'Pixie Cut',
                    'description' => 'Very short, chic, low-maintenance',
                    'category' => 'short-styles',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/pixie_cut.jpg',
                    'prompt_template' => 'pixie cut'
                ],
                [
                    'id' => 'bob',
                    'name' => 'Classic Bob',
                    'description' => 'Cut at jawline, sharp and sleek',
                    'category' => 'short-styles',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/bob.jpg',
                    'prompt_template' => 'bob'
                ],
                [
                    'id' => 'lob',
                    'name' => 'Long Bob',
                    'description' => 'Shoulder-length, very popular',
                    'category' => 'short-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/lob.jpg',
                    'prompt_template' => 'lob'
                ],
                
                // ========== MEDIUM STYLES ==========
                [
                    'id' => 'layered_cut',
                    'name' => 'Layered Cut',
                    'description' => 'Adds movement and volume',
                    'category' => 'medium-styles',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/layered_cut.jpg',
                    'prompt_template' => 'layered cut'
                ],
                [
                    'id' => 'shag_cut',
                    'name' => 'Shag Cut',
                    'description' => 'Choppy layers, rock-inspired',
                    'category' => 'medium-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/shag_cut.jpg',
                    'prompt_template' => 'shag cut'
                ],
                [
                    'id' => 'curtain_bangs',
                    'name' => 'Curtain Bangs',
                    'description' => 'Parted fringe framing face',
                    'category' => 'medium-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/curtain_bangs.jpg',
                    'prompt_template' => 'curtain bangs'
                ],
                [
                    'id' => 'shoulder_waves',
                    'name' => 'Shoulder-Length Waves',
                    'description' => 'Versatile beachy waves',
                    'category' => 'medium-styles',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/shoulder_waves.jpg',
                    'prompt_template' => 'shoulder-length waves'
                ],
                
                // ========== LONG STYLES ==========
                [
                    'id' => 'straight_long',
                    'name' => 'Straight Long Hair',
                    'description' => 'Simple, polished',
                    'category' => 'long-styles',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/straight_long.jpg',
                    'prompt_template' => 'straight long hair'
                ],
                [
                    'id' => 'layered_long',
                    'name' => 'Layered Long Hair',
                    'description' => 'Creates volume and flow',
                    'category' => 'long-styles',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/layered_long.jpg',
                    'prompt_template' => 'layered long hair'
                ],
                [
                    'id' => 'ponytail',
                    'name' => 'Ponytail',
                    'description' => 'High or low, elegant',
                    'category' => 'long-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/ponytail.jpg',
                    'prompt_template' => 'ponytail'
                ],
                [
                    'id' => 'braids',
                    'name' => 'Braids',
                    'description' => 'Single, double, box braids',
                    'category' => 'long-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/braids.jpg',
                    'prompt_template' => 'braids'
                ],
                [
                    'id' => 'beach_waves',
                    'name' => 'Beach Waves',
                    'description' => 'Loose textured waves',
                    'category' => 'long-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/beach_waves.jpg',
                    'prompt_template' => 'beach waves'
                ],
                [
                    'id' => 'hollywood_waves',
                    'name' => 'Hollywood Waves',
                    'description' => 'Glamorous, defined curls',
                    'category' => 'long-styles',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/hollywood_waves.jpg',
                    'prompt_template' => 'hollywood waves'
                ],
                
                // ========== CURLY & TEXTURED ==========
                [
                    'id' => 'afro_female',
                    'name' => 'Afro',
                    'description' => 'Rounded natural curls, bold',
                    'category' => 'curly-textured',
                    'gender' => 'female',
                    'is_free' => true,
                    'preview_url' => '/images/styles/female/afro.jpg',
                    'prompt_template' => 'afro'
                ],
                [
                    'id' => 'natural_curls',
                    'name' => 'Natural Curls',
                    'description' => 'Free-flowing layered curls',
                    'category' => 'curly-textured',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/natural_curls.jpg',
                    'prompt_template' => 'natural curls'
                ],
                [
                    'id' => 'twists_female',
                    'name' => 'Twists',
                    'description' => 'Protective styling, versatile',
                    'category' => 'curly-textured',
                    'gender' => 'female',
                    'is_free' => false,
                    'preview_url' => '/images/styles/female/twists.jpg',
                    'prompt_template' => 'twists'
                ]
            ],
            'male' => [
                // ========== SHORT CLASSICS ==========
                [
                    'id' => 'buzz_cut',
                    'name' => 'Buzz Cut',
                    'description' => 'Ultra-short, military style',
                    'category' => 'short-classics',
                    'gender' => 'male',
                    'is_free' => true,
                    'preview_url' => '/images/styles/male/buzz_cut.jpg',
                    'prompt_template' => 'buzz cut'
                ],
                [
                    'id' => 'crew_cut',
                    'name' => 'Crew Cut',
                    'description' => 'Short sides, neat top',
                    'category' => 'short-classics',
                    'gender' => 'male',
                    'is_free' => true,
                    'preview_url' => '/images/styles/male/crew_cut.jpg',
                    'prompt_template' => 'crew cut'
                ],
                [
                    'id' => 'french_crop',
                    'name' => 'French Crop',
                    'description' => 'Short with straight fringe',
                    'category' => 'short-classics',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/french_crop.jpg',
                    'prompt_template' => 'french crop'
                ],
                
                // ========== MEDIUM STYLES ==========
                [
                    'id' => 'side_part',
                    'name' => 'Side Part',
                    'description' => 'Timeless professional look',
                    'category' => 'medium-styles',
                    'gender' => 'male',
                    'is_free' => true,
                    'preview_url' => '/images/styles/male/side_part.jpg',
                    'prompt_template' => 'side part'
                ],
                [
                    'id' => 'pompadour',
                    'name' => 'Pompadour',
                    'description' => 'Voluminous top, slicked up',
                    'category' => 'medium-styles',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/pompadour.jpg',
                    'prompt_template' => 'pompadour'
                ],
                [
                    'id' => 'textured_crop',
                    'name' => 'Textured Crop',
                    'description' => 'Choppy layers, modern',
                    'category' => 'medium-styles',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/textured_crop.jpg',
                    'prompt_template' => 'textured crop'
                ],
                [
                    'id' => 'bro_flow',
                    'name' => 'Bro Flow',
                    'description' => 'Natural flow backward',
                    'category' => 'medium-styles',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/bro_flow.jpg',
                    'prompt_template' => 'bro flow'
                ],
                
                // ========== LONG STYLES ==========
                [
                    'id' => 'man_bun',
                    'name' => 'Man Bun',
                    'description' => 'Tied back, popular',
                    'category' => 'long-styles',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/man_bun.jpg',
                    'prompt_template' => 'man bun'
                ],
                [
                    'id' => 'shoulder_flow',
                    'name' => 'Shoulder-Length Flow',
                    'description' => 'Natural, layered',
                    'category' => 'long-styles',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/shoulder_flow.jpg',
                    'prompt_template' => 'shoulder-length flow'
                ],
                
                // ========== FADES & UNDERCUTS ==========
                [
                    'id' => 'low_fade',
                    'name' => 'Low Fade',
                    'description' => 'Gradual taper, trendy',
                    'category' => 'fades-undercuts',
                    'gender' => 'male',
                    'is_free' => true,
                    'preview_url' => '/images/styles/male/low_fade.jpg',
                    'prompt_template' => 'low fade'
                ],
                [
                    'id' => 'mid_fade',
                    'name' => 'Mid Fade',
                    'description' => 'Clean mid-level fade',
                    'category' => 'fades-undercuts',
                    'gender' => 'male',
                    'is_free' => true,
                    'preview_url' => '/images/styles/male/mid_fade.jpg',
                    'prompt_template' => 'mid fade'
                ],
                [
                    'id' => 'high_fade',
                    'name' => 'High Fade',
                    'description' => 'Sharp contrast',
                    'category' => 'fades-undercuts',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/high_fade.jpg',
                    'prompt_template' => 'high fade'
                ],
                [
                    'id' => 'undercut',
                    'name' => 'Disconnected Undercut',
                    'description' => 'Sharp contrast, long top',
                    'category' => 'fades-undercuts',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/undercut.jpg',
                    'prompt_template' => 'undercut'
                ],
                
                // ========== CURLY & TEXTURED ==========
                [
                    'id' => 'afro_male',
                    'name' => 'Afro',
                    'description' => 'Natural rounded curls',
                    'category' => 'curly-textured',
                    'gender' => 'male',
                    'is_free' => true,
                    'preview_url' => '/images/styles/male/afro.jpg',
                    'prompt_template' => 'afro'
                ],
                [
                    'id' => 'curly_fade',
                    'name' => 'Curly Top Fade',
                    'description' => 'Defined curls, faded sides',
                    'category' => 'curly-textured',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/curly_fade.jpg',
                    'prompt_template' => 'curly top fade'
                ],
                [
                    'id' => 'dreadlocks_male',
                    'name' => 'Dreadlocks',
                    'description' => 'Rope-like strands',
                    'category' => 'curly-textured',
                    'gender' => 'male',
                    'is_free' => false,
                    'preview_url' => '/images/styles/male/dreadlocks.jpg',
                    'prompt_template' => 'dreadlocks'
                ]
            ]
        ];
        
        // Get styles for selected gender
        $styles = $allStyles[$gender] ?? $allStyles['male']; // Fallback to male styles if gender not found

        // Don't filter by premium status - frontend handles locks for premium styles
        $filteredStyles = $styles;

        // Filter by category if specified
        if ($category) {
            $filteredStyles = array_filter($filteredStyles, function($style) use ($category) {
                return $style['category'] === $category;
            });
        }

        // Get gender-specific categories
        $genderCategories = [
            'female' => [
                ['key' => 'short-styles', 'label' => 'Short Styles', 'emoji' => '✂️'],
                ['key' => 'medium-styles', 'label' => 'Medium Styles', 'emoji' => '🌸'],
                ['key' => 'long-styles', 'label' => 'Long Styles', 'emoji' => '👸'],
                ['key' => 'curly-textured', 'label' => 'Curly & Textured', 'emoji' => '🌀']
            ],
            'male' => [
                ['key' => 'short-classics', 'label' => 'Short Classics', 'emoji' => '✂️'],
                ['key' => 'medium-styles', 'label' => 'Medium Styles', 'emoji' => '🌸'],
                ['key' => 'long-styles', 'label' => 'Long Styles', 'emoji' => '👸'],
                ['key' => 'fades-undercuts', 'label' => 'Fades & Undercuts', 'emoji' => '⚡'],
                ['key' => 'curly-textured', 'label' => 'Curly & Textured', 'emoji' => '🌀']
            ]
        ];

        return response()->json([
            'success' => true,
            'styles' => array_values($filteredStyles),
            'categories' => $genderCategories[$gender] ?? [],
            'gender' => $gender,
            'total_styles' => count($styles),
            'available_styles' => count($filteredStyles),
            'free_styles' => count(array_filter($styles, fn($s) => $s['is_free'])),
            'premium_styles' => count(array_filter($styles, fn($s) => !$s['is_free'])),
            'premium_required' => !$isPremium
        ]);
    }

    /**
     * Get available colors with comprehensive palette
     */
    public function getColors(Request $request): JsonResponse
    {
        $isPremium = $request->boolean('is_premium', false);
        $category = $request->query('category');

        $colors = [
            // ========== NATURAL COLORS (FREE) ==========
            [
                'id' => 'black',
                'name' => 'Black',
                'hex' => '#1a1a1a',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Deep natural black'
            ],
            [
                'id' => 'dark-brown',
                'name' => 'Dark Brown',
                'hex' => '#3c2415',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Rich dark brown'
            ],
            [
                'id' => 'medium-brown',
                'name' => 'Medium Brown',
                'hex' => '#8b4513',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Warm medium brown'
            ],
            [
                'id' => 'blonde',
                'name' => 'Blonde',
                'hex' => '#ffd700',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Classic golden blonde'
            ],
            [
                'id' => 'auburn',
                'name' => 'Auburn',
                'hex' => '#a52a2a',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Rich auburn red'
            ],
            [
                'id' => 'ginger',
                'name' => 'Ginger',
                'hex' => '#b06500',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Vibrant ginger red'
            ],
            [
                'id' => 'gray',
                'name' => 'Gray',
                'hex' => '#808080',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Distinguished gray'
            ],
            [
                'id' => 'platinum',
                'name' => 'Platinum',
                'hex' => '#e5e4e2',
                'category' => 'Natural',
                'is_premium' => false,
                'description' => 'Ultra-light platinum'
            ],

            // ========== MODERN COLORS (PREMIUM) ==========
            [
                'id' => 'ash-blonde',
                'name' => 'Ash Blonde',
                'hex' => '#c4a484',
                'category' => 'Modern',
                'is_premium' => true,
                'description' => 'Cool-toned ash blonde'
            ],
            [
                'id' => 'honey-blonde',
                'name' => 'Honey Blonde',
                'hex' => '#daa520',
                'category' => 'Modern',
                'is_premium' => true,
                'description' => 'Warm honey blonde'
            ],
            [
                'id' => 'chestnut',
                'name' => 'Chestnut',
                'hex' => '#954535',
                'category' => 'Modern',
                'is_premium' => true,
                'description' => 'Rich chestnut brown'
            ],
            [
                'id' => 'balayage-ombre',
                'name' => 'Balayage Ombre',
                'hex' => '#8b7355',
                'category' => 'Modern',
                'is_premium' => true,
                'description' => 'Gradient balayage effect'
            ],

            // ========== CREATIVE COLORS (PREMIUM) ==========
            [
                'id' => 'pastel-pink',
                'name' => 'Pastel Pink',
                'hex' => '#ffc0cb',
                'category' => 'Creative',
                'is_premium' => true,
                'description' => 'Soft pastel pink'
            ],
            [
                'id' => 'pastel-blue',
                'name' => 'Pastel Blue',
                'hex' => '#add8e6',
                'category' => 'Creative',
                'is_premium' => true,
                'description' => 'Dreamy pastel blue'
            ],
            [
                'id' => 'teal',
                'name' => 'Teal',
                'hex' => '#008080',
                'category' => 'Creative',
                'is_premium' => true,
                'description' => 'Vibrant teal blue-green'
            ],
            [
                'id' => 'lavender',
                'name' => 'Lavender',
                'hex' => '#e6e6fa',
                'category' => 'Creative',
                'is_premium' => true,
                'description' => 'Soft lavender purple'
            ],
            [
                'id' => 'neon-green',
                'name' => 'Neon Green',
                'hex' => '#39ff14',
                'category' => 'Creative',
                'is_premium' => true,
                'description' => 'Electric neon green'
            ],
            [
                'id' => 'rainbow',
                'name' => 'Rainbow',
                'hex' => '#ff6b6b', // Representative color
                'category' => 'Creative',
                'is_premium' => true,
                'description' => 'Multi-color rainbow effect'
            ]
        ];

        // Don't filter by premium status - frontend handles locks for premium colors
        $filteredColors = $colors;

        // Filter by category if specified
        if ($category) {
            $filteredColors = array_filter($filteredColors, function($color) use ($category) {
                return $color['category'] === $category;
            });
        }

        return response()->json([
            'success' => true,
            'colors' => array_values($filteredColors),
            'categories' => ['Natural', 'Modern', 'Creative'],
            'total_colors' => count($colors),
            'available_colors' => count($filteredColors),
            'free_colors' => count(array_filter($colors, fn($c) => !$c['is_premium'])),
            'premium_colors' => count(array_filter($colors, fn($c) => $c['is_premium'])),
            'premium_required' => !$isPremium
        ]);
    }

    /**
     * Get subscription plans for freemium model
     */
    public function getSubscriptionPlans(): JsonResponse
    {
        $plans = [
            [
                'id' => 'free',
                'name' => 'Free',
                'price' => 0,
                'currency' => 'USD',
                'features' => [
                    '50 tokens (10 transformations)',
                    '7 free styles (Fade variants, Crew Cut, Pompadour, Undercut, Bald)',
                    '8 natural colors only',
                    'Standard processing'
                ],
                'styles_count' => 7,
                'colors_count' => 8,
                'tokens_included' => 50
            ],
            [
                'id' => 'premium',
                'name' => 'Premium',
                'price' => 999, // $9.99 in cents
                'currency' => 'USD',
                'features' => [
                    'Unlimited transformations',
                    'All 19 premium styles',
                    'All 17 colors (Natural + Modern + Creative)',
                    'Priority processing',
                    'Exclusive styles and colors',
                    'One-time payment'
                ],
                'styles_count' => 19,
                'colors_count' => 17,
                'tokens_included' => 'unlimited'
            ]
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    /**
     * Get style by ID with full details
     */
    public function getStyleById(Request $request, string $styleId): JsonResponse
    {
        $isPremium = $request->boolean('is_premium', false);
        
        // Get all styles and find the requested one
        $allStyles = $this->getStyles($request)->getData()->styles;
        $style = collect($allStyles)->firstWhere('id', $styleId);
        
        if (!$style) {
            return response()->json([
                'success' => false,
                'error' => 'Style not found'
            ], 404);
        }
        
        // Check if user has access
        if (!$style->is_free && !$isPremium) {
            return response()->json([
                'success' => false,
                'error' => 'Premium subscription required',
                'requires_premium' => true
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'style' => $style
        ]);
    }

    /**
     * Get color by ID with full details
     */
    public function getColorById(Request $request, string $colorId): JsonResponse
    {
        $isPremium = $request->boolean('is_premium', false);
        
        // Get all colors and find the requested one
        $allColors = $this->getColors($request)->getData()->colors;
        $color = collect($allColors)->firstWhere('id', $colorId);
        
        if (!$color) {
            return response()->json([
                'success' => false,
                'error' => 'Color not found'
            ], 404);
        }
        
        // Check if user has access
        if ($color->is_premium && !$isPremium) {
            return response()->json([
                'success' => false,
                'error' => 'Premium subscription required',
                'requires_premium' => true
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'color' => $color
        ]);
    }
}