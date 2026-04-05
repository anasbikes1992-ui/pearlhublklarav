import { NextResponse } from 'next/server';
import { SERVER_API_BASE } from '@/lib/env';

const API_BASE = SERVER_API_BASE;

export async function POST(req: Request) {
  const cookieHeader = req.headers.get('cookie') ?? '';
  const tokenMatch = cookieHeader.match(/(?:^|;\s*)pearl_token=([^;]+)/);
  const token = tokenMatch ? decodeURIComponent(tokenMatch[1]) : null;

  if (token) {
    await fetch(`${API_BASE}/auth/logout`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    }).catch(() => {
      // Clear cookie even if backend is unavailable.
    });
  }

  const res = NextResponse.json({ message: 'Logged out' }, { status: 200 });
  res.cookies.set('pearl_token', '', {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    maxAge: 0,
    path: '/',
  });
  return res;
}
