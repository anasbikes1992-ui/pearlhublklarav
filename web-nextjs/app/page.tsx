import Link from 'next/link';

export default function HomePage() {
  return (
    <main className="page-shell">
      <h1>PearlHub Pro Web</h1>
      <p>Next.js 15 App Router scaffold with ISR-focused listing routes.</p>
      <ul>
        <li><Link href="/stays/ella">Stays in Ella</Link></li>
        <li><Link href="/property/colombo-07-luxury-villa">Property Spotlight</Link></li>
      </ul>
    </main>
  );
}
