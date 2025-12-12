<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'comic_id',
    ];

    /**
     * Get the user that owns this bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comic that is bookmarked.
     */
    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }
}
