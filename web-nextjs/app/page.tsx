import Link from 'next/link';
import type { Metadata } from 'next';
import ListingCard from '../components/listing-card';
import DiscoveryMap from '../components/map/discovery-map';
import { getFeaturedListings } from '../lib/api';

export const metadata: Metadata = {
  title: 'PearlHub Pro — Sri Lanka Luxury Marketplace',
  description:
    'Discover premium properties, curated stays, luxury vehicles, cultural events, and local artisans across Sri Lanka. The island\'s most trusted marketplace.'
};

export default async function HomePage() {
  const featuredProperties = await getFeaturedListings('property', 3);
  const featuredStays = await getFeaturedListings('stay', 3);

  const marketStats = [
    { value: '12.4k+', label: 'Properties', emoji: '🏘️' },
    { value: '3.2k+', label: 'Luxury stays', emoji: '🏨' },
    { value: '1.8k+', label: 'Vehicles', emoji: '🚗' },
    { value: '540+', label: 'Live events', emoji: '🎫' },
  ];

  const platformCards = [
    {
      title: 'Property',
      meta: 'Sale • Rent • Lease',
      description: 'Verified owners, licensed brokers, and premium homes from Colombo penthouses to southern villas.',
      count: '6,240+ listings',
      href: '/property',
      emoji: '🏘️',
      tone: 'teal',
    },
    {
      title: 'Stays',
      meta: 'Hotels • Villas • Hostels',
      description: 'Tourism-ready stays with luxury villas, boutique lodges, and city escapes across Sri Lanka.',
      count: '3,180+ listings',
      href: '/stays',
      emoji: '🏨',
      tone: 'gold',
    },
    {
      title: 'Rent-a-Vehicle',
      meta: 'Cars • Vans • Coaches',
      description: 'Self-drive, chauffeur, and tour-ready transport with executive saloons, SUVs, and buses.',
      count: '1,820+ listings',
      href: '/vehicles',
      emoji: '🚗',
      tone: 'teal',
    },
    {
      title: 'Pearl Taxi',
      meta: 'Moto • Cars • Vans • Buses',
      description: 'On-demand rides and parcel movement with live tracking, SOS, and cashless flows.',
      count: '13 service types',
      href: '/taxi',
      emoji: '🚕',
      tone: 'gold',
    },
    {
      title: 'Events & Cinema',
      meta: 'Tickets • Seats • QR Entry',
      description: 'Concerts, cinema, and sports booking with QR ticketing and real-time seat selection.',
      count: '540+ listings',
      href: '/events',
      emoji: '🎭',
      tone: 'teal',
    },
    {
      title: 'SME Marketplace',
      meta: 'Local Goods • Services',
      description: 'Sri Lankan craft, services, and authentic local business listings under one marketplace.',
      count: '1,200+ listings',
      href: '/sme',
      emoji: '🏪',
      tone: 'gold',
    },
  ];

  const searchTags = ['Beach Villas', 'Penthouse', 'EV Rentals', 'Live Concerts', 'Local Crafts'];

  const trustPoints = [
    {
      title: 'Verified Listings',
      body: 'Property owners provide documentation, brokers require consent, and listings are reviewed before surfacing publicly.',
      emoji: '🛡️'
    },
    {
      title: 'Transparent Pricing',
      body: 'Clear pricing and commissions with no hidden charge stacks, plus premium booking support for high-value transactions.',
      emoji: '💎'
    },
    {
      title: 'Interactive Maps',
      body: 'Explore properties, stays, vehicles, and event hotspots visually before you commit to a route or booking.',
      emoji: '🗺️'
    },
    {
      title: 'QR Ticket System',
      body: 'Tamper-resistant digital tickets for cinema, concerts, and event access with smoother verification at entry.',
      emoji: '🎫'
    }
  ];

  const curatedPins = [
    { lat: 6.0535, lng: 80.221, label: 'Galle Villa Collection' },
    { lat: 6.9497, lng: 80.7891, label: 'Nuwara Eliya Tea Estate Lodges' },
    { lat: 7.2906, lng: 80.6337, label: 'Kandy Boutique Residences' }
  ];

  return (
    <main className="market-home">
      <section className="market-hero">
        <div className="market-hero__grid" />
        <div className="market-hero__glow market-hero__glow--teal" />
        <div className="market-hero__glow market-hero__glow--gold" />

        <div className="market-shell market-hero__content">
          <p className="market-kicker">✨ Sri Lanka&apos;s #1 ecosystem</p>
          <h1>Sri Lanka&apos;s #1 Luxury Marketplace</h1>
          <p className="market-hero__copy">
            Discover properties, stays, vehicles, events, and local businesses in one premium marketplace.
            From colonial bungalows in Nuwara Eliya to Colombo penthouses and curated experiences across the island.
          </p>

          <div className="market-hero__actions">
            <Link className="market-btn market-btn--primary" href="/search">
              Explore marketplace
            </Link>
            <Link className="market-btn market-btn--ghost" href="/property">
              Browse properties
            </Link>
          </div>

          <div className="market-search-hub" aria-label="Popular search suggestions">
            <span className="market-search-hub__label">🔍 Search Hub</span>
            {searchTags.map((tag) => (
              <span key={tag} className="market-search-hub__tag">#{tag.toUpperCase()}</span>
            ))}
          </div>

          <div className="market-stat-row">
            {marketStats.map((stat) => (
              <article key={stat.label} className="market-stat-card">
                <span className="market-stat-card__emoji" aria-hidden="true">{stat.emoji}</span>
                <strong>{stat.value}</strong>
                <span>{stat.label}</span>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="market-section market-platforms">
        <div className="market-shell">
          <div className="market-section__intro">
            <p className="market-kicker">Explore Sri Lanka with Pearl Hub</p>
            <h2>Four powerful platforms, one seamless experience.</h2>
            <p>
              The visual direction follows the reference product closely: bold marketplace cards, dark premium surfaces,
              neon cyan highlights, and warm gold accents tuned for Sri Lankan luxury and trust.
            </p>
          </div>

          <div className="market-platform-grid">
            {platformCards.map((card) => (
              <article key={card.title} className={`market-platform-card market-platform-card--${card.tone}`}>
                <div className="market-platform-card__head">
                  <span className="market-platform-card__emoji" aria-hidden="true">{card.emoji}</span>
                  <div>
                    <p>{card.meta}</p>
                    <h3>{card.title}</h3>
                  </div>
                </div>
                <p className="market-platform-card__body">{card.description}</p>
                <div className="market-platform-card__footer">
                  <strong>{card.count}</strong>
                  <Link href={card.href}>Open section</Link>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="market-section market-showcase">
        <div className="market-shell">
          <div className="market-split-heading">
            <div>
              <p className="market-kicker">Featured marketplace picks</p>
              <h2>Premium inventory surfaced with a sharper commercial feel.</h2>
            </div>
            <Link href="/backup" className="market-inline-link">
              View previous homepage backup
            </Link>
          </div>

          <div className="market-showcase__block">
            <div className="market-showcase__heading">
              <div>
                <p className="market-mini-label">Property</p>
                <h3>Signature property collection</h3>
              </div>
              <Link href="/property">See all properties</Link>
            </div>
            <div className="listing-grid">
              {featuredProperties.map((item) => (
                <ListingCard key={item.id} item={item} href={`/property/${item.slug}`} />
              ))}
            </div>
          </div>

          <div className="market-showcase__block">
            <div className="market-showcase__heading">
              <div>
                <p className="market-mini-label">Stays</p>
                <h3>Curated hospitality highlights</h3>
              </div>
              <Link href="/stays">See all stays</Link>
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
          </div>
        </div>
      </section>

      <section className="market-section market-map-wrap" aria-label="Explore on the map">
        <div className="market-shell">
          <div className="market-section__intro market-section__intro--tight">
            <p className="market-kicker">Explore on the map</p>
            <h2>Properties, stays, vehicles, and events — all on one interactive map.</h2>
          </div>
          <div className="market-map-card">
            <div className="market-map-card__filters">
              <span>Properties</span>
              <span>Stays</span>
              <span>Vehicles</span>
              <span>Events</span>
            </div>
            <DiscoveryMap pins={curatedPins} />
          </div>
        </div>
      </section>

      <section className="market-section market-why">
        <div className="market-shell">
          <div className="market-section__intro market-section__intro--tight">
            <p className="market-kicker">Why Pearl Hub?</p>
            <h2>Trust systems and product depth, not just listing volume.</h2>
          </div>
          <div className="market-trust-grid">
            {trustPoints.map((point) => (
              <article key={point.title} className="market-trust-card">
                <span className="market-trust-card__emoji" aria-hidden="true">{point.emoji}</span>
                <h3>{point.title}</h3>
                <p>{point.body}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="market-section market-payments">
        <div className="market-shell market-payments__inner">
          <div>
            <p className="market-kicker">Accepted payments</p>
            <h2>Built for Sri Lanka. Tuned for premium conversion.</h2>
          </div>
          <div className="market-payments__chips">
            <span>PayHere</span>
            <span>LankaPay</span>
            <span>WebXPay</span>
            <span>Dialog Genie</span>
          </div>
        </div>
      </section>
    </main>
  );
}
