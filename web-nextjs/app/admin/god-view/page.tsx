'use client';

import { useEffect, useState } from 'react';

type GodViewData = {
  stats?: Record<string, number>;
  breakdowns?: {
    users_by_role?: Record<string, number>;
    bookings_by_status?: Record<string, number>;
    listings_by_vertical?: Record<string, number>;
  };
  health?: { db_ok?: boolean; cache_ok?: boolean; queue_backlog?: number };
};

export default function AdminGodViewPage() {
  const [data, setData] = useState<GodViewData>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      setError('');
      try {
        const res = await fetch('/api/admin/god-view', { credentials: 'same-origin' });
        if (!res.ok) {
          throw new Error(`Failed to load god view (${res.status})`);
        }
        const json = (await res.json()) as { data?: GodViewData };
        setData(json.data ?? {});
      } catch (e) {
        setData({});
        setError(e instanceof Error ? e.message : 'Failed to load god view');
      } finally {
        setLoading(false);
      }
    };
    void load();
  }, []);

  const stats = data.stats ?? {};

  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">God View</h1>
      <p style={{ color: 'var(--text-muted)', marginBottom: 16 }}>
        Unified control surface across users, listings, bookings, payments, social, and platform health.
      </p>
      {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}

      <div className="admin-stats-strip" style={{ marginBottom: 16 }}>
        {Object.entries(stats).map(([key, value]) => (
          <div key={key} className="admin-stat-card">
            <div className="admin-stat-card__label">{key.replaceAll('_', ' ')}</div>
            <div className="admin-stat-card__value">{Number(value).toLocaleString()}</div>
          </div>
        ))}
      </div>

      <div className="admin-tables-row">
        <div className="admin-table-card">
          <div className="admin-table-card__title">Users by role</div>
          <table className="admin-table">
            <tbody>
              {Object.entries(data.breakdowns?.users_by_role ?? {}).map(([k, v]) => (
                <tr key={k}><td>{k}</td><td>{v}</td></tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-card__title">Listings by vertical</div>
          <table className="admin-table">
            <tbody>
              {Object.entries(data.breakdowns?.listings_by_vertical ?? {}).map(([k, v]) => (
                <tr key={k}><td>{k}</td><td>{v}</td></tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-card__title">System health</div>
          {loading ? (
            <p>Loading...</p>
          ) : (
            <table className="admin-table">
              <tbody>
                <tr><td>DB</td><td>{data.health?.db_ok ? 'OK' : 'Fail'}</td></tr>
                <tr><td>Cache</td><td>{data.health?.cache_ok ? 'OK' : 'Fail'}</td></tr>
                <tr><td>Queue backlog</td><td>{data.health?.queue_backlog ?? 0}</td></tr>
              </tbody>
            </table>
          )}
        </div>
      </div>
    </section>
  );
}
