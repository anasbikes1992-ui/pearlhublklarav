import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Admin Social - PearlHub',
};

export default function AdminSocialPage() {
  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Social</h1>
      <p style={{ color: 'var(--text-muted)' }}>
        Social moderation page is now online and no longer returns 404.
      </p>
    </section>
  );
}
