# PearlHub Pro — Deployment Plan

## Overview
Full deployment of PearlHub Pro covering:
1. **Web (Next.js)** → Vercel production deployment
2. **Mobile (Flutter)** → Android APK build for Customer app

---

## Phase 1: Web UI Fix & Improvement

### Issues Found
| # | Issue | Impact |
|---|-------|--------|
| 1 | Missing page-specific CSS (`.listing-card`, `.auth-page`, `.detail-page`, `.catalog-page`, `.site-header`, `.site-footer`, etc.) | All pages render unstyled beyond base typography |
| 2 | No `.env.local` file | API calls default to `localhost:8000` which doesn't exist on Vercel |
| 3 | Fallback data works but needs graceful handling | Pages crash if API unreachable without fallback |

### Actions
- [x] Audit all CSS class names used in components vs defined in `styles.css`
- [ ] Add complete page-specific styles for: header, footer, listing cards, auth pages, detail pages, catalog pages, search, taxi, map section, city grid, trust strip, feature panels
- [ ] Add mobile hamburger menu for navigation
- [ ] Test build locally with `npm run build`
- [ ] Create `.env.local` with fallback config

---

## Phase 2: Vercel Deployment

### Prerequisites
- Vercel CLI installed (`npm i -g vercel`)
- Vercel account authenticated
- Project linked to repository

### Steps
1. Install Vercel CLI globally
2. Navigate to `web-nextjs/` directory
3. Run `vercel login` to authenticate
4. Run `vercel link` to connect to existing project (or create new)
5. Set environment variables:
   - `NEXT_PUBLIC_API_URL` — Laravel API base URL (or leave empty for fallback mode)
6. Run `vercel --prod` for production deployment
7. Verify all routes work:
   - `/` (homepage)
   - `/property`, `/property/[slug]`
   - `/stays`, `/stays/[city]`
   - `/vehicles`, `/vehicles/[slug]`
   - `/events`, `/events/[slug]`
   - `/sme`
   - `/taxi`
   - `/search?q=villa`
   - `/auth/login`, `/auth/register`

---

## Phase 3: Flutter APK Build (Customer App)

### Prerequisites
- Flutter SDK installed (>=3.4.0)
- Android SDK installed
- Java 17+ installed
- Android build tools and platform tools

### Steps
1. Generate Android project files:
   ```bash
   cd flutter-monorepo/apps/customer
   flutter create . --platforms=android
   ```
2. Configure API endpoint in build:
   ```bash
   flutter build apk --release --dart-define=API_URL=https://your-laravel-api.com/api/v1
   ```
3. Configure signing key:
   - Generate keystore: `keytool -genkey -v -keystore pearlhub.jks ...`
   - Create `android/key.properties`
   - Update `android/app/build.gradle` with signing config
4. Build release APK:
   ```bash
   flutter build apk --release
   ```
5. Output: `build/app/outputs/flutter-apk/app-release.apk`

### API Configuration
- Production: Set `API_URL` via `--dart-define`
- The `SharedApiClient` in `pearl_core` handles auth tokens, refresh callbacks, and all HTTP methods

---

## Architecture Summary

```
┌──────────────────────────────────────────────┐
│                   Vercel                      │
│  ┌─────────────────────────────────────────┐  │
│  │         Next.js 16 (web-nextjs)         │  │
│  │  - SSR/ISR pages                        │  │
│  │  - API routes (auth proxy)              │  │
│  │  - MapLibre GL interactive map          │  │
│  └──────────────────┬──────────────────────┘  │
│                     │ /api/auth/*              │
└─────────────────────┼────────────────────────┘
                      │
                      ▼
┌──────────────────────────────────────────────┐
│           Laravel API (Backend)               │
│  - /api/v1/listings, /search, /bookings      │
│  - /api/v1/auth (register, login, logout)    │
│  - /api/v1/taxi-rides, /reviews              │
│  - /api/v1/admin (stats, users)              │
│  - PayHere/WebXPay/Dialog Genie webhooks     │
└──────────────────────┬───────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│        Flutter Mobile Apps                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐     │
│  │ Customer │ │ Provider │ │  Admin   │     │
│  │   App    │ │   App    │ │   App    │     │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘     │
│       └─────────────┴─────────────┘           │
│              pearl_core package               │
│  (SharedApiClient, models, theme, services)  │
└──────────────────────────────────────────────┘
```

---

## Risk Mitigation
- **No Laravel backend deployed**: Web works with hardcoded fallback listings data — no 500 errors
- **API unreachable**: Graceful fallback to demo content for all listing pages
- **Flutter no Android dir**: Generated fresh with `flutter create .`
- **Signing key**: Use debug signing for initial APK; production keystore for Play Store later
