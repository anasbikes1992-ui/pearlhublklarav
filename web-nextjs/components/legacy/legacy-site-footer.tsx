import Link from 'next/link';

const year = new Date().getFullYear();

const footerColumns = [
  {
    title: 'Marketplace',
    links: [
      { href: '/property', label: 'Properties' },
      { href: '/stays', label: 'Stays' },
      { href: '/vehicles', label: 'Vehicles' },
      { href: '/events', label: 'Events' },
      { href: '/sme', label: 'Local Businesses' },
      { href: '/taxi', label: 'Pearl Taxi' }
    ]
  },
  {
    title: 'Discover',
    links: [
      { href: '/stays/ella', label: 'Ella' },
      { href: '/stays/galle', label: 'Galle' },
      { href: '/stays/kandy', label: 'Kandy' },
      { href: '/search', label: 'Search all' }
    ]
  },
  {
    title: 'Platform',
    links: [
      { href: '/auth/login', label: 'Sign in' },
      { href: '/auth/register', label: 'Join free' },
      { href: '/taxi', label: 'Download app' }
    ]
  }
];

export default function LegacySiteFooter() {
  return (
    <footer className="site-footer">
      <div className="site-footer__inner">
        <div className="site-footer__brand">
          <div className="site-footer__brand-mark">
            <span className="brand-mark__orb" aria-hidden="true" />
            <span className="site-footer__brand-name">PearlHub Pro</span>
          </div>
          <p className="site-footer__tagline">
            Sri Lanka&apos;s most trusted luxury marketplace — premium
            properties, curated stays, vehicles, events, local artisans, and
            Pearl Taxi, all in one place.
          </p>
          <div className="site-footer__badges">
            <span className="footer-badge">Pearl verified</span>
            <span className="footer-badge">Secure payments</span>
            <span className="footer-badge">24 h support</span>
          </div>
        </div>

        <div className="site-footer__links">
          {footerColumns.map((column) => (
            <section key={column.title}>
              <h3>{column.title}</h3>
              {column.links.map((link) => (
                <Link key={link.href} href={link.href}>
                  {link.label}
                </Link>
              ))}
            </section>
          ))}
        </div>
      </div>

      <div className="site-footer__meta">
        <span>© {year} PearlHub Pro. All rights reserved.</span>
        <span>Designed for Sri Lanka luxury travel &amp; real estate.</span>
      </div>
    </footer>
  );
}