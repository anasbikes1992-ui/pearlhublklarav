import { NextResponse } from 'next/server';
import { SERVER_API_BASE } from '@/lib/env';

const API_BASE = SERVER_API_BASE;

const DEMO_ROLES: Record<string, string> = { admin: 'admin', provider: 'provider', customer: 'customer' };

export async function GET(req: Request) {
  const cookieHeader = req.headers.get('cookie') ?? '';
  const tokenMatch = cookieHeader.match(/(?:^|;\s*)pearl_token=([^;]+)/);
  const token = tokenMatch ? decodeURIComponent(tokenMatch[1]) : null;

  if (!token) {
    return NextResponse.json({ message: 'Unauthenticated' }, { status: 401 });
  }

  // Resolve demo tokens without hitting the backend
  if (token.startsWith('demo_')) {
    try {
      const parts = token.split('_');
      const role = parts[1];
      const emailB64 = parts.slice(2).join('_');
      const decoded = Buffer.from(emailB64, 'base64').toString('utf8');
      // Support both legacy "email" tokens and newer "email|Full Name" tokens (from demo registration).
      const pipeIdx = decoded.indexOf('|');
      const email = pipeIdx > -1 ? decoded.slice(0, pipeIdx) : decoded;
      const nameFromToken = pipeIdx > -1 ? decoded.slice(pipeIdx + 1) : null;
      const nameMap: Record<string, string> = {
        'anasbikes1992@gmail.com': 'Anas Admin',
      };
      if (DEMO_ROLES[role] && email.includes('@')) {
        const resolvedName =
          nameFromToken ?? nameMap[email] ?? `${role.charAt(0).toUpperCase()}${role.slice(1)} User`;
        const user = {
          id: `demo-${role}`,
          full_name: resolvedName,
          name: resolvedName,
          email,
          role,
        };
        return NextResponse.json({ user }, { status: 200 });
      }
    } catch {
      // Fall through to unauthenticated response if token decoding fails
    }
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
