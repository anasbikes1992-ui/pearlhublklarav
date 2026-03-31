import type { Metadata } from 'next';
import Link from 'next/link';
import { getListingBySlug } from '../../../lib/api';

export const revalidate = 300;

type Props = { params: { slug: string } };

export function generateMetadata({ params }: Props): Metadata {
  const name = params.slug.replace(/-/g, ' ');
  return { title: `${name} — PearlHub Experiences` };
}

export default async function ExperienceDetailPage({ params }: Props) {
  const experience = await getListingBySlug(params.slug, 'experience');
  const price = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(experience.price);

  return (
    <main className="page-shell detail-page">
      <div className="detail-hero detail-hero--teal">
        <p className="eyebrow">{experience.category}</p>
        <h1>{experience.title}</h1>
        <div className="detail-meta-row">
          <span>{experience.location}</span>
          {experience.rating && <span>{experience.rating.toFixed(1)} rated</span>}
          <span className="listing-card__badge">{experience.badge}</span>
        </div>
        <p className="detail-price">From {experience.currency} {price} / person</p>
      </div>

      <div className="detail-columns">
        <article className="detail-article">
          <h2>About this experience</h2>
          <p>{experience.description}</p>

          <h2>What&apos;s included</h2>
          <ul className="detail-list">
            <li>Pearl-certified guide or operator with verified credentials</li>
            <li>All equipment, safety gear, and local transport</li>
            <li>Confirmation via PearlHub Customer app — no paper tickets</li>
            <li>24-hour Pearl concierge support on WhatsApp</li>
          </ul>

          <h2>Booking terms</h2>
          <ul className="detail-list">
            <li>Free cancellation up to 48 hours before the experience</li>
            <li>Small-group sizes — maximum 8 guests per booking</li>
            <li>Platform fee: 6% + 2% tourism levy included in price</li>
            <li>Escrow hold released to operator after completion</li>
          </ul>
        </article>

        <aside className="detail-sidebar">
          <h2>
            {experience.currency} {price}
            <small> / person</small>
          </h2>
          <p>{experience.location}</p>
          <Link className="btn btn-primary btn-full" href="/auth/login">
            Book experience
          </Link>
          <Link className="btn btn-secondary btn-full" href="/experiences">
            Browse all experiences
          </Link>
        </aside>
      </div>
    </main>
  );
}
