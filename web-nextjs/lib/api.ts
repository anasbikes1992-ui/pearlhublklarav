const BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';
export const API_BASE = BASE_URL;

export type Vertical = 'property' | 'stay' | 'vehicle' | 'event' | 'sme';

export type ListingItem = {
  id: string;
  slug: string;
  title: string;
  description: string;
  location: string;
  price: number;
  currency: string;
  vertical: Vertical;
  category: string;
  badge: string;
  accent: 'emerald' | 'sapphire' | 'gold' | 'amber' | 'rose';
  rating?: number;
  latitude?: number;
  longitude?: number;
};

export type AuthUser = {
  id: string;
  name: string;
  email: string;
  role: string;
};

const fallbackListings: ListingItem[] = [
  {
    id: 'property-colombo-07-luxury-villa',
    slug: 'colombo-07-luxury-villa',
    title: 'Colombo 07 Luxury Villa',
    description: 'A private urban residence with garden courtyards, concierge entry, and space for long-stay executives.',
    location: 'Colombo 07',
    price: 185000000,
    currency: 'LKR',
    vertical: 'property',
    category: 'For sale',
    badge: 'Signature listing',
    accent: 'emerald',
    rating: 4.9,
    latitude: 6.9147,
    longitude: 79.8757
  },
  {
    id: 'property-galle-fort-courtyard-home',
    slug: 'galle-fort-courtyard-home',
    title: 'Galle Fort Courtyard Home',
    description: 'A restored heritage residence tuned for boutique hospitality or private ownership inside the old fort.',
    location: 'Galle Fort',
    price: 132000000,
    currency: 'LKR',
    vertical: 'property',
    category: 'Heritage estate',
    badge: 'Fort collection',
    accent: 'gold',
    rating: 4.8,
    latitude: 6.0261,
    longitude: 80.217
  },
  {
    id: 'property-kandy-hillside-residence',
    slug: 'kandy-hillside-residence',
    title: 'Kandy Hillside Residence',
    description: 'Panoramic lake views, layered terraces, and a calm family layout designed for year-round hill-country living.',
    location: 'Kandy',
    price: 94000000,
    currency: 'LKR',
    vertical: 'property',
    category: 'Family residence',
    badge: 'Hill-country edit',
    accent: 'emerald',
    rating: 4.7,
    latitude: 7.2906,
    longitude: 80.6337
  },
  {
    id: 'stay-ella-ridge-villa',
    slug: 'ella-ridge-villa',
    title: 'Ella Ridge Villa',
    description: 'Tea-country sunrise decks, plunge-pool suites, and soft mountain air just minutes from the rail town.',
    location: 'Ella',
    price: 42000,
    currency: 'LKR',
    vertical: 'stay',
    category: 'Villa retreat',
    badge: 'Most saved stay',
    accent: 'sapphire',
    rating: 4.9,
    latitude: 6.8667,
    longitude: 81.0466
  },
  {
    id: 'stay-galle-seafront-house',
    slug: 'galle-seafront-house',
    title: 'Galle Seafront House',
    description: 'Ocean-facing suites, private chef service, and a quiet base for design-led southern coast stays.',
    location: 'Galle',
    price: 51000,
    currency: 'LKR',
    vertical: 'stay',
    category: 'Coastal stay',
    badge: 'South coast favorite',
    accent: 'sapphire',
    rating: 4.8,
    latitude: 6.0535,
    longitude: 80.221
  },
  {
    id: 'stay-kandy-lake-boutique',
    slug: 'kandy-lake-boutique',
    title: 'Kandy Lake Boutique Stay',
    description: 'A compact luxury hotel built around slow mornings, city access, and elevated local dining.',
    location: 'Kandy',
    price: 36500,
    currency: 'LKR',
    vertical: 'stay',
    category: 'Boutique hotel',
    badge: 'City escape',
    accent: 'gold',
    rating: 4.6,
    latitude: 7.2906,
    longitude: 80.6337
  },
  // Vehicles
  {
    id: 'vehicle-land-cruiser',
    slug: 'toyota-land-cruiser-300',
    title: 'Toyota Land Cruiser 300',
    description: 'Full-size luxury SUV with 8 seats, air suspension, and a dedicated driver available for multi-day island tours.',
    location: 'Colombo',
    price: 18000,
    currency: 'LKR',
    vertical: 'vehicle',
    category: 'SUV with driver',
    badge: 'Top rated',
    accent: 'amber',
    rating: 4.9,
    latitude: 6.9271,
    longitude: 79.8612
  },
  {
    id: 'vehicle-mercedes-e',
    slug: 'mercedes-e-class-saloon',
    title: 'Mercedes E-Class Saloon',
    description: 'Chauffeur-grade executive saloon for airport transfers, city tours, and corporate travel across Sri Lanka.',
    location: 'Colombo',
    price: 14500,
    currency: 'LKR',
    vertical: 'vehicle',
    category: 'Executive saloon',
    badge: 'Executive pick',
    accent: 'emerald',
    rating: 4.8,
    latitude: 6.9271,
    longitude: 79.8612
  },
  {
    id: 'vehicle-tuk-tuk',
    slug: 'tuk-tuk-galle-tour',
    title: 'Classic Tuk-Tuk City Tour',
    description: 'An authentic Sri Lankan three-wheeler experience guided by a certified local driver through city gems.',
    location: 'Galle',
    price: 4500,
    currency: 'LKR',
    vertical: 'vehicle',
    category: 'Local experience',
    badge: 'Top experience',
    accent: 'gold',
    rating: 4.7,
    latitude: 6.0535,
    longitude: 80.221
  },
  // Events
  {
    id: 'event-colombo-jazz',
    slug: 'colombo-jazz-festival-2026',
    title: 'Colombo Jazz & Blues Festival',
    description: "Three nights of world-class jazz across Colombo's premiere rooftop venues. Limited VIP tables available.",
    location: 'Colombo',
    price: 7500,
    currency: 'LKR',
    vertical: 'event',
    category: 'Music festival',
    badge: 'Selling fast',
    accent: 'rose',
    rating: 4.8,
    latitude: 6.9271,
    longitude: 79.8612
  },
  {
    id: 'event-galle-lit',
    slug: 'galle-literary-festival-2026',
    title: 'Galle Literary Festival 2026',
    description: "Sri Lanka's most celebrated cultural gathering returns to the Dutch Fort with global authors and artists.",
    location: 'Galle Fort',
    price: 5000,
    currency: 'LKR',
    vertical: 'event',
    category: 'Cultural event',
    badge: 'Cultural highlight',
    accent: 'gold',
    rating: 4.9,
    latitude: 6.0261,
    longitude: 80.217
  },
  {
    id: 'event-kandy-perahera',
    slug: 'kandy-perahera-experience',
    title: 'Kandy Esala Perahera Experience',
    description: "Premium reserved viewing packages for Sri Lanka's grandest festival — elevated seating, cultural guide.",
    location: 'Kandy',
    price: 12000,
    currency: 'LKR',
    vertical: 'event',
    category: 'Cultural festival',
    badge: 'Heritage experience',
    accent: 'amber',
    rating: 4.9,
    latitude: 7.2906,
    longitude: 80.6337
  },
  // SME
  {
    id: 'sme-ceylon-tea',
    slug: 'ceylon-tea-collective',
    title: 'Ceylon Tea Collective',
    description: 'Estate-direct Single Origin teas from Nuwara Eliya. Gift boxes, subscription tins, and corporate hampers.',
    location: 'Nuwara Eliya',
    price: 2500,
    currency: 'LKR',
    vertical: 'sme',
    category: 'Artisan food & drink',
    badge: 'Pearl verified',
    accent: 'emerald',
    rating: 4.9,
    latitude: 6.9597,
    longitude: 80.7891
  },
  {
    id: 'sme-galle-spice',
    slug: 'galle-spice-trader',
    title: 'Galle Spice Trader & Co.',
    description: 'Heritage-grade cinnamon, cardamom, and pepper sourced direct from southern province family farms.',
    location: 'Galle',
    price: 1800,
    currency: 'LKR',
    vertical: 'sme',
    category: 'Local produce',
    badge: 'Artisan business',
    accent: 'gold',
    rating: 4.7,
    latitude: 6.0535,
    longitude: 80.221
  },
  {
    id: 'sme-colombo-craft',
    slug: 'colombo-craft-studio',
    title: 'Colombo Craft Studio',
    description: 'Contemporary batik, handloom silks, and bespoke jewellery made by Colombo artisans. Ships worldwide.',
    location: 'Colombo',
    price: 3500,
    currency: 'LKR',
    vertical: 'sme',
    category: 'Artisan crafts',
    badge: 'Curated maker',
    accent: 'rose',
    rating: 4.8,
    latitude: 6.9271,
    longitude: 79.8612
  }
];

const slugify = (value: string) =>
  value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');

const ACCENT_BY_VERTICAL: Record<string, ListingItem['accent']> = {
  property: 'emerald',
  stay: 'sapphire',
  vehicle: 'amber',
  event: 'rose',
  sme: 'gold',
};

type ApiListing = {
  id: string;
  title: string;
  description?: string | null;
  price?: number | string | null;
  currency?: string | null;
  vertical?: string | null;
  latitude?: number | string | null;
  longitude?: number | string | null;
  metadata?: Record<string, unknown> | null;
};

const normalizeListing = (item: ApiListing): ListingItem => {
  const metadata = item.metadata ?? {};
  const rawVertical = item.vertical ?? 'property';
  const vertical: Vertical = (['property', 'stay', 'vehicle', 'event', 'sme'] as Vertical[]).includes(rawVertical as Vertical)
    ? (rawVertical as Vertical)
    : 'property';
  const location = typeof metadata.location === 'string' ? metadata.location : 'Sri Lanka';
  const category = typeof metadata.category === 'string' ? metadata.category : vertical;
  const badge = typeof metadata.badge === 'string' ? metadata.badge : 'Verified listing';
  const accent = (ACCENT_BY_VERTICAL[vertical] ?? 'emerald') as ListingItem['accent'];
  const slug = typeof metadata.slug === 'string' ? metadata.slug : slugify(item.title);

  return {
    id: item.id,
    slug,
    title: item.title,
    description: item.description || 'Marketplace listing synced from the PearlHub Laravel API.',
    location,
    price: Number(item.price ?? 0),
    currency: item.currency ?? 'LKR',
    vertical,
    category,
    badge,
    accent,
    rating: typeof metadata.rating === 'number' ? metadata.rating : undefined,
    latitude: item.latitude ? Number(item.latitude) : undefined,
    longitude: item.longitude ? Number(item.longitude) : undefined
  };
};

async function fetchSearch(vertical?: Vertical, query?: string): Promise<ListingItem[]> {
  const params = new URLSearchParams();

  if (vertical) {
    params.set('vertical', vertical);
  }

  if (query) {
    params.set('q', query);
  }

  const response = await fetch(`${BASE_URL}/search?${params.toString()}`, {
    next: { revalidate: 300 }
  });

  if (!response.ok) {
    throw new Error(`Search request failed with status ${response.status}`);
  }

  const payload = (await response.json()) as { data?: ApiListing[] };
  return (payload.data ?? []).map(normalizeListing);
}

const filterFallback = (vertical?: Vertical, query?: string) => {
  return fallbackListings.filter((item) => {
    const matchesVertical = vertical ? item.vertical === vertical : true;
    const matchesQuery = query
      ? `${item.title} ${item.location} ${item.description}`.toLowerCase().includes(query.toLowerCase())
      : true;
    return matchesVertical && matchesQuery;
  });
};

export async function searchListings(query: string, vertical?: Vertical): Promise<ListingItem[]> {
  try {
    const results = await fetchSearch(vertical, query);
    if (results.length > 0) return results;
  } catch {
    // fall through
  }
  return filterFallback(vertical, query);
}

export async function getFeaturedListings(vertical?: Vertical, limit = 3): Promise<ListingItem[]> {
  try {
    const items = await fetchSearch(vertical);
    return items.slice(0, limit);
  } catch {
    return filterFallback(vertical).slice(0, limit);
  }
}

export async function getListingsByCity(city: string, vertical: Vertical): Promise<ListingItem[]> {
  try {
    const results = await fetchSearch(vertical, city);
    if (results.length > 0) {
      return results;
    }
  } catch {
    // Fall through to fallback data when the API is unavailable.
  }

  return filterFallback(vertical, city);
}

export async function getListingBySlug(slug: string, vertical: Vertical = 'property'): Promise<ListingItem> {
  const apiItems = await getFeaturedListings(vertical, 12);
  const found = apiItems.find((item) => item.slug === slug);

  if (found) {
    return found;
  }

  return filterFallback(vertical).find((item) => item.slug === slug) ?? filterFallback(vertical)[0];
}
