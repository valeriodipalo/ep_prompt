<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * PRODUCTION-READY Supabase Controller
 * Implements scalable patterns following Supabase best practices
 */
class SupabaseScalableController extends Controller
{
    private string $supabaseUrl;
    private string $supabaseAnonKey;
    private string $supabaseServiceKey;
    private string $bucketName = 'hairstyle-images';

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseAnonKey = env('SUPABASE_ANON_KEY');
        $this->supabaseServiceKey = env('SUPABASE_SERVICE_KEY');
    }

    /**
     * SCALABLE image upload with proper error handling and optimization
     */
    public function uploadImageScalable(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        try {
            $this->validateConfig();
            
            // Enhanced validation for production
            $request->validate([
                'image' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpeg,jpg,png,webp,gif',
                    'max:10240', // 10MB
                    'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
                ]
            ]);

            $image = $request->file('image');
            
            // Generate optimized filename with timestamp for better organization
            $fileName = $this->generateOptimizedFileName($image);
            
            Log::info('Supabase Scalable: Starting upload', [
                'filename' => $fileName,
                'size' => $image->getSize(),
                'mime_type' => $image->getMimeType()
            ]);

            // SCALABLE APPROACH 1: Try signed upload URL first (recommended for production)
            $uploadResult = $this->uploadViaSignedUrl($image, $fileName);
            
            if (!$uploadResult['success']) {
                // FALLBACK: Direct upload with service key
                Log::info('Supabase Scalable: Signed URL failed, trying direct upload');
                $uploadResult = $this->uploadDirect($image, $fileName);
            }

            if (!$uploadResult['success']) {
                throw new \Exception('All upload methods failed: ' . $uploadResult['error']);
            }

            // Generate public URL
            $publicUrl = $this->generatePublicUrl($fileName);
            
            // Verify URL accessibility (important for fal.ai compatibility)
            $isAccessible = $this->verifyUrlAccessibility($publicUrl);
            
            if (!$isAccessible) {
                Log::error('Supabase Scalable: Uploaded file not publicly accessible', [
                    'url' => $publicUrl
                ]);
                // Don't fail here - might be temporary
            }

            // Save metadata to database (async for better performance)
            $this->saveImageMetadataAsync($fileName, $publicUrl, $image);

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Supabase Scalable: Upload completed successfully', [
                'filename' => $fileName,
                'url' => $publicUrl,
                'duration_ms' => $duration,
                'accessible' => $isAccessible
            ]);

            return response()->json([
                'success' => true,
                'url' => $publicUrl,
                'file_name' => $fileName,
                'accessible' => $isAccessible,
                'upload_method' => $uploadResult['method'],
                'duration_ms' => $duration
            ]);

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Supabase Scalable: Upload failed', [
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage(),
                'duration_ms' => $duration
            ], 500);
        }
    }

    /**
     * PRODUCTION METHOD: Upload via signed URL (recommended by Supabase for scalability)
     */
    private function uploadViaSignedUrl($image, $fileName): array
    {
        try {
            // Step 1: Get signed upload URL
            $signedUrlResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->post($this->supabaseUrl . '/storage/v1/object/upload/sign/' . $this->bucketName . '/' . $fileName, [
                'upsert' => true
            ]);

            if (!$signedUrlResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Failed to get signed URL: ' . $signedUrlResponse->body()
                ];
            }

            $signedData = $signedUrlResponse->json();
            $uploadUrl = $signedData['url'];

            // Step 2: Upload file to signed URL
            $uploadResponse = Http::withHeaders([
                'Content-Type' => $image->getMimeType()
            ])->attach(
                'file',
                file_get_contents($image->getRealPath()),
                $fileName
            )->post($uploadUrl);

            if ($uploadResponse->successful()) {
                return [
                    'success' => true,
                    'method' => 'signed_url'
                ];
            }

            return [
                'success' => false,
                'error' => 'Signed URL upload failed: ' . $uploadResponse->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Signed URL method error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * FALLBACK METHOD: Direct upload
     */
    private function uploadDirect($image, $fileName): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => $image->getMimeType(),
            ])->attach(
                'file',
                file_get_contents($image->getRealPath()),
                $fileName
            )->post($this->supabaseUrl . '/storage/v1/object/' . $this->bucketName . '/' . $fileName);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'method' => 'direct_upload'
                ];
            }

            return [
                'success' => false,
                'error' => 'Direct upload failed: ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Direct upload error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate optimized filename for better organization and performance
     */
    private function generateOptimizedFileName($image): string
    {
        $timestamp = date('Y/m/d'); // Organize by date for better performance
        $uuid = Str::uuid();
        $extension = $image->getClientOriginalExtension();
        
        return "{$timestamp}/hairstyle_{$uuid}.{$extension}";
    }

    /**
     * Generate public URL following Supabase best practices
     */
    private function generatePublicUrl($fileName): string
    {
        return $this->supabaseUrl . '/storage/v1/object/public/' . $this->bucketName . '/' . $fileName;
    }

    /**
     * Verify URL accessibility (critical for fal.ai compatibility)
     */
    private function verifyUrlAccessibility($url): bool
    {
        try {
            $response = Http::timeout(10)->head($url);
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('URL accessibility check failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Save metadata asynchronously for better performance
     */
    private function saveImageMetadataAsync($fileName, $url, $image): void
    {
        // Use Laravel queues for production (for now, just async call)
        try {
            $imageData = [
                'file_name' => $fileName,
                'original_name' => $image->getClientOriginalName(),
                'url' => $url,
                'size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'uploaded_at' => now()->toISOString()
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal'
            ])->post($this->supabaseUrl . '/rest/v1/uploaded_images', $imageData);

        } catch (\Exception $e) {
            Log::warning('Async metadata save failed (non-critical)', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate Supabase configuration
     */
    private function validateConfig(): void
    {
        if (!$this->supabaseUrl || !$this->supabaseAnonKey || !$this->supabaseServiceKey) {
            throw new \Exception('Supabase configuration incomplete. Check SUPABASE_URL, SUPABASE_ANON_KEY, and SUPABASE_SERVICE_KEY');
        }
    }

    /**
     * Get upload statistics (useful for monitoring)
     */
    public function getUploadStats(): JsonResponse
    {
        try {
            $this->validateConfig();

            // Get recent upload statistics
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->get($this->supabaseUrl . '/rest/v1/uploaded_images', [
                'select' => 'count',
                'uploaded_at' => 'gte.' . now()->subDay()->toISOString()
            ]);

            $stats = $response->json();

            return response()->json([
                'success' => true,
                'uploads_last_24h' => count($stats),
                'bucket_name' => $this->bucketName,
                'storage_url' => $this->supabaseUrl . '/storage/v1'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
