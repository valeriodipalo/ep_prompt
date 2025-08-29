<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupabaseController extends Controller
{
    private string $supabaseUrl;
    private string $supabaseKey;
    private string $supabaseServiceKey;
    private string $bucketName = 'hairstyle-images';

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_ANON_KEY');
        $this->supabaseServiceKey = env('SUPABASE_SERVICE_KEY');
    }

    private function checkSupabaseConfig()
    {
        if (!$this->supabaseUrl || !$this->supabaseKey) {
            throw new \Exception('Supabase configuration is missing. Please set SUPABASE_URL and SUPABASE_ANON_KEY in your .env file');
        }
    }

    /**
     * Upload image to Supabase Storage
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $this->checkSupabaseConfig();

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10240' // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid image file',
                    'details' => $validator->errors()
                ], 400);
            }

            $image = $request->file('image');
            $fileName = 'hairstyle_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Upload to Supabase Storage - FIXED: Use raw binary upload, not multipart
            $imageData = file_get_contents($image->getRealPath());
            
            Log::info('Supabase upload: Image data prepared', [
                'original_size' => $image->getSize(),
                'read_size' => strlen($imageData),
                'mime_type' => $image->getMimeType()
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => $image->getMimeType(),
                'Cache-Control' => 'max-age=3600'
            ])->withBody($imageData, $image->getMimeType())
              ->post($this->supabaseUrl . '/storage/v1/object/' . $this->bucketName . '/' . $fileName);

            if (!$response->successful()) {
                Log::error('Supabase upload error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Failed to upload image to storage',
                    'message' => 'Storage service temporarily unavailable'
                ], 500);
            }

            // Get public URL
            $publicUrl = $this->supabaseUrl . '/storage/v1/object/public/hairstyle-images/' . $fileName;

            // Save image record to database
            $imageRecord = $this->saveImageRecord($fileName, $publicUrl, $image);

            return response()->json([
                'success' => true,
                'url' => $publicUrl,
                'file_name' => $fileName,
                'image_id' => $imageRecord['id'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Image upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to upload image',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save image record to Supabase database
     */
    private function saveImageRecord(string $fileName, string $url, $imageFile): array
    {
        try {
            $imageData = [
                'file_name' => $fileName,
                'original_name' => $imageFile->getClientOriginalName(),
                'url' => $url,
                'size' => $imageFile->getSize(),
                'mime_type' => $imageFile->getMimeType(),
                'uploaded_at' => now()->toISOString()
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($this->supabaseUrl . '/rest/v1/uploaded_images', $imageData);

            if ($response->successful()) {
                $result = $response->json();
                return is_array($result) && count($result) > 0 ? $result[0] : [];
            }

            Log::warning('Failed to save image record to database', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];

        } catch (\Exception $e) {
            Log::warning('Failed to save image record', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get image by ID
     */
    public function getImage(Request $request): JsonResponse
    {
        try {
            $this->checkSupabaseConfig();

            $validator = Validator::make($request->all(), [
                'image_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid image ID'
                ], 400);
            }

            $imageId = $request->input('image_id');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => 'application/json'
            ])->get($this->supabaseUrl . '/rest/v1/uploaded_images?id=eq.' . $imageId);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to retrieve image'
                ], 500);
            }

            $images = $response->json();
            
            if (empty($images)) {
                return response()->json([
                    'error' => 'Image not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'image' => $images[0]
            ]);

        } catch (\Exception $e) {
            Log::error('Get image error', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Failed to retrieve image'
            ], 500);
        }
    }

    /**
     * Delete image from storage and database
     */
    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $this->checkSupabaseConfig();

            $validator = Validator::make($request->all(), [
                'file_name' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'File name is required'
                ], 400);
            }

            $fileName = $request->input('file_name');

            // Delete from storage
            $storageResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
            ])->delete($this->supabaseUrl . '/storage/v1/object/hairstyle-images/' . $fileName);

            // Delete from database
            $dbResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                'Content-Type' => 'application/json'
            ])->delete($this->supabaseUrl . '/rest/v1/uploaded_images?file_name=eq.' . $fileName);

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete image error', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Failed to delete image'
            ], 500);
        }
    }
}
