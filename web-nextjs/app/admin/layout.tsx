'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { useAuth } from '../../components/auth-context';

const NAV_ITEMS = [
  { href: '/admin', label: 'Dashboard', icon: '📊' },
  { href: '/admin/god-view', label: 'God View', icon: '👁️' },
  { href: '/admin/configs', label: 'Configs', icon: '⚙️' },
  { href: '/admin/revenue', label: 'Revenue', icon: '📈' },
  { href: '/admin/referrals', label: 'Referrals', icon: '🎁' },
  { href: '/admin/users', label: 'Users', icon: '👥' },
  { href: '/admin/listings', label: 'Listings', icon: '🏘️' },
  { href: '/admin/bookings', label: 'Bookings', icon: '📅' },
  { href: '/admin/social', label: 'Social', icon: '💬' },
  { href: '/admin/payments', label: 'Payments', icon: '💳' },
  { href: '/', label: 'Back to site', icon: '← ' },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { user } = useAuth();
  const router = useRouter();
  const pathname = usePathname();
  const checked = user !== undefined && !!user && (user as { role?: string }).role === 'admin';

  useEffect(() => {
    if (user !== undefined && (!user || (user as { role?: string }).role !== 'admin')) {
      router.replace('/');
    }
  }, [user, router]);

  if (!checked) {
    return (
      <div className="admin-access-denied">
        <p style={{ fontSize: '2rem' }}>🔒</p>
        <h2>Checking permissions...</h2>
        <p>One moment while we verify your access level.</p>
      </div>
    );
  }

  const isActive = (href: string) =>
    href === '/admin' ? pathname === '/admin' : pathname.startsWith(href);

  return (
    <div className="admin-layout">
      <aside className="admin-sidebar">
        <div className="admin-sidebar__logo">✦ PearlHub Admin</div>
        <nav className="admin-nav">
          {NAV_ITEMS.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={isActive(item.href) ? 'active' : ''}
            >
              <span aria-hidden="true">{item.icon}</span>
              {item.label}
            </Link>
          ))}
        </nav>
      </aside>
      <main className="admin-main">{children}</main>
    </div>
  );
}
