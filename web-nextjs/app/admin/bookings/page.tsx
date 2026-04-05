'use client';

import { useEffect, useState } from 'react';

type Booking = {
  id: string;
  status: string;
  payment_status: string;
  total_amount: number;
  currency: string;
  listing?: { title?: string };
  customer?: { full_name?: string; email?: string };
};

type Paginated<T> = { data: T[] };

export default function AdminBookingsPage() {
  const [rows, setRows] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await fetch('/api/admin/bookings', { credentials: 'same-origin' });
      if (!res.ok) {
        throw new Error(`Failed to load bookings (${res.status})`);
      }
      const json = (await res.json()) as { data?: Paginated<Booking> };
      setRows(json.data?.data ?? []);
    } catch (e) {
      setRows([]);
      setError(e instanceof Error ? e.message : 'Failed to load bookings');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
  }, []);

  const patchBooking = async (id: string, payload: Partial<Booking>) => {
    const res = await fetch(`/api/admin/bookings/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) {
      alert(`Failed to update booking (${res.status})`);
      return;
    }
    await load();
  };

  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Bookings CRUD</h1>
      <div className="admin-table-card" style={{ marginTop: 16 }}>
        {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}
        <table className="admin-table">
          <thead>
            <tr>
              <th>Listing</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Payment</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={5}>Loading...</td></tr>
            ) : rows.length === 0 ? (
              <tr><td colSpan={5}>No bookings found.</td></tr>
            ) : (
              rows.map((b) => (
                <tr key={b.id}>
                  <td>{b.listing?.title ?? '—'}</td>
                  <td>{b.customer?.full_name ?? b.customer?.email ?? '—'}</td>
                  <td>{b.currency} {Number(b.total_amount).toLocaleString()}</td>
                  <td>
                    <select
                      className="admin-input"
                      value={b.status}
                      onChange={(e) => void patchBooking(b.id, { status: e.target.value })}
                    >
                      <option value="pending">pending</option>
                      <option value="confirmed">confirmed</option>
                      <option value="completed">completed</option>
                      <option value="cancelled">cancelled</option>
                      <option value="refunded">refunded</option>
                    </select>
                  </td>
                  <td>
                    <select
                      className="admin-input"
                      value={b.payment_status}
                      onChange={(e) => void patchBooking(b.id, { payment_status: e.target.value })}
                    >
                      <option value="pending">pending</option>
                      <option value="authorized">authorized</option>
                      <option value="paid">paid</option>
                      <option value="failed">failed</option>
                      <option value="refunded">refunded</option>
                    </select>
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
