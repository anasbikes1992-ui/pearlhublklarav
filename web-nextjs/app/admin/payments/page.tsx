import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Admin Payments - PearlHub',
};

export default function AdminPaymentsPage() {
  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Payments</h1>
      <p style={{ color: 'var(--text-muted)' }}>
        Payments oversight page is routed and ready for settlement and transaction views.
      </p>
    </section>
  );
}
