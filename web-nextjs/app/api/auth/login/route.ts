import { NextRequest, NextResponse } from 'next/server';

// Prefer the private server-side var; fall back to the public one for local dev.
const API_BASE = process.env.API_INTERNAL_URL ?? process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

// Demo users — active when the real backend is unreachable (e.g. Vercel preview without a deployed API)
const DEMO_USERS: Record<string, { email: string; password: string; name: string; role: string }> = {
  'anasbikes1992@gmail.com': { email: 'anasbikes1992@gmail.com', password: '123456',    name: 'Anas Admin',   role: 'admin'    },
  'admin@pearlhub.lk':       { email: 'admin@pearlhub.lk',      password: 'secret123', name: 'Admin User',   role: 'admin'    },
  'provider@pearlhub.lk':    { email: 'provider@pearlhub.lk',   password: 'secret123', name: 'Test Provider',role: 'provider' },
  'customer@pearlhub.lk':    { email: 'customer@pearlhub.lk',   password: 'secret123', name: 'Test Customer',role: 'customer' },
};

export async function POST(req: NextRequest) {
  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ message: 'Invalid request body' }, { status: 400 });
  }

  let laravelRes: Response;
  let backendUnreachable = false;
  try {
    laravelRes = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    });
  } catch {
    backendUnreachable = true;
    // Fall through to demo auth below
    laravelRes = new Response(null, { status: 503 });
  }

  // Demo fallback — only triggers when the real API is unreachable
  if (backendUnreachable) {
    const { email, password } = body as { email?: string; password?: string };
    const demo = email ? DEMO_USERS[email] : undefined;
    if (demo && password === demo.password) {
      const token = `demo_${demo.role}_${Buffer.from(demo.email).toString('base64')}`;
      const user = { id: `demo-${demo.role}`, full_name: demo.name, name: demo.name, email: demo.email, role: demo.role };
      const res = NextResponse.json({ user }, { status: 200 });
      res.cookies.set('pearl_token', token, {
        httpOnly: true,
        sameSite: 'lax',
        secure: process.env.NODE_ENV === 'production',
        maxAge: 60 * 60 * 24 * 30,
        path: '/',
      });
      return res;
    }
    return NextResponse.json({ message: 'Invalid credentials' }, { status: 401 });
  }

  const data = (await laravelRes.json()) as {
    data?: { token?: string; user?: unknown };
    token?: string;
    user?: unknown;
    message?: string;
  };

  if (!laravelRes.ok) {
    return NextResponse.json({ message: data.message ?? 'Login failed' }, { status: laravelRes.status });
  }

  const token = data.data?.token ?? data.token;
  const user = data.data?.user ?? data.user;

  const res = NextResponse.json({ user }, { status: 200 });
  if (token) {
    res.cookies.set('pearl_token', token, {
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production',
      maxAge: 60 * 60 * 24 * 30,
      path: '/',
    });
  }
  return res;
}
