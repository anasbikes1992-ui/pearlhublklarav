import type { Metadata } from 'next';
import Link from 'next/link';
import { getListingBySlug } from '../../../lib/api';
import SmeProductGrid from '../../../components/sme-product-grid';
import VoiceChatRecorder from '../../../components/voice-chat-recorder';

export const revalidate = 300;

type Props = { params: { slug: string } };

export function generateMetadata({ params }: Props): Metadata {
  const name = params.slug.replace(/-/g, ' ');
  return { title: `${name} — PearlHub SME` };
}

export default async function SMEDetailPage({ params }: Props) {
  const business = await getListingBySlug(params.slug, 'sme');
  const providerId = (business as { provider_id?: string }).provider_id;
  const price = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(business.price);
  const similarLinks = ['/sme', '/search?q=artisan&vertical=sme', '/search?q=local&vertical=sme'];

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
            <li>Direct messaging with the business owner before purchase</li>
            <li>Variant support for size, color, and custom bundles</li>
          </ul>

          <h2>Finder flow (no platform booking)</h2>
          <ul className="detail-list">
            <li>Browse catalog and compare variants instantly</li>
            <li>Send text or voice inquiry directly to provider chat</li>
            <li>Complete payment off-platform with provider preferred method</li>
          </ul>

          <SmeProductGrid listing={business} />

          <section className="trust-badges">
            <h2>Trust badges</h2>
            <div className="trust-badges__row">
              <span>Verified Seller</span>
              <span>Tax Profile Validated</span>
              <span>Fast Reply</span>
              <span>Top Rated</span>
            </div>
          </section>

          <section className="similar-listings">
            <h2>Similar listings</h2>
            <div className="similar-listings__row">
              {similarLinks.map((href) => (
                <Link key={href} href={href} className="btn btn-secondary">Explore {href}</Link>
              ))}
            </div>
          </section>
        </article>

        <aside className="detail-sidebar">
          <h2>
            {business.currency} {price}
          </h2>
          <p>{business.location}</p>
          <Link className="btn btn-primary btn-full" href="/auth/login">
            Contact business
          </Link>
          <button className="btn btn-secondary btn-full" type="button">
            Add to favorites
          </button>
          <VoiceChatRecorder listingId={business.id} receiverId={providerId} />
          <Link className="btn btn-secondary btn-full" href="/sme">
            Browse local businesses
          </Link>

          <div className="provider-earnings-card">
            <h3>Provider earnings snapshot</h3>
            <p>Monthly self-reported sales: LKR 1,250,000</p>
            <p>Plan: Gold</p>
            <p>Products active: 142 / 500</p>
          </div>
        </aside>
      </div>
    </main>
  );
}
