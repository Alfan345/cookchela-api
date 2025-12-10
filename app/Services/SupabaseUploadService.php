<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class SupabaseUploadService
{
    /**
     * Upload a file to Supabase Storage.
     *
     * @param string $bucket     Bucket name (e.g. 'avatars', 'recipes')
     * @param string $path       File path/filename, e.g. '1/avatar_1234.jpg'
     * @param UploadedFile $file The file to upload (instance of UploadedFile)
     * @return string            The relative path (to save in DB), or throw Exception if failed
     * @throws \Exception
     */
    public static function upload(string $bucket, string $path, UploadedFile $file): string
    {
        $supabaseUrl = config('services.supabase.url');
        $serviceRoleKey = config('services.supabase.service_role_key');

        $endpoint = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$path}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceRoleKey,
            'apikey' => $serviceRoleKey,
            'Content-Type' => $file->getMimeType(),
        ])->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
          ->post($endpoint);

        if (!$response->successful()) {
            throw new \Exception('Gagal upload ke Supabase! ' . $response->body());
        }

        // Return path yang disimpan, agar URL publik bisa diakses dengan accessor Model
        return $path;
    }
}