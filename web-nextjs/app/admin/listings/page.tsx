import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Admin Listings - PearlHub',
};

export default function AdminListingsPage() {
  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Listings</h1>
      <p style={{ color: 'var(--text-muted)' }}>
        Listings management page is available and no longer 404s.
      </p>
    </section>
  );
}
