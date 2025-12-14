<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'bio' => $this->bio,
            'avatar' => ImageHelper::cdnUrl($this->avatar),
            'website' => $this->website,

            // Pivot data (role from author_comic table)
            'role' => $this->whenPivotLoaded('author_comic', function () {
                return $this->pivot->role;
            }),

            // Counts
            'comics_count' => $this->when(isset($this->comics_count), $this->comics_count),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
