<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'comic_id',
        'rating',
        'review',
    ];

    /**
     * Get the user that owns this rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comic being rated.
     */
    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }
}
