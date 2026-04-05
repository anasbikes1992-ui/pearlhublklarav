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
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? 'https://web-nextjs-sage-pi.vercel.app'),
  title: {
    default: 'PearlHub Pro - Sri Lanka Luxury Marketplace',
    template: '%s | PearlHub Pro',
  },
  description: 'Sri Lanka multi-vertical luxury marketplace for properties, stays, vehicles, events, and experiences.',
  alternates: {
    canonical: '/',
  },
  openGraph: {
    type: 'website',
    locale: 'en_LK',
    url: '/',
    siteName: 'PearlHub Pro',
    title: 'PearlHub Pro - Sri Lanka Luxury Marketplace',
    description: 'Discover premium stays, properties, transport, experiences, and SME services in Sri Lanka.',
  },
  twitter: {
    card: 'summary_large_image',
    title: 'PearlHub Pro - Sri Lanka Luxury Marketplace',
    description: 'Discover premium stays, properties, transport, experiences, and SME services in Sri Lanka.',
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-image-preview': 'large',
      'max-snippet': -1,
      'max-video-preview': -1,
    },
  },
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
