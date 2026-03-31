import { NextResponse } from 'next/server';

// Prefer the private server-side var; fall back to the public one for local dev.
const API_BASE = process.env.API_INTERNAL_URL ?? process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

export async function GET(req: Request) {
  const cookieHeader = req.headers.get('cookie') ?? '';
  const tokenMatch = cookieHeader.match(/(?:^|;\s*)pearl_token=([^;]+)/);
  const token = tokenMatch ? decodeURIComponent(tokenMatch[1]) : null;

  if (!token) {
    return NextResponse.json({ message: 'Unauthenticated' }, { status: 401 });
  }

  let laravelRes: Response;
  try {
    laravelRes = await fetch(`${API_BASE}/users/profile`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });
  } catch {
    return NextResponse.json({ message: 'Backend unreachable' }, { status: 503 });
  }

  if (!laravelRes.ok) {
    return NextResponse.json({ message: 'Unauthenticated' }, { status: laravelRes.status });
  }

  const payload = (await laravelRes.json()) as {
    data?: {
      id: string;
      full_name: string;
      email: string;
      role: string;
    };
  };

  const user = payload.data
    ? {
        ...payload.data,
        name: payload.data.full_name,
      }
    : null;

  return NextResponse.json({ user }, { status: 200 });
}
