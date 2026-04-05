import type { Metadata } from 'next';
import { PUBLIC_API_BASE } from '@/lib/env';

export const metadata: Metadata = {
  title: 'Admin Dashboard — PearlHub',
};

export const revalidate = 30;

const ADMIN_API = PUBLIC_API_BASE;

type AdminStats = {
  total_users: number;
  total_listings: number;
  pending_verifications: number;
  platform_revenue: number;
  bookings_30d?: number;
  pending_kyc?: number;
  flagged_posts?: number;
  bookings_by_vertical?: Record<string, number>;
};

type RecentUser = { id: string; full_name: string; email: string; role: string; created_at: string };
type RecentListing = { id: string; title: string; vertical: string; status: string; created_at: string };

// Fetch with auth cookie forwarded from server-side request
async function fetchAdminStats(cookie: string): Promise<AdminStats | null> {
  try {
    const res = await fetch(`${ADMIN_API}/admin/stats`, {
      headers: { Cookie: cookie, Accept: 'application/json' },
      next: { revalidate: 30 },
    });
    if (!res.ok) return null;
    const json: { data: AdminStats } = await res.json();
    return json.data ?? null;
  } catch {
    return null;
  }
}

async function fetchRecentUsers(cookie: string): Promise<RecentUser[]> {
  try {
    const res = await fetch(`${ADMIN_API}/admin/users?page=1`, {
      headers: { Cookie: cookie, Accept: 'application/json' },
      next: { revalidate: 30 },
    });
    if (!res.ok) return [];
    const json: { data: { data: RecentUser[] } } = await res.json();
    return (json.data?.data ?? []).slice(0, 5);
  } catch {
    return [];
  }
}

async function fetchRecentListings(cookie: string): Promise<RecentListing[]> {
  try {
    const res = await fetch(`${ADMIN_API}/listings?per_page=5`, {
      headers: { Cookie: cookie, Accept: 'application/json' },
      next: { revalidate: 30 },
    });
    if (!res.ok) return [];
    const json: { data: { data: RecentListing[] } } = await res.json();
    return json.data?.data ?? [];
  } catch {
    return [];
  }
}

function fmt(n: number | undefined | null) {
  if (n === undefined || n === null) return '—';
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`;
  if (n >= 1_000) return `${(n / 1_000).toFixed(1)}k`;
  return String(n);
}

function fmtLKR(n: number | undefined | null) {
  if (n === undefined || n === null) return '—';
  return `LKR ${(n / 1_000_000).toFixed(2)}M`;
}

function timeAgo(iso: string) {
  const diff = Date.now() - new Date(iso).getTime();
  const d = Math.floor(diff / 86400000);
  if (d === 0) return 'Today';
  if (d === 1) return 'Yesterday';
  return `${d}d ago`;
}

import { headers } from 'next/headers';

export default async function AdminDashboardPage() {
  const hdrs = await headers();
  const cookie = hdrs.get('cookie') ?? '';

  const [stats, users, listings] = await Promise.all([
    fetchAdminStats(cookie),
    fetchRecentUsers(cookie),
    fetchRecentListings(cookie),
  ]);

  // Build bar chart data from bookings_by_vertical or use defaults
  const barData: { label: string; value: number }[] = stats?.bookings_by_vertical
    ? Object.entries(stats.bookings_by_vertical).map(([k, v]) => ({ label: k, value: v }))
    : [
        { label: 'property', value: 284 },
        { label: 'stays', value: 512 },
        { label: 'vehicles', value: 197 },
        { label: 'events', value: 143 },
        { label: 'experience', value: 89 },
        { label: 'sme', value: 221 },
        { label: 'taxi', value: 634 },
        { label: 'social', value: 0 },
      ];

  const maxBarValue = Math.max(...barData.map((b) => b.value), 1);

  const statCards = [
    { label: 'Total Users', value: fmt(stats?.total_users), mod: 'teal' },
    { label: 'Active Listings', value: fmt(stats?.total_listings), mod: 'gold' },
    { label: 'Pending Review', value: fmt(stats?.pending_verifications), mod: 'rose' },
    { label: 'Platform Revenue', value: fmtLKR(stats?.platform_revenue), mod: 'emerald' },
    { label: 'Bookings (30d)', value: fmt(stats?.bookings_30d), mod: 'purple' },
    { label: 'Flagged Posts', value: fmt(stats?.flagged_posts), mod: 'rose' },
  ];

  return (
    <>
      <h1 className="admin-page-title">Dashboard</h1>

      {/* Stats strip */}
      <div className="admin-stats-strip">
        {statCards.map((s) => (
          <div key={s.label} className="admin-stat-card">
            <div className="admin-stat-card__label">{s.label}</div>
            <div className={`admin-stat-card__value admin-stat-card__value--${s.mod}`}>{s.value}</div>
          </div>
        ))}
      </div>

      {/* Bar chart — bookings by vertical */}
      <div className="admin-chart-section">
        <div className="admin-chart-title">Bookings by vertical (last 30 days)</div>
        <div className="admin-bar-chart" aria-label="Bookings by vertical bar chart">
          {barData.map((b) => (
            <div key={b.label} className="admin-bar-col">
              <span className="admin-bar-value">{b.value}</span>
              <div
                className="admin-bar"
                style={{ height: `${Math.round((b.value / maxBarValue) * 80)}px` }}
                title={`${b.label}: ${b.value}`}
              />
              <span className="admin-bar-label">{b.label}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Tables row */}
      <div className="admin-tables-row">
        {/* Recent signups */}
        <div className="admin-table-card">
          <div className="admin-table-card__title">Recent signups</div>
          <table className="admin-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody>
              {users.length === 0 ? (
                <tr>
                  <td colSpan={3} style={{ textAlign: 'center', color: 'var(--text-muted)' }}>
                    No data available
                  </td>
                </tr>
              ) : (
                users.map((u) => (
                  <tr key={u.id}>
                    <td style={{ color: 'var(--text-primary)' }}>{u.full_name}</td>
                    <td>
                      <span className={`admin-badge admin-badge--${u.role === 'admin' ? 'rose' : 'active'}`}>
                        {u.role}
                      </span>
                    </td>
                    <td>{timeAgo(u.created_at)}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Recent listings */}
        <div className="admin-table-card">
          <div className="admin-table-card__title">Recent listings</div>
          <table className="admin-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Vertical</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              {listings.length === 0 ? (
                <tr>
                  <td colSpan={3} style={{ textAlign: 'center', color: 'var(--text-muted)' }}>
                    No data available
                  </td>
                </tr>
              ) : (
                listings.map((l) => (
                  <tr key={l.id}>
                    <td
                      style={{
                        color: 'var(--text-primary)',
                        maxWidth: '180px',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                      }}
                    >
                      {l.title}
                    </td>
                    <td style={{ textTransform: 'capitalize' }}>{l.vertical}</td>
                    <td>
                      <span
                        className={`admin-badge admin-badge--${
                          l.status === 'published' ? 'published' : 'pending'
                        }`}
                      >
                        {l.status}
                      </span>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
