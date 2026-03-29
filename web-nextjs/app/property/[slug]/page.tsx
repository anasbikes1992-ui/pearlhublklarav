import { getListingBySlug } from '../../../lib/api';

export const revalidate = 300;

export default async function PropertyDetailPage({ params }: { params: { slug: string } }) {
  const listing = await getListingBySlug(params.slug);

  return (
    <main className="page-shell">
      <h1>{listing.title}</h1>
      <p>{listing.description}</p>
      <p>Price: {listing.currency} {listing.price}</p>
    </main>
  );
}
