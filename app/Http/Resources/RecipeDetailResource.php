<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecipeDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image,
            'description' => $this->description,
            'cooking_time' => $this->cooking_time,
            'servings' => $this->servings,
            'likes_count' => $this->likes_count ?? 0,
            'bookmarks_count' => $this->bookmarks_count ?? 0,
            'is_liked' => (bool) ($this->is_liked ?? false),
            'is_bookmarked' => (bool) ($this->is_bookmarked ?? false),

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'username' => $this->user?->username,
                'avatar_url' => $this->user?->avatar ?? null,
                'followers_count' => $this->user?->followers_count ?? 0,
                'is_followed' => (bool) ($this->is_followed ?? false),
            ],

            'ingredients' => $this->ingredients?->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->name,
                    'quantity' => $i->quantity,
                    'unit' => $i->unit,
                ];
            })->values(),

            'steps' => $this->steps?->map(function ($s) {
                return [
                    'id' => $s->id,
                    'step_number' => $s->step_number,
                    'description' => $s->description,
                    'image_url' => $s->image ?? null,
                ];
            })->values(),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
