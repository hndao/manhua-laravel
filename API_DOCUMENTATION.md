# Manhua Laravel API Documentation

Base URL: `http://127.0.0.1:8000/api/v1`

## 📚 Comics Endpoints

### GET `/comics`
Get paginated list of comics with filters

**Query Parameters:**
- `per_page` (int, default: 20) - Items per page
- `status` (string) - Filter by status: `ongoing`, `completed`, `hiatus`, `cancelled`
- `type` (string) - Filter by type: `manga`, `manhua`, `manhwa`, `webtoon`
- `genre` (string) - Filter by genre slug
- `featured` (boolean) - Filter featured comics
- `sort_by` (string, default: `created_at`) - Sort field
- `sort_order` (string, default: `desc`) - Sort order: `asc`, `desc`

**Response:**
```json
{
  "data": [
    {
      "id": 159,
      "title": "Ác Nữ Xà Lan",
      "slug": "ac-nu-xa-lan",
      "description": "...",
      "cover_image": "qq-comics/thumbnails/656052.jpg",
      "status": "ongoing",
      "type": "manhua",
      "total_chapters": 0,
      "total_views": 0,
      "average_rating": 0,
      "total_ratings": 0,
      "release_date": null,
      "is_featured": false,
      "authors": [
        {
          "id": 1,
          "name": "Xưởng Zha Ya",
          "slug": "xuong-zha-ya",
          "role": "both",
          "created_at": "2025-12-12T04:05:23.000000Z"
        }
      ],
      "genres": [
        {
          "id": 3,
          "name": "Comedy",
          "slug": "comedy",
          "created_at": "2025-12-12T04:04:57.000000Z"
        }
      ],
      "chapters_count": 6,
      "created_at": "2025-12-12T04:06:13.000000Z",
      "updated_at": "2025-12-12T04:06:13.000000Z"
    }
  ],
  "total": 187,
  "per_page": 20,
  "current_page": 1,
  "last_page": 10
}
```

### GET `/comics/{slug}`
Get single comic details with all chapters

**Response:**
```json
{
  "id": 60,
  "title": "Tổng thống Hormone",
  "slug": "tong-thong-hormone",
  "authors": [...],
  "genres": [...],
  "chapters": [...]
}
```

### GET `/comics/{slug}/chapters`
Get paginated chapters for a comic

**Query Parameters:**
- `per_page` (int, default: 50)

### GET `/search`
Search comics by title, description, or author

**Query Parameters:**
- `q` (string, required) - Search query

**Response:**
```json
{
  "data": [...]
}
```

## 📖 Chapters Endpoints

### GET `/chapters/{id}`
Get chapter details (increments view count)

**Response:**
```json
{
  "id": 3401,
  "comic_id": 120,
  "title": "Chapter 1",
  "chapter_number": 1,
  "comic": {...}
}
```

### GET `/chapters/{id}/pages`
Get all pages for a chapter

**Response:**
```json
[
  {
    "id": 1,
    "page_number": 1,
    "image_url": "...",
    "width": 800,
    "height": 1200
  }
]
```

## 🏷️ Genres Endpoints

### GET `/genres`
Get all genres with comic counts

**Response:**
```json
[
  {
    "id": 1,
    "name": "Action",
    "slug": "action",
    "comics_count": 18
  }
]
```

### GET `/genres/{slug}`
Get single genre details

### GET `/genres/{slug}/comics`
Get paginated comics for a genre

**Query Parameters:**
- `per_page` (int, default: 20)
- `sort_by` (string, default: `created_at`)
- `sort_order` (string, default: `desc`)

## 👤 Authors Endpoints

### GET `/authors`
Get paginated list of authors

**Query Parameters:**
- `per_page` (int, default: 50)
- `search` (string) - Search by name
- `sort_by` (string, default: `name`)
- `sort_order` (string, default: `asc`)

### GET `/authors/{slug}`
Get single author details

### GET `/authors/{slug}/comics`
Get paginated comics for an author

## 🔐 Protected Endpoints (Require Authentication)

### GET `/user`
Get authenticated user details

**Headers:**
```
Authorization: Bearer {token}
```

### GET `/bookmarks`
Get user's bookmarked comics

### POST `/bookmarks/{comic_id}`
Add comic to bookmarks

### DELETE `/bookmarks/{comic_id}`
Remove comic from bookmarks

### GET `/history`
Get user's reading history

### POST `/history`
Update reading history

**Body:**
```json
{
  "comic_id": 60,
  "chapter_id": 3401,
  "last_page_read": 5
}
```

### POST `/comics/{comic_id}/rate`
Rate a comic

**Body:**
```json
{
  "rating": 5,
  "review": "Great comic!"
}
```

## 🌐 CORS Configuration

Allowed origins:
- `http://localhost:3000` (Next.js dev)
- `http://127.0.0.1:3000`

## 📊 Database Stats

- **Comics**: 187
- **Chapters**: 9,387
- **Authors**: 161
- **Genres**: 32
- **Comic-Genre relationships**: 569

## 🎨 API Resources

All API responses are transformed using Laravel API Resources for consistent JSON structure:

### Resources Created:
- **ComicResource** - Transforms comic data with authors, genres, chapters
- **ChapterResource** - Transforms chapter data with comic info
- **GenreResource** - Transforms genre data with comic counts
- **AuthorResource** - Transforms author data with role (from pivot table)
- **PageResource** - Transforms page data
- **BookmarkResource** - Transforms bookmark data with comic info
- **ReadingHistoryResource** - Transforms reading history with comic and chapter info

### Response Structure:
All single resources are wrapped in `{"data": {...}}`.
All collections are wrapped in `{"data": [...]}` with pagination metadata.

### CDN Image URLs:
All image URLs are automatically converted to full CDN URLs:
- **cover_image**: `https://cdn.truyenvie.com/qq-comics/thumbnails/649568.jpg`
- **avatar**: `https://cdn.truyenvie.com/authors/avatar.jpg`
- **image_url** (pages): `https://cdn.truyenvie.com/qq-comics/pages/image.jpg`

Configure CDN URL in `.env`:
```
CDN_URL=https://cdn.truyenvie.com
```

