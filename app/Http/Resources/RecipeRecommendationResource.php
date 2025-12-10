<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecipeRecommendationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image,
            'cooking_time' => $this->cooking_time,
            'likes_count' => $this->likes_count ?? 0,
        ];
    }
}
