<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComicResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => ImageHelper::cdnUrl($this->cover_image),
            'status' => $this->status,
            'type' => $this->type,
            'total_chapters' => $this->total_chapters,
            'total_views' => $this->total_views,
            'average_rating' => $this->average_rating ? (float) $this->average_rating : null,
            'total_ratings' => $this->total_ratings,
            'release_date' => $this->release_date?->format('Y-m-d'),
            'is_featured' => (bool) $this->is_featured,

            // Relationships
            'authors' => AuthorResource::collection($this->whenLoaded('authors')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'chapters' => ChapterResource::collection($this->whenLoaded('chapters')),

            // Counts
            'chapters_count' => $this->when(isset($this->chapters_count), $this->chapters_count),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
