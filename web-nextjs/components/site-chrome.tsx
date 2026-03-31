'use client';

import { usePathname } from 'next/navigation';
import type { ReactNode } from 'react';
import LegacySiteFooter from './legacy/legacy-site-footer';
import LegacySiteHeader from './legacy/legacy-site-header';
import SiteFooter from './site-footer';
import SiteHeader from './site-header';

export default function SiteChrome({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const useLegacyChrome = pathname.startsWith('/backup');

  return (
    <div className="site-shell">
      {useLegacyChrome ? <LegacySiteHeader /> : <SiteHeader />}
      {children}
      {useLegacyChrome ? <LegacySiteFooter /> : <SiteFooter />}
    </div>
  );
}