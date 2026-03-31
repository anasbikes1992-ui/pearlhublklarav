import ListingCard from '../../components/listing-card';
import { getFeaturedListings } from '../../lib/api';

export const revalidate = 300;

export default async function ExperiencesPage() {
  const experiences = await getFeaturedListings('experience', 6);

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--experience">
        <p className="eyebrow">Experiences &amp; Tours</p>
        <h1>Iconic Sri Lanka moments — whale watching, safaris, surf, and ancient culture.</h1>
        <p>
          Handpicked by our Pearl concierge team: certified whale-watch captains off Mirissa, licensed safari guides at Yala
          and Wilpattu, surf schools at Arugam Bay, and immersive tea estate walks in the hill country. Every experience is
          Pearl-verified, fully insured, and bookable with one tap.
        </p>
      </section>

      <section className="filter-row" aria-label="Experience filters">
        <span>Whale watching</span>
        <span>Safari</span>
        <span>Surf &amp; water sports</span>
        <span>Cultural tours</span>
        <span>Tea estate</span>
        <span>Cooking &amp; wellness</span>
      </section>

      <section className="listing-grid">
        {experiences.map((item) => (
          <ListingCard key={item.id} item={item} href={`/experiences/${item.slug}`} />
        ))}
      </section>
    </main>
  );
}
