import { NextRequest, NextResponse } from 'next/server';

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

export async function POST(req: NextRequest) {
  let body: { name?: string; email?: string; password?: string; password_confirmation?: string };
  try {
    body = (await req.json()) as { name?: string; email?: string; password?: string; password_confirmation?: string };
  } catch {
    return NextResponse.json({ message: 'Invalid request body' }, { status: 400 });
  }

  let laravelRes: Response | null = null;
  try {
    laravelRes = await fetch(`${API_BASE}/auth/register`, {
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
      return NextResponse.json({ message: data.message ?? 'Registration failed' }, { status: laravelRes.status });
    }

    const res = NextResponse.json(data, { status: 201 });
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
  const email = body.email ?? '';
  const name = body.name ?? 'Demo User';
  const demoData = {
    user: { id: `demo-${Date.now()}`, name, email, role: 'customer' },
    token: `demo-token-register-${Date.now()}`,
  };

  const res = NextResponse.json(demoData, { status: 201 });
  res.cookies.set('pearl_token', demoData.token, {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    maxAge: 60 * 60 * 24 * 30,
    path: '/',
  });
  return res;
}
