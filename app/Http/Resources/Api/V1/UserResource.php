<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,

            // Only show email to the owner
            'email' => $this->when($isOwner, $this->email),

            'avatar_url' => $this->avatar_url,
            'followers_count' => $this->followers_count ??  0,
            'following_count' => $this->following_count ?? 0,
            'recipes_count' => $this->when(
                $this->relationLoaded('recipes') || isset($this->recipes_count),
                fn() => $this->recipes_count ??  $this->recipes->count()
            ),

            // Only show language preference to the owner
            'language' => $this->when($isOwner, $this->language?->value ?? 'id'),

            // Show follow status when viewing other users
            'is_followed' => $this->when(
                ! $isOwner && $request->user(),
                fn() => $this->isFollowedBy($request->user())
            ),

            'email_verified_at' => $this->when($isOwner, $this->email_verified_at?->toISOString()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->when($isOwner, $this->updated_at->toISOString()),
        ];
    }
}