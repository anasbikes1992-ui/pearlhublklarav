'use client';

import { useEffect } from 'react';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Keep client-side visibility for unexpected render/runtime failures.
    console.error('Unhandled app error:', error);
  }, [error]);

  return (
    <main className="auth-page auth-page--gold" style={{ minHeight: '100vh', alignItems: 'center' }}>
      <section className="auth-card auth-card--gold" style={{ maxWidth: 560 }}>
        <div className="auth-gold-kicker">System notice</div>
        <h1 style={{ margin: '0 0 8px', color: '#f8f6ef' }}>Something went wrong</h1>
        <p style={{ margin: '0 0 16px', color: 'rgba(255, 255, 255, 0.85)' }}>
          We could not complete that request. Please retry.
        </p>
        <button className="btn-gold" onClick={reset}>
          Try again
        </button>
      </section>
    </main>
  );
}
