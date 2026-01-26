# API Reference

## Base URL

```
Development: http://127.0.0.1:8001/api/v1
Production: https://api.yourdomain.com/api/v1
```

## Authentication

All protected endpoints require an `Authorization` header:

```
Authorization: Bearer {access_token}
```

## Response Format

### Success Response
```json
{
  "data": {
    // Response data
  }
}
```

### Paginated Response
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 20,
    "to": 20,
    "total": 200
  }
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Authentication Endpoints

### Register User

**POST** `/api/v1/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:** `201 Created`
```json
{
  "access_token": "1|abc123...",
  "refresh_token": "2|def456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-12-19T10:00:00.000000Z"
  }
}
```

### Login User

**POST** `/api/v1/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:** `200 OK`
```json
{
  "access_token": "1|abc123...",
  "refresh_token": "2|def456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Refresh Token

**POST** `/api/v1/refresh`

**Headers:**
```
Authorization: Bearer {refresh_token}
```

**Response:** `200 OK`
```json
{
  "access_token": "3|ghi789...",
  "refresh_token": "4|jkl012...",
  "token_type": "Bearer"
}
```

### Logout

**POST** `/api/v1/logout`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

### Get Current User

**GET** `/api/v1/user`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-12-19T10:00:00.000000Z"
  }
}
```

## Comics Endpoints

### List Comics

**GET** `/api/v1/comics`

**Query Parameters:**
- `per_page` (int, default: 20): Items per page
- `page` (int, default: 1): Page number
- `status` (string): Filter by status (ongoing, completed, hiatus, cancelled)
- `genre` (string): Filter by genre slug
- `type` (string): Filter by type (manga, manhua, manhwa, webtoon)
- `sort` (string): Sort by (latest, views, rating, title)

**Response:** `200 OK` (Paginated)

### Get Comic by Slug

**GET** `/api/v1/comics/{slug}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "title": "Comic Title",
    "slug": "comic-title",
    "description": "Comic description...",
    "cover_image": "https://...",
    "status": "ongoing",
    "type": "manhua",
    "total_chapters": 100,
    "total_views": 50000,
    "average_rating": 4.5,
    "total_ratings": 1000,
    "authors": [...],
    "genres": [...],
    "chapters": [...]
  }
}
```

### Get Comic Chapters

**GET** `/api/v1/comics/{slug}/chapters`

**Query Parameters:**
- `per_page` (int, default: 50)
- `page` (int, default: 1)

**Response:** `200 OK` (Paginated)

### Get Featured Comics

**GET** `/api/v1/comics/featured`

**Query Parameters:**
- `limit` (int, default: 10)

**Response:** `200 OK`

### Get Hot Comics

**GET** `/api/v1/comics/hot`

**Query Parameters:**
- `limit` (int, default: 10)

**Response:** `200 OK`

### Get Recently Updated

**GET** `/api/v1/comics/recent`

**Query Parameters:**
- `limit` (int, default: 20)

**Response:** `200 OK`

### Get Top by Views

**GET** `/api/v1/comics/top-views`

**Query Parameters:**
- `per_page` (int, default: 20)
- `status` (string): Filter by status
- `genre` (string): Filter by genre slug

**Response:** `200 OK` (Paginated)

### Get Top by Rating

**GET** `/api/v1/comics/top-rating`

**Query Parameters:**
- `per_page` (int, default: 20)
- `status` (string): Filter by status
- `genre` (string): Filter by genre slug

**Response:** `200 OK` (Paginated)

### Get Newest Comics

**GET** `/api/v1/comics/newest`

**Query Parameters:**
- `per_page` (int, default: 20)
- `status` (string): Filter by status
- `genre` (string): Filter by genre slug

**Response:** `200 OK` (Paginated)

## Chapters Endpoints

### Get Chapter

**GET** `/api/v1/chapters/{id}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "comic_id": 1,
    "title": "Chapter 1",
    "slug": "chapter-1",
    "chapter_number": 1,
    "volume_number": 1,
    "total_pages": 20,
    "total_views": 5000,
    "published_at": "2025-01-01",
    "comic": {
      "id": 1,
      "title": "Comic Title",
      "slug": "comic-title",
      "chapters": [...]
    }
  }
}
```

**Note:** This endpoint increments the chapter view count.

### Get Chapter Pages

**GET** `/api/v1/chapters/{id}/pages`

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "page_number": 1,
      "image_url": "https://...",
      "width": 800,
      "height": 1200
    }
  ]
}
```

## Genres Endpoints

### List Genres

**GET** `/api/v1/genres`

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Action",
      "slug": "action",
      "comics_count": 150
    }
  ]
}
```

### Get Genre

**GET** `/api/v1/genres/{slug}`

**Response:** `200 OK`

### Get Genre Comics

**GET** `/api/v1/genres/{slug}/comics`

**Query Parameters:**
- `per_page` (int, default: 20)
- `page` (int, default: 1)

**Response:** `200 OK` (Paginated)

## Search Endpoint

### Search Comics

**GET** `/api/v1/search`

**Query Parameters:**
- `q` (string): Search query
- `status` (string): Filter by status
- `genre` (string): Filter by genre slug
- `sort` (string): Sort by (latest, views, rating, title)
- `per_page` (int, default: 20)

**Response:** `200 OK` (Paginated)

## User Endpoints (Protected)

### Get Bookmarks

**GET** `/api/v1/user/bookmarks`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "comic": {
        "id": 1,
        "title": "Comic Title",
        "slug": "comic-title",
        "cover_image": "https://..."
      },
      "created_at": "2025-12-19T10:00:00.000000Z"
    }
  ]
}
```

### Add Bookmark

**POST** `/api/v1/user/bookmarks`

**Request Body:**
```json
{
  "comic_id": 1
}
```

**Response:** `201 Created`

### Remove Bookmark

**DELETE** `/api/v1/user/bookmarks/{id}`

**Response:** `200 OK`

### Get Reading History

**GET** `/api/v1/user/history`

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "comic": {...},
      "chapter": {...},
      "last_page_read": 5,
      "updated_at": "2025-12-19T10:00:00.000000Z"
    }
  ]
}
```

### Update Reading History

**POST** `/api/v1/user/history`

**Request Body:**
```json
{
  "comic_id": 1,
  "chapter_id": 10,
  "last_page_read": 5
}
```

**Response:** `200 OK`

**Note:** First-time reading increments comic total_views.

### Delete History Entry

**DELETE** `/api/v1/user/history/{id}`

**Response:** `200 OK`

### Rate Comic

**POST** `/api/v1/user/rate`

**Request Body:**
```json
{
  "comic_id": 1,
  "rating": 5
}
```

**Response:** `200 OK`

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

