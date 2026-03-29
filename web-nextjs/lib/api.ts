const BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

export async function getListingsByCity(city: string, vertical: string) {
  const response = await fetch(`${BASE_URL}/search?q=${encodeURIComponent(city)}&vertical=${vertical}`, {
    next: { revalidate: 300 }
  });

  if (!response.ok) {
    return [];
  }

  const payload = await response.json();
  return payload.data ?? [];
}

export async function getListingBySlug(slug: string) {
  return {
    title: slug.replace(/-/g, ' '),
    description: 'Property detail fetched via API integration in next phase.',
    currency: 'LKR',
    price: '0.00'
  };
}
