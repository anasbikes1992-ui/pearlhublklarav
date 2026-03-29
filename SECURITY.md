# Security Policy — PearlHub

## Supported Versions

| Version | Supported |
|---------|-----------|
| Latest  | ✅ Yes    |

## Reporting a Vulnerability

Please **do not** open public GitHub issues for security vulnerabilities.

Email security findings to: **security@pearlhub.com**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (optional)

You will receive an acknowledgement within 48 hours and a full response within 7 days.

---

## Security Hardening Checklist

Use this checklist before every production deployment.

### Application

- [ ] `APP_DEBUG=false` is set in production
- [ ] `APP_ENV=production` is set
- [ ] `APP_KEY` is a freshly generated, unique key (`php artisan key:generate`)
- [ ] `SESSION_ENCRYPT=true` is enabled
- [ ] `SESSION_SECURE_COOKIE=true` is enabled
- [ ] `SESSION_DRIVER=database` (not `cookie`)
- [ ] CSRF protection is active on all state-changing routes
- [ ] `LOG_LEVEL=warning` (not `debug`) in production

### Database

- [ ] Strong, unique database password (32+ characters)
- [ ] `DB_SSLMODE=require` enforced
- [ ] Database host is NOT publicly accessible (allowlist Vercel/app IPs only)
- [ ] Database backups are encrypted and stored off-site
- [ ] No credentials or connection strings committed to git

### Payment Gateways

- [ ] All webhook secrets are set via environment variables (never hardcoded)
- [ ] Webhook signatures are validated on every incoming request
- [ ] Payment credentials rotated after any suspected exposure
- [ ] Live/production credentials are separate from test credentials

### Secrets Management

- [ ] All secrets stored in Vercel Environment Variables (not in code)
- [ ] `.env` is listed in `.gitignore`
- [ ] No real secrets appear in `.env.example` or `.env.production.example`
- [ ] AWS IAM keys follow the principle of least privilege
- [ ] API keys are rotated regularly (every 90 days minimum)

### Network & HTTPS

- [ ] HTTPS enforced for all traffic (automatic via Vercel)
- [ ] HSTS header enabled: `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- [ ] `X-Content-Type-Options: nosniff` header present
- [ ] `X-Frame-Options: DENY` header present
- [ ] `X-XSS-Protection: 1; mode=block` header present
- [ ] `Referrer-Policy: strict-origin-when-cross-origin` header present
- [ ] CORS configured to allow only trusted origins

### Dependencies

- [ ] `composer audit` run — no known vulnerabilities
- [ ] `npm audit` run — no known vulnerabilities
- [ ] `laravel/framework` is on the latest patch release
- [ ] `filament/filament` is up-to-date
- [ ] `laravel/sanctum` is up-to-date

### WebSockets (Reverb)

- [ ] `REVERB_SCHEME=https` in production
- [ ] Reverb app secret is a strong, randomly generated value
- [ ] WebSocket connections authenticated via Sanctum

---

## Security Headers (Laravel Middleware)

Add the following middleware to `app/Http/Middleware/SecurityHeaders.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return $response
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }
}
```

Register it in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

---

## Webhook Signature Verification

Always verify payment gateway webhook signatures before processing:

```php
// PayHere example
$payload  = $request->getContent();
$expected = hash_hmac('sha256', $payload, config('services.payhere.webhook_secret'));

if (! hash_equals($expected, $request->header('X-PayHere-Signature', ''))) {
    abort(401, 'Invalid webhook signature');
}
```

---

## Dependency Audit Commands

```bash
# Laravel/PHP
composer audit

# Node / Next.js
cd web-nextjs && npm audit

# Update all packages
composer update
cd web-nextjs && npm update
```

---

## Incident Response

1. **Rotate** all affected secrets immediately
2. **Revoke** any compromised API keys or tokens
3. **Notify** affected users if personal data was exposed
4. **Document** the incident and root cause
5. **Patch** the vulnerability before re-deploying
6. **Review** audit logs for the scope of the breach
