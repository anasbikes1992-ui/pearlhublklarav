import type { Metadata } from 'next';
import Link from 'next/link';
import { getListingBySlug } from '../../../lib/api';

export const revalidate = 300;

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  return { title: `${slug.replace(/-/g, ' ')} — PearlHub Events` };
}

export default async function EventDetailPage({ params }: Props) {
  const { slug } = await params;
  const event = await getListingBySlug(slug, 'event');
  const price = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(event.price);

  return (
    <main className="page-shell detail-page">
      <div className="detail-hero detail-hero--rose">
        <p className="eyebrow">{event.category}</p>
        <h1>{event.title}</h1>
        <div className="detail-meta-row">
          <span>{event.location}</span>
          {event.rating && <span>{event.rating.toFixed(1)} rated</span>}
          <span className="listing-card__badge">{event.badge}</span>
        </div>
      </div>

      <div className="detail-columns">
        <article className="detail-article">
          <h2>About this event</h2>
          <p>{event.description}</p>

          <h2>What&apos;s included</h2>
          <ul className="detail-list">
            <li>Reserved premium seating or viewing area</li>
            <li>Complimentary welcome drink and Pearl hospitality</li>
            <li>QR ticket delivered to your PearlHub Customer app</li>
            <li>Dedicated on-site Pearl concierge</li>
          </ul>

          <h2>Ticket terms</h2>
          <ul className="detail-list">
            <li>Seat holds expire after 10 minutes if unpaid (auto-release)</li>
            <li>Cancellation: Full refund up to 7 days before event</li>
            <li>Platform fee: 8% included in displayed price</li>
          </ul>
        </article>

        <aside className="detail-sidebar">
          <h2>
            {event.currency} {price}
            <small> / ticket</small>
          </h2>
          <p>{event.location}</p>
          <Link className="btn btn-primary btn-full" href="/auth/login">
            Reserve tickets
          </Link>
          <Link className="btn btn-secondary btn-full" href="/events">
            Browse all events
          </Link>
        </aside>
      </div>
    </main>
  );
}
