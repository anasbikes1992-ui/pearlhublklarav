#!/usr/bin/env node

const base = process.argv[2] || process.env.MONITOR_BASE_URL || 'https://web-nextjs-sage-pi.vercel.app';

const routes = [
  '/',
  '/backup',
  '/backup/admin',
  '/auth/login',
  '/auth/register',
  '/admin',
  '/admin/users',
  '/admin/listings',
  '/admin/bookings',
  '/admin/social',
  '/admin/payments',
  '/property',
  '/stays',
  '/vehicles',
  '/events',
  '/experiences',
  '/sme',
  '/taxi',
  '/social',
  '/search',
];

async function check(route) {
  const url = `${base.replace(/\/$/, '')}${route}`;
  try {
    const res = await fetch(url, { method: 'GET', redirect: 'follow' });
    return { route, status: res.status, ok: res.ok };
  } catch (error) {
    return { route, status: 'ERR', ok: false, error: String(error) };
  }
}

const results = await Promise.all(routes.map(check));

const failures = results.filter((r) => !r.ok);
for (const r of results) {
  const badge = r.ok ? 'OK ' : 'BAD';
  // Keep this plain for easy grep in CI logs.
  console.log(`${badge} ${String(r.status).padEnd(3)} ${r.route}`);
}

if (failures.length > 0) {
  console.error(`\nRoute monitor found ${failures.length} failing route(s) on ${base}`);
  process.exit(1);
}

console.log(`\nAll ${results.length} routes are healthy on ${base}`);
