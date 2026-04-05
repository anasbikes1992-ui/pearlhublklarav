# Contabo Ubuntu 24.04 Deployment Runbook

This runbook is tailored for PearlHub production deployment on Contabo VPS.

## Target
- Domain: `pearlhub.lk`
- API domain (recommended): `api.pearlhub.lk`
- Server OS: Ubuntu 24.04
- Server IP: `84.247.149.182`

## Security First (Do This Immediately)
1. Rotate all exposed credentials (server root password, portal password, API secrets).
2. Create a non-root sudo user and disable password SSH login when key auth works.
3. Keep secrets only in server-side `.env` and never commit them.

## DNS
Create these A records:
- `@` -> `84.247.149.182` (if web is on this server)
- `api` -> `84.247.149.182`

## 1) Initial Server Bootstrap
SSH to server and run:

```bash
sudo bash /opt/scripts/contabo_bootstrap.sh
```

If script is not copied yet, see `scripts/deploy/contabo_bootstrap.sh` in this repo and paste it to server at `/opt/scripts/contabo_bootstrap.sh`.

## 2) Prepare Application Directory
Use:
- App path: `/var/www/pearlhub-api`
- Nginx site file: `/etc/nginx/sites-available/pearlhub-api`
- PHP-FPM socket: `/run/php/php8.3-fpm.sock`

## 3) Deploy Code
If using Git:

```bash
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
git clone <YOUR_REPO_URL> /var/www/pearlhub-api
cd /var/www/pearlhub-api
```

Then run deploy script:

```bash
bash scripts/deploy/deploy_laravel.sh
```

## 4) Configure Laravel Production Environment
Create `/var/www/pearlhub-api/.env` from `docs/templates/.env.production.example`.

Critical values:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://api.pearlhub.lk`
- DB credentials
- Redis credentials
- Mail credentials
- CORS/Sanctum domains including mobile/web origins

## 5) Nginx + SSL
1. Copy template from `scripts/deploy/nginx-pearlhub-api.conf` to `/etc/nginx/sites-available/pearlhub-api`.
2. Update `server_name` and `root` path.
3. Enable site:

```bash
sudo ln -sf /etc/nginx/sites-available/pearlhub-api /etc/nginx/sites-enabled/pearlhub-api
sudo nginx -t
sudo systemctl reload nginx
```

4. Enable TLS:

```bash
sudo certbot --nginx -d api.pearlhub.lk --non-interactive --agree-tos -m <YOUR_EMAIL>
```

## 6) Queue Workers + Scheduler
1. Copy `scripts/deploy/supervisor-laravel-worker.conf` to `/etc/supervisor/conf.d/laravel-worker.conf`.
2. Apply:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

3. Add cron:

```bash
* * * * * cd /var/www/pearlhub-api && php artisan schedule:run >> /dev/null 2>&1
```

## 7) Post-Deploy Verification
Run:

```bash
curl -I https://api.pearlhub.lk/api/v1/health
php artisan about
php artisan route:list | grep api
sudo supervisorctl status
```

Check app flows:
- Customer login
- Provider login + listing + booking status update
- Admin login + verification approve/reject + user status toggle

## 8) Release Build for Android Apps
From Windows machine:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/mobile/build_android_release.ps1 -ApiUrl "https://api.pearlhub.lk/api/v1" -BuildName "1.0.0" -BuildNumber 1 -Artifact both
```

Generated outputs:
- APK: `build/app/outputs/flutter-apk/app-release.apk`
- AAB: `build/app/outputs/bundle/release/app-release.aab`

## 9) Recommended Production Split
- Laravel API: Contabo Ubuntu
- Next.js frontend: Vercel (optional) or same Contabo box
- Flutter: build artifacts locally/CI and upload to Play Console
