<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'comic_id',
        'title',
        'slug',
        'chapter_number',
        'volume_number',
        'total_pages',
        'views',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    /**
     * Get the comic that owns this chapter.
     */
    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    /**
     * Get the pages for this chapter.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('page_number');
    }

    /**
     * Get the reading history for this chapter.
     */
    public function readingHistory(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }
}
