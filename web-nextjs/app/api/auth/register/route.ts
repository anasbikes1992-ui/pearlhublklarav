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
  let backendUnavailable = false;
  try {
    laravelRes = await fetch(`${API_BASE}/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    });
  } catch {
    backendUnavailable = true;
    laravelRes = new Response(null, { status: 503 });
  }

  if (laravelRes.status >= 500) {
    backendUnavailable = true;
  }

  // Demo fallback — triggers when the real API is unreachable or failing with 5xx.
  if (backendUnavailable) {
    const { full_name, email, password } = body as { full_name?: string; email?: string; password?: string };
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email) || !password || password.length < 8) {
      return NextResponse.json(
        { message: 'Valid email and password (min 8 characters) are required' },
        { status: 422 }
      );
    }
    const displayName = full_name?.trim() || 'Customer';
    // Encode "email|name" so the /api/auth/me route can recover both fields
    const payload = `${email}|${displayName}`;
    const token = `demo_customer_${Buffer.from(payload).toString('base64')}`;
    const user = { id: `demo-${email}`, full_name: displayName, name: displayName, email, role: 'customer' };
    const response = NextResponse.json({ user }, { status: 201 });
    response.cookies.set('pearl_token', token, {
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production',
      maxAge: 60 * 60 * 24 * 30,
      path: '/',
    });
    return response;
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
