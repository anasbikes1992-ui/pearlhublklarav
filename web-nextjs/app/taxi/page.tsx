'use client';

import Link from 'next/link';
import { useState } from 'react';
import { useAuth } from '../../components/auth-context';
import { DEMO_DRIVERS, type DemoDriver } from '../../lib/taxi-demo-data';

const CITIES = [
  'Colombo', 'Kandy', 'Galle', 'Negombo', 'Matara',
  'Jaffna', 'Trincomalee', 'Batticaloa', 'Nuwara Eliya',
  'Anuradhapura', 'Polonnaruwa', 'Badulla', 'Ratnapura',
  'Kurunegala', 'Puttalam', 'Vavuniya',
];

const CITY_COORDS: Record<string, [number, number]> = {
  'Colombo':      [6.9271,  79.8612],
  'Kandy':        [7.2906,  80.6337],
  'Galle':        [6.0535,  80.2210],
  'Negombo':      [7.2081,  79.8358],
  'Matara':       [5.9549,  80.5550],
  'Jaffna':       [9.6615,  80.0255],
  'Trincomalee':  [8.5874,  81.2152],
  'Batticaloa':   [7.7172,  81.6956],
  'Nuwara Eliya': [6.9597,  80.7891],
  'Anuradhapura': [8.3114,  80.4037],
  'Polonnaruwa':  [7.9403,  81.0188],
  'Badulla':      [6.9934,  81.0550],
  'Ratnapura':    [6.6828,  80.3992],
  'Kurunegala':   [7.4818,  80.3609],
  'Puttalam':     [8.0362,  79.8284],
  'Vavuniya':     [8.7514,  80.4977],
};

type BookedRide = {
  id: string;
  pickup_city: string;
  dropoff_city: string;
  fare_estimate: number;
  driver: DemoDriver;
  status: string;
  created_at: string;
};

function estimateFare(from: string, to: string): number {
  if (from === to) return 500;
  const fromCoords = CITY_COORDS[from] ?? [6.9271, 79.8612];
  const toCoords   = CITY_COORDS[to]   ?? [7.2906, 80.6337];
  const dlat = fromCoords[0] - toCoords[0];
  const dlng = fromCoords[1] - toCoords[1];
  const distKm = Math.sqrt(dlat * dlat + dlng * dlng) * 111;
  return Math.round((350 + distKm * 35) / 50) * 50;
}

export default function TaxiPage() {
  const { user } = useAuth();
  const [pickup,         setPickup]         = useState('Colombo');
  const [dropoff,        setDropoff]        = useState('Kandy');
  const [selectedDriver, setSelectedDriver] = useState<string>(DEMO_DRIVERS[0].id);
  const [submitting,     setSubmitting]     = useState(false);
  const [error,          setError]          = useState('');
  const [bookedRide,     setBookedRide]     = useState<BookedRide | null>(null);

  const fare = estimateFare(pickup, dropoff);

  const handleBook = async (e: React.FormEvent) => {
    e.preventDefault();
    if (pickup === dropoff) {
      setError('Pickup and dropoff locations must be different.');
      return;
    }
    setError('');
    setSubmitting(true);
    try {
      const fromCoords = CITY_COORDS[pickup] ?? [6.9271, 79.8612];
      const toCoords   = CITY_COORDS[dropoff] ?? [7.2906, 80.6337];
      const res = await fetch('/api/taxi-rides', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
          pickup_city:       pickup,
          dropoff_city:      dropoff,
          pickup_latitude:   fromCoords[0],
          pickup_longitude:  fromCoords[1],
          dropoff_latitude:  toCoords[0],
          dropoff_longitude: toCoords[1],
          driver_id:         selectedDriver,
          fare_estimate:     fare,
        }),
      });
      const data = (await res.json()) as { data?: BookedRide; message?: string };
      if (!res.ok) throw new Error(data.message ?? 'Booking failed');
      const ride = data.data;
      if (!ride) throw new Error('Invalid response from server');
      if (!ride.driver) {
        ride.driver = DEMO_DRIVERS.find((d) => d.id === selectedDriver) ?? DEMO_DRIVERS[0];
      }
      setBookedRide(ride);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Booking failed');
    } finally {
      setSubmitting(false);
    }
  };

  /* ── Confirmed booking view ── */
  if (bookedRide) {
    return (
      <main className="page-shell taxi-page">
        <section className="taxi-confirmation">
          <div className="taxi-confirm-icon">🚖</div>
          <h1>Ride Confirmed!</h1>
          <p className="taxi-confirm-sub">Your Pearl Taxi is on the way. Sit tight!</p>

          <div className="taxi-confirm-card">
            <div className="taxi-route-display">
              <span className="taxi-route-pin">📍</span>
              <div className="taxi-route-cities">
                <span>{bookedRide.pickup_city}</span>
                <span className="taxi-route-arrow">→</span>
                <span>{bookedRide.dropoff_city}</span>
              </div>
              <span className="taxi-route-pin">🏁</span>
            </div>

            <div className="taxi-confirm-meta">
              <div className="taxi-confirm-row">
                <span className="taxi-confirm-label">Ride ID</span>
                <span className="taxi-confirm-val taxi-confirm-val--mono">{bookedRide.id.slice(0, 20)}…</span>
              </div>
              <div className="taxi-confirm-row">
                <span className="taxi-confirm-label">Fare estimate</span>
                <span className="taxi-confirm-val taxi-confirm-val--fare">LKR {bookedRide.fare_estimate.toLocaleString()}</span>
              </div>
              <div className="taxi-confirm-row">
                <span className="taxi-confirm-label">Status</span>
                <span className="taxi-status-badge">{bookedRide.status}</span>
              </div>
            </div>

            <div className="taxi-driver-summary">
              <div className="taxi-driver-avatar">{bookedRide.driver.avatar}</div>
              <div className="taxi-driver-summary__info">
                <p className="taxi-driver-name">{bookedRide.driver.name}</p>
                <p className="taxi-driver-detail">{bookedRide.driver.vehicle} · {bookedRide.driver.plate}</p>
                <p className="taxi-driver-detail">ETA: {bookedRide.driver.eta}</p>
              </div>
            </div>
          </div>

          <button className="btn btn-secondary" onClick={() => setBookedRide(null)}>
            Book another ride
          </button>
        </section>
      </main>
    );
  }

  /* ── Auth loading state ── */
  if (user === undefined) {
    return (
      <main className="page-shell taxi-page">
        <div className="taxi-loading">Loading…</div>
      </main>
    );
  }

  /* ── Unauthenticated view ── */
  if (user === null) {
    return (
      <main className="page-shell taxi-page">
        <section className="taxi-hero">
          <div className="hero-badge">🚕 Pearl Taxi</div>
          <h1>Book a certified Pearl driver</h1>
          <p>Sign in or create an account to book a ride across Sri Lanka.</p>
          <div className="hero-actions">
            <Link className="btn btn-primary" href="/auth/login">Sign in</Link>
            <Link className="btn btn-secondary" href="/auth/register">Create account</Link>
          </div>
        </section>
      </main>
    );
  }

  /* ── Authenticated booking view ── */
  return (
    <main className="page-shell taxi-page">
      <section className="taxi-hero">
        <div className="hero-badge">🚕 Pearl Taxi</div>
        <h1>Book your ride</h1>
        <p>
          Welcome, <strong>{user.full_name ?? user.name}</strong>. Pearl-verified drivers,
          live fare estimate, island-wide coverage.
        </p>
      </section>

      <div className="taxi-layout">
        {/* ── Trip form ── */}
        <div className="taxi-form-panel">
          <h2 className="taxi-panel-heading">Trip details</h2>
          <form className="taxi-form" onSubmit={handleBook}>
            {error && <div className="auth-error">{error}</div>}

            <div className="auth-field">
              <label htmlFor="pickup">Pickup city</label>
              <select id="pickup" value={pickup} onChange={(e) => setPickup(e.target.value)}>
                {CITIES.map((c) => <option key={c}>{c}</option>)}
              </select>
            </div>

            <div className="auth-field">
              <label htmlFor="dropoff">Dropoff city</label>
              <select id="dropoff" value={dropoff} onChange={(e) => setDropoff(e.target.value)}>
                {CITIES.map((c) => <option key={c}>{c}</option>)}
              </select>
            </div>

            <div className="taxi-fare-estimate">
              <span>Estimated fare</span>
              <strong>LKR {fare.toLocaleString()}</strong>
            </div>

            <button className="btn btn-primary btn-full" type="submit" disabled={submitting}>
              {submitting ? 'Booking…' : 'Book ride'}
            </button>
          </form>
        </div>

        {/* ── Driver selection ── */}
        <div className="taxi-drivers-panel">
          <h2 className="taxi-panel-heading">Available drivers</h2>
          <div className="taxi-drivers-list">
            {DEMO_DRIVERS.map((driver) => (
              <button
                key={driver.id}
                type="button"
                className={`taxi-driver-card${selectedDriver === driver.id ? ' taxi-driver-card--selected' : ''}`}
                onClick={() => setSelectedDriver(driver.id)}
              >
                <div className="taxi-driver-card__avatar">{driver.avatar}</div>
                <div className="taxi-driver-card__info">
                  <p className="taxi-driver-card__name">{driver.name}</p>
                  <p className="taxi-driver-card__vehicle">{driver.vehicle}</p>
                  <p className="taxi-driver-card__plate">{driver.plate}</p>
                </div>
                <div className="taxi-driver-card__stats">
                  <div className="taxi-driver-card__rating">★ {driver.rating}</div>
                  <div className="taxi-driver-card__eta">{driver.eta}</div>
                  <div className="taxi-driver-card__trips">{driver.trips} trips</div>
                </div>
              </button>
            ))}
          </div>
        </div>
      </div>
    </main>
  );
}

