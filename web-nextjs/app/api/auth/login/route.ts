import { NextRequest, NextResponse } from 'next/server';

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

const DEMO_USERS: Record<string, { id: string; name: string; email: string; role: string }> = {
  'admin@pearlhub.lk': { id: 'demo-admin', name: 'Admin User', email: 'admin@pearlhub.lk', role: 'admin' },
  'provider@pearlhub.lk': { id: 'demo-provider', name: 'Provider User', email: 'provider@pearlhub.lk', role: 'provider' },
  'customer@pearlhub.lk': { id: 'demo-customer', name: 'Customer User', email: 'customer@pearlhub.lk', role: 'customer' },
};

// Demo credentials — only used when the Laravel backend is unreachable.
// When NEXT_PUBLIC_API_URL points to a live backend, these are never checked.
const DEMO_PASSWORD = 'password';

function demoLogin(email: string, password: string) {
  const user = DEMO_USERS[email];
  if (!user || password !== DEMO_PASSWORD) return null;
  return { user, token: `demo-token-${user.role}` };
}

export async function POST(req: NextRequest) {
  let body: { email?: string; password?: string };
  try {
    body = (await req.json()) as { email?: string; password?: string };
  } catch {
    return NextResponse.json({ message: 'Invalid request body' }, { status: 400 });
  }

  let laravelRes: Response | null = null;
  try {
    laravelRes = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    });
  } catch {
    // Laravel backend not available — fall through to demo mode
  }

  if (laravelRes) {
    const data = (await laravelRes.json()) as { token?: string; user?: unknown; message?: string };

    if (!laravelRes.ok) {
      return NextResponse.json({ message: data.message ?? 'Login failed' }, { status: laravelRes.status });
    }

    const res = NextResponse.json(data, { status: 200 });
    if (data.token) {
      res.cookies.set('pearl_token', data.token, {
        httpOnly: true,
        sameSite: 'lax',
        secure: process.env.NODE_ENV === 'production',
        maxAge: 60 * 60 * 24 * 30,
        path: '/',
      });
    }
    return res;
  }

  // Demo mode fallback when Laravel backend is unreachable
  const demo = demoLogin(body.email ?? '', body.password ?? '');
  if (!demo) {
    return NextResponse.json({ message: 'Invalid credentials' }, { status: 401 });
  }

  const res = NextResponse.json(demo, { status: 200 });
  res.cookies.set('pearl_token', demo.token, {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    maxAge: 60 * 60 * 24 * 30,
    path: '/',
  });
  return res;
}
