export default function Loading() {
  return (
    <main className="auth-page auth-page--gold" style={{ minHeight: '100vh', alignItems: 'center' }}>
      <section className="auth-card auth-card--gold" style={{ maxWidth: 560 }}>
        <div className="auth-gold-kicker">Loading</div>
        <h1 style={{ margin: '0 0 8px', color: '#f8f6ef' }}>Preparing your experience</h1>
        <p style={{ margin: 0, color: 'rgba(255, 255, 255, 0.85)' }}>
          Please wait a moment while we load the latest data.
        </p>
      </section>
    </main>
  );
}
