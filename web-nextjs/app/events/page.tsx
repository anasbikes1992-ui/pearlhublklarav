import ListingCard from '../../components/listing-card';
import { getFeaturedListings } from '../../lib/api';

export const revalidate = 300;

export default async function EventsPage() {
  const events = await getFeaturedListings('event', 6);

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--event">
        <p className="eyebrow">Experiences &amp; Events</p>
        <h1>Curated cultural events, festivals, and live performances across Sri Lanka.</h1>
        <p>
          From the Galle Literary Festival to Kandy Esala Perahera — book premium reserved packages with Pearl hospitality
          included. QR ticket delivery to your app.
        </p>
      </section>

      <section className="filter-row" aria-label="Event filters">
        <span>Cultural festivals</span>
        <span>Music &amp; arts</span>
        <span>Wellness retreats</span>
        <span>Private dining</span>
        <span>Sporting events</span>
      </section>

      <section className="listing-grid">
        {events.map((item) => (
          <ListingCard key={item.id} item={item} href={`/events/${item.slug}`} />
        ))}
      </section>
    </main>
  );
}
