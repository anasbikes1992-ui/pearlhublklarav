'use client';

import { useEffect, useState } from 'react';

type SocialPost = {
  id: string;
  content: string;
  is_flagged: boolean;
  is_pinned: boolean;
  author?: { full_name?: string; email?: string };
};

type Paginated<T> = { data: T[] };

export default function AdminSocialPage() {
  const [rows, setRows] = useState<SocialPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await fetch('/api/admin/social/posts', { credentials: 'same-origin' });
      if (!res.ok) {
        throw new Error(`Failed to load social posts (${res.status})`);
      }
      const json = (await res.json()) as { data?: Paginated<SocialPost> };
      setRows(json.data?.data ?? []);
    } catch (e) {
      setRows([]);
      setError(e instanceof Error ? e.message : 'Failed to load social posts');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
  }, []);

  const patchPost = async (id: string, payload: Partial<SocialPost>) => {
    const res = await fetch(`/api/admin/social/posts/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) {
      alert(`Failed to update post (${res.status})`);
      return;
    }
    await load();
  };

  const deletePost = async (id: string) => {
    if (!confirm('Delete this post?')) return;
    const res = await fetch(`/api/admin/social/posts/${id}`, { method: 'DELETE', credentials: 'same-origin' });
    if (!res.ok) {
      alert(`Failed to delete post (${res.status})`);
      return;
    }
    await load();
  };

  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Social Moderation CRUD</h1>
      <div className="admin-table-card" style={{ marginTop: 16 }}>
        {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}
        <table className="admin-table">
          <thead>
            <tr>
              <th>Author</th>
              <th>Content</th>
              <th>Flags</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={4}>Loading...</td></tr>
            ) : rows.length === 0 ? (
              <tr><td colSpan={4}>No posts found.</td></tr>
            ) : (
              rows.map((p) => (
                <tr key={p.id}>
                  <td>{p.author?.full_name ?? p.author?.email ?? '—'}</td>
                  <td style={{ maxWidth: 380, whiteSpace: 'normal' }}>{p.content}</td>
                  <td>
                    <span className={`admin-badge admin-badge--${p.is_flagged ? 'rose' : 'active'}`}>
                      {p.is_flagged ? 'flagged' : 'clean'}
                    </span>
                  </td>
                  <td style={{ display: 'flex', gap: 8 }}>
                    <button className="market-btn market-btn--ghost market-btn--sm" onClick={() => void patchPost(p.id, { is_flagged: !p.is_flagged })}>
                      {p.is_flagged ? 'Unflag' : 'Flag'}
                    </button>
                    <button className="market-btn market-btn--ghost market-btn--sm" onClick={() => void patchPost(p.id, { is_pinned: !p.is_pinned })}>
                      {p.is_pinned ? 'Unpin' : 'Pin'}
                    </button>
                    <button className="market-btn market-btn--ghost market-btn--sm" onClick={() => void deletePost(p.id)}>
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
