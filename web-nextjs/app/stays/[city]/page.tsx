import ListingCard from '../../../components/listing-card';
import { getListingsByCity } from '../../../lib/api';

export const revalidate = 300;

export default async function StaysByCityPage({ params }: { params: { city: string } }) {
  const listings = await getListingsByCity(params.city, 'stay');

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--stay">
        <p className="eyebrow">Destination route</p>
        <h1>Stays in {params.city}</h1>
        <p>Destination-first inventory, matching the editorial travel tone of the wider PearlHub reference experience.</p>
      </section>

      <section className="listing-grid">
        {listings.map((item) => (
          <ListingCard key={item.id} item={item} href={`/stays/${params.city}`} variant="stay" />
        ))}
      </section>
    </main>
  );
}
