import { getListingsByCity } from '../../../lib/api';

export const revalidate = 300;

export default async function StaysByCityPage({ params }: { params: { city: string } }) {
  const listings = await getListingsByCity(params.city, 'stay');

  return (
    <main className="page-shell">
      <h1>Stays in {params.city}</h1>
      <p>ISR enabled. Revalidates every 5 minutes.</p>
      <ul>
        {listings.map((item: { id: string; title: string }) => (
          <li key={item.id}>{item.title}</li>
        ))}
      </ul>
    </main>
  );
}
