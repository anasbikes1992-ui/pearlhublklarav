/**
 * Vercel Edge Proxy — proxy.ts
 *
 * Runs at the edge before a page is served.
 */

import { NextRequest, NextResponse } from 'next/server';

let geolocationLookup: ((req: NextRequest) => { country?: string; city?: string }) | null = null;

try {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  geolocationLookup = require('@vercel/functions').geolocation;
} catch {
  /* not available locally */
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp|ico|css|js|woff2?|ttf|eot)).*)'
  ]
};

const PROTECTED_ROUTES = ['/bookings', '/dashboard', '/account', '/admin'];
const AUTH_ROUTES = ['/auth/login', '/auth/register'];

export async function proxy(req: NextRequest) {
  const { pathname } = req.nextUrl;
  const sessionToken = req.cookies.get('pearl_token')?.value;
  const isAuthenticated = !!sessionToken;

  if (PROTECTED_ROUTES.some((route) => pathname.startsWith(route)) && !isAuthenticated) {
    const loginUrl = req.nextUrl.clone();
    loginUrl.pathname = '/auth/login';
    loginUrl.searchParams.set('next', pathname);
    return NextResponse.redirect(loginUrl);
  }

  if (AUTH_ROUTES.some((route) => pathname === route) && isAuthenticated) {
    const homeUrl = req.nextUrl.clone();
    homeUrl.pathname = '/';
    homeUrl.searchParams.delete('next');
    return NextResponse.redirect(homeUrl);
  }

  const response = NextResponse.next();

  const geo = geolocationLookup ? geolocationLookup(req) : {};
  const country = (geo as Record<string, string | undefined>).country ?? 'LK';
  const city = (geo as Record<string, string | undefined>).city ?? '';
  response.headers.set('x-user-country', country);
  response.headers.set('x-user-city', city);

  response.headers.set('X-Frame-Options', 'DENY');
  response.headers.set('X-Content-Type-Options', 'nosniff');
  response.headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
  response.headers.set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
  response.headers.set(
    'Content-Security-Policy',
    [
      "default-src 'self'",
      // 'unsafe-inline' required for Next.js inline hydration scripts
      "script-src 'self' 'unsafe-inline'",
      "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
      // 'self' required because next/font self-hosts fonts at build time
      "font-src 'self' https://fonts.gstatic.com",
      "img-src 'self' data: blob: https:",
      "connect-src 'self' https: wss:",
      "frame-src 'none'"
    ].join('; ')
  );

  if (
    pathname.startsWith('/property') ||
    pathname.startsWith('/stays') ||
    pathname.startsWith('/vehicles') ||
    pathname.startsWith('/events') ||
    pathname.startsWith('/sme')
  ) {
    response.headers.set('Cache-Control', 'public, s-maxage=300, stale-while-revalidate=600');
  }

  response.headers.set('x-authenticated', isAuthenticated ? '1' : '0');

  return response;
}