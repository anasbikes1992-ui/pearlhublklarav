import type { MetadataRoute } from 'next';

const site = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://web-nextjs-sage-pi.vercel.app';

export default function sitemap(): MetadataRoute.Sitemap {
  const routes = [
    '/',
    '/property',
    '/stays',
    '/vehicles',
    '/events',
    '/experiences',
    '/sme',
    '/taxi',
    '/social',
    '/search',
    '/auth/login',
    '/auth/register',
    '/backup',
  ];

  return routes.map((route) => ({
    url: `${site}${route}`,
    lastModified: new Date(),
    changeFrequency: route === '/' ? 'daily' : 'weekly',
    priority: route === '/' ? 1 : 0.7,
  }));
}
