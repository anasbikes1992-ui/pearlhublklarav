# PearlHub Laravel API (v1)

Laravel 11 + PostgreSQL backend foundation for PearlHub Pro multi-vertical marketplace.

This repository has been bootstrapped with:

- API versioning via `/api/v1`
- UUID primary keys across domain tables
- Domain models for Users, Profiles, Listings, Bookings, Wallet Transactions, Taxi Rides
- Repository pattern (`ListingRepositoryInterface` + Eloquent implementation)
- Service layer (`ListingService`)
- Payment gateway abstraction for PayHere + WebXPay

## Current Structure Highlights

- API routes: `routes/api.php`
- API controllers: `app/Http/Controllers/Api/V1`
- Validation requests: `app/Http/Requests`
- Domain models: `app/Models`
- Domain services: `app/Services`
- Repository contracts/implementations: `app/Repositories`
- Migrations: `database/migrations`

## Local Setup

1. Install PHP 8.2+ and Composer.
2. Copy `.env.example` to `.env`.
3. Run:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

4. Health endpoint:

```bash
GET /api/v1/health
```

## Notes

- Sanctum is already declared in `composer.json` and should be installed with dependencies.
- Payment gateway classes are scaffold stubs; production signature validation, idempotency, queue handling, and retries should be added before launch.
- This is a clean baseline for implementing booking, escrow, taxi matching, KYC, and Filament admin workflows.
