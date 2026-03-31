import Link from 'next/link';
import type { ListingItem } from '../lib/api';

type ListingCardProps = {
  item: ListingItem;
  href: string;
  variant?: 'property' | 'stay';
};

const VERTICAL_LABEL: Record<string, string> = {
  property: 'Signature property',
  stay: 'Curated stay',
  vehicle: 'Premium vehicle',
  event: 'Live experience',
  sme: 'Local business'
};

const VERTICAL_EMOJI: Record<string, string> = {
  property: '🏛️',
  stay: '🏝️',
  vehicle: '🚗',
  event: '🎭',
  sme: '🛍️'
};

const formatPrice = (item: ListingItem) => {
  const value = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(item.price);
  if (item.vertical === 'stay') return `LKR ${value} / night`;
  if (item.vertical === 'vehicle') return `LKR ${value} / day`;
  if (item.vertical === 'event') return `LKR ${value} / ticket`;
  return `LKR ${value}`;
};

const renderStars = (rating: number) => {
  const full = Math.floor(rating);
  const half = rating % 1 >= 0.5;
  const stars = [];
  for (let i = 0; i < 5; i++) {
    if (i < full) stars.push('★');
    else if (i === full && half) stars.push('½');
    else stars.push('☆');
  }
  return stars.join('');
};

export default function ListingCard({ item, href }: ListingCardProps) {
  const emoji = VERTICAL_EMOJI[item.vertical] ?? '🏛️';

  return (
    <article className="listing-card">
      <div className={`listing-card__media listing-card__media--${item.accent}`}>
        <span className="listing-card__emoji" aria-hidden="true">{emoji}</span>
        <span className="listing-card__badge">{item.badge}</span>
        <div className="listing-card__media-copy">
          <p>{VERTICAL_LABEL[item.vertical] ?? 'Listing'}</p>
          <strong>{item.location}</strong>
        </div>
      </div>

      <div className="listing-card__body">
        <div className="listing-card__meta-row">
          <span className="listing-card__category">{item.category}</span>
          {item.rating ? (
            <span className="listing-card__rating" title={`${item.rating} out of 5`}>
              <span className="star-text" aria-hidden="true">{renderStars(item.rating)}</span>
              <span className="rating-num">{item.rating.toFixed(1)}</span>
            </span>
          ) : null}
        </div>
        <h3>{item.title}</h3>
        <p>{item.description}</p>
        <div className="listing-card__footer">
          <strong className="listing-card__price">{formatPrice(item)}</strong>
          <Link href={href} className="listing-card__cta">
            View details →
          </Link>
        </div>
      </div>
    </article>
  );
}