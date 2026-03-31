import { NextRequest, NextResponse } from 'next/server';

// Prefer the private server-side var; fall back to the public one for local dev.
const API_BASE = process.env.API_INTERNAL_URL ?? process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

export async function POST(req: NextRequest) {
  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ message: 'Invalid request body' }, { status: 400 });
  }

  let laravelRes: Response;
  try {
    laravelRes = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    });
  } catch {
    return NextResponse.json({ message: 'Backend unreachable' }, { status: 503 });
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
