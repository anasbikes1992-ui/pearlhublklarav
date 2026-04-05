import type { Metadata } from 'next';
import { Cormorant_Garamond, Inter } from 'next/font/google';
import { AuthProvider } from '../components/auth-context';
import ConciergeBubble from '../components/concierge-bubble';
import SiteChrome from '../components/site-chrome';
import './styles.css';

const displayFont = Cormorant_Garamond({
  subsets: ['latin'],
  variable: '--font-display',
  weight: ['500', '600', '700']
});

const bodyFont = Inter({
  subsets: ['latin'],
  variable: '--font-body',
  weight: ['400', '500', '600', '700']
});

export const metadata: Metadata = {
  title: 'PearlHub Pro - Sri Lanka Luxury Marketplace',
  description: 'Sri Lanka multi-vertical luxury marketplace for properties, stays, vehicles, events, and experiences'
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${displayFont.variable} ${bodyFont.variable}`}>
      <body>
        <AuthProvider>
          <SiteChrome>{children}</SiteChrome>
          <ConciergeBubble />
        </AuthProvider>
      </body>
    </html>
  );
}
