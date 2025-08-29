<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Style Controller
 * Manages hairstyles and colors, including premium content
 */
class StyleController extends Controller
{
    /**
     * Get all available styles with premium indicators
     */
    public function getStyles(Request $request)
    {
        $userIsPremium = $request->boolean('is_premium', false);

        $styles = [
            // Free styles (available to everyone)
            [
                'id' => 'short_classic',
                'name' => 'Short Classic',
                'gender' => 'both',
                'is_premium' => false,
                'preview_url' => '/images/styles/short_classic.jpg',
                'description' => 'Classic short hairstyle'
            ],
            [
                'id' => 'medium_waves',
                'name' => 'Medium Waves',
                'gender' => 'both',
                'is_premium' => false,
                'preview_url' => '/images/styles/medium_waves.jpg',
                'description' => 'Natural wavy medium-length hair'
            ],
            [
                'id' => 'long_straight',
                'name' => 'Long Straight',
                'gender' => 'both',
                'is_premium' => false,
                'preview_url' => '/images/styles/long_straight.jpg',
                'description' => 'Long straight hair'
            ],

            // Premium styles (require subscription or premium tokens)
            [
                'id' => 'pixie_textured',
                'name' => 'Textured Pixie',
                'gender' => 'female',
                'is_premium' => true,
                'preview_url' => '/images/styles/pixie_textured.jpg',
                'description' => 'Modern textured pixie cut with layers'
            ],
            [
                'id' => 'undercut_fade',
                'name' => 'Undercut Fade',
                'gender' => 'male',
                'is_premium' => true,
                'preview_url' => '/images/styles/undercut_fade.jpg',
                'description' => 'Sharp undercut with fade transition'
            ],
            [
                'id' => 'beach_waves',
                'name' => 'Beach Waves',
                'gender' => 'female',
                'is_premium' => true,
                'preview_url' => '/images/styles/beach_waves.jpg',
                'description' => 'Effortless beach wave texture'
            ],
            [
                'id' => 'man_bun',
                'name' => 'Modern Man Bun',
                'gender' => 'male',
                'is_premium' => true,
                'preview_url' => '/images/styles/man_bun.jpg',
                'description' => 'Styled man bun with undercut sides'
            ],
            [
                'id' => 'layered_bob',
                'name' => 'Layered Bob',
                'gender' => 'female',
                'is_premium' => true,
                'preview_url' => '/images/styles/layered_bob.jpg',
                'description' => 'Sophisticated layered bob with movement'
            ]
        ];

        // Filter styles based on premium status
        $availableStyles = array_filter($styles, function ($style) use ($userIsPremium) {
            return !$style['is_premium'] || $userIsPremium;
        });

        return response()->json([
            'success' => true,
            'styles' => array_values($availableStyles),
            'total_styles' => count($styles),
            'available_styles' => count($availableStyles),
            'premium_styles_locked' => !$userIsPremium ? count($styles) - count($availableStyles) : 0
        ]);
    }

    /**
     * Get all available colors with premium indicators
     */
    public function getColors(Request $request)
    {
        $userIsPremium = $request->boolean('is_premium', false);

        $colors = [
            // Free colors (available to everyone)
            [
                'id' => 'natural_brown',
                'name' => 'Natural Brown',
                'hex' => '#8B4513',
                'is_premium' => false,
                'description' => 'Rich natural brown'
            ],
            [
                'id' => 'classic_black',
                'name' => 'Classic Black',
                'hex' => '#000000',
                'is_premium' => false,
                'description' => 'Deep classic black'
            ],
            [
                'id' => 'honey_blonde',
                'name' => 'Honey Blonde',
                'hex' => '#DAA520',
                'is_premium' => false,
                'description' => 'Warm honey blonde'
            ],
            [
                'id' => 'auburn_red',
                'name' => 'Auburn Red',
                'hex' => '#A52A2A',
                'is_premium' => false,
                'description' => 'Classic auburn red'
            ],

            // Premium colors (require subscription or premium tokens)
            [
                'id' => 'platinum_blonde',
                'name' => 'Platinum Blonde',
                'hex' => '#E5E4E2',
                'is_premium' => true,
                'description' => 'Ultra-light platinum blonde'
            ],
            [
                'id' => 'rose_gold',
                'name' => 'Rose Gold',
                'hex' => '#B76E79',
                'is_premium' => true,
                'description' => 'Trendy rose gold shade'
            ],
            [
                'id' => 'silver_grey',
                'name' => 'Silver Grey',
                'hex' => '#C0C0C0',
                'is_premium' => true,
                'description' => 'Modern silver grey'
            ],
            [
                'id' => 'electric_blue',
                'name' => 'Electric Blue',
                'hex' => '#0080FF',
                'is_premium' => true,
                'description' => 'Vibrant electric blue'
            ],
            [
                'id' => 'violet_purple',
                'name' => 'Violet Purple',
                'hex' => '#8A2BE2',
                'is_premium' => true,
                'description' => 'Rich violet purple'
            ],
            [
                'id' => 'emerald_green',
                'name' => 'Emerald Green',
                'hex' => '#50C878',
                'is_premium' => true,
                'description' => 'Bold emerald green'
            ],
            [
                'id' => 'sunset_orange',
                'name' => 'Sunset Orange',
                'hex' => '#FF8C00',
                'is_premium' => true,
                'description' => 'Vibrant sunset orange'
            ],
            [
                'id' => 'cotton_candy',
                'name' => 'Cotton Candy Pink',
                'hex' => '#FFB6C1',
                'is_premium' => true,
                'description' => 'Soft cotton candy pink'
            ]
        ];

        // Filter colors based on premium status
        $availableColors = array_filter($colors, function ($color) use ($userIsPremium) {
            return !$color['is_premium'] || $userIsPremium;
        });

        return response()->json([
            'success' => true,
            'colors' => array_values($availableColors),
            'total_colors' => count($colors),
            'available_colors' => count($availableColors),
            'premium_colors_locked' => !$userIsPremium ? count($colors) - count($availableColors) : 0
        ]);
    }

    /**
     * Get subscription plans
     */
    public function getSubscriptionPlans()
    {
        $plans = [
            [
                'id' => 'basic',
                'name' => 'Basic Plan',
                'price' => 999, // $9.99
                'price_id' => 'price_basic_monthly',
                'monthly_tokens' => 50,
                'features' => [
                    '50 transformations per month',
                    'Access to all basic styles',
                    'Standard processing speed',
                    'Email support'
                ],
                'popular' => false
            ],
            [
                'id' => 'premium',
                'name' => 'Premium Plan',
                'price' => 1999, // $19.99
                'price_id' => 'price_premium_monthly',
                'monthly_tokens' => 200,
                'features' => [
                    '200 transformations per month',
                    'Access to ALL premium styles',
                    'Exclusive color palette',
                    'Priority processing',
                    'Premium support'
                ],
                'popular' => true
            ],
            [
                'id' => 'pro',
                'name' => 'Pro Plan',
                'price' => 4999, // $49.99
                'price_id' => 'price_pro_monthly',
                'monthly_tokens' => 500,
                'features' => [
                    '500 transformations per month',
                    'Access to ALL premium styles',
                    'Exclusive color palette',
                    'Priority processing',
                    'API access',
                    'Dedicated support',
                    'Custom style requests'
                ],
                'popular' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    /**
     * Get token packages for one-time purchases
     */
    public function getTokenPackages()
    {
        $packages = [
            [
                'id' => 'small',
                'name' => 'Starter Pack',
                'tokens' => 25,
                'price' => 499, // $4.99
                'price_per_token' => 0.20,
                'description' => 'Perfect for trying premium features'
            ],
            [
                'id' => 'medium',
                'name' => 'Popular Pack',
                'tokens' => 60,
                'price' => 999, // $9.99
                'price_per_token' => 0.17,
                'description' => 'Great value for regular users',
                'popular' => true
            ],
            [
                'id' => 'large',
                'name' => 'Power Pack',
                'tokens' => 150,
                'price' => 1999, // $19.99
                'price_per_token' => 0.13,
                'description' => 'Best value for heavy users'
            ]
        ];

        return response()->json([
            'success' => true,
            'packages' => $packages
        ]);
    }
}
