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
  sme: 'Local business',
};

const formatPrice = (item: ListingItem) => {
  const value = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(item.price);
  if (item.vertical === 'stay') return `${item.currency} ${value} / night`;
  if (item.vertical === 'vehicle') return `${item.currency} ${value} / day`;
  if (item.vertical === 'event') return `${item.currency} ${value} / ticket`;
  return `${item.currency} ${value}`;
};

export default function ListingCard({ item, href }: ListingCardProps) {
  return (
    <article className="listing-card">
      <div className={`listing-card__media listing-card__media--${item.accent}`}>
        <span className="listing-card__badge">{item.badge}</span>
        <div className="listing-card__media-copy">
          <p>{VERTICAL_LABEL[item.vertical] ?? 'Listing'}</p>
          <strong>{item.location}</strong>
        </div>
      </div>

      <div className="listing-card__body">
        <div className="listing-card__meta-row">
          <span>{item.category}</span>
          {item.rating ? <span>{item.rating.toFixed(1)} rating</span> : null}
        </div>
        <h3>{item.title}</h3>
        <p>{item.description}</p>
        <div className="listing-card__footer">
          <strong>{formatPrice(item)}</strong>
          <Link href={href}>View details</Link>
        </div>
      </div>
    </article>
  );
}