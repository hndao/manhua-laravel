<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\PageResource;
use App\Models\Chapter;

class ChapterController extends Controller
{
    /**
     * Display the specified chapter with comic info
     */
    public function show(Chapter $chapter)
    {
        // Increment views
        $chapter->increment('views');

        $chapter->load(['comic.authors', 'comic.genres']);

        return new ChapterResource($chapter);
    }

    /**
     * Get pages for a specific chapter
     */
    public function pages(Chapter $chapter)
    {
        $pages = $chapter->pages()
            ->with('chapter')
            ->orderBy('page_number', 'asc')
            ->get();

        return PageResource::collection($pages);
    }
}
