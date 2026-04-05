'use client';

import { useEffect, useState } from 'react';

type AdminUser = {
  id: string;
  full_name: string;
  email: string;
  role: 'admin' | 'provider' | 'customer' | 'driver';
  is_active: boolean;
  created_at: string;
};

type Paginated<T> = { data: T[]; current_page: number; last_page: number };

export default function AdminUsersPage() {
  const [rows, setRows] = useState<AdminUser[]>([]);
  const [loading, setLoading] = useState(true);
  const [roleFilter, setRoleFilter] = useState('');
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const qs = roleFilter ? `?role=${encodeURIComponent(roleFilter)}` : '';
      const res = await fetch(`/api/admin/users${qs}`, { credentials: 'same-origin' });
      if (!res.ok) {
        throw new Error(`Failed to load users (${res.status})`);
      }
      const json = (await res.json()) as { data?: Paginated<AdminUser> };
      setRows(json.data?.data ?? []);
    } catch (e) {
      setRows([]);
      setError(e instanceof Error ? e.message : 'Failed to load users');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [roleFilter]);

  const patchUser = async (id: string, payload: Partial<Pick<AdminUser, 'is_active' | 'role'>>) => {
    const res = await fetch(`/api/admin/users/${id}`, {
      method: 'PUT',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) {
      const body = (await res.json().catch(() => ({}))) as { message?: string };
      alert(body.message ?? `Failed to update user (${res.status})`);
      return;
    }
    await load();
  };

  return (
    <section className="admin-chart-section">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12 }}>
        <h1 className="admin-page-title">Users CRUD</h1>
        <select
          className="admin-input"
          value={roleFilter}
          onChange={(e) => setRoleFilter(e.target.value)}
          style={{ maxWidth: 180 }}
        >
          <option value="">All roles</option>
          <option value="admin">admin</option>
          <option value="provider">provider</option>
          <option value="customer">customer</option>
          <option value="driver">driver</option>
        </select>
      </div>

      <div className="admin-table-card" style={{ marginTop: 16 }}>
        {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}
        <table className="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={5}>Loading...</td></tr>
            ) : rows.length === 0 ? (
              <tr><td colSpan={5}>No users found.</td></tr>
            ) : (
              rows.map((u) => (
                <tr key={u.id}>
                  <td>{u.full_name}</td>
                  <td>{u.email}</td>
                  <td>
                    <select
                      className="admin-input"
                      value={u.role}
                      onChange={(e) => void patchUser(u.id, { role: e.target.value as AdminUser['role'] })}
                    >
                      <option value="admin">admin</option>
                      <option value="provider">provider</option>
                      <option value="customer">customer</option>
                      <option value="driver">driver</option>
                    </select>
                  </td>
                  <td>
                    <span className={`admin-badge admin-badge--${u.is_active ? 'active' : 'pending'}`}>
                      {u.is_active ? 'active' : 'inactive'}
                    </span>
                  </td>
                  <td>
                    <button
                      className="market-btn market-btn--ghost market-btn--sm"
                      onClick={() => void patchUser(u.id, { is_active: !u.is_active })}
                    >
                      {u.is_active ? 'Deactivate' : 'Activate'}
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
