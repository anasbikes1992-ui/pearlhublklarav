import Link from 'next/link';
import ListingCard from '../listing-card';
import DiscoveryMap from '../map/discovery-map';
import { getFeaturedListings } from '../../lib/api';

export default async function LegacyHomePage() {
  const featuredProperties = await getFeaturedListings('property', 3);
  const featuredStays = await getFeaturedListings('stay', 3);

  const highlightCities = [
    {
      name: 'Ella',
      vibe: 'Tea-country hideaways & ridge villas',
      href: '/stays/ella',
      emoji: '🌿',
      accent: 'ella'
    },
    {
      name: 'Galle',
      vibe: 'Coastal heritage stays & ocean homes',
      href: '/stays/galle',
      emoji: '🌊',
      accent: 'galle'
    },
    {
      name: 'Kandy',
      vibe: 'Hill-country city retreats',
      href: '/stays/kandy',
      emoji: '🏔️',
      accent: 'kandy'
    }
  ];

  const trustStats = [
    { value: '12,400+', label: 'Verified listings', emoji: '🏛️' },
    { value: '4.9 ★', label: 'Average host rating', emoji: '⭐' },
    { value: '24 h', label: 'Avg. response time', emoji: '⚡' }
  ];

  const uspPanels = [
    {
      eyebrow: 'Pearl-verified hosts',
      title: 'Every listing is reviewed — not just listed.',
      body:
        'We manually verify each property, stay, and service before it appears on PearlHub Pro. No unverified feeds, no ghost listings.'
    },
    {
      eyebrow: 'Integrated payments',
      title: 'PayHere, WebXPay & Dialog Genie built-in.',
      body:
        'Secure local payment gateways with wallet escrow and instant settlement. Book with confidence — funds held until check-in confirmed.'
    }
  ];

  const curatedPins = [
    { lat: 6.0535, lng: 80.221, label: 'Galle Villa Collection' },
    { lat: 6.9497, lng: 80.7891, label: 'Nuwara Eliya Tea Estate Lodges' },
    { lat: 7.2906, lng: 80.6337, label: 'Kandy Boutique Residences' }
  ];

  return (
    <>
      <section className="backup-banner">
        <div className="backup-banner__inner">
          <strong>Legacy UI backup</strong>
          <span>This is the preserved previous homepage. The redesigned main experience is at /.</span>
          <Link href="/">Open redesigned home</Link>
        </div>
      </section>

      <main className="page-shell page-home">
        <section className="hero">
          <div className="hero-content">
            <p className="eyebrow hero-badge">Sri Lanka Curated Marketplace</p>
            <h1>
              Find stays and signature properties designed around
              place, not just price.
            </h1>
            <p className="hero-copy">
              PearlHub Pro unites premium villas, design-forward homes, luxury
              vehicles, cultural events, and local artisans — with live
              route-level updates and Pearl-verified trust.
            </p>
            <div className="hero-cta-row">
              <Link className="btn btn-primary" href="/stays/ella">
                Explore stays
              </Link>
              <Link className="btn btn-secondary" href="/property">
                Browse properties
              </Link>
            </div>
          </div>
        </section>

        <section className="trust-strip" aria-label="Platform trust indicators">
          {trustStats.map((stat) => (
            <article key={stat.label}>
              <span className="trust-emoji" aria-hidden="true">{stat.emoji}</span>
              <strong>{stat.value}</strong>
              <span>{stat.label}</span>
            </article>
          ))}
        </section>

        <section className="city-grid" aria-label="Highlighted destinations">
          {highlightCities.map((city) => (
            <article className={`city-card city-card--${city.accent}`} key={city.name}>
              <span className="city-card__emoji" aria-hidden="true">{city.emoji}</span>
              <h2>{city.name}</h2>
              <p>{city.vibe}</p>
              <Link href={city.href} className="text-link">
                Browse city listings
              </Link>
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
              <ListingCard
                key={item.id}
                item={item}
                href={`/stays/${item.location.toLowerCase()}`}
                variant="stay"
              />
            ))}
          </div>
        </section>

        <section className="map-section" aria-label="Discovery map">
          <header>
            <h2>Discover Sri Lanka at a glance</h2>
            <p>Zoom into demand hotspots and jump straight into curated collections.</p>
          </header>
          <DiscoveryMap pins={curatedPins} />
        </section>

        <section className="feature-panel-grid" aria-label="Why PearlHub Pro">
          {uspPanels.map((panel) => (
            <article key={panel.eyebrow}>
              <p className="eyebrow">{panel.eyebrow}</p>
              <h2>{panel.title}</h2>
              <p>{panel.body}</p>
            </article>
          ))}
        </section>
      </main>
    </>
  );
}