'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useAuth } from './auth-context';

const verticals = [
  { href: '/', label: 'Home' },
  { href: '/property', label: 'Property' },
  { href: '/stays', label: 'Stays' },
  { href: '/vehicles', label: 'Vehicles' },
  { href: '/events', label: 'Events' },
  { href: '/sme', label: 'SME' },
  { href: '/taxi', label: 'Taxi' },
];

export default function SiteHeader() {
  const pathname = usePathname();
  const router = useRouter();
  const { user, logout } = useAuth();

  const handleLogout = () => {
    logout();
    router.push('/');
  };

  return (
    <header className="site-header">
      <div className="site-header__bar">
        <p>Laravel API · Next.js ISR · Flutter Monorepo · Sri Lanka luxury marketplace</p>
      </div>
      <div className="site-header__inner">
        <Link className="brand-mark" href="/">
          <span className="brand-mark__orb" />
          <span>
            <strong>PearlHub Pro</strong>
            <small>Sri Lanka premium marketplace</small>
          </span>
        </Link>

        <nav className="primary-nav" aria-label="Primary navigation">
          {verticals.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={pathname === item.href || (item.href !== '/' && pathname.startsWith(item.href)) ? 'active' : ''}
            >
              {item.label}
            </Link>
          ))}
        </nav>

        <div className="site-header__actions">
          <Link href="/search" className="search-btn" aria-label="Search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
          </Link>
          {user ? (
            <>
              <span className="status-pill">{user.name.split(' ')[0]}</span>
              <button className="btn btn-secondary" onClick={handleLogout}>
                Sign out
              </button>
            </>
          ) : (
            <>
              <Link className="btn btn-secondary" href="/auth/login">
                Sign in
              </Link>
              <Link className="btn btn-primary" href="/auth/register">
                Join free
              </Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}