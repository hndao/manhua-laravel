# Manhua Project - Next Steps

## ✅ Completed (Laravel Backend)

### 1. Database & Models
- ✅ Created 9 migrations (comics, chapters, pages, genres, authors, bookmarks, reading_history, ratings, pivot tables)
- ✅ Created 8 Eloquent models with full relationships
- ✅ Seeded database with 187 comics, 9,387 chapters, 161 authors, 32 genres

### 2. API Backend
- ✅ Installed Laravel Sanctum for API authentication
- ✅ Created RESTful API routes (v1 versioning)
- ✅ Built 4 API controllers (Comic, Chapter, Genre, Author)
- ✅ Created 8 API Resources for consistent JSON responses
- ✅ Configured CORS for Next.js frontend
- ✅ Tested all endpoints successfully

### 3. API Endpoints Available
```
GET  /api/v1/comics                    - List comics (paginated, filterable)
GET  /api/v1/comics/{slug}             - Get comic details
GET  /api/v1/comics/{slug}/chapters    - Get comic chapters
GET  /api/v1/search?q={query}          - Search comics
GET  /api/v1/genres                    - List all genres
GET  /api/v1/genres/{slug}/comics      - Get comics by genre
GET  /api/v1/authors                   - List authors
GET  /api/v1/authors/{slug}/comics     - Get comics by author
GET  /api/v1/chapters/{id}             - Get chapter details
GET  /api/v1/chapters/{id}/pages       - Get chapter pages

Protected (require auth):
GET  /api/v1/user                      - Get user profile
GET  /api/v1/bookmarks                 - Get user bookmarks
POST /api/v1/bookmarks/{comic_id}      - Add bookmark
DEL  /api/v1/bookmarks/{comic_id}      - Remove bookmark
GET  /api/v1/history                   - Get reading history
POST /api/v1/history                   - Update reading history
POST /api/v1/comics/{id}/rate          - Rate a comic
```

## 🚀 Next Steps (Frontend)

### 1. Next.js Project Setup
**Location:** `/Users/hungdao/Documents/projects/P2/manhua-nextjs`

**Already installed:**
- ✅ Next.js 16 with App Router
- ✅ TypeScript
- ✅ Tailwind CSS
- ✅ ESLint
- ✅ axios (for API calls)
- ✅ swr (for data fetching)

### 2. Files to Create

#### API Client (`lib/api.ts`)
```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;
```

#### TypeScript Types (`types/index.ts`)
- Comic, Author, Genre, Chapter, Page types
- PaginatedResponse, ApiResponse types
- Query parameter types

#### Pages to Build
1. **Home Page** (`app/page.tsx`) - Featured comics, latest updates
2. **Comics List** (`app/comics/page.tsx`) - Browse all comics with filters
3. **Comic Detail** (`app/comics/[slug]/page.tsx`) - Comic info + chapter list
4. **Chapter Reader** (`app/comics/[slug]/chapters/[id]/page.tsx`) - Read chapter
5. **Genre Page** (`app/genres/[slug]/page.tsx`) - Comics by genre
6. **Author Page** (`app/authors/[slug]/page.tsx`) - Comics by author
7. **Search Page** (`app/search/page.tsx`) - Search results

#### Components to Build
- `ComicCard` - Display comic thumbnail
- `ChapterList` - List of chapters
- `PageReader` - Image viewer for reading
- `GenreFilter` - Filter comics by genre
- `Navbar` - Navigation header
- `Footer` - Site footer

### 3. Environment Variables
Create `.env.local`:
```
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api/v1
```

### 4. Run Development Servers

**Laravel (Terminal 1):**
```bash
cd /Users/hungdao/Documents/projects/P2/manhua-laravel
php artisan serve
# Runs on http://127.0.0.1:8000
```

**Next.js (Terminal 2):**
```bash
cd /Users/hungdao/Documents/projects/P2/manhua-nextjs
npm run dev
# Runs on http://localhost:3000
```

## 📚 Documentation

- **API Documentation:** `API_DOCUMENTATION.md`
- **Database Stats:** 187 comics, 9,387 chapters, 161 authors, 32 genres

## 🎯 Recommended Development Order

1. Set up API client and types
2. Build Home page with featured comics
3. Build Comic list page with pagination
4. Build Comic detail page
5. Build Chapter reader
6. Add search functionality
7. Add user authentication (optional)
8. Add bookmarks and reading history (requires auth)

## 🔗 Useful Commands

```bash
# Laravel
php artisan serve              # Start server
php artisan migrate:fresh --seed  # Reset database
php artisan tinker             # Interactive console

# Next.js
npm run dev                    # Start dev server
npm run build                  # Build for production
npm run start                  # Start production server
```

---

**Ready to continue in the Next.js workspace!** 🚀

