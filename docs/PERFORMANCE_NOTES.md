# Performance Optimization Notes

## Database Indexing Strategy

### ⚠️ Important Considerations

**Indexes are a trade-off:**
- ✅ **Pros:** Faster SELECT queries (especially WHERE, JOIN, ORDER BY)
- ❌ **Cons:** 
  - Consume disk storage (can be significant on large tables)
  - Slow down INSERT/UPDATE/DELETE operations
  - Require maintenance (VACUUM, ANALYZE)

### Current Indexes Added

#### Comics Table
- `deleted_at` - Used in soft delete queries ✅
- `created_at` - Used for sorting by date ✅
- `status` - Used for filtering (ongoing, completed, etc.) ✅
- `slug` - Used for route model binding lookups ✅
- `(deleted_at, created_at)` - Composite index for common query pattern ⚠️

**Note:** The composite index `(deleted_at, created_at)` may be redundant if individual indexes exist. Consider removing if storage is a concern.

#### Chapters Table
- `deleted_at` - Used in soft delete queries ✅
- `comic_id` - Foreign key, used in JOINs ✅
- `(comic_id, deleted_at)` - Composite for filtering chapters by comic ✅

#### Pivot Tables
- `comic_genre.comic_id` - Used in genre filtering ✅
- `comic_genre.genre_id` - Used in reverse lookups ✅
- `author_comic.comic_id` - Used in author filtering ✅
- `author_comic.author_id` - Used in reverse lookups ✅

#### Genres Table
- `name` - Used for sorting alphabetically ✅

### Monitoring Index Usage

```sql
-- PostgreSQL: Check index usage
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_scan as index_scans,
    idx_tup_read as tuples_read,
    idx_tup_fetch as tuples_fetched
FROM pg_stat_user_indexes
WHERE schemaname = 'public'
ORDER BY idx_scan ASC;

-- Find unused indexes (idx_scan = 0)
SELECT 
    schemaname,
    tablename,
    indexname,
    pg_size_pretty(pg_relation_size(indexrelid)) as index_size
FROM pg_stat_user_indexes
WHERE idx_scan = 0
AND schemaname = 'public';
```

---

## SELECT * Problem

### ❌ Avoid SELECT *

**Why it's bad:**
- Fetches unnecessary columns (wastes bandwidth and memory)
- Slower query execution (more data to transfer)
- Larger cache entries (inefficient caching)
- Breaks when table schema changes

### Current Issues

**Route Model Binding:**
```php
// This uses SELECT * by default
public function show(Comic $comic) { ... }
```

**Query:** `select * from "comics" where "slug" = '...' limit 1`

### Solutions

#### Option 1: Customize Route Model Binding
```php
// In Comic model
public function resolveRouteBinding($value, $field = null)
{
    return $this->select([
        'id', 'title', 'slug', 'description', 'cover_image',
        'status', 'type', 'total_chapters', 'total_views',
        'average_rating', 'total_ratings', 'release_date',
        'is_featured', 'created_at', 'updated_at', 'deleted_at'
    ])->where($field ?? 'slug', $value)->firstOrFail();
}
```

#### Option 2: Use Query Builder Instead
```php
public function show($slug)
{
    $comic = Comic::select([
        'id', 'title', 'slug', 'description', 'cover_image',
        'status', 'type', 'total_chapters', 'total_views',
        'average_rating', 'total_ratings', 'release_date',
        'is_featured', 'created_at', 'updated_at'
    ])
    ->where('slug', $slug)
    ->with(['authors:id,name,slug', 'genres:id,name,slug'])
    ->firstOrFail();
    
    return new ComicResource($comic);
}
```

#### Option 3: Specify Columns in Eager Loading
```php
$comic->load([
    'authors:id,name,slug,role',
    'genres:id,name,slug',
    'chapters:id,comic_id,title,slug,chapter_number,total_pages'
]);
```

### Recommended Action

For now, we're using route model binding for convenience. In production:
1. **Monitor query performance** with slow query log
2. **Profile actual usage** to see if SELECT * is a bottleneck
3. **Optimize only if needed** (premature optimization is the root of all evil)

---

## Query Caching Strategy

### Current Implementation

**Comics List:** 5 minutes TTL
**Genres List:** 10 minutes TTL  
**Comic Detail:** 10 minutes TTL

### Cache Invalidation

⚠️ **Important:** Currently, cache is NOT invalidated when data changes.

**TODO:** Implement cache invalidation:
```php
// In Comic model
protected static function booted()
{
    static::saved(function ($comic) {
        Cache::forget("comic_detail_{$comic->slug}");
        Cache::forget('genres_list');
        // Clear comics list cache with pattern
        Cache::flush(); // Or use tags if using Redis
    });
}
```

---

## Performance Metrics

### Before Optimization
- Homepage: 16+ queries, ~3-4 seconds
- Comic Detail: 8 duplicate queries, ~1.2 seconds

### After Optimization
- Homepage: 4 queries (first load), 0 queries (cached)
- Comic Detail: 1 query (first load), 0 queries (cached)

**Improvement:** ~75-100% reduction in query count

