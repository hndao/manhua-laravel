# Database Query Logging & Performance Tracking

This document explains how to use the query logging feature to track and optimize database performance.

## Overview

The query logging system automatically logs all database queries with their execution times, helping you identify performance bottlenecks and slow queries.

## Features

- ✅ **Automatic Query Logging** - All queries are logged with execution time
- ✅ **Slow Query Detection** - Queries taking > 100ms are logged separately
- ✅ **Daily Log Rotation** - Logs are rotated daily to prevent large files
- ✅ **Performance Statistics** - CLI command to analyze query performance
- ✅ **Color-Coded Output** - Visual indicators for query performance
- ✅ **Environment-Aware** - Only enabled in local environment

## Configuration

### Enable Query Logging

Add this to your `.env` file:

```env
DB_LOG_QUERIES=true
```

### Log Files

Query logs are stored in `storage/logs/`:

- **`query-YYYY-MM-DD.log`** - All queries with execution times
- **`slow-query-YYYY-MM-DD.log`** - Only slow queries (> 100ms)

### Log Retention

- Regular query logs: **7 days**
- Slow query logs: **14 days**

## Usage

### View Query Statistics

Display performance statistics for all queries:

```bash
php artisan query:stats
```

### View Only Slow Queries

Show only queries that took more than 100ms:

```bash
php artisan query:stats --slow
```

### View Today's Queries

Show only queries from today:

```bash
php artisan query:stats --today
```

### Limit Number of Results

Show top 50 slowest queries:

```bash
php artisan query:stats --limit=50
```

### Combine Options

Show top 10 slow queries from today:

```bash
php artisan query:stats --slow --today --limit=10
```

## Output Example

```
📊 Query Performance Summary
+----------------+------------------+
| Metric         | Value            |
+----------------+------------------+
| Total Queries  | 1,234            |
| Total Time     | 45,678.90 ms     |
| Average Time   | 37.02 ms         |
| Slowest Query  | 1,234.56 ms      |
| Fastest Query  | 0.12 ms          |
+----------------+------------------+

🐌 Top 20 Slowest Queries

1. [1234.56 ms] SELECT * FROM comics WHERE status = 'ongoing' ORDER BY updated_at DESC LIMIT 100
2. [856.23 ms] SELECT * FROM chapters WHERE comic_id IN (1, 2, 3, ...) ORDER BY chapter_number
3. [456.78 ms] SELECT comics.*, COUNT(chapters.id) as chapter_count FROM comics LEFT JOIN...
```

## Performance Color Coding

Queries are color-coded based on execution time:

- 🟢 **Green** - Fast (< 100ms)
- 🔵 **Cyan** - Moderate (100-499ms)
- 🟡 **Yellow** - Slow (500-999ms)
- 🔴 **Red** - Very Slow (≥ 1000ms)

## Optimization Tips

### 1. Identify N+1 Queries

Look for repeated similar queries:

```sql
SELECT * FROM comics WHERE id = 1
SELECT * FROM comics WHERE id = 2
SELECT * FROM comics WHERE id = 3
```

**Solution:** Use eager loading:
```php
Comic::with('chapters')->get();
```

### 2. Add Database Indexes

If you see slow queries on specific columns:

```sql
SELECT * FROM comics WHERE status = 'ongoing' -- Slow!
```

**Solution:** Add an index:
```php
Schema::table('comics', function (Blueprint $table) {
    $table->index('status');
});
```

### 3. Optimize Large Result Sets

Queries returning many rows:

```sql
SELECT * FROM chapters WHERE comic_id = 123 -- Returns 1000+ rows
```

**Solution:** Use pagination or limit results:
```php
Chapter::where('comic_id', 123)->paginate(20);
```

### 4. Use Query Caching

For frequently accessed data:

```php
Cache::remember('popular_comics', 3600, function () {
    return Comic::orderBy('total_views', 'desc')->limit(10)->get();
});
```

## Disable Query Logging

To disable query logging (e.g., in production):

```env
DB_LOG_QUERIES=false
```

Or remove the line entirely (defaults to `false`).

## Production Considerations

⚠️ **Warning:** Query logging can impact performance and generate large log files.

**Recommendations for Production:**

1. **Disable by default** - Only enable when debugging performance issues
2. **Use APM tools** - Consider using New Relic, Datadog, or Laravel Telescope instead
3. **Monitor disk space** - Query logs can grow quickly under high traffic
4. **Temporary debugging** - Enable only for short periods to diagnose issues

## Integration with Laravel Telescope

For more advanced query monitoring, consider installing [Laravel Telescope](https://laravel.com/docs/telescope):

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Telescope provides:
- Real-time query monitoring
- Query duplication detection
- Request/response inspection
- Job monitoring
- And much more!

## Troubleshooting

### No queries in log file

1. Check that `DB_LOG_QUERIES=true` is set in `.env`
2. Restart the Laravel server after changing `.env`
3. Make sure you're in `local` environment (`APP_ENV=local`)
4. Verify log file permissions in `storage/logs/`

### Log files too large

1. Reduce retention days in `config/logging.php`
2. Use `--today` option to view only recent queries
3. Consider using Laravel Telescope for production monitoring

### Can't find slow queries

1. Check `storage/logs/slow-query-YYYY-MM-DD.log`
2. Adjust the threshold in `AppServiceProvider.php` (currently 100ms)
3. Use `php artisan query:stats --slow` to view slow query statistics

## See Also

- [Laravel Database Documentation](https://laravel.com/docs/database)
- [Laravel Query Builder](https://laravel.com/docs/queries)
- [Database Optimization Guide](https://laravel.com/docs/eloquent#optimizing-eloquent)
- [Laravel Telescope](https://laravel.com/docs/telescope)

