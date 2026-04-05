import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Admin Bookings - PearlHub',
};

export default function AdminBookingsPage() {
  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Bookings</h1>
      <p style={{ color: 'var(--text-muted)' }}>
        Bookings review page is routed and ready for API integration.
      </p>
    </section>
  );
}
