import type { Metadata } from 'next';
import './styles.css';

export const metadata: Metadata = {
  title: 'PearlHub Pro',
  description: 'Sri Lanka multi-vertical luxury marketplace'
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
