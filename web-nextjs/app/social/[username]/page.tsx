import Link from 'next/link';
import type { Metadata } from 'next';
import { PUBLIC_API_BASE } from '@/lib/env';

export const revalidate = 120;

const SOCIAL_API = PUBLIC_API_BASE;

type PostSummary = {
  id: string;
  content: string;
  media_urls: string[];
  vertical_tag: string | null;
  likes_count: number;
  comments_count: number;
  created_at: string;
};

type ProfileData = {
  id: string;
  name: string;
  photo: string | null;
  followers_count: number;
  following_count: number;
  posts_count: number;
  posts: { data: PostSummary[] };
};

export async function generateMetadata({
  params,
}: {
  params: Promise<{ username: string }>;
}): Promise<Metadata> {
  const { username } = await params;
  return {
    title: `Profile — PearlHub Social`,
    description: `Community profile on PearlHub — posts, listings, and reviews by user ${username}.`,
  };
}

async function getProfile(userId: string): Promise<ProfileData | null> {
  try {
    const res = await fetch(`${SOCIAL_API}/social/users/${userId}/profile`, {
      next: { revalidate: 120 },
    });
    if (!res.ok) return null;
    const json: { data: ProfileData } = await res.json();
    return json.data ?? null;
  } catch {
    return null;
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

export default async function SocialProfilePage({
  params,
}: {
  params: Promise<{ username: string }>;
}) {
  const { username } = await params;
  const profile = await getProfile(username);

  if (!profile) {
    return (
      <main className="market-shell" style={{ paddingTop: '4rem', paddingBottom: '4rem' }}>
        <div style={{ textAlign: 'center' }}>
          <p style={{ fontSize: '3rem' }}>👤</p>
          <h2>Profile not found</h2>
          <p style={{ color: 'var(--text-secondary)' }}>
            This user may not exist or their profile is private.
          </p>
          <Link href="/social" className="market-btn market-btn--ghost" style={{ marginTop: '1.5rem' }}>
            Back to feed
          </Link>
        </div>
      </main>
    );
  }

  const posts = profile.posts?.data ?? [];

  return (
    <main>
      {/* Profile header */}
      <div className="page-intro page-intro--social">
        <div className="market-shell">
          <Link
            href="/social"
            className="social-back-link"
            aria-label="Back to social feed"
          >
            ← Social feed
          </Link>

          <div className="social-profile-hero">
            <div className="social-profile-avatar" aria-hidden="true">
              {profile.photo ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img src={profile.photo} alt={profile.name} />
              ) : (
                initials(profile.name)
              )}
            </div>
            <div>
              <h1 style={{ fontSize: 'clamp(1.5rem, 3vw, 2.25rem)' }}>{profile.name}</h1>
              <div className="social-profile-stats">
                <span>
                  <strong>{profile.posts_count}</strong> posts
                </span>
                <span>
                  <strong>{profile.followers_count}</strong> followers
                </span>
                <span>
                  <strong>{profile.following_count}</strong> following
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Posts grid */}
      <div className="market-shell" style={{ paddingTop: '2rem', paddingBottom: '4rem' }}>
        {posts.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '3rem 0', color: 'var(--text-secondary)' }}>
            <p>No posts yet.</p>
          </div>
        ) : (
          <div className="social-feed">
            {posts.map((post) => (
              <article key={post.id} className="social-post">
                <div className="social-post__header">
                  <div className="social-post__avatar" aria-hidden="true">
                    {initials(profile.name)}
                  </div>
                  <div className="social-post__meta">
                    <span className="social-post__author">{profile.name}</span>
                    <span className="social-post__time">{timeAgo(post.created_at)}</span>
                  </div>
                  {post.vertical_tag && (
                    <span className="social-post__badge">
                      {VERTICAL_EMOJI[post.vertical_tag] ?? '📌'} {post.vertical_tag}
                    </span>
                  )}
                </div>

                <p className="social-post__body">{post.content}</p>

                <div className="social-post__actions">
                  <span className="social-action-btn">♥ {post.likes_count}</span>
                  <span className="social-action-btn">💬 {post.comments_count}</span>
                </div>
              </article>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
