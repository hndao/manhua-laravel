<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReadingHistoryResource extends JsonResource
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
            'user_id' => $this->user_id,
            'comic_id' => $this->comic_id,
            'chapter_id' => $this->chapter_id,
            'last_page_read' => $this->last_page_read,
            'last_read_at' => $this->last_read_at?->toISOString(),

            // Relationships
            'comic' => new ComicResource($this->whenLoaded('comic')),
            'chapter' => new ChapterResource($this->whenLoaded('chapter')),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
