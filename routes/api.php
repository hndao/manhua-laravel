<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\ComicController;
use App\Http\Controllers\Api\GenreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Comics
    Route::get('/comics', [ComicController::class, 'index']);
    Route::get('/comics/{comic:slug}', [ComicController::class, 'show']);
    Route::get('/comics/{comic:slug}/chapters', [ComicController::class, 'chapters']);

    // Chapters
    Route::get('/chapters/{chapter}', [ChapterController::class, 'show']);
    Route::get('/chapters/{chapter}/pages', [ChapterController::class, 'pages']);

    // Genres
    Route::get('/genres', [GenreController::class, 'index']);
    Route::get('/genres/{genre:slug}', [GenreController::class, 'show']);
    Route::get('/genres/{genre:slug}/comics', [GenreController::class, 'comics']);

    // Authors
    Route::get('/authors', [AuthorController::class, 'index']);
    Route::get('/authors/{author:slug}', [AuthorController::class, 'show']);
    Route::get('/authors/{author:slug}/comics', [AuthorController::class, 'comics']);

    // Search
    Route::get('/search', [ComicController::class, 'search']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);

    // User bookmarks
    Route::get('/bookmarks', [ComicController::class, 'bookmarks']);
    Route::get('/bookmarks/{comic}/check', [ComicController::class, 'checkBookmark']);
    Route::post('/bookmarks/{comic}', [ComicController::class, 'addBookmark']);
    Route::delete('/bookmarks/{comic}', [ComicController::class, 'removeBookmark']);

    // Reading history
    Route::get('/history', [ComicController::class, 'history']);
    Route::post('/history', [ComicController::class, 'updateHistory']);
    Route::delete('/history/{comic}', [ComicController::class, 'deleteHistory']);

    // Ratings
    Route::get('/comics/{comic}/rating', [ComicController::class, 'getUserRating']);
    Route::post('/comics/{comic}/rate', [ComicController::class, 'rate']);
});
