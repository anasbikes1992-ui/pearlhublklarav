# PearlHub — Sri Lanka's Multi-Vertical Marketplace Platform

> A unified platform connecting providers and customers across five service verticals in Sri Lanka: **Property**, **Stays**, **Vehicles**, **Events**, and **SME Food & Services**.

---

## Platform Overview

| Layer | Technology | Purpose |
|---|---|---|
| **API Backend** | Laravel 11 + PostgreSQL | REST API, auth, payments, escrow |
| **Web Frontend** | Next.js 16 + TypeScript | Customer-facing marketplace |
| **Provider App** | Flutter (Android/iOS) | Business owner dashboard |
| **Admin App** | Flutter (Android/iOS) | Platform operations & moderation |
| **Shared SDK** | `pearl_core` Dart package | API client, auth, shared models |

---

## Repository Structure

```
pearlhublklarav/
├── app/                          # Laravel — controllers, models, services
│   ├── Http/Controllers/Api/V1/  # REST API controllers
│   ├── Http/Middleware/          # EnsureAdminRole, etc.
│   ├── Models/                   # Eloquent models (UUID-first)
│   └── Services/                 # Business logic
├── database/
│   ├── migrations/               # 19 migrations (users → reviews)
│   ├── factories/                # ListingFactory, ReviewFactory
│   └── seeders/                  # DatabaseSeeder, ListingSeeder
├── routes/api.php                # All v1 API routes
├── web-nextjs/                   # Next.js 16 web app
│   ├── app/                      # App Router pages
│   ├── components/               # Reusable UI components
│   └── lib/                      # API client, types
└── flutter-monorepo/
    ├── packages/pearl_core/      # Shared Dart SDK (Dio, Sanctum)
    └── apps/
        ├── customer/             # Customer marketplace app
        ├── provider/             # Provider business app
        └── admin/                # Admin command center
```

---

## API Endpoints (v1)

### Public
```
GET  /api/v1/health
GET  /api/v1/listings              # Browse all listings (filterable)
GET  /api/v1/listings/{id}         # Listing detail
GET  /api/v1/listings/{id}/reviews # Listing reviews
GET  /api/v1/search                # Full-text + geo search
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
```

### Authenticated (Sanctum)
```
GET  /api/v1/listings/my           # Provider's own listings
POST /api/v1/listings              # Create listing
PUT  /api/v1/listings/{id}         # Update listing
GET  /api/v1/users/profile         # Get profile
PUT  /api/v1/users/profile         # Update profile
POST /api/v1/listings/{id}/reviews # Post a review
POST /api/v1/bookings
PUT  /api/v1/bookings/{id}
POST /api/v1/taxi-rides
POST /api/v1/listings/{id}/verify  # Submit for verification
```

### Admin Only (role: admin)
```
GET /api/v1/admin/stats
GET /api/v1/admin/users
PUT /api/v1/admin/users/{id}       # Toggle is_active
```

---

## Database Schema (Key Tables)

| Table | Key Fields |
|---|---|
| `users` | id (UUID), full_name, email, role, is_active |
| `profiles` | user_id, bio, avatar_url, id_verified |
| `listings` | id (UUID), title, slug, vertical, provider_id, status, lat, lng, price |
| `reviews` | id (UUID), listing_id, reviewer_id, rating (3.1), body |
| `bookings` | id (UUID), listing_id, customer_id, status, price_snapshot |
| `taxi_rides` | id (UUID), origin, destination, fare, status |
| `wallets` | user_id, balance, currency |
| `escrows` | booking_id, amount, released_at |

---

## Local Setup

### Backend (Laravel)

**Requirements:** PHP 8.2+, Composer, PostgreSQL 15+

```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
# Edit .env: DB_*, SANCTUM_STATEFUL_DOMAINS, etc.

# 3. Generate key & migrate
php artisan key:generate
php artisan migrate
php artisan db:seed

# 4. Serve
php artisan serve
# → http://localhost:8000/api/v1/health
```

**Test credentials (seeded):**
| Role | Email | Password |
|---|---|---|
| Admin | admin@pearlhub.lk | password |
| Provider | provider@pearlhub.lk | password |
| Customer | customer@pearlhub.lk | password |

### Web Frontend (Next.js)

```bash
cd web-nextjs
cp .env.local.example .env.local
# Set NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
npm install
npm run dev
# → http://localhost:3000
```

### Flutter Apps

```bash
cd flutter-monorepo

# Install shared SDK
cd packages/pearl_core && flutter pub get && cd ../..

# Provider app
cd apps/provider
flutter pub get
flutter run --dart-define=API_URL=http://10.0.2.2:8000/api/v1

# Admin app
cd apps/admin
flutter pub get
flutter run --dart-define=API_URL=http://10.0.2.2:8000/api/v1
```

> Use `10.0.2.2` for Android emulator to reach host localhost.

---

## Deployment

### Next.js → Vercel

```bash
cd web-nextjs
npx vercel --prod
```

Set in Vercel dashboard:
- `NEXT_PUBLIC_API_URL` → `https://api.pearlhub.lk/api/v1`

### Laravel → Server (Forge / Railway / VPS)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
```

---

## Tech Stack

- **Laravel 11** — API, Sanctum auth, Scout search, Reverb WebSockets, Filament admin
- **PostgreSQL** — Primary database with UUID keys
- **Next.js 16** — App Router, TypeScript, Tailwind CSS, MapLibre GL
- **Flutter 3.x** — Cross-platform mobile (customer + provider + admin)
- **pearl_core** — Shared Dart package: Dio HTTP client, secure token storage

---

## Five Verticals

| Vertical | Icon | Description |
|---|---|---|
| Property | 🏘️ | Buy/rent land and residences |
| Stays | 🌴 | Short-term accommodation (Airbnb-style) |
| Vehicles | 🚗 | Car, tuktuk, and motorcycle rentals |
| Events | 🎉 | Venues, catering, and entertainment |
| SME | 🍛 | Local food stalls, shops, and services |

---

## Environment Variables

### Laravel `.env`
```env
APP_NAME=PearlHub
APP_URL=https://api.pearlhub.lk
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pearlhub
DB_USERNAME=postgres
DB_PASSWORD=secret
SANCTUM_STATEFUL_DOMAINS=pearlhub.lk,localhost:3000
FRONTEND_URL=https://pearlhub.lk
```

### Next.js `.env.local`
```env
NEXT_PUBLIC_API_URL=https://api.pearlhub.lk/api/v1
```
