import type { Metadata } from 'next';
import { AuthProvider } from '../components/auth-context';
import SiteFooter from '../components/site-footer';
import SiteHeader from '../components/site-header';
import './styles.css';

export const metadata: Metadata = {
  title: 'PearlHub Pro - Sri Lanka Luxury Marketplace',
  description: 'Sri Lanka multi-vertical luxury marketplace for properties, stays, vehicles, events, and experiences'
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>
        <AuthProvider>
          <div className="site-shell">
            <SiteHeader />
            {children}
            <SiteFooter />
          </div>
        </AuthProvider>
      </body>
    </html>
  );
}
