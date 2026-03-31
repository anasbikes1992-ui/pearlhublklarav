import type { Metadata } from 'next';
import Link from 'next/link';
import { getListingBySlug } from '../../../lib/api';

export const revalidate = 300;

type Props = { params: { slug: string } };

export function generateMetadata({ params }: Props): Metadata {
  const name = params.slug.replace(/-/g, ' ');
  return { title: `${name} — PearlHub SME` };
}

export default async function SMEDetailPage({ params }: Props) {
  const business = await getListingBySlug(params.slug, 'sme');
  const price = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(business.price);

  return (
    <main className="page-shell detail-page">
      <div className="detail-hero detail-hero--gold">
        <p className="eyebrow">{business.category}</p>
        <h1>{business.title}</h1>
        <div className="detail-meta-row">
          <span>{business.location}</span>
          {business.rating && <span>{business.rating.toFixed(1)} rated</span>}
          <span className="listing-card__badge">{business.badge}</span>
        </div>
        <p className="detail-price">{business.currency} {price}</p>
      </div>

      <div className="detail-columns">
        <article className="detail-article">
          <h2>About this business</h2>
          <p>{business.description}</p>

          <h2>What&apos;s offered</h2>
          <ul className="detail-list">
            <li>Pearl-verified local business with reviewed credentials</li>
            <li>Nationwide shipping or local delivery available</li>
            <li>Secure payment with PearlHub escrow protection</li>
            <li>Direct messaging with the business owner</li>
          </ul>

          <h2>Order &amp; delivery</h2>
          <ul className="detail-list">
            <li>Order confirmation sent via PearlHub app and email</li>
            <li>3% platform commission included in displayed price</li>
            <li>Dispute resolution via Pearl concierge team</li>
          </ul>
        </article>

        <aside className="detail-sidebar">
          <h2>
            {business.currency} {price}
          </h2>
          <p>{business.location}</p>
          <Link className="btn btn-primary btn-full" href="/auth/login">
            Contact business
          </Link>
          <Link className="btn btn-secondary btn-full" href="/sme">
            Browse local businesses
          </Link>
        </aside>
      </div>
    </main>
  );
}
