# PearlHub Go-Live Report

## Current Status

- Backend codebase audited and hardened.
- Local Laravel setup switched from PostgreSQL to MySQL and is working.
- PHP loads `pdo_mysql` successfully.
- MySQL Windows service `MySQL80` is running.
- Database migrations and seeders complete successfully.
- Laravel backend runs successfully on `http://127.0.0.1:8000`.
- Next.js frontend runs successfully on `http://127.0.0.1:3000`.
- Demo API login works successfully.

## Changes Applied

- Enabled `pdo_mysql` in `C:\tools\php85\php.ini`.
- Switched Laravel `.env` and `.env.example` to MySQL defaults.
- Switched local Laravel session/cache/queue drivers to beginner-safe defaults:
  - `SESSION_DRIVER=file`
  - `CACHE_STORE=file`
  - `QUEUE_CONNECTION=sync`
- Replaced PostgreSQL-only `ilike` with cross-database `like` in search.
- Updated README database examples to MySQL.

## Verified

- `php artisan --version` works.
- `php artisan route:cache` worked earlier.
- `php -m` shows `pdo_mysql` is loaded.
- `app/Services/SearchService.php` passes `php -l`.
- `php artisan migrate:fresh --seed --force` completes successfully.
- `GET /api/v1/health` returns success.
- `GET /` on Laravel returns `200 OK`.
- `GET /` on Next.js returns `200 OK`.
- `POST /api/v1/auth/login` works for the seeded admin user.

## Demo Credentials

Use these for local testing:

- Admin: `admin@pearlhub.lk` / `secret123`
- Provider: `provider@pearlhub.lk` / `secret123`
- Customer: `customer@pearlhub.lk` / `secret123`

## Local Database Status

- Database name: `pearlhub`
- Connection: MySQL on `127.0.0.1:3306`
- Local `.env` is configured and working.

## What To Set In .env

Update these values with your real MySQL account:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pearlhub
DB_USERNAME=root
DB_PASSWORD=YOUR_MYSQL_PASSWORD
```

This is already configured locally now. Keep these values for your machine unless your MySQL setup changes.

## SQL To Create The Database

Run this in MySQL Workbench or any MySQL client:

```sql
CREATE DATABASE IF NOT EXISTS pearlhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Local Test Steps

To rebuild the local environment from scratch, run:

```powershell
cd "d:\Pearl La\pearlhublklarav"
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan serve
```

Then open:

- `http://127.0.0.1:8000`
- `http://127.0.0.1:8000/api/v1/health`

## Web Frontend

```powershell
cd "d:\Pearl La\pearlhublklarav\web-nextjs"
npm install
npm run dev
```

Open:

- `http://localhost:3000`
- `http://127.0.0.1:3000`

## Flutter Apps

Example customer app run command:

```powershell
cd "d:\Pearl La\pearlhublklarav\flutter-monorepo\apps\customer"
flutter pub get
flutter run --dart-define=API_URL=http://10.0.2.2:8000/api/v1
```

## Go-Live Readiness

Production is not ready to deploy until these are confirmed:

- Production MySQL credentials set on the target server.
- Production database migrated successfully.
- Admin bootstrap strategy confirmed.
- Laravel production `.env` completed.
- Next.js production env vars completed.
- Payment gateway production keys added.
- Mail configuration added.
- Queue worker and scheduler deployment plan confirmed.
- Domain, SSL, backups, and monitoring confirmed.

## Git Status

Changes were made locally in the workspace.

Nothing has been pushed to GitHub by me.
