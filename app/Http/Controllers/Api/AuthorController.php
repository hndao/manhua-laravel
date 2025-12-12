<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\ComicResource;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of all authors
     */
    public function index(Request $request)
    {
        $query = Author::withCount('comics');

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 50);

        return AuthorResource::collection($query->paginate($perPage));
    }

    /**
     * Display the specified author
     */
    public function show(Author $author)
    {
        $author->loadCount('comics');

        return new AuthorResource($author);
    }

    /**
     * Get comics for a specific author
     */
    public function comics(Request $request, Author $author)
    {
        $query = $author->comics()
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
