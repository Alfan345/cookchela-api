<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;

class UserRecipeController extends Controller
{
    public function index(Request $request, string $username)
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 50));

        $user = User::where('username', $username)->firstOrFail();

        $recipes = Recipe::query()
            ->where('user_id', $user->id)
            ->select(['id', 'title', 'image', 'cooking_time', 'servings', 'likes_count', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $data = $recipes->getCollection()->map(function ($r) {
            return [
                'id' => $r->id,
                'title' => $r->title,
                'image_url' => $r->image,
                'cooking_time' => $r->cooking_time,
                'servings' => $r->servings,
                'likes_count' => $r->likes_count ?? 0,
                'created_at' => $r->created_at,
            ];
        });

        $recipes->setCollection($data);

        return response()->json([
            'success' => true,
            'message' => 'Daftar resep berhasil diambil',
            'data' => $recipes->items(),
            'pagination' => [
                'current_page' => $recipes->currentPage(),
                'last_page' => $recipes->lastPage(),
                'per_page' => $recipes->perPage(),
                'total' => $recipes->total(),
                'from' => $recipes->firstItem(),
                'to' => $recipes->lastItem(),
                'has_more_pages' => $recipes->hasMorePages(),
            ],
        ]);
    }
}
