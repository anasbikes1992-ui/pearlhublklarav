import Link from 'next/link';

const footerColumns = [
  {
    title: 'Marketplace',
    links: [
      { href: '/property', label: 'Property grid' },
      { href: '/stays', label: 'Stay discovery' },
      { href: '/property/colombo-07-luxury-villa', label: 'Spotlight detail' }
    ]
  },
  {
    title: 'Platform',
    links: [
      { href: '/', label: 'Home experience' },
      { href: '/stays/ella', label: 'Ella city route' },
      { href: '/stays/galle', label: 'Galle city route' }
    ]
  }
];

export default function SiteFooter() {
  return (
    <footer className="site-footer">
      <div className="site-footer__inner">
        <div className="site-footer__brand">
          <p className="eyebrow">PearlHub Pro</p>
          <h2>Marketplace surfaces, booking-ready structure, and a premium island travel aesthetic.</h2>
          <p>
            This web shell mirrors the reference product direction while staying aligned with the Laravel backend that already
            provides listings, search, bookings, payments, and escrow foundations.
          </p>
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
        <span>Built with Next.js 16 and a Laravel API base.</span>
        <span>Designed for Sri Lanka luxury property and stays discovery.</span>
      </div>
    </footer>
  );
}