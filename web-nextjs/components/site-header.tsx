'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useState } from 'react';
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
  const [mobileOpen, setMobileOpen] = useState(false);

  const handleLogout = () => {
    logout();
    router.push('/');
  };

  const isActive = (href: string) =>
    pathname === href || (href !== '/' && pathname.startsWith(href));

  return (
    <header className="market-header">
      <div className="market-header__bar">
        <p>GRABBER MOBILITY SOLUTIONS · Registered in Sri Lanka · Premium multi-vertical marketplace</p>
      </div>
      <div className="market-header__inner">
        <Link className="market-brand" href="/">
          <span className="market-brand__orb" aria-hidden="true">✦</span>
          <span>
            <strong>Pearl Hub</strong>
            <small>Sri Lanka premium</small>
          </span>
        </Link>

        <nav className="market-nav" aria-label="Primary navigation">
          {verticals.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={isActive(item.href) ? 'active' : ''}
            >
              {item.label}
            </Link>
          ))}
        </nav>

        <div className="market-header__actions">
          <Link href="/backup" className="market-header__backup">
            Current UI backup
          </Link>
          <Link href="/search" className="market-search-btn" aria-label="Search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
          </Link>
          {user ? (
            <>
              <span className="market-status-pill">{(user.full_name ?? user.name)?.split(' ')[0]}</span>
              <button className="market-btn market-btn--ghost market-btn--sm" onClick={handleLogout}>
                Sign out
              </button>
            </>
          ) : (
            <>
              <Link className="market-btn market-btn--ghost market-btn--sm" href="/auth/login">
                Sign in
              </Link>
              <Link className="market-btn market-btn--primary market-btn--sm" href="/auth/register">
                Join free
              </Link>
            </>
          )}
          <button
            className="market-mobile-toggle"
            aria-label="Open menu"
            onClick={() => setMobileOpen(true)}
          >
            <span /><span /><span />
          </button>
        </div>
      </div>

      <nav className={`market-mobile-nav${mobileOpen ? ' open' : ''}`} aria-label="Mobile navigation">
        <button className="market-mobile-nav__close" onClick={() => setMobileOpen(false)} aria-label="Close menu">
          ✕
        </button>
        {verticals.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={isActive(item.href) ? 'active' : ''}
            onClick={() => setMobileOpen(false)}
          >
            {item.label}
          </Link>
        ))}
        <Link href="/backup" onClick={() => setMobileOpen(false)}>
          Current UI backup
        </Link>
        {user ? (
          <button className="market-btn market-btn--ghost" onClick={() => { handleLogout(); setMobileOpen(false); }}>
            Sign out
          </button>
        ) : (
          <>
            <Link className="market-btn market-btn--ghost" href="/auth/login" onClick={() => setMobileOpen(false)}>Sign in</Link>
            <Link className="market-btn market-btn--primary" href="/auth/register" onClick={() => setMobileOpen(false)}>Join free</Link>
          </>
        )}
      </nav>
    </header>
  );
}