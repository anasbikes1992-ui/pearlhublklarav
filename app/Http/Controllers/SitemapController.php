<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = Cache::remember('sitemap_xml', 3600, function () {
            return $this->generateSitemap();
        });

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function generateSitemap(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages
        $staticPages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/listings', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/about', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/how-it-works', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $xml .= $this->buildUrlEntry($page['url'], now(), $page['changefreq'], $page['priority']);
        }

        // Vertical pages
        $verticals = ['property', 'stay', 'vehicle', 'event', 'sme', 'experience'];
        foreach ($verticals as $vertical) {
            $xml .= $this->buildUrlEntry("/listings/{$vertical}", now(), 'daily', '0.8');
        }

        // Active listings
        $listings = Listing::query()
            ->where('status', 'published')
            ->where('is_hidden', false)
            ->select('id', 'slug', 'vertical', 'updated_at')
            ->get();

        foreach ($listings as $listing) {
            $url = "/listings/{$listing->vertical}/{$listing->slug}";
            $xml .= $this->buildUrlEntry($url, $listing->updated_at, 'weekly', '0.7');
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function buildUrlEntry(string $path, Carbon $lastMod, string $changefreq, string $priority): string
    {
        $fullUrl = config('app.frontend_url', 'https://pearlhub.lk') . $path;
        
        return "  <url>\n" .
               "    <loc>{$fullUrl}</loc>\n" .
               "    <lastmod>{$lastMod->toDateString()}</lastmod>\n" .
               "    <changefreq>{$changefreq}</changefreq>\n" .
               "    <priority>{$priority}</priority>\n" .
               "  </url>\n";
    }

    public function robots(): Response
    {
        $content = "User-agent: *\n" .
                   "Disallow: /admin/\n" .
                   "Disallow: /api/\n" .
                   "Disallow: /checkout/\n" .
                   "Allow: /listings/\n" .
                   "\n" .
                   "Sitemap: " . config('app.frontend_url', 'https://pearlhub.lk') . "/sitemap.xml\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
