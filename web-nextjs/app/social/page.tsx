import Link from 'next/link';
import type { Metadata } from 'next';
import { PUBLIC_API_BASE } from '@/lib/env';

export const metadata: Metadata = {
  title: 'Social Community — PearlHub',
  description:
    'Stories, reviews, and community posts across Sri Lanka\'s premier marketplace. Share experiences, explore local life, and connect with the PearlHub community.'
};

export const revalidate = 60;

const SOCIAL_API = PUBLIC_API_BASE;

type Post = {
  id: string;
  author: { id: string; name: string; photo: string | null } | null;
  content: string;
  media_urls: string[];
  vertical_tag: string | null;
  listing_id: string | null;
  likes_count: number;
  comments_count: number;
  is_pinned: boolean;
  created_at: string;
};

type FeedResponse = {
  data: Post[];
  current_page: number;
  last_page: number;
};

const VERTICAL_FILTERS = [
  { key: '', label: 'All' },
  { key: 'property', label: 'Property' },
  { key: 'stays', label: 'Stays' },
  { key: 'vehicles', label: 'Vehicles' },
  { key: 'events', label: 'Events' },
  { key: 'experience', label: 'Experiences' },
  { key: 'sme', label: 'SME' },
  { key: 'taxi', label: 'Pearl Taxi' },
  { key: 'social', label: 'General' },
];

const VERTICAL_EMOJI: Record<string, string> = {
  property: '🏘️',
  stays: '🏨',
  vehicles: '🚗',
  events: '🎭',
  experience: '🌊',
  sme: '🏪',
  taxi: '🚕',
  social: '💬',
};

const FALLBACK_POSTS: Post[] = [
  {
    id: 'f1',
    author: { id: '1', name: 'Nilusha Perera', photo: null },
    content:
      'Stayed at a gorgeous villa in Galle last weekend — ocean-facing infinity pool, colonial architecture, and the most attentive host. Highly recommend it to anyone visiting the southern coast! 🏖️',
    media_urls: [],
    vertical_tag: 'stays',
    listing_id: null,
    likes_count: 142,
    comments_count: 18,
    is_pinned: true,
    created_at: '2026-03-30T08:00:00Z',
  },
  {
    id: 'f2',
    author: { id: '2', name: 'Kavindu Silva', photo: null },
    content:
      'Just booked a whale watching tour through PearlHub Experiences — the team was amazing and we saw blue whales off Mirissa. Absolutely bucket-list worthy! Highly recommend booking early.',
    media_urls: [],
    vertical_tag: 'experience',
    listing_id: null,
    likes_count: 98,
    comments_count: 11,
    is_pinned: false,
    created_at: '2026-03-28T14:30:00Z',
  },
  {
    id: 'f3',
    author: { id: '3', name: 'Tharushi Fernando', photo: null },
    content:
      'Pearl Taxi got me from Bandaranaike airport to Colombo 7 in under 40 minutes, tracked the whole way on the app. Cleaner than any cab I\'ve used and the driver was great. This is the standard.',
    media_urls: [],
    vertical_tag: 'taxi',
    listing_id: null,
    likes_count: 76,
    comments_count: 8,
    is_pinned: false,
    created_at: '2026-03-27T18:00:00Z',
  },
  {
    id: 'f4',
    author: { id: '4', name: 'Ashen Jayawardena', photo: null },
    content:
      'Used the SME Marketplace to get a custom batik set made for a wedding gift — artisan was in Kandy, communication was smooth through the platform, delivered in 5 days. Local craftsmanship at its best.',
    media_urls: [],
    vertical_tag: 'sme',
    listing_id: null,
    likes_count: 61,
    comments_count: 4,
    is_pinned: false,
    created_at: '2026-03-26T10:00:00Z',
  },
  {
    id: 'f5',
    author: { id: '5', name: 'Dilini Rajapaksa', photo: null },
    content:
      'Found the perfect 3BR apartment near Colombo 3 through PearlHub Property. The virtual tour saved me 3 site visits and the broker response time was same-day. Moving in next month! 🏡',
    media_urls: [],
    vertical_tag: 'property',
    listing_id: null,
    likes_count: 53,
    comments_count: 7,
    is_pinned: false,
    created_at: '2026-03-25T09:00:00Z',
  },
  {
    id: 'f6',
    author: { id: '6', name: 'Chanaka Weerasinghe', photo: null },
    content:
      'QR tickets for the Kasun Kalhara concert worked flawlessly — no queues, instant scan at the gate. That\'s how every event should be done. Events vertical is seriously underrated on here.',
    media_urls: [],
    vertical_tag: 'events',
    listing_id: null,
    likes_count: 44,
    comments_count: 5,
    is_pinned: false,
    created_at: '2026-03-24T20:00:00Z',
  },
];

async function getFeed(vertical?: string): Promise<Post[]> {
  try {
    const url = new URL(`${SOCIAL_API}/social/feed`);
    if (vertical) url.searchParams.set('vertical', vertical);
    const res = await fetch(url.toString(), { next: { revalidate: 60 } });
    if (!res.ok) return FALLBACK_POSTS;
    const json: { data: FeedResponse } = await res.json();
    return json.data?.data ?? FALLBACK_POSTS;
  } catch {
    return FALLBACK_POSTS;
  }
}

function initials(name: string) {
  return name
    .split(' ')
    .slice(0, 2)
    .map((n) => n[0]?.toUpperCase() ?? '')
    .join('');
}

function timeAgo(iso: string) {
  const diff = Date.now() - new Date(iso).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 60) return `${mins}m ago`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h ago`;
  return `${Math.floor(hrs / 24)}d ago`;
}

export default async function SocialPage({
  searchParams,
}: {
  searchParams: Promise<{ vertical?: string }>;
}) {
  const { vertical } = await searchParams;
  const posts = await getFeed(vertical);

  return (
    <main>
      <div className="page-intro page-intro--social">
        <div className="market-shell">
          <p className="eyebrow">PearlHub community</p>
          <h1>Social Feed</h1>
          <p className="market-hero__copy">
            Stories, reviews, and real experiences from our community across every vertical — property, stays,
            vehicles, events, taxi, experiences, and everyday Sri Lankan life.
          </p>
        </div>
      </div>

      <div className="market-shell" style={{ paddingTop: '2rem', paddingBottom: '4rem' }}>
        {/* Vertical filter tabs */}
        <div className="social-filter-tabs" role="tablist" aria-label="Filter by vertical">
          {VERTICAL_FILTERS.map((f) => (
            <Link
              key={f.key}
              href={f.key ? `/social?vertical=${f.key}` : '/social'}
              className={`social-filter-tab${(!vertical && f.key === '') || vertical === f.key ? ' active' : ''}`}
              role="tab"
              aria-selected={(!vertical && f.key === '') || vertical === f.key}
            >
              {f.label}
            </Link>
          ))}
        </div>

        {/* Post feed */}
        <div className="social-feed" aria-label="Community feed">
          {posts.map((post) => (
            <article key={post.id} className={`social-post${post.is_pinned ? ' social-post--pinned' : ''}`}>
              <div className="social-post__header">
                <div className="social-post__avatar" aria-hidden="true">
                  {initials(post.author?.name ?? 'PH')}
                </div>
                <div className="social-post__meta">
                  <Link
                    href={`/social/${post.author?.id ?? 'unknown'}`}
                    className="social-post__author"
                  >
                    {post.author?.name ?? 'PearlHub User'}
                  </Link>
                  <span className="social-post__time">{timeAgo(post.created_at)}</span>
                </div>
                {post.vertical_tag && (
                  <span className="social-post__badge">
                    {VERTICAL_EMOJI[post.vertical_tag] ?? '📌'} {post.vertical_tag}
                  </span>
                )}
                {post.is_pinned && (
                  <span className="social-post__pinned" title="Pinned post">📌 Pinned</span>
                )}
              </div>

              <p className="social-post__body">{post.content}</p>

              {post.media_urls.length > 0 && (
                <div className="social-post__media">
                  {post.media_urls.map((url, i) => (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img key={i} src={url} alt={`Post media ${i + 1}`} loading="lazy" />
                  ))}
                </div>
              )}

              <div className="social-post__actions">
                <button className="social-action-btn" aria-label={`${post.likes_count} likes`} disabled>
                  <span aria-hidden="true">♥</span> {post.likes_count}
                </button>
                <button className="social-action-btn" aria-label={`${post.comments_count} comments`} disabled>
                  <span aria-hidden="true">💬</span> {post.comments_count}
                </button>
                {post.listing_id && (
                  <Link
                    href={`/property/${post.listing_id}`}
                    className="social-action-btn social-action-btn--link"
                  >
                    View listing →
                  </Link>
                )}
              </div>
            </article>
          ))}
        </div>

        <div className="social-cta">
          <p>Have a story to share?</p>
          <Link href="/auth/login" className="market-btn market-btn--primary">
            Sign in to post
          </Link>
        </div>
      </div>
    </main>
  );
}
