import { NextResponse, NextRequest } from 'next/server';
import { SERVER_API_BASE } from '@/lib/env';

const API_BASE = SERVER_API_BASE;

function extractToken(req: NextRequest): string | null {
  const cookieHeader = req.headers.get('cookie') ?? '';
  const match = cookieHeader.match(/(?:^|;\s*)pearl_token=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export async function GET(req: NextRequest) {
  return proxyToBackend(req, 'GET');
}

export async function POST(req: NextRequest) {
  return proxyToBackend(req, 'POST');
}

export async function PATCH(req: NextRequest) {
  return proxyToBackend(req, 'PATCH');
}

async function proxyToBackend(req: NextRequest, method: string) {
  const token = extractToken(req);
  if (!token) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  // Strip /api/payments prefix to get the backend path
  const url = new URL(req.url);
  const backendPath = url.pathname.replace('/api/payments', '/payments');
  const backendUrl = `${API_BASE}${backendPath}${url.search}`;

  const headers: Record<string, string> = {
    Accept: 'application/json',
    Authorization: `Bearer ${token}`,
  };

  let body: string | undefined;
  if (method !== 'GET') {
    try {
      body = JSON.stringify(await req.json());
      headers['Content-Type'] = 'application/json';
    } catch {
      // No body
    }
  }

  try {
    const response = await fetch(backendUrl, { method, headers, body });
    const data = await response.json();
    return NextResponse.json(data, { status: response.status });
  } catch {
    return NextResponse.json({ error: 'Backend unavailable' }, { status: 502 });
  }
}
