<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestSupabaseController extends Controller
{
    /**
     * Test koneksi ke Supabase Storage
     */
    public function test()
    {
        $supabaseUrl = config('services.supabase.url');
        $serviceRoleKey = config('services.supabase.service_role_key');
        $bucket = config('services.supabase.bucket_avatars', 'avatars');

        // Cek konfigurasi
        $config = [
            'supabase_url' => $supabaseUrl ?  '✅ Set' : '❌ Not Set',
            'service_role_key' => $serviceRoleKey ? '✅ Set (' . substr($serviceRoleKey, 0, 20) . '...)' : '❌ Not Set',
            'bucket_avatars' => $bucket,
            'bucket_recipes' => config('services.supabase.bucket_recipes', 'recipes'),
        ];

        // Test koneksi ke Supabase - List buckets
        $testConnection = [
            'status' => 'unknown',
            'message' => '',
            'buckets' => [],
        ];

        if ($supabaseUrl && $serviceRoleKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $serviceRoleKey,
                    'apikey' => $serviceRoleKey,
                ])->get("{$supabaseUrl}/storage/v1/bucket");

                if ($response->successful()) {
                    $testConnection['status'] = '✅ Connected';
                    $testConnection['message'] = 'Berhasil terhubung ke Supabase Storage';
                    $testConnection['buckets'] = collect($response->json())->pluck('name')->toArray();
                } else {
                    $testConnection['status'] = '❌ Failed';
                    $testConnection['message'] = 'Status: ' . $response->status() . ' - ' . $response->body();
                }
            } catch (\Exception $e) {
                $testConnection['status'] = '❌ Error';
                $testConnection['message'] = $e->getMessage();
            }
        } else {
            $testConnection['status'] = '❌ Not Configured';
            $testConnection['message'] = 'SUPABASE_URL atau SUPABASE_SERVICE_ROLE_KEY belum diset di . env';
        }

        // Test list files di bucket
        $bucketFiles = [
            'status' => 'unknown',
            'files' => [],
        ];

        if ($testConnection['status'] === '✅ Connected') {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $serviceRoleKey,
                    'apikey' => $serviceRoleKey,
                ])->post("{$supabaseUrl}/storage/v1/object/list/{$bucket}", [
                    'limit' => 10,
                    'offset' => 0,
                ]);

                if ($response->successful()) {
                    $bucketFiles['status'] = '✅ Can Access Bucket';
                    $bucketFiles['files'] = $response->json();
                } else {
                    $bucketFiles['status'] = '❌ Cannot Access Bucket';
                    $bucketFiles['message'] = $response->body();
                }
            } catch (\Exception $e) {
                $bucketFiles['status'] = '❌ Error';
                $bucketFiles['message'] = $e->getMessage();
            }
        }

        return response()->json([
            'title' => '🧪 Supabase Storage Connection Test',
            'config' => $config,
            'connection_test' => $testConnection,
            'bucket_test' => $bucketFiles,
            'env_example' => [
                'SUPABASE_URL' => 'https://xxxxx.supabase.co',
                'SUPABASE_SERVICE_ROLE_KEY' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.. .',
                'SUPABASE_BUCKET_AVATARS' => 'avatars',
                'SUPABASE_BUCKET_RECIPES' => 'recipes',
            ],
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Test upload file ke Supabase Storage
     */
    public function testUpload(Request $request)
    {
        $supabaseUrl = config('services.supabase.url');
        $serviceRoleKey = config('services.supabase.service_role_key');
        $bucket = $request->input('bucket', config('services.supabase.bucket_avatars', 'avatars'));

        if (!$supabaseUrl || ! $serviceRoleKey) {
            return response()->json([
                'success' => false,
                'message' => 'Supabase belum dikonfigurasi di .env',
            ], 400);
        }

        // Buat file test sederhana (1x1 pixel PNG)
        $testFileName = 'test_upload_' . time() . '.txt';
        $testContent = 'Test upload dari CookChella API - ' . now()->toDateTimeString();

        try {
            // Upload file
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $serviceRoleKey,
                'apikey' => $serviceRoleKey,
                'Content-Type' => 'text/plain',
            ])->withBody($testContent, 'text/plain')
              ->post("{$supabaseUrl}/storage/v1/object/{$bucket}/{$testFileName}");

            if ($response->successful()) {
                $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$testFileName}";

                return response()->json([
                    'success' => true,
                    'message' => '✅ Upload berhasil! ',
                    'data' => [
                        'bucket' => $bucket,
                        'file_name' => $testFileName,
                        'public_url' => $publicUrl,
                        'response' => $response->json(),
                    ],
                    'next_step' => "Buka URL ini di browser untuk verifikasi: {$publicUrl}",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Upload gagal',
                    'error' => [
                        'status' => $response->status(),
                        'body' => $response->json() ?? $response->body(),
                    ],
                    'troubleshoot' => [
                        'Pastikan bucket sudah dibuat di Supabase Dashboard',
                        'Pastikan bucket policy mengizinkan upload',
                        'Cek apakah SERVICE_ROLE_KEY benar',
                    ],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error saat upload',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test upload image ke Supabase Storage
     */
    public function testUploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bucket' => 'nullable|string',
        ]);

        $supabaseUrl = config('services.supabase.url');
        $serviceRoleKey = config('services.supabase.service_role_key');
        $bucket = $request->input('bucket', config('services.supabase.bucket_avatars', 'avatars'));

        if (!$supabaseUrl || ! $serviceRoleKey) {
            return response()->json([
                'success' => false,
                'message' => 'Supabase belum dikonfigurasi',
            ], 400);
        }

        $file = $request->file('image');
        $fileName = 'test_' . time() . '.' . $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $serviceRoleKey,
                'apikey' => $serviceRoleKey,
                'Content-Type' => $mimeType,
            ])->withBody(file_get_contents($file->getRealPath()), $mimeType)
              ->post("{$supabaseUrl}/storage/v1/object/{$bucket}/{$fileName}");

            if ($response->successful()) {
                $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$fileName}";

                return response()->json([
                    'success' => true,
                    'message' => '✅ Image upload berhasil!',
                    'data' => [
                        'bucket' => $bucket,
                        'file_name' => $fileName,
                        'public_url' => $publicUrl,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Upload gagal',
                    'error' => $response->json() ?? $response->body(),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}