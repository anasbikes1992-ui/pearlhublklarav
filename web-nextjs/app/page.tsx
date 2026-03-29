import Link from 'next/link';
import ListingCard from '../components/listing-card';
import DiscoveryMap from '../components/map/discovery-map';
import { getFeaturedListings } from '../lib/api';

export default async function HomePage() {
  const featuredProperties = await getFeaturedListings('property', 3);
  const featuredStays = await getFeaturedListings('stay', 3);

  const highlightCities = [
    { name: 'Ella', vibe: 'Tea-country hideaways', href: '/stays/ella' },
    { name: 'Galle', vibe: 'Coastal heritage stays', href: '/stays/galle' },
    { name: 'Kandy', vibe: 'Hill-country city escapes', href: '/stays/kandy' }
  ];

  const curatedPins = [
    { lat: 6.0535, lng: 80.221, label: 'Galle Villa Collection' },
    { lat: 6.9497, lng: 80.7891, label: 'Nuwara Eliya Tea Estate Lodges' },
    { lat: 7.2906, lng: 80.6337, label: 'Kandy Boutique Residences' }
  ];

  return (
    <main className="page-shell page-home">
      <section className="hero">
        <p className="eyebrow">Sri Lanka Curated Marketplace</p>
        <h1>Find stays and signature properties designed around place, not just price.</h1>
        <p className="hero-copy">
          PearlHub Pro brings together premium villas, design-forward homes, and handpicked city stays with live route-level
          updates.
        </p>
        <div className="hero-cta-row">
          <Link className="btn btn-primary" href="/stays/ella">Explore stays</Link>
          <Link className="btn btn-secondary" href="/property/colombo-07-luxury-villa">View spotlight property</Link>
        </div>
      </section>

      <section className="trust-strip" aria-label="Platform trust indicators">
        <article>
          <strong>12.4k+</strong>
          <span>marketplace listings shaped for luxury search</span>
        </article>
        <article>
          <strong>Laravel API</strong>
          <span>search, bookings, payments, escrow, and verification foundations</span>
        </article>
        <article>
          <strong>Next.js web</strong>
          <span>responsive shell, route-level detail pages, and Vercel deployment</span>
        </article>
      </section>

      <section className="city-grid" aria-label="Highlighted cities">
        {highlightCities.map((city) => (
          <article className="city-card" key={city.name}>
            <h2>{city.name}</h2>
            <p>{city.vibe}</p>
            <Link href={city.href}>Browse city listings</Link>
          </article>
        ))}
      </section>

      <section className="catalog-section">
        <div className="section-heading">
          <div>
            <p className="eyebrow">Property marketplace</p>
            <h2>Residential and hospitality assets presented like a premium portfolio.</h2>
          </div>
          <Link className="text-link" href="/property">
            Open property grid
          </Link>
        </div>
        <div className="listing-grid">
          {featuredProperties.map((item) => (
            <ListingCard key={item.id} item={item} href={`/property/${item.slug}`} />
          ))}
        </div>
      </section>

      <section className="catalog-section">
        <div className="section-heading">
          <div>
            <p className="eyebrow">Stay discovery</p>
            <h2>City and destination stays with a softer hospitality-driven presentation.</h2>
          </div>
          <Link className="text-link" href="/stays">
            Explore stay collection
          </Link>
        </div>
        <div className="listing-grid">
          {featuredStays.map((item) => (
            <ListingCard key={item.id} item={item} href={`/stays/${item.location.toLowerCase()}`} variant="stay" />
          ))}
        </div>
      </section>

      <section className="map-section" aria-label="Discovery map">
        <header>
          <h2>Discover regions at a glance</h2>
          <p>Zoom into demand hotspots and jump straight into curated collections.</p>
        </header>
        <DiscoveryMap pins={curatedPins} />
      </section>

      <section className="feature-panel-grid" aria-label="Core platform capabilities">
        <article>
          <p className="eyebrow">What is implemented</p>
          <h2>Backend logic is not empty scaffolding.</h2>
          <p>
            The Laravel codebase already includes real search routes, listing CRUD, bookings, wallet and escrow models, and
            payment gateway abstractions. The main gap was frontend surface area, not total platform absence.
          </p>
        </article>
        <article>
          <p className="eyebrow">What still needs expansion</p>
          <h2>Feature parity with the reference app.</h2>
          <p>
            The reference source includes many more verticals and interaction layers. This Next app now matches its premium UI
            direction more closely, but advanced dashboards, auth flows, and operational tooling still need dedicated builds.
          </p>
        </article>
      </section>
    </main>
  );
}
