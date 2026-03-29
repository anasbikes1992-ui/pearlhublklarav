# Deployment Guide — PearlHub

This guide walks you through deploying the PearlHub monorepo (Laravel API + Next.js frontend) to Vercel, with all required external services.

---

## Architecture Overview

```
pearlhublklarav/          ← Git root (monorepo)
├── app/                  ← Laravel 11 API backend
├── web-nextjs/           ← Next.js 15 frontend
├── vercel.json           ← Root Vercel configuration
└── web-nextjs/vercel.json
```

> **Note**: Vercel serves the Next.js frontend. The Laravel backend must be deployed separately (e.g., Laravel Forge, Railway, Render, or a VPS). Vercel's Next.js frontend calls the Laravel API via `NEXT_PUBLIC_API_URL`.

---

## Phase 1: Provision External Services

Before deploying to Vercel, set up the following services:

### 1.1 PostgreSQL Database
Recommended providers:
- [Neon](https://neon.tech) (serverless, free tier)
- [Railway](https://railway.app)
- [AWS RDS](https://aws.amazon.com/rds/)
- [Supabase](https://supabase.com)

Requirements:
- Enable SSL/TLS connections (`sslmode=require`)
- Note the host, port, database name, username, and password

### 1.2 Redis (Cache & Queues)
- [Upstash](https://upstash.com) (serverless Redis, free tier)
- [Redis Cloud](https://redis.com/redis-enterprise-cloud/)

### 1.3 File Storage (AWS S3 or compatible)
- Create an S3 bucket (or use Cloudflare R2, Backblaze B2)
- Create an IAM user with `s3:GetObject`, `s3:PutObject`, `s3:DeleteObject` permissions on that bucket only
- Note: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_DEFAULT_REGION`

### 1.4 Mail Provider
- [Mailgun](https://mailgun.com)
- [SendGrid](https://sendgrid.com)
- [AWS SES](https://aws.amazon.com/ses/)

### 1.5 Meilisearch
- [Meilisearch Cloud](https://cloud.meilisearch.com) (managed)
- Or self-hosted with HTTPS

### 1.6 Laravel Backend Hosting
Deploy the Laravel root to one of:
- [Railway](https://railway.app) — easiest, connects to PostgreSQL & Redis
- [Laravel Forge](https://forge.laravel.com) + VPS
- [Render](https://render.com)

After deploying the backend, note the public API URL (e.g., `https://api.pearlhub.com`).

---

## Phase 2: Configure Environment Variables

### 2.1 Generate a new APP_KEY
```bash
php artisan key:generate --show
# Copy the output: base64:xxxxxxx=
```

### 2.2 Laravel Backend `.env` (on your backend host)
Copy `.env.production.example` to `.env` and fill in every `REPLACE_WITH_` value.

Critical settings:
```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
DB_SSLMODE=require
REVERB_SCHEME=https
LOG_LEVEL=warning
```

### 2.3 Vercel Environment Variables (Next.js frontend)
In your Vercel project dashboard → **Settings → Environment Variables**, add:

| Variable | Value |
|---|---|
| `NEXT_PUBLIC_API_URL` | `https://your-laravel-api-host.com` |
| `NEXT_PUBLIC_REVERB_APP_KEY` | Your Reverb app key |
| `NEXT_PUBLIC_REVERB_HOST` | Your Reverb host |
| `NEXT_PUBLIC_REVERB_PORT` | `443` |
| `NEXT_PUBLIC_REVERB_SCHEME` | `https` |

---

## Phase 3: Deploy the Next.js Frontend to Vercel

### 3.1 Connect Your Repository

1. Go to [vercel.com/new](https://vercel.com/new)
2. Import the `anasbikes1992-ui/pearlhublklarav` repository
3. Vercel will detect `vercel.json` at the root

### 3.2 Configure Build Settings in Vercel Dashboard

| Setting | Value |
|---|---|
| **Root Directory** | `web-nextjs` |
| **Framework Preset** | Next.js |
| **Build Command** | `npm run build` |
| **Output Directory** | `.next` |
| **Install Command** | `npm install` |

### 3.3 Deploy

Click **Deploy**. Vercel will:
1. Install dependencies (`npm install` in `web-nextjs/`)
2. Build the Next.js app (`npm run build`)
3. Deploy to `pearlhublklarav.vercel.app`

---

## Phase 4: Deploy the Laravel Backend

### 4.1 Run Migrations
```bash
php artisan migrate --force
```

### 4.2 Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 4.3 Start the Queue Worker
```bash
php artisan queue:work --queue=default --tries=3 --timeout=90
```

### 4.4 Start Reverb WebSocket Server
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

---

## Phase 5: Post-Deployment Verification

```bash
# 1. Check HTTPS and security headers
curl -sI https://pearlhublklarav.vercel.app | grep -E "strict-transport|x-content|x-frame"

# 2. Verify the Next.js build is serving correctly
curl -s https://pearlhublklarav.vercel.app | head -20

# 3. Test the Laravel API health endpoint
curl -s https://your-laravel-api-host.com/api/health

# 4. Check Vercel deployment logs
vercel logs https://pearlhublklarav.vercel.app
```

---

## Phase 6: Custom Domain (Optional)

1. Vercel Dashboard → **Settings → Domains**
2. Add your custom domain (e.g., `pearlhub.com`)
3. Follow Vercel's DNS configuration instructions
4. SSL certificate is provisioned automatically

---

## Rollback

To roll back to a previous deployment:
1. Vercel Dashboard → **Deployments**
2. Find the last successful deployment
3. Click **...** → **Promote to Production**

Or via CLI:
```bash
vercel rollback
```

---

## Monitoring

- **Vercel Analytics**: Enable in Vercel Dashboard → Analytics
- **Error Tracking**: Add [Sentry](https://sentry.io) to both Laravel and Next.js
- **Uptime Monitoring**: Use [Better Uptime](https://betteruptime.com) or [UptimeRobot](https://uptimerobot.com)
- **Database Monitoring**: Enable slow query logs on your PostgreSQL provider

---

## Troubleshooting

| Problem | Solution |
|---|---|
| Build fails — missing env var | Add the variable in Vercel Dashboard → Environment Variables |
| API calls fail (CORS) | Set `SANCTUM_STATEFUL_DOMAINS` to include your Vercel domain |
| WebSocket not connecting | Verify `REVERB_SCHEME=https` and port 443 |
| File uploads fail | Check AWS S3 bucket policy and IAM permissions |
| Migrations fail | Ensure `DB_SSLMODE=require` and DB is accessible from backend host |
