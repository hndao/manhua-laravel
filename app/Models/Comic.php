<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'status',
        'type',
        'total_chapters',
        'total_views',
        'average_rating',
        'total_ratings',
        'release_date',
        'is_featured',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_featured' => 'boolean',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Get the authors that belong to this comic.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_comic')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the genres that belong to this comic.
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'comic_genre');
    }

    /**
     * Get the chapters for this comic.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * Get the bookmarks for this comic.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the reading history for this comic.
     */
    public function readingHistory(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    /**
     * Get the ratings for this comic.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
