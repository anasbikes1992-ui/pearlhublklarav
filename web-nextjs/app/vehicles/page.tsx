import ListingCard from '../../components/listing-card';
import { getFeaturedListings } from '../../lib/api';

export const revalidate = 300;

export default async function VehiclesPage() {
  const vehicles = await getFeaturedListings('vehicle', 6);

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--vehicle">
        <p className="eyebrow">Vehicle marketplace</p>
        <h1>Premium cars, drivers, and bespoke island transport experiences.</h1>
        <p>
          Executive saloons, 4WDs with chauffeurs, vintage Defenders, and authentic tuk-tuk tours — all Pearl-verified and
          ready to book.
        </p>
      </section>

      <section className="filter-row" aria-label="Vehicle filters">
        <span>With driver</span>
        <span>Self-drive</span>
        <span>Executive saloon</span>
        <span>SUV / 4WD</span>
        <span>Local experience</span>
      </section>

      <section className="listing-grid">
        {vehicles.map((item) => (
          <ListingCard key={item.id} item={item} href={`/vehicles/${item.slug}`} />
        ))}
      </section>
    </main>
  );
}
