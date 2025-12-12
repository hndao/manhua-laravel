<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookmarkResource;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ComicResource;
use App\Http\Resources\ReadingHistoryResource;
use App\Models\Comic;
use Illuminate\Http\Request;

class ComicController extends Controller
{
    /**
     * Display a listing of comics with pagination and filters
     */
    public function index(Request $request)
    {
        $query = Comic::with(['authors', 'genres'])
            ->withCount('chapters');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by genre
        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('slug', $request->genre);
            });
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);

        return ComicResource::collection($query->paginate($perPage));
    }

    /**
     * Display the specified comic
     */
    public function show(Comic $comic)
    {
        $comic->load(['authors', 'genres', 'chapters' => function ($query) {
            $query->orderBy('chapter_number', 'asc');
        }]);

        return new ComicResource($comic);
    }

    /**
     * Get chapters for a specific comic
     */
    public function chapters(Comic $comic)
    {
        $chapters = $comic->chapters()
            ->orderBy('chapter_number', 'asc')
            ->paginate(50);

        return ChapterResource::collection($chapters);
    }

    /**
     * Search comics
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $comics = Comic::with(['authors', 'genres'])
            ->where('title', 'ILIKE', "%{$query}%")
            ->orWhere('description', 'ILIKE', "%{$query}%")
            ->orWhereHas('authors', function ($q) use ($query) {
                $q->where('name', 'ILIKE', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return ComicResource::collection($comics);
    }

    /**
     * Get user's bookmarks
     */
    public function bookmarks(Request $request)
    {
        $user = $request->user();

        $bookmarks = $user->bookmarks()
            ->with('comic.authors', 'comic.genres')
            ->latest()
            ->paginate(20);

        return BookmarkResource::collection($bookmarks);
    }

    /**
     * Add comic to bookmarks
     */
    public function addBookmark(Request $request, Comic $comic)
    {
        $user = $request->user();

        $user->bookmarks()->firstOrCreate([
            'comic_id' => $comic->id,
        ]);

        return response()->json(['message' => 'Comic added to bookmarks']);
    }

    /**
     * Remove comic from bookmarks
     */
    public function removeBookmark(Request $request, Comic $comic)
    {
        $user = $request->user();

        $user->bookmarks()->where('comic_id', $comic->id)->delete();

        return response()->json(['message' => 'Comic removed from bookmarks']);
    }

    /**
     * Get user's reading history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $history = $user->readingHistory()
            ->with('comic.authors', 'chapter')
            ->latest('last_read_at')
            ->paginate(20);

        return ReadingHistoryResource::collection($history);
    }

    /**
     * Update reading history
     */
    public function updateHistory(Request $request)
    {
        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'chapter_id' => 'required|exists:chapters,id',
            'last_page_read' => 'nullable|integer',
        ]);

        $user = $request->user();

        $user->readingHistory()->updateOrCreate(
            [
                'comic_id' => $validated['comic_id'],
            ],
            [
                'chapter_id' => $validated['chapter_id'],
                'last_page_read' => $validated['last_page_read'] ?? 0,
                'last_read_at' => now(),
            ]
        );

        return response()->json(['message' => 'Reading history updated']);
    }

    /**
     * Rate a comic
     */
    public function rate(Request $request, Comic $comic)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $user->ratings()->updateOrCreate(
            ['comic_id' => $comic->id],
            $validated
        );

        // Update comic's average rating
        $avgRating = $comic->ratings()->avg('rating');
        $totalRatings = $comic->ratings()->count();

        $comic->update([
            'average_rating' => $avgRating,
            'total_ratings' => $totalRatings,
        ]);

        return response()->json(['message' => 'Rating submitted successfully']);
    }
}
