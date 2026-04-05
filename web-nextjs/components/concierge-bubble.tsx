'use client';

import { useState } from 'react';

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

export default function ConciergeBubble() {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [reply, setReply] = useState('Ask me for recommendations, translations, or booking help.');
  const [loading, setLoading] = useState(false);

  const askConcierge = async () => {
    if (!query.trim()) {
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(`${API_BASE}/concierge/chat`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          query,
          context: { source: 'nextjs-bubble' },
        }),
      });

      if (!response.ok) {
        throw new Error('Concierge request failed');
      }

      const payload = await response.json();
      setReply(payload?.data?.reply ?? 'I could not generate a response right now.');
    } catch {
      setReply('Concierge is temporarily unavailable. Please try again in a few moments.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="concierge-shell">
      {open && (
        <div className="concierge-card">
          <h3>Pearl Concierge AI</h3>
          <p>{reply}</p>
          <div className="concierge-row">
            <input
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              placeholder="Ask about stays, vehicles, SME products, or translation"
            />
            <button onClick={askConcierge} disabled={loading}>
              {loading ? '...' : 'Send'}
            </button>
          </div>
        </div>
      )}
      <button className="concierge-bubble" onClick={() => setOpen((value) => !value)} aria-label="Open AI concierge">
        AI
      </button>
    </div>
  );
}
