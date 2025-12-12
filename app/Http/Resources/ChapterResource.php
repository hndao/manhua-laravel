<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
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
            'comic_id' => $this->comic_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'chapter_number' => $this->chapter_number,
            'volume_number' => $this->volume_number,
            'total_pages' => $this->total_pages,
            'views' => $this->views,
            'published_at' => $this->published_at?->toISOString(),

            // Relationships
            'comic' => new ComicResource($this->whenLoaded('comic')),
            'pages' => PageResource::collection($this->whenLoaded('pages')),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
