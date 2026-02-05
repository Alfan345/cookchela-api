<div align="center">

# 🍳 CookChella API

**RESTful API Backend untuk Platform Berbagi Resep Masakan**

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Database-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)

**[API Documentation](#-dokumentasi-api) • [Live Demo](https://cookchella-api.onrender.com/api/v1/health) • [Tech Stack](#-tech-stack)**

</div>

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Utama](#-fitur-utama)
- [Tech Stack](#-tech-stack)
- [Arsitektur & Best Practices](#-arsitektur--best-practices)
- [Quick Start](#-quick-start)
- [Dokumentasi API](#-dokumentasi-api)
- [Environment Setup](#-environment-setup)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

---

## 🎯 Tentang Proyek

**CookChella API** adalah backend API modern untuk platform berbagi resep masakan yang memungkinkan pengguna untuk:
- Membuat, berbagi, dan menemukan resep masakan
- Berinteraksi dengan komunitas melalui fitur like dan bookmark
- Mencari resep berdasarkan keyword atau bahan yang tersedia
- Mengikuti chef favorit dan mendapatkan rekomendasi personal

### 🌟 Mengapa CookChella?

Proyek ini dikembangkan dengan fokus pada:
- ✅ **Clean Architecture** - Service layer pattern untuk maintainability
- ✅ **RESTful Best Practices** - Consistent API design dengan versioning
- ✅ **Security First** - Laravel Sanctum authentication, input validation
- ✅ **Scalability** - Cloud storage integration (Supabase), optimized queries
- ✅ **Developer Experience** - Comprehensive API documentation, consistent response format
- ✅ **Production Ready** - Docker support, error handling, rate limiting

---

## ✨ Fitur Utama

### 🔐 Authentication & Authorization
- **Multi-provider Authentication**
  - Email & Password (Laravel Sanctum)
  - Google OAuth 2.0 (Socialite)
- **Token-based API Authentication**
- **Profile Management** dengan upload avatar

### 👨‍🍳 Recipe Management (CRUD)
- **Create** - Upload resep dengan foto, ingredients, dan step-by-step instructions
- **Read** - Timeline feed, detail resep, dan recommendations
- **Update/Delete** - Full control untuk pemilik resep
- **Image Upload** - Integrasi dengan Supabase Storage

### 💙 Social Features
- **Like System** - Like/unlike resep dengan counter
- **Bookmark** - Simpan resep favorit
- **Follow System** - Follow/unfollow users
- **User Profiles** - View resep by username

### 🔍 Advanced Search
- **Full-text Search** - Cari berdasarkan title & description
- **Ingredient-based Search** - Cari resep berdasarkan bahan yang Anda punya
- **Search Suggestions** - Autocomplete untuk UX lebih baik
- **Search History** - Track pencarian user (dapat dihapus)
- **Filters** - Sort by relevance, cooking time, dll

### 📊 Master Data
- **Categories** - Kategorisasi resep (Breakfast, Lunch, Dinner, dll)
- **Difficulty Levels** - Easy, Medium, Hard
- **Cooking Times** - Range waktu memasak

---

## 🛠 Tech Stack

### Backend Framework
- **Laravel 12.0** - Modern PHP framework dengan fitur terbaru
- **PHP 8.2+** - Latest PHP version dengan typed properties, enums, dll

### Database & Storage
- **PostgreSQL** - Relational database utama
- **Supabase Storage** - Cloud storage untuk images (S3-compatible)
- **Laravel Eloquent ORM** - Database abstraction layer

### Authentication & Security
- **Laravel Sanctum** - API token authentication
- **Laravel Socialite** - OAuth provider (Google)
- **Validation Layer** - Form Request validation
- **Middleware** - Rate limiting, JSON response forcing

### DevOps & Tools
- **Docker** - Containerization untuk deployment
- **Composer** - PHP dependency manager
- **PHPUnit** - Unit & feature testing
- **Laravel Pint** - Code style fixer
- **Vite** - Asset bundling

### Dependencies Highlights
```json
{
  "laravel/framework": "^12.0",
  "laravel/sanctum": "^4.2",
  "laravel/socialite": "^5.23",
  "league/flysystem-aws-s3-v3": "^3.30"
}
```

---

## 🏗 Arsitektur & Best Practices

### 🎨 Clean Architecture Pattern

Proyek ini mengikuti **Service-Repository Pattern** untuk separation of concerns:

```
app/
├── Http/
│   ├── Controllers/Api/V1/     # Thin controllers (hanya handle HTTP)
│   ├── Requests/              # Form Request Validation
│   └── Middleware/            # Custom middleware
├── Services/                  # Business logic layer
│   ├── AuthService.php
│   ├── RecipeService.php
│   ├── BookmarkService.php
│   └── SearchService.php
├── Models/                    # Eloquent models
├── Traits/
│   └── ApiResponse.php       # Consistent response formatting
└── Providers/
```

### 📐 Prinsip yang Diterapkan

#### 1. **Single Responsibility Principle (SRP)**
- Controllers hanya handle HTTP request/response
- Services mengandung business logic
- Models hanya untuk database interaction

#### 2. **DRY (Don't Repeat Yourself)**
- Trait `ApiResponse` untuk unified response format
- Service layer untuk reusable business logic
- Form Requests untuk validation rules

#### 3. **API Versioning**
```php
Route::prefix('v1')->group(function () {
    // All v1 routes here
});
```

#### 4. **Consistent Response Format**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {},
  "meta": {
    "timestamp": "2026-02-05T10:00:00.000000Z",
    "request_id": "uuid"
  }
}
```

#### 5. **Error Handling**
- Global exception handling di `bootstrap/app.php`
- Custom error responses untuk API
- HTTP status codes yang sesuai (401, 404, 422, 500)

#### 6. **Security Practices**
- ✅ Input validation di semua endpoints
- ✅ CSRF protection untuk web routes
- ✅ Rate limiting
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Laravel sanitization)

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 atau lebih tinggi
- Composer
- PostgreSQL 14+
- Node.js & NPM (untuk asset compilation)

### 1. Clone Repository
```bash
git clone https://github.com/Alfan345/cookchela-api.git
cd cookchela-api
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME="CookChella API"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cookchela
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Supabase Storage
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_SERVICE_ROLE_KEY=your_service_role_key
SUPABASE_BUCKET_AVATARS=avatars
SUPABASE_BUCKET_RECIPES=recipes

# Google OAuth (optional)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/v1/auth/google/callback
```

### 4. Database Migration
```bash
php artisan migrate --seed
```

### 5. Run Development Server
```bash
composer run dev
# Atau manual:
php artisan serve
```

API akan tersedia di `http://localhost:8000/api/v1`

### 🐳 Menggunakan Docker

```bash
# Build image
docker build -t cookchela-api .

# Run container
docker run -p 8080:8080 --env-file .env cookchela-api
```

---

## 📚 Dokumentasi API

### Base URLs

| Environment | URL |
|------------|-----|
| **Production** | `https://cookchella-api.onrender.com/api/v1` |
| **Development** | `http://localhost:8000/api/v1` |

### Authentication

Sebagian besar endpoints memerlukan Bearer Token:

```http
Authorization: Bearer {your_access_token}
```

### Core Endpoints

#### 🔐 Authentication
```http
POST   /auth/register          # Register user baru
POST   /auth/login             # Login dengan email/password
POST   /auth/google            # Login dengan Google OAuth
POST   /auth/logout            # Logout (hapus token)
GET    /auth/me                # Get current user profile
```

#### 👤 User Management
```http
GET    /users/{username}               # Get user profile
GET    /users/{username}/recipes       # Get user's recipes
GET    /users/{username}/followers     # Get followers list
GET    /users/{username}/following     # Get following list
POST   /users/{username}/follow        # Follow user
DELETE /users/{username}/follow        # Unfollow user
PUT    /users/profile                  # Update profile
POST   /users/avatar                   # Update avatar
```

#### 🍳 Recipes
```http
GET    /recipes/timeline              # Get feed/timeline (public)
GET    /recipes/recommendations       # Get personalized recommendations
GET    /recipes/{id}                  # Get recipe detail
POST   /recipes                       # Create recipe (auth)
PUT    /recipes/{id}                  # Update recipe (auth)
DELETE /recipes/{id}                  # Delete recipe (auth)
POST   /recipes/{id}/like             # Like recipe (auth)
DELETE /recipes/{id}/like             # Unlike recipe (auth)
```

#### 🔖 Bookmarks
```http
GET    /bookmarks                     # Get user's bookmarks
POST   /bookmarks/{recipeId}          # Add bookmark
DELETE /bookmarks/{recipeId}          # Remove bookmark
GET    /bookmarks/{recipeId}/check    # Check if bookmarked
```

#### 🔍 Search
```http
GET    /search/recipes                         # Search recipes
POST   /search/ingredients                     # Search by ingredients
GET    /search/suggestions?q={query}           # Get autocomplete suggestions
GET    /search/history                         # Get search history (auth)
DELETE /search/history                         # Clear all history (auth)
DELETE /search/history/{keyword}               # Delete specific history (auth)
```

#### 📊 Master Data
```http
GET    /master/categories             # Get all categories
GET    /master/difficulty-levels      # Get difficulty levels
GET    /master/cooking-times          # Get cooking time ranges
```

### Request Example

**Create Recipe:**
```bash
curl -X POST https://cookchella-api.onrender.com/api/v1/recipes \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: multipart/form-data" \
  -F "title=Nasi Goreng Spesial" \
  -F "image=@/path/to/image.jpg" \
  -F "description=Nasi goreng dengan bumbu rahasia" \
  -F "cooking_time=30" \
  -F "servings=2" \
  -F "category_id=2" \
  -F "difficulty_level=easy" \
  -F "ingredients[0][name]=Nasi" \
  -F "ingredients[0][quantity]=500" \
  -F "ingredients[0][unit]=gram" \
  -F "steps[0][description]=Panaskan minyak di wajan"
```

### Response Example

**Success Response:**
```json
{
  "success": true,
  "message": "Resep berhasil dibuat",
  "data": {
    "id": 123,
    "title": "Nasi Goreng Spesial",
    "image_url": "https://storage.example.com/recipes/...",
    "author": {
      "id": 1,
      "username": "chef_alfan",
      "name": "Alfan",
      "avatar_url": "https://..."
    },
    "cooking_time": 30,
    "servings": 2,
    "likes_count": 0,
    "is_liked": false,
    "is_bookmarked": false
  },
  "meta": {
    "timestamp": "2026-02-05T10:00:00.000000Z",
    "request_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "cooking_time": ["The cooking time must be at least 1."]
  },
  "meta": {
    "timestamp": "2026-02-05T10:00:00.000000Z",
    "request_id": "..."
  }
}
```

### 📖 Full API Documentation

Untuk dokumentasi lengkap dengan semua parameter, validation rules, dan response examples, lihat:
- [API Contract Documentation (README.md)](./README_API.md) - Dokumentasi lengkap dari project

---

## ⚙️ Environment Setup

### Required Environment Variables

```env
# Application
APP_NAME="CookChella API"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=db.your-database-host.com
DB_PORT=5432
DB_DATABASE=cookchela_prod
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password

# Supabase Storage
SUPABASE_URL=https://xxxxx.supabase.co
SUPABASE_SERVICE_ROLE_KEY=eyJhbGc...
SUPABASE_BUCKET_AVATARS=avatars
SUPABASE_BUCKET_RECIPES=recipes

# Google OAuth
GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxx
GOOGLE_REDIRECT_URI=${APP_URL}/api/v1/auth/google/callback

# Rate Limiting
API_RATE_LIMIT=60  # requests per minute
```

### Supabase Storage Setup

1. Buat project di [Supabase](https://supabase.com)
2. Buat 2 buckets:
   - `avatars` - untuk user profile pictures
   - `recipes` - untuk recipe images
3. Set public access untuk kedua buckets
4. Copy `SUPABASE_URL` dan `SUPABASE_SERVICE_ROLE_KEY` ke `.env`

---

## 🧪 Testing

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=RecipeTest

# With coverage
php artisan test --coverage
```

### Code Style
```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

---

## 🚢 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate production app key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Optimize configurations:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] Setup queue worker (jika menggunakan jobs)
- [ ] Configure rate limiting
- [ ] Setup SSL/HTTPS
- [ ] Configure CORS headers

### Deployment Platforms

#### Render.com (Recommended)
```yaml
# render.yaml
services:
  - type: web
    name: cookchela-api
    env: docker
    dockerfilePath: ./Dockerfile
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_KEY
        sync: false
      - key: DB_CONNECTION
        value: pgsql
```

#### Docker Deployment
```bash
# Build production image
docker build -t cookchela-api:latest .

# Run with environment file
docker run -d \
  --name cookchela-api \
  -p 8080:8080 \
  --env-file .env.production \
  cookchela-api:latest
```

---

## 📈 Project Highlights

### Technical Excellence
- ✅ **Modern PHP Stack** - Laravel 12, PHP 8.2+, latest dependencies
- ✅ **Clean Code** - PSR standards, service layer pattern, SOLID principles
- ✅ **RESTful API Design** - Consistent endpoints, proper HTTP methods & status codes
- ✅ **Security Best Practices** - Token auth, input validation, SQL injection protection
- ✅ **Cloud Integration** - Supabase storage for scalable file handling
- ✅ **Production Ready** - Docker support, error handling, logging

### Features yang Stand Out
- 🎯 **Advanced Search** - Full-text search + ingredient-based search
- 🎯 **Social Features** - Follow system, likes, bookmarks
- 🎯 **OAuth Integration** - Google login untuk better UX
- 🎯 **Optimized Queries** - Eager loading, pagination, indexing
- 🎯 **API Versioning** - Prepared for future updates

### Dokumentasi & Developer Experience
- 📚 **Comprehensive API Docs** - Semua endpoints terdokumentasi
- 📚 **Clear Code Comments** - Self-documenting code
- 📚 **Easy Setup** - `.env.example`, migration seeds
- 📚 **Consistent Response** - Unified JSON format

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Jika Anda ingin berkontribusi:

1. Fork repository ini
2. Buat branch untuk feature baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation

---

## 👨‍💻 Author

**Alfan**
- GitHub: [@Alfan345](https://github.com/Alfan345)
- LinkedIn: [Your LinkedIn](https://linkedin.com/in/yourprofile) _(optional)_
- Portfolio: [Your Website](https://yourwebsite.com) _(optional)_

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [Supabase](https://supabase.com) - Open source Firebase alternative
- [Render](https://render.com) - Cloud hosting platform

---

<div align="center">

**⭐ Jika proyek ini bermanfaat, jangan lupa berikan star!**

Made with ❤️ by [Alfan](https://github.com/Alfan345)

</div>
