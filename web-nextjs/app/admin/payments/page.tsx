'use client';

import { useEffect, useState } from 'react';

type Tx = {
  id: string;
  provider: string;
  external_reference: string;
  amount: number;
  currency: string;
  status: string;
  wallet?: { user?: { full_name?: string; email?: string } };
};

type Summary = {
  successful_amount: number;
  failed_amount: number;
  pending_amount: number;
};

type PaymentsPayload = {
  transactions?: { data?: Tx[] };
  summary?: Summary;
};

export default function AdminPaymentsPage() {
  const [rows, setRows] = useState<Tx[]>([]);
  const [summary, setSummary] = useState<Summary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await fetch('/api/admin/payments', { credentials: 'same-origin' });
      if (!res.ok) {
        throw new Error(`Failed to load payments (${res.status})`);
      }
      const json = (await res.json()) as { data?: PaymentsPayload };
      setRows(json.data?.transactions?.data ?? []);
      setSummary(json.data?.summary ?? null);
    } catch (e) {
      setRows([]);
      setSummary(null);
      setError(e instanceof Error ? e.message : 'Failed to load payments');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
  }, []);

  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Payments View</h1>

      <div className="admin-stats-strip" style={{ marginBottom: 16 }}>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Successful</div><div className="admin-stat-card__value admin-stat-card__value--emerald">LKR {summary?.successful_amount?.toLocaleString() ?? '0'}</div></div>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Pending</div><div className="admin-stat-card__value admin-stat-card__value--gold">LKR {summary?.pending_amount?.toLocaleString() ?? '0'}</div></div>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Failed</div><div className="admin-stat-card__value admin-stat-card__value--rose">LKR {summary?.failed_amount?.toLocaleString() ?? '0'}</div></div>
      </div>

      <div className="admin-table-card">
        {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}
        <table className="admin-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Provider</th>
              <th>Reference</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={5}>Loading...</td></tr>
            ) : rows.length === 0 ? (
              <tr><td colSpan={5}>No transactions found.</td></tr>
            ) : (
              rows.map((t) => (
                <tr key={t.id}>
                  <td>{t.wallet?.user?.full_name ?? t.wallet?.user?.email ?? '—'}</td>
                  <td>{t.provider}</td>
                  <td>{t.external_reference}</td>
                  <td>{t.currency} {Number(t.amount).toLocaleString()}</td>
                  <td><span className={`admin-badge admin-badge--${t.status === 'succeeded' ? 'published' : 'pending'}`}>{t.status}</span></td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </section>
  );
}
