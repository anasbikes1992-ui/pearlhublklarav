import ListingCard from '../../components/listing-card';
import { getFeaturedListings } from '../../lib/api';

export default async function StaysIndexPage() {
  const stays = await getFeaturedListings('stay', 6);

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--stay">
        <p className="eyebrow">Stay discovery</p>
        <h1>Hotels, villas, and boutique escapes arranged like a high-end travel editorial.</h1>
        <p>
          The UI direction follows the reference product: strong category framing, premium typography, and an immediate visual
          path into destination-based browsing.
        </p>
      </section>

      <section className="filter-row" aria-label="Stay filters">
        <span>Villas</span>
        <span>Boutique hotels</span>
        <span>South coast</span>
        <span>Hill country</span>
      </section>

      <section className="listing-grid">
        {stays.map((item) => (
          <ListingCard key={item.id} item={item} href={`/stays/${item.location.toLowerCase()}`} variant="stay" />
        ))}
      </section>
    </main>
  );
}