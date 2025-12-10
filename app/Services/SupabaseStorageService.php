<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    protected string $url;
    protected string $serviceKey;
    protected string $bucketRecipes;

    public function __construct()
    {
        // Pastikan di .env sudah ada:
        // SUPABASE_URL=https://xxxxx.supabase.co
        // SUPABASE_SERVICE_ROLE_KEY=...
        // SUPABASE_BUCKET_RECIPES=recipes
        $this->url           = rtrim(env('SUPABASE_URL', ''), '/');
        $this->serviceKey    = env('SUPABASE_SERVICE_ROLE_KEY', '');
        $this->bucketRecipes = env('SUPABASE_BUCKET_RECIPES', 'recipes');
    }

    /**
     * Header default untuk semua request ke Supabase Storage
     */
    protected function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->serviceKey}",
            'apikey'        => $this->serviceKey,
        ];
    }

    /**
     * Upload image recipe ke bucket Supabase
     * return: full public URL (disimpan di kolom image)
     */
    public function uploadRecipeImage(int $recipeId, UploadedFile $file): string
    {
        $ext      = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'image_' . time() . '_' . Str::random(6) . '.' . $ext;
        $path     = "{$recipeId}/{$filename}";

        // Endpoint upload direct object
        $endpoint = "{$this->url}/storage/v1/object/{$this->bucketRecipes}/{$path}";

        $response = Http::withHeaders($this->headers())
            ->attach('file', file_get_contents($file->getRealPath()), $filename)
            ->post($endpoint);

        if (! $response->successful()) {
            // Optional: bisa dd($response->body()) pas debugging
            throw new \RuntimeException('Gagal upload image ke Supabase Storage');
        }

        // Public URL pattern dari kontrak
        return "{$this->url}/storage/v1/object/public/{$this->bucketRecipes}/{$path}";
    }

    /**
     * Normalize path yang disimpan:
     * - Bisa berupa full URL public
     * - Bisa berupa path di bucket (mis: "19/image_xxx.png")
     * - Bisa juga "recipes/19/image_xxx.png"
     */
    protected function normalizeRecipePath(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $publicPrefix  = "{$this->url}/storage/v1/object/public/{$this->bucketRecipes}/";
        $privatePrefix = "{$this->url}/storage/v1/object/{$this->bucketRecipes}/";

        // Kalau value = full public URL
        if (Str::startsWith($value, $publicPrefix)) {
            return Str::after($value, $publicPrefix);
        }

        // Kalau value = full private URL
        if (Str::startsWith($value, $privatePrefix)) {
            return Str::after($value, $privatePrefix);
        }

        // Kalau value diawali nama bucket, contoh: "recipes/19/image_xxx.png"
        if (Str::startsWith($value, "{$this->bucketRecipes}/")) {
            return Str::after($value, "{$this->bucketRecipes}/");
        }

        // Selain itu anggap sudah relative path di dalam bucket, ex: "19/image_xxx.png"
        return $value;
    }

    /**
     * Hapus image recipe dari Supabase Storage
     * Dipakai di RecipeService::deleteRecipe()
     */
    public function deleteRecipeImage(string $storedPathOrUrl): void
    {
        $path = $this->normalizeRecipePath($storedPathOrUrl);

        if ($path === '') {
            // Nggak ada yang perlu dihapus
            return;
        }

        // Coba delete langsung object-nya
        $endpoint = "{$this->url}/storage/v1/object/{$this->bucketRecipes}/{$path}";

        $response = Http::withHeaders($this->headers())
            ->delete($endpoint);

        // Kalau gagal, untuk sekarang kita nggak throw supaya
        // penghapusan resep tetap lanjut. Kalau mau strict bisa di-throw.
        // if (! $response->successful()) {
        //     throw new \RuntimeException('Gagal menghapus image dari Supabase Storage');
        // }
    }
}
