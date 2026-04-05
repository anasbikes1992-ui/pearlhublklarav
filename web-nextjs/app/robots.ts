import type { MetadataRoute } from 'next';

const site = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://web-nextjs-sage-pi.vercel.app';

export default function robots(): MetadataRoute.Robots {
  return {
    rules: {
      userAgent: '*',
      allow: ['/', '/property', '/stays', '/vehicles', '/events', '/experiences', '/sme', '/taxi', '/social', '/search'],
      disallow: ['/admin', '/api', '/auth/logout'],
    },
    sitemap: `${site}/sitemap.xml`,
    host: site,
  };
}
