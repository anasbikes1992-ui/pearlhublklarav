'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useState } from 'react';
import { useAuth } from '../auth-context';

const verticals = [
  { href: '/', label: 'Home' },
  { href: '/property', label: 'Property' },
  { href: '/stays', label: 'Stays' },
  { href: '/vehicles', label: 'Vehicles' },
  { href: '/events', label: 'Events' },
  { href: '/sme', label: 'SME' },
  { href: '/taxi', label: 'Taxi' },
];

export default function LegacySiteHeader() {
  const pathname = usePathname();
  const router = useRouter();
  const { user, logout } = useAuth();
  const [mobileOpen, setMobileOpen] = useState(false);

  const handleLogout = () => {
    logout();
    router.push('/');
  };

  const isActive = (href: string) =>
    pathname === href || (href !== '/' && pathname.startsWith(href));

  return (
    <header className="site-header">
      <div className="site-header__bar">
        <p>Sri Lanka&apos;s Premium Luxury Marketplace — Properties · Stays · Vehicles · Events · Taxi</p>
      </div>
      <div className="site-header__inner">
        <Link className="brand-mark" href="/backup">
          <span className="brand-mark__orb" />
          <span>
            <strong>PearlHub Pro</strong>
            <small>Legacy UI backup</small>
          </span>
        </Link>

        <nav className="primary-nav" aria-label="Primary navigation">
          {verticals.map((item) => (
            <Link
              key={item.href}
              href={item.href === '/' ? '/backup' : item.href}
              className={isActive(item.href) ? 'active' : ''}
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
              <span className="status-pill">{(user.full_name ?? user.name)?.split(' ')[0]}</span>
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
          <button
            className="mobile-menu-toggle"
            aria-label="Open menu"
            onClick={() => setMobileOpen(true)}
          >
            <span /><span /><span />
          </button>
        </div>
      </div>

      <nav className={`mobile-nav${mobileOpen ? ' open' : ''}`} aria-label="Mobile navigation">
        <button className="mobile-nav__close" onClick={() => setMobileOpen(false)} aria-label="Close menu">
          ✕
        </button>
        {verticals.map((item) => (
          <Link
            key={item.href}
            href={item.href === '/' ? '/backup' : item.href}
            className={isActive(item.href) ? 'active' : ''}
            onClick={() => setMobileOpen(false)}
          >
            {item.label}
          </Link>
        ))}
        {user ? (
          <button className="btn btn-secondary" onClick={() => { handleLogout(); setMobileOpen(false); }}>
            Sign out
          </button>
        ) : (
          <>
            <Link className="btn btn-secondary" href="/auth/login" onClick={() => setMobileOpen(false)}>Sign in</Link>
            <Link className="btn btn-primary" href="/auth/register" onClick={() => setMobileOpen(false)}>Join free</Link>
          </>
        )}
      </nav>
    </header>
  );
}