# PearlHub Full Technical Documentation

## 1. System Overview
PearlHub is a multi-platform marketplace system with:
- Laravel 11 backend API and admin tooling
- Web frontend (Next.js workspace module)
- Flutter apps for customer, provider, and admin operations (Android-capable)
- MySQL database and queue-based background processing

Primary domains include property, stays, vehicles, events, and SME services.

## 2. High-Level Architecture
- Backend API: Laravel 11 (PHP 8.2)
- Authentication: Laravel Sanctum
- Realtime/WebSocket: Laravel Reverb
- Search: Laravel Scout + Meilisearch
- Admin panel: Filament (path: /admin)
- Queue processing: Laravel queue worker (database queue)
- Database: MySQL 8 (Coolify-managed in production)
- Build/deploy: Nixpacks + Coolify

## 3. Repository Structure
### Web/Backend workspace
- app/: Domain logic, API controllers, middleware, services, jobs
- routes/api.php: API v1 endpoint map
- config/: Framework and service configuration
- database/migrations: schema evolution
- database/seeders: data seed routines
- web-nextjs/: web frontend module
- .github/workflows/deploy.yml: production deployment trigger to Coolify

### Mobile workspace
- Apps/customer: customer Flutter app
- Apps/provider: provider Flutter app
- Apps/admin: admin Flutter app
- flutter-monorepo/packages/pearl_core: shared Flutter package for core services/models

## 4. Backend Stack And Core Packages
From composer.json:
- PHP: ^8.2
- laravel/framework: ^11.31
- laravel/sanctum: ^4.0
- laravel/reverb: ^1.4
- laravel/scout: ^10.13
- filament/filament: ^3.2
- meilisearch/meilisearch-php: ^1.8
- maatwebsite/excel: ^3.1

## 5. API Documentation (routes/api.php)
All API routes are namespaced under /api/v1.

### Public endpoints
- GET /health
- POST /auth/register
- POST /auth/login
- POST /payments/webhooks/webxpay
- POST /payments/webhooks/genie
- POST /payments/webhooks/koko-pay
- POST /payments/webhooks/mint-pay
- GET /search
- POST /concierge/chat
- POST /promo-codes/validate
- POST /fees/calculate
- GET /listings
- GET /listings/{listing}
- GET /listings/{listing}/reviews
- GET /social/feed
- GET /social/posts/{post}/comments
- GET /social/users/{user}/profile

### Authenticated endpoints (auth:sanctum)
- POST /auth/logout
- GET /listings/my
- POST /listings
- PUT/PATCH /listings/{listing}
- DELETE /listings/{listing}
- POST /payments/checkout
- GET /users/profile
- PUT /users/profile
- POST /listings/{listing}/reviews
- bookings resource (except destroy)
- taxi-rides resource (except destroy)
- POST /listings/{listing}/verify
- POST /property/owner-listing
- POST /property/broker-listing
- promo code management routes
- cashback routes
- wallet routes
- chat routes
- SME subscription/product/report routes
- social write routes

### Admin-only endpoints (inside auth and admin middleware)
- GET /admin/stats
- GET /admin/users
- PUT /admin/users/{userId}
- POST /cashback/{cashbackRecord}/credit

## 6. Authentication And Security
- Token/session auth via Sanctum
- Route protection with auth:sanctum middleware
- Role-based middleware for admin routes
- Throttling on auth endpoints
- Ownership middleware used for protected listing mutations

## 7. Environment Variables (Backend)
Key application variables include:
- APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- SESSION_DRIVER, QUEUE_CONNECTION, CACHE_STORE
- SANCTUM_STATEFUL_DOMAINS
- FRONTEND_URL
- Payment provider credentials and webhook secrets
- Reverb and Meilisearch variables

Reference baseline: .env.example

## 8. Database And State
- Database engine: MySQL
- Typical local defaults: 127.0.0.1:3306, database pearlhub
- Session driver: database
- Queue connection: database
- Cache store: database

## 9. Filament Admin Panel
Configured in AdminPanelProvider:
- Panel ID: admin
- Path: /admin
- Includes dashboard and discovered resources from app/Filament/Resources
- Uses session, CSRF, auth middleware stack

## 10. Build And Runtime (Nixpacks + Procfile)
Production build/runtime behavior:
- Build includes composer install (no-dev) and frontend build
- Web process starts Laravel app server
- Worker process handles queues
- Post-deploy command configured in Coolify:
  - php artisan migrate:fresh --seed --force

## 11. CI/CD
GitHub workflow deploy.yml:
- Trigger: push to main and manual dispatch
- Action: calls Coolify deploy API endpoint
- Required GitHub secret:
  - COOLIFY_TOKEN

Deployment endpoint pattern:
- POST {COOLIFY_URL}/api/v1/deploy?uuid={COOLIFY_APP_UUID}&force=false

## 12. Server Documentation (Production)
### Hosting/Orchestration
- Coolify instance used for app/database resources
- Laravel app resource configured with Nixpacks
- MySQL resource enabled and connected through internal network

### Required production settings
- Correct APP_URL for production domain
- DB host set to internal Coolify service name
- APP_KEY set and preserved
- APP_DEBUG=false
- Log level tuned for production
- Queue worker process running continuously

### Operational checklist
- Verify env variables after changes
- Ensure queue worker healthy after each deploy
- Validate migration/seed completion in deployment logs
- Verify /api/v1/health endpoint
- Verify main web and app API flows

## 13. Web App Documentation
Web module lives at web-nextjs.

Expected integration pattern:
- Uses Laravel API as backend source of truth
- Must align with FRONTEND_URL and SANCTUM_STATEFUL_DOMAINS
- Should consume /api/v1 routes and authenticated endpoints via session/token strategy

Recommended web checks each release:
- Auth login/logout roundtrip
- Listings browse/detail
- Search endpoint integration
- Booking/payment flow where enabled
- Admin route visibility restrictions

## 14. Android App Documentation (Flutter)
This workspace includes three Flutter apps with Android targets:
- Apps/customer
- Apps/provider
- Apps/admin

### Package/app identifiers from pubspec
- customer app name: customer
- provider app name: pearlhub_provider
- admin app name: pearlhub_admin

### Shared technical stack
- Flutter SDK (Dart SDK ^3.11.3)
- State management: provider
- Navigation: go_router
- HTTP: dio

### Customer app (Apps/customer)
- Entry: lib/main.dart
- Initializes SharedApiClient with API_URL dart-define fallback:
  - http://127.0.0.1:8000/api/v1
- Initializes AuthService before runApp
- Registers services via MultiProvider/ProxyProvider pattern

### Provider and admin apps
- Similar Flutter stack with provider/go_router/dio
- Include additional dependencies such as intl, path_provider, uuid
- Intended for provider operations and administrative operations respectively

### Shared package
- flutter-monorepo/packages/pearl_core
- Contains shared models, API client, and common services

## 15. Android Build And Release Guidance
Typical commands (CI or local):
- flutter pub get
- flutter analyze
- flutter test
- flutter build apk --release
- flutter build appbundle --release

Release artifact expectations:
- APK for direct install/testing
- AAB for Play Console submissions

## 16. End-To-End Integration Notes (Web + Android)
- All clients should target the same API version (/api/v1)
- Consistent auth/session behavior is required across web and apps
- Any new backend route should be reflected in web and Flutter service layers
- Keep API_URL and production app URLs synchronized across environments

## 17. Troubleshooting Quick Reference
### Backend
- 500 errors after deploy: verify APP_KEY, DB credentials, migration output
- Auth issues: verify SANCTUM_STATEFUL_DOMAINS and FRONTEND_URL
- Slow or stuck jobs: verify queue worker process and queue table health

### Web
- CORS/session issues: verify frontend origin and backend config alignment
- Missing data views: confirm API endpoint and auth token/session validity

### Android
- API connection failure: verify API_URL/host reachability from device/emulator
- Build failures: run flutter clean, flutter pub get, re-run build
- Auth persistence issues: verify local secure/session storage logic in app services

## 18. Documentation Ownership And Update Policy
Recommended process:
- Update this documentation whenever routes, env vars, deployment process, or mobile app architecture changes
- Keep deployment workflow and production env list synchronized
- Version docs with release milestones

## 19. Current Production Deployment Snapshot
- App deployment is orchestrated via Coolify API and GitHub Actions
- Post-deploy command configured to refresh and seed database on each deploy
- MySQL is provisioned in Coolify and linked as backend database resource

## 20. Pending Enhancements (Recommended)
- Add OpenAPI/Swagger export for API consumers
- Add per-app Android release checklist with signing configs and store tracks
- Add operational runbook with rollback strategy and incident response flow
- Add architecture diagrams for payment and booking flows
