<?php

// Enforce Indian Standard Time (IST – UTC+5:30) system-wide
if (date_default_timezone_get() !== 'Asia/Kolkata') {
    date_default_timezone_set('Asia/Kolkata');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../..'));
}

if (!function_exists('ist_now')) {
    /**
     * Get current Indian Standard Time (IST) datetime string (Y-m-d H:i:s)
     */
    function ist_now(): string {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('ist_date')) {
    /**
     * Format any timestamp or date string into Indian Standard Time (IST)
     */
    function ist_date(string $format = 'Y-m-d H:i:s', $timestamp = null): string {
        if ($timestamp === null) {
            $timestamp = time();
        } elseif (is_string($timestamp) && !is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        return date($format, (int)$timestamp);
    }
}

if (!function_exists('get_base_path')) {
    /**
     * Returns the relative base URL path (e.g. '/Farmlelo' or '')
     */
    function get_base_path(): string {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return '';
        }
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($base === '/' || $base === '.' || strpos($base, ':') !== false) {
            return '';
        }
        $base = rtrim($base, '/');
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
            if (stripos($uri, $base) === 0) {
                return substr($uri, 0, strlen($base));
            }
        }
        return $base;
    }
}

if (!function_exists('url')) {
    /**
     * Generate an application URL relative to the app base path
     */
    function url(string $path = ''): string {
        $path = '/' . ltrim($path, '/');
        $base = get_base_path();
        return $base . $path;
    }
}

if (!function_exists('base_url')) {
    /**
     * Generate a full or relative base URL
     */
    function base_url(string $path = ''): string {
        return url($path);
    }
}

if (!function_exists('absolute_url')) {
    /**
     * Generate a fully qualified absolute URL (scheme + host + base_path + path)
     * Essential for canonical links, sitemaps, Open Graph, and search engines.
     */
    function absolute_url(string $path = ''): string {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Check if APP_URL is defined in environment
        $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
        $isLocalHost = false;
        if (!empty($_SERVER['HTTP_HOST']) && (
            str_contains($_SERVER['HTTP_HOST'], 'localhost') ||
            str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')
        )) {
            $isLocalHost = true;
        }

        // In production with a set APP_URL, prefer configured canonical root
        if (!empty($appUrl) && !$isLocalHost && !str_contains($appUrl, 'localhost')) {
            $root = rtrim($appUrl, '/');
            $cleanPath = '/' . ltrim($path, '/');
            return $root . $cleanPath;
        }

        // Dynamically detect scheme and host
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $scheme = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'stayora.com';

        $relUrl = url($path);
        $relUrl = '/' . ltrim($relUrl, '/');
        return rtrim($scheme . $host, '/') . $relUrl;
    }
}

if (!function_exists('site_canonical_url')) {
    /**
     * Get the clean canonical URL of current page without tracking query parameters
     */
    function site_canonical_url(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'] ?? '/';
        
        // Allowed query parameters for canonical representation
        $allowedParams = ['id', 'page'];
        $canonicalQuery = [];
        
        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            foreach ($allowedParams as $param) {
                if (isset($queryParams[$param]) && $queryParams[$param] !== '') {
                    $canonicalQuery[$param] = $queryParams[$param];
                }
            }
        }
        
        $base = get_base_path();
        if ($base !== '' && stripos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        
        $finalPath = ltrim($path, '/');
        if (!empty($canonicalQuery)) {
            $finalPath .= '?' . http_build_query($canonicalQuery);
        }
        
        return absolute_url($finalPath);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL
     */
    function asset(string $path = ''): string {
        $path = '/' . ltrim($path, '/');
        return get_base_path() . $path;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to an application route and terminate execution
     */
    function redirect(string $path): void {
        $target = (preg_match('#^https?://#i', $path)) ? $path : url($path);
        header("Location: {$target}");
        exit();
    }
}

if (!function_exists('farmhouse_upload_path')) {
    /**
     * Absolute filesystem path to the shared farmhouse image upload directory
     */
    function farmhouse_upload_path(): string {
        $path = dirname(__DIR__, 2) . '/assets/images/uploads/farmhouses/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }
}

if (!function_exists('farmhouse_img_url')) {
    /**
     * Resolve a farmhouse image path/filename to a consistent, accessible public URL
     * across Admin, Owner, and Public panels.
     */
    function farmhouse_img_url(?string $img, string $fallback = 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80'): string {
        if (empty($img)) {
            return $fallback;
        }

        $img = trim($img);

        // If it's already an absolute web URL or data URL
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '//') || str_starts_with($img, 'data:')) {
            return $img;
        }

        // If it already starts with asset prefix
        if (str_starts_with($img, 'assets/')) {
            return asset($img);
        }

        $clean = ltrim($img, '/');
        $basename = basename($clean);
        $projectRoot = dirname(__DIR__, 2);

        // 1. Check in assets/images/uploads/farmhouses/
        if (file_exists($projectRoot . '/assets/images/uploads/farmhouses/' . $basename)) {
            return asset('assets/images/uploads/farmhouses/' . $basename);
        }

        // 2. Check if clean path matches directly under assets/images/uploads/
        if (file_exists($projectRoot . '/assets/images/uploads/' . $clean)) {
            return asset('assets/images/uploads/' . $clean);
        }

        // 3. Check in assets/images/uploads/ by basename
        if (file_exists($projectRoot . '/assets/images/uploads/' . $basename)) {
            return asset('assets/images/uploads/' . $basename);
        }

        // 4. Check in assets/images/ (e.g. legacy seed images img1.jpg)
        if (file_exists($projectRoot . '/assets/images/' . $basename)) {
            return asset('assets/images/' . $basename);
        }

        // 5. Default shared upload path
        return asset('assets/images/uploads/farmhouses/' . $basename);
    }
}

if (!function_exists('resolveFarmImgHome')) {
    function resolveFarmImgHome(?string $img): string {
        return farmhouse_img_url($img);
    }
}

if (!function_exists('resolveFarmImgFH')) {
    function resolveFarmImgFH(?string $img): string {
        return farmhouse_img_url($img);
    }
}

if (!function_exists('resolveFarmImg')) {
    function resolveFarmImg(?string $img): string {
        return farmhouse_img_url($img);
    }
}

if (!function_exists('build_google_map_embed_url')) {
    /**
     * Convert any Google Maps link, iframe, coordinates, or location string
     * into a working Google Maps Embed URL for <iframe> display.
     */
    function build_google_map_embed_url(?string $mapUrl, string $fallback = ''): string {
        $raw = trim((string)$mapUrl);

        // 1. If user pasted an iframe embed code, extract src
        if (preg_match('/src=["\']([^"\']+)["\']/i', $raw, $matches)) {
            $raw = $matches[1];
        }

        // 2. If it's already an embed URL
        if (stripos($raw, 'google.com/maps/embed') !== false) {
            return $raw;
        }

        // 3. If empty, use fallback query
        if (empty($raw)) {
            $q = trim($fallback);
            return !empty($q) 
                ? 'https://maps.google.com/maps?q=' . urlencode($q) . '&t=&z=14&ie=UTF8&iwloc=&output=embed'
                : 'https://maps.google.com/maps?q=India&t=&z=10&ie=UTF8&iwloc=&output=embed';
        }

        // 4. Check for coordinates in place or view URL (e.g. @24.5854,73.7125)
        if (preg_match('/@([0-9.-]+,[0-9.-]+)/', $raw, $matches)) {
            return 'https://maps.google.com/maps?q=' . urlencode($matches[1]) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
        }

        // 5. Check if 'q=' or 'query=' parameter is present
        $parsedQuery = parse_url($raw, PHP_URL_QUERY);
        if ($parsedQuery) {
            parse_str($parsedQuery, $queryParams);
            if (!empty($queryParams['q'])) {
                return 'https://maps.google.com/maps?q=' . urlencode($queryParams['q']) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
            }
            if (!empty($queryParams['query'])) {
                return 'https://maps.google.com/maps?q=' . urlencode($queryParams['query']) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
            }
        }

        // 6. Check for place name in URL path (e.g. /maps/place/Place+Name/)
        if (preg_match('#/maps/place/([^/@?]+)#', $raw, $matches)) {
            $placeName = urldecode(str_replace('+', ' ', $matches[1]));
            return 'https://maps.google.com/maps?q=' . urlencode($placeName) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
        }

        // 7. If raw coordinate format "24.5854, 73.7125"
        if (preg_match('/^-?[0-9]{1,3}\.[0-9]+,\s*-?[0-9]{1,3}\.[0-9]+$/', $raw)) {
            return 'https://maps.google.com/maps?q=' . urlencode($raw) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
        }

        // 8. If short link (e.g. maps.app.goo.gl or goo.gl/maps)
        if (stripos($raw, 'maps.app.goo.gl') !== false || stripos($raw, 'goo.gl/maps') !== false) {
            $resolved = null;
            if (function_exists('curl_init')) {
                $ch = curl_init($raw);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                if ($effectiveUrl && $effectiveUrl !== $raw) {
                    $resolved = $effectiveUrl;
                }
            }
            if ($resolved) {
                if (preg_match('/@([0-9.-]+,[0-9.-]+)/', $resolved, $m)) {
                    return 'https://maps.google.com/maps?q=' . urlencode($m[1]) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
                }
                if (preg_match('#/maps/place/([^/@?]+)#', $resolved, $m)) {
                    $placeName = urldecode(str_replace('+', ' ', $m[1]));
                    return 'https://maps.google.com/maps?q=' . urlencode($placeName) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
                }
            }
        }

        // 9. If non-empty query / address / general query fallback
        if (!empty($raw) && !str_starts_with($raw, 'http')) {
            return 'https://maps.google.com/maps?q=' . urlencode($raw) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
        }

        $fallbackQ = !empty($fallback) ? $fallback : 'India';
        return 'https://maps.google.com/maps?q=' . urlencode($fallbackQ) . '&t=&z=14&ie=UTF8&iwloc=&output=embed';
    }
}

if (!function_exists('build_google_map_direct_url')) {
    /**
     * Get a direct clickable Google Maps link for navigation & opening in app.
     */
    function build_google_map_direct_url(?string $mapUrl, string $fallback = ''): string {
        $raw = trim((string)$mapUrl);

        if (preg_match('/src=["\']([^"\']+)["\']/i', $raw, $matches)) {
            $raw = $matches[1];
        }

        if (!empty($raw) && (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://'))) {
            return $raw;
        }

        $query = !empty($raw) ? $raw : (!empty($fallback) ? $fallback : 'India');
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($query);
    }
}

if (!function_exists('get_stayora_rule_icon')) {
    /**
     * Context-aware Material Symbols icon for property house rules
     */
    function get_stayora_rule_icon(?string $ruleName, ?string $existingIcon = ''): string {
        $icon = trim((string)$existingIcon);
        if (!empty($icon) && $icon !== 'gavel') {
            return $icon;
        }

        $name = strtolower(trim((string)$ruleName));
        if (str_contains($name, 'alcohol') || str_contains($name, 'liquor') || str_contains($name, 'drink') || str_contains($name, 'bar') || str_contains($name, 'beer') || str_contains($name, 'wine')) {
            return 'sports_bar';
        }
        if (str_contains($name, 'music') || str_contains($name, 'sound') || str_contains($name, 'speaker') || str_contains($name, 'dj') || str_contains($name, 'noise') || str_contains($name, 'volume')) {
            return 'volume_up';
        }
        if (str_contains($name, 'pet') || str_contains($name, 'dog') || str_contains($name, 'cat') || str_contains($name, 'animal')) {
            return 'pets';
        }
        if (str_contains($name, 'pool') || str_contains($name, 'swim')) {
            return 'pool';
        }
        if (str_contains($name, 'part') || str_contains($name, 'event') || str_contains($name, 'gather') || str_contains($name, 'celebrat')) {
            return 'celebration';
        }
        if (str_contains($name, 'smok') || str_contains($name, 'cigar') || str_contains($name, 'hookah') || str_contains($name, 'tobacco')) {
            return 'smoking_rooms';
        }
        if (str_contains($name, 'cook') || str_contains($name, 'kitchen') || str_contains($name, 'food') || str_contains($name, 'meal') || str_contains($name, 'non-veg') || str_contains($name, 'veg')) {
            return 'restaurant';
        }
        if (str_contains($name, 'id') || str_contains($name, 'identity') || str_contains($name, 'proof') || str_contains($name, 'gov') || str_contains($name, 'document')) {
            return 'badge';
        }
        if (str_contains($name, 'quiet') || str_contains($name, 'silent') || str_contains($name, 'night') || str_contains($name, 'sleep')) {
            return 'bedtime';
        }
        if (str_contains($name, 'clean') || str_contains($name, 'trash') || str_contains($name, 'waste') || str_contains($name, 'litter') || str_contains($name, 'garbage') || str_contains($name, 'dustbin')) {
            return 'cleaning_services';
        }
        if (str_contains($name, 'visitor') || str_contains($name, 'guest') || str_contains($name, 'bachelor') || str_contains($name, 'couple') || str_contains($name, 'family')) {
            return 'group';
        }
        if (str_contains($name, 'check') || str_contains($name, 'time') || str_contains($name, 'hour')) {
            return 'schedule';
        }
        if (str_contains($name, 'fire') || str_contains($name, 'bonfire') || str_contains($name, 'flame') || str_contains($name, 'cracker')) {
            return 'local_fire_department';
        }
        if (str_contains($name, 'park') || str_contains($name, 'car') || str_contains($name, 'vehicle')) {
            return 'local_parking';
        }

        return 'verified_user';
    }
}

if (!function_exists('get_stayora_amenity_icon')) {
    /**
     * Resolves appropriate Material Symbols icon for property amenities
     */
    function get_stayora_amenity_icon(?string $name, ?string $category = '', ?string $existingIcon = ''): string {
        $icon = trim((string)$existingIcon);
        if (!empty($icon) && $icon !== 'check') {
            return $icon;
        }

        $n = strtolower(trim((string)$name));
        $c = strtolower(trim((string)$category));

        if (str_contains($n, 'pool') || str_contains($n, 'swim')) return 'pool';
        if (str_contains($n, 'air') || str_contains($n, 'ac') || str_contains($n, 'cool')) return 'ac_unit';
        if (str_contains($n, 'wifi') || str_contains($n, 'internet')) return 'wifi';
        if (str_contains($n, 'chef') || str_contains($n, 'kitchen') || str_contains($n, 'cook') || str_contains($n, 'dining')) return 'restaurant';
        if (str_contains($n, 'pet') || str_contains($n, 'dog') || str_contains($n, 'animal')) return 'pets';
        if (str_contains($n, 'garden') || str_contains($n, 'lawn') || str_contains($n, 'yard') || str_contains($n, 'tree')) return 'yard';
        if (str_contains($n, 'park') || str_contains($n, 'garage')) return 'local_parking';
        if (str_contains($n, 'bbq') || str_contains($n, 'grill') || str_contains($n, 'bonfire') || str_contains($n, 'fire')) return 'outdoor_grill';
        if (str_contains($n, 'jacuzzi') || str_contains($n, 'tub') || str_contains($n, 'spa')) return 'hot_tub';
        if (str_contains($n, 'game') || str_contains($n, 'billiard') || str_contains($n, 'snooker') || str_contains($n, 'play')) return 'sports_esports';
        if (str_contains($n, 'cinema') || str_contains($n, 'tv') || str_contains($n, 'theatre') || str_contains($n, 'movie')) return 'tv';
        if (str_contains($n, 'music') || str_contains($n, 'speaker') || str_contains($n, 'audio') || str_contains($n, 'sound')) return 'speaker';
        if (str_contains($n, 'security') || str_contains($n, 'cctv') || str_contains($n, 'guard') || str_contains($n, 'butler')) return 'shield';
        if (str_contains($n, 'helipad') || str_contains($n, 'valet')) return 'local_taxi';
        if (str_contains($n, 'bed') || str_contains($n, 'bedroom') || str_contains($n, 'mattress')) return 'bed';
        if (str_contains($n, 'bath') || str_contains($n, 'washroom') || str_contains($n, 'toilet')) return 'shower';
        if (str_contains($n, 'view') || str_contains($n, 'mountain') || str_contains($n, 'lake')) return 'landscape';
        if (str_contains($n, 'trail') || str_contains($n, 'orchard') || str_contains($n, 'nature')) return 'park';

        if ($c === 'comfort') return 'ac_unit';
        if ($c === 'dining') return 'restaurant';
        if ($c === 'leisure') return 'pool';
        if ($c === 'outdoor') return 'nature_people';
        if ($c === 'entertainment') return 'sports_esports';
        if ($c === 'wellness') return 'spa';
        if ($c === 'technology') return 'wifi';
        if ($c === 'service') return 'room_service';
        if ($c === 'bedroom') return 'bed';

        return 'check_circle';
    }
}

if (!function_exists('encrypt_id')) {
    /**
     * Encrypt an entity ID for secure URL generation and link sharing
     */
    function encrypt_id($id): string {
        return \App\Helpers\CryptoHelper::encryptId($id);
    }
}

if (!function_exists('decrypt_id')) {
    /**
     * Decrypt a secure URL token into an integer ID
     */
    function decrypt_id($token): int {
        return \App\Helpers\CryptoHelper::decryptId($token);
    }
}