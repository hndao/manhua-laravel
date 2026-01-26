# Manhua Reader - Backend API Documentation

## Overview

This is the backend API for the Manhua Reader platform, built with Laravel 11. It provides RESTful API endpoints for managing comics, chapters, users, and reading data.

## Technology Stack

- **Framework**: Laravel 11.x
- **PHP**: 8.2+
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (Token-based)
- **API**: RESTful JSON API
- **Cache**: Redis (optional)

## Project Structure

```
manhua-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/              # API Controllers
│   │   ├── Middleware/           # Custom middleware
│   │   └── Resources/            # API Resources (transformers)
│   ├── Models/                   # Eloquent models
│   └── Services/                 # Business logic services
├── config/                       # Configuration files
│   ├── sanctum.php              # Sanctum configuration
│   └── cors.php                 # CORS configuration
├── database/
│   ├── migrations/              # Database migrations
│   ├── seeders/                 # Database seeders
│   └── factories/               # Model factories
├── routes/
│   └── api.php                  # API routes
├── storage/                     # File storage
└── docs/                        # Documentation
```

## Key Features

### 1. Authentication & Authorization
- Token-based authentication using Laravel Sanctum
- Refresh token mechanism (7-day access, 30-day refresh)
- Automatic token refresh
- Protected routes for authenticated users

### 2. Comics Management
- CRUD operations for comics
- Chapter management
- Page management
- Genre and author relationships
- View counting
- Rating system

### 3. User Features
- User registration and login
- Bookmarks management
- Reading history tracking
- Comic ratings
- User preferences

### 4. Search & Discovery
- Full-text search
- Filter by status, genre, type
- Sort by views, rating, date
- Pagination support

### 5. Performance
- Database query optimization
- Eager loading relationships
- API resource transformers
- Response caching (optional)

## Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & npm (for asset compilation)

### Installation

1. Install dependencies:
```bash
composer install
```

2. Configure environment:
```bash
cp .env.example .env
```

3. Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=manhua_db
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3001
SESSION_DOMAIN=localhost
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Seed database (optional):
```bash
php artisan db:seed
```

7. Start development server:
```bash
php artisan serve --port=8001
```

8. API available at: http://127.0.0.1:8001/api/v1

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/register` | Register new user | No |
| POST | `/api/v1/login` | Login user | No |
| POST | `/api/v1/logout` | Logout user | Yes |
| POST | `/api/v1/refresh` | Refresh access token | Yes |
| GET | `/api/v1/user` | Get current user | Yes |

### Comics

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/comics` | List all comics | No |
| GET | `/api/v1/comics/{slug}` | Get comic details | No |
| GET | `/api/v1/comics/{slug}/chapters` | Get comic chapters | No |
| GET | `/api/v1/comics/featured` | Get featured comics | No |
| GET | `/api/v1/comics/hot` | Get hot comics | No |
| GET | `/api/v1/comics/recent` | Get recently updated | No |
| GET | `/api/v1/comics/top-views` | Get top by views | No |
| GET | `/api/v1/comics/top-rating` | Get top by rating | No |
| GET | `/api/v1/comics/newest` | Get newest comics | No |

### Chapters

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/chapters/{id}` | Get chapter details | No |
| GET | `/api/v1/chapters/{id}/pages` | Get chapter pages | No |

### Genres

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/genres` | List all genres | No |
| GET | `/api/v1/genres/{slug}` | Get genre details | No |
| GET | `/api/v1/genres/{slug}/comics` | Get genre comics | No |

### Search

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/search` | Search comics | No |

### User Features (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/user/bookmarks` | Get user bookmarks | Yes |
| POST | `/api/v1/user/bookmarks` | Add bookmark | Yes |
| DELETE | `/api/v1/user/bookmarks/{id}` | Remove bookmark | Yes |
| GET | `/api/v1/user/history` | Get reading history | Yes |
| POST | `/api/v1/user/history` | Update reading history | Yes |
| DELETE | `/api/v1/user/history/{id}` | Delete history entry | Yes |
| POST | `/api/v1/user/rate` | Rate a comic | Yes |

## Database Schema

### Core Tables

**comics**
- id, title, slug, description, cover_image
- status, type, total_chapters, total_views
- average_rating, total_ratings
- timestamps, soft deletes

**chapters**
- id, comic_id, title, slug
- chapter_number, volume_number
- total_pages, views
- timestamps, soft deletes

**pages**
- id, chapter_id, page_number
- image_url, width, height
- timestamps

**genres**
- id, name, slug
- timestamps

**authors**
- id, name, slug, role
- timestamps

**users**
- id, name, email, password
- timestamps

**bookmarks**
- id, user_id, comic_id
- timestamps

**reading_history**
- id, user_id, comic_id, chapter_id
- last_page_read
- timestamps

**ratings**
- id, user_id, comic_id, rating
- timestamps

## Authentication Flow

### Registration/Login
```
1. POST /api/v1/register or /api/v1/login
2. Receive: { access_token, refresh_token, user }
3. Store tokens in client
4. Use access_token in Authorization header
```

### Token Refresh
```
1. Access token expires (7 days)
2. POST /api/v1/refresh with refresh_token
3. Receive: { access_token, refresh_token }
4. Update stored tokens
5. Retry original request
```

### Protected Requests
```
Authorization: Bearer {access_token}
```

## Configuration

### Sanctum Configuration

**Token Expiration** (`config/sanctum.php`):
- Access Token: 7 days (10080 minutes)
- Refresh Token: 30 days (43200 minutes)

**Token Abilities**:
- Access Token: `['*']` (all abilities)
- Refresh Token: `['refresh']` (refresh only)

### CORS Configuration

**Allowed Origins** (`config/cors.php`):
- Development: `http://localhost:3001`
- Production: Configure in `.env`

## Documentation Files

1. **README.md** (this file) - Project overview and getting started
2. **API_REFERENCE.md** - Detailed API endpoint documentation
3. **DATABASE_SCHEMA.md** - Database structure and relationships
4. **DEPLOYMENT_GUIDE.md** - Production deployment instructions
5. **BUSINESS_OVERVIEW.md** - Business context and features
6. **QUERY_LOGGING.md** - Database query logging and performance tracking

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Database Operations
```bash
# Fresh migration
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Fresh migration with seed
php artisan migrate:fresh --seed
```

### Query Performance Monitoring

Enable query logging to track database performance:

```bash
# Enable in .env
DB_LOG_QUERIES=true

# View query statistics
php artisan query:stats

# View only slow queries (> 100ms)
php artisan query:stats --slow

# View today's queries
php artisan query:stats --today --limit=20
```

**Log Files:**
- `storage/logs/query-YYYY-MM-DD.log` - All queries
- `storage/logs/slow-query-YYYY-MM-DD.log` - Slow queries only

See [QUERY_LOGGING.md](./QUERY_LOGGING.md) for detailed documentation.

## Support

For issues or questions:
- Check API documentation in `/docs`
- Review database schema
- Check Laravel logs in `storage/logs`

