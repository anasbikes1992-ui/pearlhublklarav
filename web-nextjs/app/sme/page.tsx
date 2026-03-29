import ListingCard from '../../components/listing-card';
import { getFeaturedListings } from '../../lib/api';

export const revalidate = 300;

export default async function SMEPage() {
  const businesses = await getFeaturedListings('sme', 6);

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--sme">
        <p className="eyebrow">SME Marketplace</p>
        <h1>Sri Lanka&apos;s finest artisan producers, local crafts, and boutique businesses.</h1>
        <p>
          Discover Pearl-verified small businesses — from estate-direct tea producers and spice traders to craft studios and
          bespoke jewellers. Ship nationwide or gift-wrap for guests.
        </p>
      </section>

      <section className="filter-row" aria-label="SME filters">
        <span>Food &amp; drink</span>
        <span>Artisan crafts</span>
        <span>Wellness</span>
        <span>Local produce</span>
        <span>Gift hampers</span>
      </section>

      <section className="listing-grid">
        {businesses.map((item) => (
          <ListingCard key={item.id} item={item} href={`/sme/${item.slug}`} />
        ))}
      </section>
    </main>
  );
}
