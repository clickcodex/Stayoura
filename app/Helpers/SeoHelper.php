<?php
namespace App\Helpers;

use App\Helpers\SiteHelper;

class SeoHelper {

    /**
     * Get site settings cache from SiteHelper
     */
    public static function getSiteSettings(): array {
        return SiteHelper::getSettings() ?? [];
    }

    /**
     * Normalize and detect current route identifier
     */
    public static function getCurrentRoute(): string {
        $base = get_base_path();
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        if ($base !== '' && stripos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $uri = trim($uri, '/');
        if (stripos($uri, 'index.php') === 0) {
            $uri = trim(substr($uri, strlen('index.php')), '/');
        }

        return $uri;
    }

    /**
     * Resolve default metadata based on the current route and site settings
     */
    public static function resolveMetadata(array $overrides = []): array {
        $settings = self::getSiteSettings();
        $route    = self::getCurrentRoute();

        $siteName = !empty($settings['site_name']) ? $settings['site_name'] : 'Stayora';
        $tagline  = !empty($settings['tagline'])   ? $settings['tagline']   : 'Luxury Farmhouses & Private Villa Escapes';

        // Base defaults
        $meta = [
            'title'            => "{$siteName} | Luxury Farmhouses, Private Pool Villas & Weekend Escapes in India",
            'description'      => !empty($settings['meta_description']) 
                                    ? $settings['meta_description'] 
                                    : "Book verified luxury farmhouses, private pool villas, party estates & holiday retreats in Indore, Surat, Delhi NCR, Hyderabad & Mumbai. Best price guaranteed on {$siteName}.",
            'keywords'         => !empty($settings['meta_keywords'])
                                    ? $settings['meta_keywords']
                                    : "luxury farmhouses, private pool villas, farm stays india, weekend getaways, farmhouse for rent, party villas indore, farmhouses near surat, delhi ncr farmhouses, vacation rentals, stayora",
            'author'           => $siteName,
            'canonical'        => site_canonical_url(),
            'robots'           => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'og_type'          => 'website',
            'og_image'         => absolute_url('assets/images/uploads/luxury_pool_hero.jpg'),
            'og_image_alt'     => "{$siteName} - Luxury Farmhouses and Private Pool Villas",
            'twitter_card'     => 'summary_large_image',
            'site_name'        => $siteName,
            'schema'           => [],
            'breadcrumbs'      => []
        ];

        // Route-specific intelligent defaults
        switch ($route) {
            case '':
            case 'home':
                $meta['title'] = "{$siteName} | Luxury Farmhouses, Private Pool Villas & Stays in India";
                $meta['description'] = "Discover and book handpicked luxury farmhouses and private pool villas across Indore, Surat, Delhi NCR, Hyderabad, and Mumbai. Verified amenities & instant support on {$siteName}.";
                $meta['og_type'] = 'website';
                break;

            case 'farmhouses':
                $location = trim($_GET['location'] ?? '');
                $search   = trim($_GET['search'] ?? '');
                if (!empty($location)) {
                    $meta['title'] = "Top Luxury Farmhouses in " . htmlspecialchars($location) . " | {$siteName}";
                    $meta['description'] = "Explore verified luxury farmhouses with private pools, gardens, and party spaces in " . htmlspecialchars($location) . ". Check availability and book online on {$siteName}.";
                } elseif (!empty($search)) {
                    $meta['title'] = '"' . htmlspecialchars($search) . '" Farmhouses & Villas | ' . $siteName;
                    $meta['description'] = "Search results for " . htmlspecialchars($search) . " farmhouses and private villas across India on {$siteName}.";
                } else {
                    $meta['title'] = "Explore Verified Luxury Farmhouses & Pool Villas | {$siteName}";
                    $meta['description'] = "Browse India's premier collection of private farmhouses, holiday villas, and event spaces for weekend parties, family getaways, and corporate stays.";
                }
                $meta['breadcrumbs'] = [
                    ['name' => 'Home', 'url' => absolute_url('home')],
                    ['name' => 'Farmhouses', 'url' => absolute_url('farmhouses')]
                ];
                break;

            case 'about':
                $meta['title'] = "About Us | {$siteName} - India's Trusted Farmhouse Booking Platform";
                $meta['description'] = "Learn about {$siteName}'s mission to connect travelers and event hosts with extraordinary private farmhouses, pool estates, and rural stays across India.";
                $meta['breadcrumbs'] = [
                    ['name' => 'Home', 'url' => absolute_url('home')],
                    ['name' => 'About Us', 'url' => absolute_url('about')]
                ];
                break;

            case 'contact':
                $meta['title'] = "Contact Customer Support & Farm Inquiries | {$siteName}";
                $meta['description'] = "Need help finding a farmhouse or listing your property? Contact the {$siteName} customer care team via phone, WhatsApp, or email.";
                $meta['breadcrumbs'] = [
                    ['name' => 'Home', 'url' => absolute_url('home')],
                    ['name' => 'Contact Us', 'url' => absolute_url('contact')]
                ];
                break;

            case 'why-choose-us':
                $meta['title'] = "Why Choose {$siteName} - 100% Verified Farmhouses & Stays";
                $meta['description'] = "Discover why thousands trust {$siteName} for weekend retreats and celebrations: 100% verified properties, direct owner rates, and 24/7 dedicated support.";
                $meta['breadcrumbs'] = [
                    ['name' => 'Home', 'url' => absolute_url('home')],
                    ['name' => 'Why Choose Us', 'url' => absolute_url('why-choose-us')]
                ];
                break;

            case 'list_your_farm':
                $meta['title'] = "List Your Farmhouse on {$siteName} | Earn Steady Rental Income";
                $meta['description'] = "Partner with {$siteName} to list your private farmhouse, villa, or resort. Reach thousands of verified guests and maximize your property earnings.";
                $meta['breadcrumbs'] = [
                    ['name' => 'Home', 'url' => absolute_url('home')],
                    ['name' => 'List Your Farm', 'url' => absolute_url('list_your_farm')]
                ];
                break;

            case 'terms_conditions':
                $meta['title'] = "Terms & Conditions | {$siteName}";
                $meta['description'] = "Read the official terms and conditions for booking and listing farmhouses on the {$siteName} platform.";
                break;

            case 'privacy':
                $meta['title'] = "Privacy Policy | {$siteName}";
                $meta['description'] = "Read our commitment to protecting your personal data, privacy, and secure booking transactions on {$siteName}.";
                break;

            case 'cancellation_policy':
                $meta['title'] = "Cancellation & Refund Policy | {$siteName}";
                $meta['description'] = "Understand {$siteName}'s transparent booking cancellation, rescheduling, and refund rules.";
                break;

            case 'cookie_policy':
                $meta['title'] = "Cookie Policy | {$siteName}";
                $meta['description'] = "Learn how {$siteName} uses cookies to improve your farmhouse browsing and booking experience.";
                break;

            // Auth & User account pages should NOT be indexed by search engines
            case 'login':
            case 'register':
            case 'forgot-password':
            case 'reset-password':
            case 'wishlist':
            case 'my-wishlist':
            case 'user/wishlist':
            case 'dashboard':
            case 'user/dashboard':
                $meta['robots'] = 'noindex, nofollow, noarchive';
                break;
        }

        // Apply any manual overrides passed from view/controller
        foreach ($overrides as $k => $v) {
            if ($v !== null && $v !== '') {
                $meta[$k] = $v;
            }
        }

        return $meta;
    }

    /**
     * Render all HTML Head SEO Meta Tags
     */
    public static function renderMetaTags(array $metadata): void {
        $title       = htmlspecialchars($metadata['title'] ?? 'Stayora');
        $description = htmlspecialchars($metadata['description'] ?? '');
        $keywords    = htmlspecialchars($metadata['keywords'] ?? '');
        $author      = htmlspecialchars($metadata['author'] ?? 'Stayora');
        $canonical   = htmlspecialchars($metadata['canonical'] ?? absolute_url());
        $robots      = htmlspecialchars($metadata['robots'] ?? 'index, follow');
        $ogType      = htmlspecialchars($metadata['og_type'] ?? 'website');
        $ogImage     = htmlspecialchars($metadata['og_image'] ?? absolute_url('assets/images/uploads/luxury_pool_hero.jpg'));
        $ogImageAlt  = htmlspecialchars($metadata['og_image_alt'] ?? $title);
        $twitterCard = htmlspecialchars($metadata['twitter_card'] ?? 'summary_large_image');
        $siteName    = htmlspecialchars($metadata['site_name'] ?? 'Stayora');

        $settings = self::getSiteSettings();
        $googleVerify = $settings['google_site_verification'] ?? (getenv('GOOGLE_SITE_VERIFICATION') ?: ($_ENV['GOOGLE_SITE_VERIFICATION'] ?? ''));
        $bingVerify   = $settings['bing_site_verification']   ?? (getenv('BING_SITE_VERIFICATION')   ?: ($_ENV['BING_SITE_VERIFICATION']   ?? ''));

        ?>
  <!-- Primary SEO Meta Tags -->
  <title><?= $title ?></title>
  <meta name="description" content="<?= $description ?>"/>
<?php if (!empty($keywords)): ?>
  <meta name="keywords" content="<?= $keywords ?>"/>
<?php endif; ?>
  <meta name="author" content="<?= $author ?>"/>
  <meta name="robots" content="<?= $robots ?>"/>
  <link rel="canonical" href="<?= $canonical ?>"/>

  <!-- Geographic / Local SEO Meta Tags (India Targeting) -->
  <meta name="geo.region" content="IN"/>
  <meta name="geo.placename" content="India"/>
  <meta name="rating" content="general"/>
  <meta name="theme-color" content="#16A5DE"/>
  <meta name="mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-title" content="<?= $siteName ?>"/>
  <meta name="apple-mobile-web-app-status-bar-style" content="default"/>

  <!-- Open Graph Protocol / Facebook / WhatsApp / LinkedIn -->
  <meta property="og:type" content="<?= $ogType ?>"/>
  <meta property="og:site_name" content="<?= $siteName ?>"/>
  <meta property="og:url" content="<?= $canonical ?>"/>
  <meta property="og:title" content="<?= $title ?>"/>
  <meta property="og:description" content="<?= $description ?>"/>
  <meta property="og:image" content="<?= $ogImage ?>"/>
  <meta property="og:image:alt" content="<?= $ogImageAlt ?>"/>
  <meta property="og:locale" content="en_IN"/>

  <!-- Twitter (X) Card Meta Tags -->
  <meta name="twitter:card" content="<?= $twitterCard ?>"/>
  <meta name="twitter:title" content="<?= $title ?>"/>
  <meta name="twitter:description" content="<?= $description ?>"/>
  <meta name="twitter:image" content="<?= $ogImage ?>"/>
  <meta name="twitter:image:alt" content="<?= $ogImageAlt ?>"/>

<?php if (!empty($googleVerify)): ?>
  <!-- Google Search Console Verification -->
  <meta name="google-site-verification" content="<?= htmlspecialchars($googleVerify) ?>"/>
<?php endif; ?>

<?php if (!empty($bingVerify)): ?>
  <!-- Bing Webmaster Tools Verification -->
  <meta name="msvalidate.01" content="<?= htmlspecialchars($bingVerify) ?>"/>
<?php endif; ?>
<?php
    }

    /**
     * Render Schema.org JSON-LD Structured Data
     */
    public static function renderSchemaJsonLd(array $metadata): void {
        $settings = self::getSiteSettings();
        $siteName = !empty($settings['site_name']) ? $settings['site_name'] : 'Stayora';
        $siteUrl  = absolute_url();
        $logoUrl  = !empty($settings['logo']) 
            ? (str_starts_with($settings['logo'], 'http') ? $settings['logo'] : absolute_url('assets/images/uploads/settings/' . $settings['logo']))
            : absolute_url('assets/images/logo.webp');

        $phone = !empty($settings['mobile_number']) ? $settings['mobile_number'] : '+91-9111666415';
        $email = !empty($settings['email']) ? $settings['email'] : 'contact@stayora.com';

        $socialLinks = array_values(array_filter([
            $settings['instagram_link'] ?? 'https://instagram.com/farmleloofficial',
            $settings['facebook_link']  ?? '',
            $settings['youtube_link']   ?? '',
            $settings['twitter_link']   ?? '',
        ]));

        // 1. Organization & LocalBusiness Schema
        $orgSchema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Organization',
            '@id'           => $siteUrl . '#organization',
            'name'          => $siteName,
            'url'           => $siteUrl,
            'logo'          => [
                '@type'  => 'ImageObject',
                'url'    => $logoUrl,
                'caption'=> $siteName
            ],
            'contactPoint'  => [
                '@type'             => 'ContactPoint',
                'telephone'         => $phone,
                'contactType'       => 'customer service',
                'areaServed'        => 'IN',
                'availableLanguage' => ['English', 'Hindi']
            ]
        ];
        if (!empty($socialLinks)) {
            $orgSchema['sameAs'] = $socialLinks;
        }

        // 2. WebSite Schema with Sitelinks SearchBox
        $websiteSchema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            '@id'             => $siteUrl . '#website',
            'url'             => $siteUrl,
            'name'            => $siteName,
            'description'     => $metadata['description'] ?? '',
            'publisher'       => [
                '@id' => $siteUrl . '#organization'
            ],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => absolute_url('farmhouses?search={search_term_string}')
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ];

        $schemas = [$orgSchema, $websiteSchema];

        // 3. BreadcrumbList Schema (if breadcrumbs defined)
        if (!empty($metadata['breadcrumbs']) && is_array($metadata['breadcrumbs'])) {
            $itemList = [];
            $pos = 1;
            foreach ($metadata['breadcrumbs'] as $bc) {
                $itemList[] = [
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $bc['name'] ?? '',
                    'item'     => $bc['url'] ?? ''
                ];
            }
            $schemas[] = [
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $itemList
            ];
        }

        // 4. Custom Schema (e.g. VacationRental / LodgingBusiness / ItemList)
        if (!empty($metadata['custom_schema'])) {
            if (isset($metadata['custom_schema']['@context'])) {
                $schemas[] = $metadata['custom_schema'];
            } elseif (is_array($metadata['custom_schema'])) {
                foreach ($metadata['custom_schema'] as $sch) {
                    $schemas[] = $sch;
                }
            }
        }

        ?>
  <!-- Schema.org Structured Data (JSON-LD) for Search Engines -->
<?php foreach ($schemas as $sch): ?>
  <script type="application/ld+json">
<?= json_encode($sch, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
<?php endforeach; ?>
<?php
    }
}
