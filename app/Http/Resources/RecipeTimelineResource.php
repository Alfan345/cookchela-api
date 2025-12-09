<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecipeTimelineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image,
            'description' => $this->description ? mb_substr($this->description, 0, 120) : null,
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
            ],
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
