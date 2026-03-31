import type { Metadata } from 'next';
import Link from 'next/link';
import { getListingBySlug } from '../../../lib/api';

export const revalidate = 300;

type Props = { params: { slug: string } };

export function generateMetadata({ params }: Props): Metadata {
  return { title: `${params.slug.replace(/-/g, ' ')} — PearlHub Vehicles` };
}

export default async function VehicleDetailPage({ params }: Props) {
  const vehicle = await getListingBySlug(params.slug, 'vehicle');
  const price = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(vehicle.price);

  return (
    <main className="page-shell detail-page">
      <div className={`detail-hero detail-hero--amber`}>
        <p className="eyebrow">{vehicle.category}</p>
        <h1>{vehicle.title}</h1>
        <div className="detail-meta-row">
          <span>{vehicle.location}</span>
          {vehicle.rating && <span>{vehicle.rating.toFixed(1)} rating</span>}
          <span className="listing-card__badge">{vehicle.badge}</span>
        </div>
      </div>

      <div className="detail-columns">
        <article className="detail-article">
          <h2>About this vehicle</h2>
          <p>{vehicle.description}</p>

          <h2>What&apos;s included</h2>
          <ul className="detail-list">
            <li>Pearl-verified driver or self-drive option</li>
            <li>Comprehensive insurance cover</li>
            <li>Fuel for agreed route (with-driver bookings)</li>
            <li>24/7 PearlHub roadside support</li>
          </ul>

          <h2>Booking terms</h2>
          <ul className="detail-list">
            <li>Minimum booking: 1 full day</li>
            <li>Cancellation: Free up to 48 hours before pickup</li>
            <li>Platform commission: 8% included in displayed rate</li>
          </ul>
        </article>

        <aside className="detail-sidebar">
          <h2>
            {vehicle.currency} {price}
            <small> / day</small>
          </h2>
          <p>{vehicle.location}</p>
          <Link className="btn btn-primary btn-full" href="/auth/login">
            Request booking
          </Link>
          <Link className="btn btn-secondary btn-full" href="/vehicles">
            Browse all vehicles
          </Link>
        </aside>
      </div>
    </main>
  );
}
