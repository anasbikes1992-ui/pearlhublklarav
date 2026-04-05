import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Admin Users - PearlHub',
};

export default function AdminUsersPage() {
  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Users</h1>
      <p style={{ color: 'var(--text-muted)' }}>
        User management panel is now routed correctly. Data table wiring can be extended from
        the backend users endpoint.
      </p>
    </section>
  );
}
