import { NextRequest, NextResponse } from 'next/server';

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

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
