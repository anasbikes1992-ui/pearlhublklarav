'use client';

import { useEffect, useState } from 'react';

type Listing = {
  id: string;
  title: string;
  vertical: string;
  status: string;
  is_hidden: boolean;
  price: number;
};

type Paginated<T> = { data: T[] };

export default function AdminListingsPage() {
  const [rows, setRows] = useState<Listing[]>([]);
  const [loading, setLoading] = useState(true);
  const [q, setQ] = useState('');
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const qs = q ? `?q=${encodeURIComponent(q)}` : '';
      const res = await fetch(`/api/admin/listings${qs}`, { credentials: 'same-origin' });
      if (!res.ok) {
        throw new Error(`Failed to load listings (${res.status})`);
      }
      const json = (await res.json()) as { data?: Paginated<Listing> };
      setRows(json.data?.data ?? []);
    } catch (e) {
      setRows([]);
      setError(e instanceof Error ? e.message : 'Failed to load listings');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const patchListing = async (id: string, payload: Partial<Listing>) => {
    const res = await fetch(`/api/admin/listings/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) {
      alert(`Failed to update listing (${res.status})`);
      return;
    }
    await load();
  };

  const deleteListing = async (id: string) => {
    if (!confirm('Delete this listing?')) return;
    const res = await fetch(`/api/admin/listings/${id}`, { method: 'DELETE', credentials: 'same-origin' });
    if (!res.ok) {
      alert(`Failed to delete listing (${res.status})`);
      return;
    }
    await load();
  };

  return (
    <section className="admin-chart-section">
      <div style={{ display: 'flex', gap: 8, alignItems: 'center', justifyContent: 'space-between' }}>
        <h1 className="admin-page-title">Listings CRUD</h1>
        <div style={{ display: 'flex', gap: 8 }}>
          <input
            className="admin-input"
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="Search title/slug"
          />
          <button className="market-btn market-btn--primary market-btn--sm" onClick={() => void load()}>
            Search
          </button>
        </div>
      </div>

      <div className="admin-table-card" style={{ marginTop: 16 }}>
        {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}
        <table className="admin-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Vertical</th>
              <th>Status</th>
              <th>Hidden</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={5}>Loading...</td></tr>
            ) : rows.length === 0 ? (
              <tr><td colSpan={5}>No listings found.</td></tr>
            ) : (
              rows.map((l) => (
                <tr key={l.id}>
                  <td>{l.title}</td>
                  <td>{l.vertical}</td>
                  <td>
                    <select
                      className="admin-input"
                      value={l.status}
                      onChange={(e) => void patchListing(l.id, { status: e.target.value })}
                    >
                      <option value="draft">draft</option>
                      <option value="published">published</option>
                      <option value="pending_verification">pending_verification</option>
                      <option value="rejected">rejected</option>
                      <option value="archived">archived</option>
                    </select>
                  </td>
                  <td>
                    <button
                      className="market-btn market-btn--ghost market-btn--sm"
                      onClick={() => void patchListing(l.id, { is_hidden: !l.is_hidden })}
                    >
                      {l.is_hidden ? 'Unhide' : 'Hide'}
                    </button>
                  </td>
                  <td>
                    <button className="market-btn market-btn--ghost market-btn--sm" onClick={() => void deleteListing(l.id)}>
                      Delete
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </section>
  );
}
