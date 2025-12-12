<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'bio',
        'avatar',
        'website',
    ];

    /**
     * Get the comics that belong to this author.
     */
    public function comics(): BelongsToMany
    {
        return $this->belongsToMany(Comic::class, 'author_comic')
            ->withPivot('role')
            ->withTimestamps();
    }
}
