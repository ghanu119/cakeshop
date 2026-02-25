<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];
        $base = url('/');

        $urls[] = ['loc' => $base, 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('contact.index'), 'priority' => '0.8', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'daily'];

        Product::active()->get(['slug', 'updated_at'])->each(function (Product $p) use (&$urls) {
            $urls[] = [
                'loc' => route('products.show', $p->slug),
                'lastmod' => $p->updated_at?->toW3cString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        });

        Category::active()->get(['slug', 'updated_at'])->each(function (Category $c) use (&$urls) {
            $urls[] = [
                'loc' => route('categories.show', $c->slug),
                'lastmod' => $c->updated_at?->toW3cString(),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url>';
            $xml .= '<loc>' . htmlspecialchars($u['loc']) . '</loc>';
            if (! empty($u['lastmod'])) {
                $xml .= '<lastmod>' . $u['lastmod'] . '</lastmod>';
            }
            $xml .= '<priority>' . ($u['priority'] ?? '0.5') . '</priority>';
            $xml .= '<changefreq>' . ($u['changefreq'] ?? 'weekly') . '</changefreq>';
            $xml .= '</url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Charset' => 'UTF-8',
        ]);
    }
}
