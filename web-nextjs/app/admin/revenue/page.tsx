'use client';

import { useEffect, useState } from 'react';

type RevenueData = {
  summary?: {
    booking_gross_lkr?: number;
    transaction_incoming_lkr?: number;
    referral_cash_bonus_lkr?: number;
  };
  by_vertical?: Array<{ vertical: string; bookings: number; gross: number }>;
  daily?: Array<{ day: string; bookings: number; gross: number }>;
};

export default function AdminRevenuePage() {
  const [data, setData] = useState<RevenueData>({});
  const [loading, setLoading] = useState(true);
  const [days, setDays] = useState(30);
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await fetch(`/api/admin/revenue/incoming?days=${days}`, { credentials: 'same-origin' });
      if (!res.ok) throw new Error(`Failed to load revenue (${res.status})`);
      const json = (await res.json()) as { data?: RevenueData };
      setData(json.data ?? {});
    } catch (e) {
      setData({});
      setError(e instanceof Error ? e.message : 'Failed to load revenue');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [days]);

  return (
    <section className="admin-chart-section">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <h1 className="admin-page-title">Revenue Incoming</h1>
        <select className="admin-input" value={days} onChange={(e) => setDays(Number(e.target.value))} style={{ maxWidth: 160 }}>
          <option value={30}>Last 30 days</option>
          <option value={90}>Last 90 days</option>
          <option value={180}>Last 180 days</option>
          <option value={365}>Last 365 days</option>
        </select>
      </div>

      {error ? <p style={{ color: '#fca5a5' }}>{error}</p> : null}

      <div className="admin-stats-strip" style={{ marginTop: 12 }}>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Booking gross</div><div className="admin-stat-card__value admin-stat-card__value--emerald">LKR {Number(data.summary?.booking_gross_lkr ?? 0).toLocaleString()}</div></div>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Incoming tx</div><div className="admin-stat-card__value admin-stat-card__value--gold">LKR {Number(data.summary?.transaction_incoming_lkr ?? 0).toLocaleString()}</div></div>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Referral cash out</div><div className="admin-stat-card__value admin-stat-card__value--rose">LKR {Number(data.summary?.referral_cash_bonus_lkr ?? 0).toLocaleString()}</div></div>
      </div>

      <div className="admin-tables-row" style={{ marginTop: 16 }}>
        <div className="admin-table-card">
          <div className="admin-table-card__title">By vertical</div>
          <table className="admin-table">
            <thead><tr><th>Vertical</th><th>Bookings</th><th>Gross</th></tr></thead>
            <tbody>
              {(data.by_vertical ?? []).map((row) => (
                <tr key={row.vertical}><td>{row.vertical}</td><td>{row.bookings}</td><td>LKR {Number(row.gross).toLocaleString()}</td></tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-card__title">Daily trend</div>
          <table className="admin-table">
            <thead><tr><th>Day</th><th>Bookings</th><th>Gross</th></tr></thead>
            <tbody>
              {(data.daily ?? []).slice(-15).map((row) => (
                <tr key={row.day}><td>{row.day}</td><td>{row.bookings}</td><td>LKR {Number(row.gross).toLocaleString()}</td></tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {loading ? <p style={{ marginTop: 8 }}>Loading...</p> : null}
    </section>
  );
}
