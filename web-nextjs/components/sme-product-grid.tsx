import type { ListingItem } from '../lib/api';

type SmeProductGridProps = {
  listing: ListingItem;
};

const sampleVariants = [
  { sku: 'S-001', color: 'Black', size: 'Small', price: 2500 },
  { sku: 'S-002', color: 'Blue', size: 'Medium', price: 2750 },
  { sku: 'S-003', color: 'Gold', size: 'Large', price: 2900 },
];

export default function SmeProductGrid({ listing }: SmeProductGridProps) {
  return (
    <section className="sme-products">
      <h2>Product catalog</h2>
      <div className="sme-products__grid">
        {sampleVariants.map((variant) => (
          <article key={variant.sku} className="sme-products__card">
            <p className="sme-products__sku">{variant.sku}</p>
            <h3>{listing.title} - {variant.size}</h3>
            <p>Color: {variant.color}</p>
            <p>Price: {listing.currency} {variant.price.toLocaleString('en-LK')}</p>
            <span className="listing-card__badge">In stock</span>
          </article>
        ))}
      </div>
    </section>
  );
}
