import ListingCard from '../../components/listing-card';
import { getFeaturedListings } from '../../lib/api';

export default async function PropertyIndexPage() {
  const properties = await getFeaturedListings('property', 6);

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--property">
        <p className="eyebrow">Property marketplace</p>
        <h1>Premium homes, investment-ready assets, and hospitality-grade estates.</h1>
        <p>
          This surface borrows the reference PearlHub direction: premium hero treatment, scannable catalog cards, and clear
          context for pricing and location.
        </p>
      </section>

      <section className="filter-row" aria-label="Property filters">
        <span>For sale</span>
        <span>Heritage homes</span>
        <span>Hill-country residences</span>
        <span>Urban villas</span>
      </section>

      <section className="listing-grid">
        {properties.map((item) => (
          <ListingCard key={item.id} item={item} href={`/property/${item.slug}`} />
        ))}
      </section>
    </main>
  );
}