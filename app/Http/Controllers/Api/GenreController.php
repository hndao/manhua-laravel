<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComicResource;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * Display a listing of all genres
     */
    public function index()
    {
        // Cache genres list for 10 minutes
        $genres = \Cache::remember('genres_list', 600, function () {
            return Genre::withCount('comics')
                ->orderBy('name', 'asc')
                ->get();
        });

        return GenreResource::collection($genres);
    }

    /**
     * Display the specified genre
     */
    public function show(Genre $genre)
    {
        $genre->loadCount('comics');

        return new GenreResource($genre);
    }

    /**
     * Get comics for a specific genre
     */
    public function comics(Request $request, Genre $genre)
    {
        $query = $genre->comics()
            ->with(['authors', 'genres'])
            ->withCount('chapters');

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);

        return ComicResource::collection($query->paginate($perPage));
    }
}
