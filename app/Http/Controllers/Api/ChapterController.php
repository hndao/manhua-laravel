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
        // Increment views (only once per request)
        $chapter->increment('views');

        // Cache chapter details for 10 minutes
        $cacheKey = "chapter_detail_{$chapter->id}";

        $chapterData = \Cache::remember($cacheKey, 600, function () use ($chapter) {
            $chapter->load([
                'comic.authors',
                'comic.genres',
                'comic.chapters' => function ($query) {
                    $query->orderBy('chapter_number', 'asc');
                }
            ]);

            return $chapter;
        });

        return new ChapterResource($chapterData);
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
