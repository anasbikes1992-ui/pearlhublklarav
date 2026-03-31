import Link from 'next/link';
import { getListingBySlug } from '../../../lib/api';

export const revalidate = 300;

export default async function PropertyDetailPage({ params }: { params: { slug: string } }) {
  const listing = await getListingBySlug(params.slug, 'property');

  return (
    <main className="page-shell detail-page">
      <section className={`detail-hero detail-hero--${listing.accent}`}>
        <p className="eyebrow">Property spotlight</p>
        <h1>{listing.title}</h1>
        <p>{listing.description}</p>
        <div className="detail-meta-row">
          <span>{listing.location}</span>
          <span>{listing.category}</span>
          {listing.rating ? <span>{listing.rating.toFixed(1)} rating</span> : null}
        </div>
        <p className="detail-price">Price: {listing.currency} {new Intl.NumberFormat('en-LK').format(listing.price)}</p>
      </section>

      <section className="detail-columns">
        <article className="list-page">
          <h2>Why this listing feels premium</h2>
          <p>
            The reference PearlHub experience presents properties with stronger narrative context, clearer market positioning,
            and a more editorial listing rhythm. This page adopts that same direction for the Next.js frontend.
          </p>
          <ul className="stacked-list">
            <li>Location-led presentation rather than raw database output.</li>
            <li>Premium pricing emphasis and category framing.</li>
            <li>Ready to bind to deeper Laravel listing metadata as the API expands.</li>
          </ul>
        </article>
        <aside className="list-page detail-sidebar">
          <h2>Next actions</h2>
          <p>Use the catalog routes to keep exploring related inventory.</p>
          <div className="hero-cta-row">
            <Link className="btn btn-primary" href="/property">
              More properties
            </Link>
            <Link className="btn btn-secondary" href="/stays">
              Browse stays
            </Link>
          </div>
        </aside>
      </section>
    </main>
  );
}
