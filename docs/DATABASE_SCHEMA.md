# Database Schema

## Overview

The database schema is designed to support a comic reading platform with user management, content organization, and reading tracking features.

## Entity Relationship Diagram

```
users ──┬─── bookmarks ─── comics
        │
        └─── reading_history ─── chapters ─── pages
                              │
                              └─── comics ──┬─── comic_author ─── authors
                                            │
                                            └─── comic_genre ─── genres
```

## Tables

### users

Stores user account information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | User ID |
| name | VARCHAR(255) | NOT NULL | User's full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address |
| email_verified_at | TIMESTAMP | NULL | Email verification time |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| remember_token | VARCHAR(100) | NULL | Remember me token |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)

**Relationships:**
- Has many: bookmarks, reading_history, ratings

---

### comics

Stores comic information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Comic ID |
| title | VARCHAR(255) | NOT NULL | Comic title |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| description | TEXT | NULL | Comic description |
| cover_image | VARCHAR(500) | NULL | Cover image URL |
| status | ENUM | NOT NULL | ongoing, completed, hiatus, cancelled |
| type | ENUM | NOT NULL | manga, manhua, manhwa, webtoon |
| total_chapters | INT | DEFAULT 0 | Total chapter count |
| total_views | INT | DEFAULT 0 | Total view count |
| average_rating | DECIMAL(3,2) | DEFAULT 0.00 | Average rating (0-5) |
| total_ratings | INT | DEFAULT 0 | Total rating count |
| release_date | DATE | NULL | Original release date |
| is_featured | BOOLEAN | DEFAULT FALSE | Featured flag |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- INDEX (status)
- INDEX (type)
- INDEX (is_featured)
- INDEX (total_views)
- INDEX (average_rating)

**Relationships:**
- Has many: chapters, bookmarks, reading_history, ratings
- Belongs to many: authors (through comic_author), genres (through comic_genre)

---

### chapters

Stores chapter information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Chapter ID |
| comic_id | BIGINT | FK, NOT NULL | Reference to comics.id |
| title | VARCHAR(255) | NOT NULL | Chapter title |
| slug | VARCHAR(255) | NOT NULL | URL-friendly slug |
| chapter_number | INT | NOT NULL | Chapter number |
| volume_number | INT | NULL | Volume number |
| total_pages | INT | DEFAULT 0 | Total page count |
| views | INT | DEFAULT 0 | Chapter view count |
| published_at | DATE | NULL | Publication date |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (comic_id) REFERENCES comics(id) ON DELETE CASCADE
- UNIQUE (comic_id, chapter_number)
- INDEX (comic_id, chapter_number)
- INDEX (published_at)

**Relationships:**
- Belongs to: comic
- Has many: pages, reading_history

---

### pages

Stores page images for chapters.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Page ID |
| chapter_id | BIGINT | FK, NOT NULL | Reference to chapters.id |
| page_number | INT | NOT NULL | Page number |
| image_url | VARCHAR(500) | NOT NULL | Image URL |
| width | INT | NULL | Image width in pixels |
| height | INT | NULL | Image height in pixels |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
- UNIQUE (chapter_id, page_number)
- INDEX (chapter_id, page_number)

**Relationships:**
- Belongs to: chapter

---

### genres

Stores genre/category information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Genre ID |
| name | VARCHAR(100) | UNIQUE, NOT NULL | Genre name |
| slug | VARCHAR(100) | UNIQUE, NOT NULL | URL-friendly slug |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name)
- UNIQUE (slug)

**Relationships:**
- Belongs to many: comics (through comic_genre)

---

### authors

Stores author/artist information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Author ID |
| name | VARCHAR(255) | NOT NULL | Author name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| role | ENUM | NOT NULL | author, artist, both |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)

**Relationships:**
- Belongs to many: comics (through comic_author)

---

### comic_genre (Pivot Table)

Links comics to genres (many-to-many).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Pivot ID |
| comic_id | BIGINT | FK, NOT NULL | Reference to comics.id |
| genre_id | BIGINT | FK, NOT NULL | Reference to genres.id |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (comic_id) REFERENCES comics(id) ON DELETE CASCADE
- FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
- UNIQUE (comic_id, genre_id)

---

### comic_author (Pivot Table)

Links comics to authors (many-to-many).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Pivot ID |
| comic_id | BIGINT | FK, NOT NULL | Reference to comics.id |
| author_id | BIGINT | FK, NOT NULL | Reference to authors.id |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (comic_id) REFERENCES comics(id) ON DELETE CASCADE
- FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
- UNIQUE (comic_id, author_id)

---

### bookmarks

Stores user bookmarks.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Bookmark ID |
| user_id | BIGINT | FK, NOT NULL | Reference to users.id |
| comic_id | BIGINT | FK, NOT NULL | Reference to comics.id |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (comic_id) REFERENCES comics(id) ON DELETE CASCADE
- UNIQUE (user_id, comic_id)
- INDEX (user_id)

**Relationships:**
- Belongs to: user, comic

---

### reading_history

Tracks user reading progress.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | History ID |
| user_id | BIGINT | FK, NOT NULL | Reference to users.id |
| comic_id | BIGINT | FK, NOT NULL | Reference to comics.id |
| chapter_id | BIGINT | FK, NOT NULL | Reference to chapters.id |
| last_page_read | INT | DEFAULT 0 | Last page number read |
| created_at | TIMESTAMP | NULL | First read timestamp |
| updated_at | TIMESTAMP | NULL | Last read timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (comic_id) REFERENCES comics(id) ON DELETE CASCADE
- FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
- UNIQUE (user_id, comic_id)
- INDEX (user_id)

**Relationships:**
- Belongs to: user, comic, chapter

---

### ratings

Stores user comic ratings.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Rating ID |
| user_id | BIGINT | FK, NOT NULL | Reference to users.id |
| comic_id | BIGINT | FK, NOT NULL | Reference to comics.id |
| rating | TINYINT | NOT NULL | Rating value (1-5) |
| created_at | TIMESTAMP | NULL | Creation timestamp |
| updated_at | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (comic_id) REFERENCES comics(id) ON DELETE CASCADE
- UNIQUE (user_id, comic_id)
- INDEX (comic_id)

**Relationships:**
- Belongs to: user, comic

---

## Data Integrity Rules

### Cascading Deletes

- Deleting a comic cascades to: chapters, pages, bookmarks, reading_history, ratings
- Deleting a chapter cascades to: pages, reading_history
- Deleting a user cascades to: bookmarks, reading_history, ratings

### Soft Deletes

- comics: Soft deleted (can be restored)
- chapters: Soft deleted (can be restored)

### Unique Constraints

- Comic slug must be unique
- Chapter number must be unique per comic
- Page number must be unique per chapter
- User can only bookmark a comic once
- User can only have one reading history entry per comic
- User can only rate a comic once

## Performance Considerations

### Indexes

- All foreign keys are indexed
- Frequently queried columns (status, type, views, rating) are indexed
- Composite indexes for common query patterns

### Query Optimization

- Use eager loading for relationships
- Paginate large result sets
- Cache frequently accessed data (genres, featured comics)
- Use database transactions for data consistency

