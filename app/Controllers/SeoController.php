<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\CryptoHelper;
use Exception;

class SeoController {

    /**
     * Generate dynamic XML Sitemap for search engine indexation
     */
    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');

        $staticPages = [
            ['path' => '',                    'priority' => '1.0', 'changefreq' => 'daily'],
            ['path' => 'home',                'priority' => '1.0', 'changefreq' => 'daily'],
            ['path' => 'farmhouses',          'priority' => '0.9', 'changefreq' => 'daily'],
            ['path' => 'why-choose-us',       'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => 'about',               'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => 'contact',             'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => 'list_your_farm',      'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => 'terms_conditions',    'priority' => '0.5', 'changefreq' => 'yearly'],
            ['path' => 'privacy',             'priority' => '0.5', 'changefreq' => 'yearly'],
            ['path' => 'cancellation_policy', 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['path' => 'cookie_policy',       'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        // Top city destination landing URLs for organic local search
        $topCities = ['Indore', 'Surat', 'Delhi', 'Hyderabad', 'Daman', 'Ahmedabad', 'Mumbai'];
        $cityPages = [];
        foreach ($topCities as $c) {
            $cityPages[] = [
                'path'       => 'farmhouses?location=' . urlencode($c),
                'priority'   => '0.85',
                'changefreq' => 'weekly'
            ];
        }

        // Fetch active approved farmhouses from database
        $farmhouses = [];
        try {
            $db = Database::connect();
            $query = "SELECT f.id, f.title, f.location, f.category, f.created_at,
                             (SELECT image_path FROM farmhouse_images WHERE farmhouse_id = f.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
                      FROM farmhouses f
                      WHERE f.status = 'active' AND f.admin_approval_status = 'approved'
                      ORDER BY f.id DESC";
            $res = $db->query($query);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $farmhouses[] = $row;
                }
            }
        } catch (Exception $e) {
            error_log('Sitemap DB fetch error: ' . $e->getMessage());
        }

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml[] = '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        // 1. Core static pages
        $today = date('Y-m-d');
        foreach ($staticPages as $page) {
            $loc = absolute_url($page['path']);
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($loc) . '</loc>';
            $xml[] = '    <lastmod>' . $today . '</lastmod>';
            $xml[] = '    <changefreq>' . $page['changefreq'] . '</changefreq>';
            $xml[] = '    <priority>' . $page['priority'] . '</priority>';
            $xml[] = '  </url>';
        }

        // 2. City filter pages
        foreach ($cityPages as $cp) {
            $loc = absolute_url($cp['path']);
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($loc) . '</loc>';
            $xml[] = '    <lastmod>' . $today . '</lastmod>';
            $xml[] = '    <changefreq>' . $cp['changefreq'] . '</changefreq>';
            $xml[] = '    <priority>' . $cp['priority'] . '</priority>';
            $xml[] = '  </url>';
        }

        // 3. Dynamic active farmhouses with image tags
        foreach ($farmhouses as $fh) {
            $encId = CryptoHelper::encrypt($fh['id']);
            $loc = absolute_url('farmhouse_details?id=' . urlencode($encId));
            $lastmod = !empty($fh['created_at']) ? date('Y-m-d', strtotime($fh['created_at'])) : $today;

            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($loc) . '</loc>';
            $xml[] = '    <lastmod>' . $lastmod . '</lastmod>';
            $xml[] = '    <changefreq>weekly</changefreq>';
            $xml[] = '    <priority>0.9</priority>';

            if (!empty($fh['primary_image'])) {
                $imgUrl = farmhouse_img_url($fh['primary_image']);
                if (str_starts_with($imgUrl, '/')) {
                    $imgUrl = absolute_url(ltrim($imgUrl, '/'));
                }
                $xml[] = '    <image:image>';
                $xml[] = '      <image:loc>' . htmlspecialchars($imgUrl) . '</image:loc>';
                $xml[] = '      <image:title>' . htmlspecialchars($fh['title'] . ' in ' . $fh['location']) . '</image:title>';
                $xml[] = '    </image:image>';
            }

            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        echo implode("\n", $xml);
        exit();
    }

    /**
     * Generate dynamic robots.txt output
     */
    public function robots(): void {
        header('Content-Type: text/plain; charset=utf-8');

        $sitemapUrl = absolute_url('sitemap.xml');

        echo "# ==================================================\n";
        echo "# Stayora Robots.txt - Search Engine Crawl Directives\n";
        echo "# ==================================================\n\n";
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Allow: /assets/\n";
        echo "Allow: /farmhouses\n";
        echo "Allow: /farmhouse_details\n";
        echo "Allow: /about\n";
        echo "Allow: /contact\n";
        echo "Allow: /why-choose-us\n";
        echo "Allow: /list_your_farm\n\n";

        echo "# Disallow administrative, owner and private user routes\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /owner/\n";
        echo "Disallow: /user/\n";
        echo "Disallow: /database/\n";
        echo "Disallow: /storage/\n";
        echo "Disallow: /vendor/\n";
        echo "Disallow: /app/\n";
        echo "Disallow: /login\n";
        echo "Disallow: /register\n";
        echo "Disallow: /forgot-password\n";
        echo "Disallow: /reset-password\n";
        echo "Disallow: /wishlist\n";
        echo "Disallow: /*?booking=*\n";
        echo "Disallow: /*?booking_error=*\n";
        echo "Disallow: /*?ref=*\n\n";

        echo "# XML Sitemap Location for Google, Bing, Yahoo, Yandex\n";
        echo "Sitemap: {$sitemapUrl}\n";
        exit();
    }
}
