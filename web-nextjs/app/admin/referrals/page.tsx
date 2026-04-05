'use client';

import { FormEvent, useEffect, useState } from 'react';

type ReferralSummary = {
  totals?: {
    referrals?: number;
    points_awarded?: number;
    cash_awarded_lkr?: number;
    users_with_points?: number;
  };
  top_referrers?: Array<{ referrer_id: string; full_name: string; email: string; referrals: number; points_awarded: number; cash_awarded_lkr: number }>;
};

export default function AdminReferralsPage() {
  const [summary, setSummary] = useState<ReferralSummary>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [saving, setSaving] = useState(false);

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await fetch('/api/admin/referrals/summary', { credentials: 'same-origin' });
      if (!res.ok) throw new Error(`Failed to load referral summary (${res.status})`);
      const json = (await res.json()) as { data?: ReferralSummary };
      setSummary(json.data ?? {});
    } catch (e) {
      setSummary({});
      setError(e instanceof Error ? e.message : 'Failed to load referral summary');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
  }, []);

  const onGrant = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    const userId = (form.elements.namedItem('user_id') as HTMLInputElement).value;
    const points = Number((form.elements.namedItem('points') as HTMLInputElement).value || '0');
    const cash = Number((form.elements.namedItem('cash_bonus_lkr') as HTMLInputElement).value || '0');
    const note = (form.elements.namedItem('note') as HTMLInputElement).value;

    setSaving(true);
    try {
      const res = await fetch('/api/admin/referrals/grant-bonus', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, points, cash_bonus_lkr: cash, note }),
      });
      if (!res.ok) {
        const body = (await res.json().catch(() => ({}))) as { message?: string };
        throw new Error(body.message ?? `Failed to grant bonus (${res.status})`);
      }
      form.reset();
      await load();
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Failed to grant bonus');
    } finally {
      setSaving(false);
    }
  };

  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Referral Bonuses</h1>
      {error ? <p style={{ color: '#fca5a5' }}>{error}</p> : null}

      <div className="admin-stats-strip" style={{ marginTop: 12 }}>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Total referrals</div><div className="admin-stat-card__value">{summary.totals?.referrals ?? 0}</div></div>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Points awarded</div><div className="admin-stat-card__value">{summary.totals?.points_awarded ?? 0}</div></div>
        <div className="admin-stat-card"><div className="admin-stat-card__label">Cash awarded</div><div className="admin-stat-card__value">LKR {Number(summary.totals?.cash_awarded_lkr ?? 0).toLocaleString()}</div></div>
      </div>

      <div className="admin-table-card" style={{ marginTop: 16 }}>
        <div className="admin-table-card__title">Grant manual referral bonus</div>
        <form onSubmit={(e) => void onGrant(e)} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 2fr auto', gap: 8 }}>
          <input className="admin-input" name="user_id" placeholder="Target user UUID" required />
          <input className="admin-input" name="points" type="number" min={0} defaultValue={0} />
          <input className="admin-input" name="cash_bonus_lkr" type="number" min={0} step="0.01" defaultValue={0} />
          <input className="admin-input" name="note" placeholder="Reason / note" />
          <button className="market-btn market-btn--primary market-btn--sm" type="submit" disabled={saving}>{saving ? 'Saving...' : 'Grant'}</button>
        </form>
      </div>

      <div className="admin-table-card" style={{ marginTop: 16 }}>
        <div className="admin-table-card__title">Top referrers</div>
        <table className="admin-table">
          <thead><tr><th>Name</th><th>Email</th><th>Referrals</th><th>Points</th><th>Cash</th></tr></thead>
          <tbody>
            {(summary.top_referrers ?? []).map((r) => (
              <tr key={r.referrer_id}><td>{r.full_name}</td><td>{r.email}</td><td>{r.referrals}</td><td>{r.points_awarded}</td><td>LKR {Number(r.cash_awarded_lkr).toLocaleString()}</td></tr>
            ))}
          </tbody>
        </table>
      </div>

      {loading ? <p style={{ marginTop: 8 }}>Loading...</p> : null}
    </section>
  );
}
