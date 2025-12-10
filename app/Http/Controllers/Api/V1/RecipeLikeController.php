<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeLikeController extends Controller
{
    public function store(Request $request, $id)
    {
        $user = $request->user();

        $recipe = Recipe::query()->findOrFail($id);

        $alreadyLiked = Like::query()
            ->where('user_id', $user->id)
            ->where('recipe_id', $recipe->id)
            ->exists();

        if (! $alreadyLiked) {
            DB::transaction(function () use ($user, $recipe) {
                Like::query()->create([
                    'user_id' => $user->id,
                    'recipe_id' => $recipe->id,
                ]);

                $recipe->increment('likes_count');
            });
        }

        $recipe->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Resep berhasil disukai',
            'data' => [
                'is_liked' => true,
                'likes_count' => (int) $recipe->likes_count,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-Id') ?? (string) \Illuminate\Support\Str::uuid(),
            ],
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $recipe = Recipe::query()->findOrFail($id);

        $like = Like::query()
            ->where('user_id', $user->id)
            ->where('recipe_id', $recipe->id)
            ->first();

        if ($like) {
            DB::transaction(function () use ($like, $recipe) {
                $like->delete();
                if ((int) $recipe->likes_count > 0) {
                    $recipe->decrement('likes_count');
                }
            });
        }

        $recipe->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Like berhasil dihapus',
            'data' => [
                'is_liked' => false,
                'likes_count' => (int) $recipe->likes_count,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-Id') ?? (string) \Illuminate\Support\Str::uuid(),
            ],
        ], 200);
    }
}
