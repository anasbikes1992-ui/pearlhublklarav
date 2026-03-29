import Link from 'next/link';

export default function TaxiPage() {
  return (
    <main className="page-shell catalog-page">
      <section className="page-intro page-intro--taxi">
        <p className="eyebrow">Pearl Taxi</p>
        <h1>Real-time ride matching across Sri Lanka — built for luxury travel.</h1>
        <p>
          Book a certified driver instantly or schedule rides in advance. Live GPS tracking, fare transparency, and Pearl-verified
          drivers only — available via the PearlHub Customer app.
        </p>
      </section>

      <section className="taxi-how-it-works">
        <div className="section-heading">
          <div>
            <p className="eyebrow">How it works</p>
            <h2>From tap to arrival in minutes.</h2>
          </div>
        </div>
        <div className="taxi-steps">
          <div className="taxi-step">
            <span className="taxi-step__num">01</span>
            <h3>Open the app</h3>
            <p>Launch PearlHub Customer, tap &ldquo;Pearl Taxi,&rdquo; and share your pickup location.</p>
          </div>
          <div className="taxi-step">
            <span className="taxi-step__num">02</span>
            <h3>Instant driver match</h3>
            <p>Our matching engine finds the nearest Pearl-verified driver in real time via Laravel Reverb WebSockets.</p>
          </div>
          <div className="taxi-step">
            <span className="taxi-step__num">03</span>
            <h3>Track live</h3>
            <p>Watch your driver on the map. Get ETA updates and arrival alerts — even on spotty Sri Lankan data connections.</p>
          </div>
          <div className="taxi-step">
            <span className="taxi-step__num">04</span>
            <h3>Pay securely</h3>
            <p>PayHere or WebXPay integration. Rate your journey and earn PearlPoints redeemable on stays and properties.</p>
          </div>
        </div>
      </section>

      <section className="taxi-cta-block">
        <h2>Download the PearlHub Customer app</h2>
        <p>Pearl Taxi is available exclusively in the mobile app. Web booking is coming soon.</p>
        <div className="hero-cta-row">
          <Link className="btn btn-primary" href="/auth/register">
            Create account
          </Link>
          <Link className="btn btn-secondary" href="/">
            Back to marketplace
          </Link>
        </div>
      </section>
    </main>
  );
}
