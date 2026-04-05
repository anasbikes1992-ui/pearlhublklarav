import { NextRequest, NextResponse } from 'next/server';
import { SERVER_API_BASE } from '@/lib/env';

const API_BASE = SERVER_API_BASE;

export async function POST(req: NextRequest) {
  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ message: 'Invalid request body' }, { status: 400 });
  }

  let laravelRes: Response;
  try {
    laravelRes = await fetch(`${API_BASE}/auth/register`, {
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
    return NextResponse.json({ message: data.message ?? 'Registration failed' }, { status: laravelRes.status });
  }

  const token = data.data?.token ?? data.token;
  const user = data.data?.user ?? data.user;

  const res = NextResponse.json({ user }, { status: 201 });
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
