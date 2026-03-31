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
      { href: '/experiences', label: 'Experiences & Tours' },
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
      { href: '/taxi', label: 'Download app' },
      { href: '/backup', label: 'Current UI backup' }
    ]
  },
  {
    title: 'Legal',
    links: [
      { href: '/search', label: 'Privacy-ready search' },
      { href: '/events', label: 'QR ticket flows' },
      { href: '/property', label: 'Verified listings' },
      { href: '/sme', label: 'For business' }
    ]
  }
];

export default function SiteFooter() {
  return (
    <footer className="market-footer">
      <div className="market-footer__inner">
        <div className="market-footer__brand">
          <div className="market-footer__mark">
            <span className="market-brand__orb" aria-hidden="true">✦</span>
            <span className="market-footer__brand-name">Pearl Hub Sri Lanka</span>
          </div>
          <p className="market-footer__tagline">
            Sri Lanka&apos;s premier multi-vertical marketplace. Properties, stays, vehicles,
            events, experiences, taxi, and local SMEs in one premium ecosystem.
          </p>
          <div className="market-footer__payments">
            <p>Accepted payments</p>
            <div>
              <span>PayHere</span>
              <span>LankaPay</span>
              <span>WebXPay</span>
              <span>Dialog Genie</span>
            </div>
          </div>
        </div>

        <div className="market-footer__links">
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

      <div className="market-footer__meta">
        <span>© {year} Grabber Mobility Solutions (Pvt) Ltd · Pearl Hub. All rights reserved.</span>
        <span>No. 1, De Mel Place, Colombo 03, Sri Lanka</span>
      </div>
    </footer>
  );
}