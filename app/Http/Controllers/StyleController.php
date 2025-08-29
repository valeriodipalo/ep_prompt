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
     * Get available hairstyles with freemium model
     */
    public function getStyles(Request $request): JsonResponse
    {
        $isPremium = $request->boolean('is_premium', false);
        $category = $request->query('category');

        $styles = [
            // ========== ANCHOR STYLES (FREE) ==========
            [
                'id' => 'fade_low',
                'name' => 'Low Fade',
                'description' => 'Clean low fade with gradual transition',
                'category' => 'Short',
                'complexity' => 'medium',
                'is_free' => true,
                'preview_url' => '/images/styles/fade_low.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'blonde'],
                'prompt_template' => 'low fade haircut with gradual transition'
            ],
            [
                'id' => 'fade_mid',
                'name' => 'Mid Fade',
                'description' => 'Classic mid-level fade cut',
                'category' => 'Short',
                'complexity' => 'medium',
                'is_free' => true,
                'preview_url' => '/images/styles/fade_mid.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'blonde'],
                'prompt_template' => 'mid fade haircut with clean sides'
            ],
            [
                'id' => 'fade_high',
                'name' => 'High Fade',
                'description' => 'Sharp high fade for modern look',
                'category' => 'Short',
                'complexity' => 'medium',
                'is_free' => true,
                'preview_url' => '/images/styles/fade_high.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'blonde'],
                'prompt_template' => 'high fade haircut with sharp contrast'
            ],
            [
                'id' => 'crew_cut',
                'name' => 'Crew Cut',
                'description' => 'Professional short military-style cut',
                'category' => 'Professional',
                'complexity' => 'low',
                'is_free' => true,
                'preview_url' => '/images/styles/crew_cut.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'gray'],
                'prompt_template' => 'crew cut military style haircut'
            ],
            [
                'id' => 'pompadour',
                'name' => 'Pompadour',
                'description' => 'Classic vintage voluminous style',
                'category' => 'Professional',
                'complexity' => 'high',
                'is_free' => true,
                'preview_url' => '/images/styles/pompadour.jpg',
                'default_colors' => ['black', 'dark-brown', 'blonde', 'gray'],
                'prompt_template' => 'classic pompadour with volume and height'
            ],
            [
                'id' => 'undercut',
                'name' => 'Undercut',
                'description' => 'Trendy disconnected undercut',
                'category' => 'Trendy',
                'complexity' => 'medium',
                'is_free' => true,
                'preview_url' => '/images/styles/undercut.jpg',
                'default_colors' => ['black', 'dark-brown', 'blonde', 'ash-blonde'],
                'prompt_template' => 'undercut with disconnected sides'
            ],
            [
                'id' => 'bald',
                'name' => 'Bald',
                'description' => 'Clean shaved head',
                'category' => 'No_Hair',
                'complexity' => 'low',
                'is_free' => true,
                'preview_url' => '/images/styles/bald.jpg',
                'default_colors' => [], // No colors needed
                'prompt_template' => 'completely bald shaved head'
            ],

            // ========== PREMIUM STYLES (TRY-ON) ==========
            [
                'id' => 'buzz_cut',
                'name' => 'Buzz Cut',
                'description' => 'Ultra-short all-over buzz',
                'category' => 'Short',
                'complexity' => 'low',
                'is_free' => false,
                'preview_url' => '/images/styles/buzz_cut.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'gray'],
                'prompt_template' => 'buzz cut very short all over'
            ],
            [
                'id' => 'ivy_league',
                'name' => 'Ivy League',
                'description' => 'Sophisticated Ivy League cut',
                'category' => 'Professional',
                'complexity' => 'medium',
                'is_free' => false,
                'preview_url' => '/images/styles/ivy_league.jpg',
                'default_colors' => ['dark-brown', 'medium-brown', 'blonde', 'gray'],
                'prompt_template' => 'ivy league haircut with side part'
            ],
            [
                'id' => 'quiff',
                'name' => 'Quiff',
                'description' => 'Modern textured quiff style',
                'category' => 'Professional',
                'complexity' => 'medium',
                'is_free' => false,
                'preview_url' => '/images/styles/quiff.jpg',
                'default_colors' => ['dark-brown', 'blonde', 'ash-blonde', 'chestnut'],
                'prompt_template' => 'modern quiff with textured volume'
            ],
            [
                'id' => 'french_crop',
                'name' => 'French Crop',
                'description' => 'Textured crop with fringe',
                'category' => 'Short',
                'complexity' => 'medium',
                'is_free' => false,
                'preview_url' => '/images/styles/french_crop.jpg',
                'default_colors' => ['dark-brown', 'medium-brown', 'blonde', 'ash-blonde'],
                'prompt_template' => 'french crop with textured fringe'
            ],
            [
                'id' => 'caesar_cut',
                'name' => 'Caesar Cut',
                'description' => 'Classic Roman-inspired cut',
                'category' => 'Short',
                'complexity' => 'low',
                'is_free' => false,
                'preview_url' => '/images/styles/caesar_cut.jpg',
                'default_colors' => ['black', 'dark-brown', 'gray', 'medium-brown'],
                'prompt_template' => 'caesar cut with forward fringe'
            ],
            [
                'id' => 'slick_back',
                'name' => 'Slick Back',
                'description' => 'Elegant slicked-back style',
                'category' => 'Professional',
                'complexity' => 'medium',
                'is_free' => false,
                'preview_url' => '/images/styles/slick_back.jpg',
                'default_colors' => ['black', 'dark-brown', 'gray', 'chestnut'],
                'prompt_template' => 'slicked back hair with shine'
            ],
            [
                'id' => 'textured_crop',
                'name' => 'Textured Crop',
                'description' => 'Modern textured short cut',
                'category' => 'Short',
                'complexity' => 'medium',
                'is_free' => false,
                'preview_url' => '/images/styles/textured_crop.jpg',
                'default_colors' => ['dark-brown', 'blonde', 'ash-blonde', 'honey-blonde'],
                'prompt_template' => 'textured crop with messy styling'
            ],
            [
                'id' => 'curly_top_fade',
                'name' => 'Curly Top Fade',
                'description' => 'Fade with curly textured top',
                'category' => 'Short',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/curly_top_fade.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'auburn'],
                'prompt_template' => 'curly top with faded sides'
            ],
            [
                'id' => 'afro',
                'name' => 'Afro',
                'description' => 'Natural Afro hairstyle',
                'category' => 'Cultural_Identity',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/afro.jpg',
                'default_colors' => ['black', 'dark-brown', 'medium-brown', 'auburn', 'gray'],
                'prompt_template' => 'natural afro hairstyle with volume'
            ],
            [
                'id' => 'dreadlocks',
                'name' => 'Dreadlocks',
                'description' => 'Traditional dreadlock style',
                'category' => 'Cultural_Identity',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/dreadlocks.jpg',
                'default_colors' => ['black', 'dark-brown', 'auburn', 'blonde', 'gray'],
                'prompt_template' => 'dreadlocks with natural texture'
            ],
            [
                'id' => 'man_bun',
                'name' => 'Man Bun',
                'description' => 'Long hair tied in a bun',
                'category' => 'Long',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/man_bun.jpg',
                'default_colors' => ['dark-brown', 'medium-brown', 'blonde', 'auburn', 'chestnut'],
                'prompt_template' => 'man bun with long hair tied up'
            ],
            [
                'id' => 'top_knot',
                'name' => 'Top Knot',
                'description' => 'Medium length top knot',
                'category' => 'Medium',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/top_knot.jpg',
                'default_colors' => ['black', 'dark-brown', 'blonde', 'ash-blonde'],
                'prompt_template' => 'top knot with undercut sides'
            ],
            [
                'id' => 'mohawk',
                'name' => 'Mohawk',
                'description' => 'Bold mohawk style',
                'category' => 'Trendy',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/mohawk.jpg',
                'default_colors' => ['black', 'blonde', 'pastel-pink', 'neon-green', 'teal'],
                'prompt_template' => 'mohawk with shaved sides and center strip'
            ],
            [
                'id' => 'faux_hawk',
                'name' => 'Faux Hawk',
                'description' => 'Subtle faux hawk style',
                'category' => 'Trendy',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/faux_hawk.jpg',
                'default_colors' => ['dark-brown', 'blonde', 'ash-blonde', 'honey-blonde'],
                'prompt_template' => 'faux hawk with styled center'
            ],
            [
                'id' => 'long_layered',
                'name' => 'Long Layered',
                'description' => 'Long layered hairstyle',
                'category' => 'Long',
                'complexity' => 'high',
                'is_free' => false,
                'preview_url' => '/images/styles/long_layered.jpg',
                'default_colors' => ['dark-brown', 'medium-brown', 'blonde', 'auburn', 'balayage-ombre', 'chestnut'],
                'prompt_template' => 'long layered hair with movement'
            ]
        ];

        // Filter by premium status
        $filteredStyles = array_filter($styles, function($style) use ($isPremium) {
            return $style['is_free'] || $isPremium;
        });

        // Filter by category if specified
        if ($category) {
            $filteredStyles = array_filter($filteredStyles, function($style) use ($category) {
                return $style['category'] === $category;
            });
        }

        return response()->json([
            'success' => true,
            'styles' => array_values($filteredStyles),
            'categories' => ['Short', 'Medium', 'Long', 'Professional', 'Trendy', 'Cultural_Identity', 'No_Hair'],
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

        // Filter by premium status
        $filteredColors = array_filter($colors, function($color) use ($isPremium) {
            return !$color['is_premium'] || $isPremium;
        });

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