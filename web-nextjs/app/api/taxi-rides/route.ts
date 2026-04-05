import { NextRequest, NextResponse } from 'next/server';
import { SERVER_API_BASE } from '@/lib/env';
import { DEMO_DRIVERS } from '@/lib/taxi-demo-data';

function extractToken(req: NextRequest): string | null {
  const cookieHeader = req.headers.get('cookie') ?? '';
  const match = cookieHeader.match(/(?:^|;\s*)pearl_token=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export async function GET(req: NextRequest) {
  const token = extractToken(req);
  if (!token) return NextResponse.json({ message: 'Unauthenticated' }, { status: 401 });

  if (token.startsWith('demo_')) {
    return NextResponse.json({ data: [] });
  }

  try {
    const laravelRes = await fetch(`${SERVER_API_BASE}/taxi-rides`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = (await laravelRes.json()) as unknown;
    return NextResponse.json(data, { status: laravelRes.status });
  } catch {
    return NextResponse.json({ data: [] });
  }
}

export async function POST(req: NextRequest) {
  const token = extractToken(req);
  if (!token) return NextResponse.json({ message: 'Unauthenticated' }, { status: 401 });

  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ message: 'Invalid request body' }, { status: 400 });
  }

  if (token.startsWith('demo_')) {
    const b = body as Record<string, unknown>;

    // Server-side enforcement: pickup and dropoff must differ
    if (
      typeof b.pickup_city === 'string' &&
      typeof b.dropoff_city === 'string' &&
      b.pickup_city === b.dropoff_city
    ) {
      return NextResponse.json(
        { message: 'Pickup and dropoff cities must be different' },
        { status: 422 }
      );
    }

    const driverId = typeof b.driver_id === 'string' ? b.driver_id : 'd1';
    const driver = DEMO_DRIVERS.find((d) => d.id === driverId) ?? DEMO_DRIVERS[0];
    const ride = {
      id: `demo-ride-${Date.now()}`,
      status: 'searching',
      pickup_city: typeof b.pickup_city === 'string' ? b.pickup_city : 'Colombo',
      dropoff_city: typeof b.dropoff_city === 'string' ? b.dropoff_city : 'Kandy',
      pickup_latitude: typeof b.pickup_latitude === 'number' ? b.pickup_latitude : 6.9271,
      pickup_longitude: typeof b.pickup_longitude === 'number' ? b.pickup_longitude : 79.8612,
      dropoff_latitude: typeof b.dropoff_latitude === 'number' ? b.dropoff_latitude : 7.2906,
      dropoff_longitude: typeof b.dropoff_longitude === 'number' ? b.dropoff_longitude : 80.6337,
      fare_estimate: typeof b.fare_estimate === 'number' ? b.fare_estimate : 2800,
      driver,
      created_at: new Date().toISOString(),
    };
    return NextResponse.json({ data: ride }, { status: 201 });
  }

  try {
    const laravelRes = await fetch(`${SERVER_API_BASE}/taxi-rides`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify(body),
    });
    const data = (await laravelRes.json()) as unknown;
    return NextResponse.json(data, { status: laravelRes.status });
  } catch {
    return NextResponse.json({ message: 'Backend unreachable' }, { status: 503 });
  }
}

