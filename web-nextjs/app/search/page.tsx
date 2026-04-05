import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import ListingCard from '../../components/listing-card';
import DiscoveryMap from '../../components/map/discovery-map';
import { searchListings, type Vertical } from '../../lib/api';

type SearchParams = { q?: string; vertical?: string };

export function generateMetadata({ searchParams }: { searchParams: SearchParams }): Metadata {
  const q = searchParams.q ?? '';
  return {
    title: q ? `"${q}" — PearlHub Search` : 'Search — PearlHub Pro',
    description: `Search across properties, stays, vehicles, events, and local businesses in Sri Lanka.`,
  };
}

const VALID_VERTICALS: Vertical[] = ['property', 'stay', 'vehicle', 'event', 'sme'];

export default async function SearchPage({ searchParams }: { searchParams: SearchParams }) {
  const q = (searchParams.q ?? '').trim();
  const rawVertical = searchParams.vertical ?? '';
  const vertical = VALID_VERTICALS.includes(rawVertical as Vertical) ? (rawVertical as Vertical) : undefined;

  if (!q) {
    redirect('/');
  }

  const results = await searchListings(q, vertical);
  const pins = results
    .filter((item) => typeof item.latitude === 'number' && typeof item.longitude === 'number')
    .map((item) => ({
      lat: item.latitude as number,
      lng: item.longitude as number,
      label: item.title,
    }));

  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--search">
        <p className="eyebrow">Search results</p>
        <h1>
          {results.length} {results.length === 1 ? 'result' : 'results'} for &ldquo;{q}&rdquo;
          {vertical ? ` in ${vertical}` : ''}
        </h1>
        <p>Showing curated matches from properties, stays, vehicles, events, and local businesses across Sri Lanka.</p>
      </section>

      <form className="search-bar-form" action="/search" method="get">
        <input className="search-input" name="q" defaultValue={q} placeholder="Search by name, city, or category…" autoFocus />
        <select className="search-select" name="vertical" defaultValue={vertical ?? ''}>
          <option value="">All categories</option>
          {VALID_VERTICALS.map((v) => (
            <option key={v} value={v}>
              {v.charAt(0).toUpperCase() + v.slice(1)}
            </option>
          ))}
        </select>
        <button className="btn btn-primary" type="submit">
          Search
        </button>
      </form>

      {results.length === 0 ? (
        <div className="search-empty">
          <p>No listings matched your search. Try a different keyword or browse a vertical from the navigation.</p>
        </div>
      ) : (
        <>
          {pins.length > 0 && (
            <section className="search-map">
              <h2>Map view</h2>
              <DiscoveryMap pins={pins} />
            </section>
          )}
          <section className="listing-grid">
            {results.map((item) => (
              <ListingCard key={item.id} item={item} href={`/${item.vertical === 'stay' ? 'stays' : item.vertical}/${item.slug}`} />
            ))}
          </section>
        </>
      )}
    </main>
  );
}
