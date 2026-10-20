<?php
use App\Helpers\CryptoHelper;

// ── High-Converting SEO Meta ──
$pageTitle       = "Stayora | Luxury Farmhouses, Private Pool Villas & Weekend Escapes in India";
$pageDescription = "Book verified luxury farmhouses & private pool villas across Indore, Surat, Delhi NCR, Hyderabad & Mumbai. Direct owner prices, verified amenities & instant support.";
$pageKeywords    = "farmhouse booking, private pool villas, luxury farmhouses indore, weekend villas surat, delhi ncr farmhouses, holiday homes india, stayora";
$canonicalUrl    = absolute_url();

include __DIR__ . "/Includes/header.php";
/**
 * Views/home.php
 * Dynamic, high-conversion luxury home page — rendered by HomeController::index()
 *
 * Variables available from controller:
 *  $farmhouses          – array of active farmhouse rows
 *  $availableLocations  – array of distinct location strings
 *  $availableAmenities  – array of ['id'=>…,'name'=>…]
 *  $filters             – current active filter values
 *  $wishlistedIds       – flat int array of farmhouse IDs in user's wishlist
 */

// ── Group farmhouses by location for the city sections ──
$byCity = [];
foreach ($farmhouses as $fh) {
    $city = trim($fh['location'] ?? 'Other');
    $byCity[$city][] = $fh;
}

// Predefined top city definitions with descriptive badges
$citySections = [
    'Indore'    => ['label' => 'Near Indore',    'desc' => 'Explore luxury farmhouses & party villas around Indore for weekend stays.'],
    'Surat'     => ['label' => 'Near Surat',     'desc' => 'Trusted private pool farmhouses near Surat for parties and family time.'],
    'Delhi'     => ['label' => 'Near Delhi NCR', 'desc' => 'Top-rated luxury estates near Delhi, Gurgaon & Noida for weekend getaways.'],
    'Hyderabad' => ['label' => 'Near Hyderabad', 'desc' => 'Scenic farmhouses near Hyderabad for celebrations, weddings & corporate retreats.'],
    'Daman'     => ['label' => 'Near Daman',     'desc' => 'Beachside farmhouses & villas near Daman for unforgettable celebrations.'],
    'Ahmedabad' => ['label' => 'Near Ahmedabad', 'desc' => 'Verified farmhouses near Ahmedabad & Gandhinagar for events and stays.'],
    'Mumbai'    => ['label' => 'Near Mumbai',    'desc' => 'Premium luxury villas in Lonavala, Alibaug & Karjat for quick escapes.'],
];

// Active filter helpers
$activeSearch    = htmlspecialchars($filters['search']   ?? '');
$activeLocation  = htmlspecialchars($filters['location'] ?? '');
$activeMaxPrice  = (int)($filters['max_price'] ?? 0);
$activeSort      = htmlspecialchars($filters['sort']     ?? '');
$activeAmenities = $filters['amenities'] ?? [];

$isFiltered = $activeSearch || $activeLocation || $activeMaxPrice || $activeSort || !empty($activeAmenities);
$isLoggedIn = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
$wishlistedIds = $wishlistedIds ?? [];

if (!function_exists('fmtPrice')) {
    function fmtPrice(float $price): string {
        return '₹' . number_format($price, 0, '.', ',');
    }
}

if (!function_exists('resolveFarmImg')) {
    function resolveFarmImg(?string $img): string {
        return farmhouse_img_url($img, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=75');
    }
}

// ── Close-up Enhanced Property Card Render Function ──
function renderCard(array $fh, array $wishlistedIds, bool $isLoggedIn): string {
    $id        = (int)$fh['id'];
    $title     = htmlspecialchars($fh['title'] ?? 'Luxury Farmhouse');
    $location  = htmlspecialchars($fh['location'] ?? 'India');
    $price     = (float)($fh['price'] ?? 0);
    $roomPrice = !empty($fh['allow_room_booking']) && (float)($fh['room_price'] ?? 0) > 0 ? (float)$fh['room_price'] : $price;
    $bedrooms  = isset($fh['bedrooms']) && $fh['bedrooms'] !== null ? (int)$fh['bedrooms'] : 0;
    $capacity  = isset($fh['night_capacity']) && $fh['night_capacity'] !== null && (int)$fh['night_capacity'] > 0
                    ? (int)$fh['night_capacity']
                    : (isset($fh['day_capacity']) && $fh['day_capacity'] !== null ? (int)$fh['day_capacity'] : 0);
    $category  = htmlspecialchars($fh['category'] ?? 'Farmhouse');
    $nego      = !empty($fh['is_negotiable']);
    $coverImg  = farmhouse_img_url($fh['cover_image'] ?? null);

    $amenitiesStr = $fh['amenities'] ?? '';
    $hasPool   = stripos($amenitiesStr, 'pool') !== false;

    $rating    = number_format(4.3 + ($id % 7) * 0.1, 1);
    $priceOld  = (int)($roomPrice * 1.30);
    $encId     = CryptoHelper::encrypt($id);
    $isWishlisted = in_array($id, $wishlistedIds);

    ob_start();
?>
<div class="hm-card-item">
    <a href="<?= url('farmhouse_details?id=' . urlencode($encId)) ?>" class="hm-prop-card" title="<?= $title ?>">
        <div class="hm-card-img-wrap">
            <img
                src="<?= $coverImg ?>"
                alt="<?= $title ?>"
                loading="lazy"
                onerror="this.src='https://placehold.co/600x420/E8F0EC/1F4D3A?text=Stayora+Estate'"
            />
            <div class="hm-card-badges">
                <span class="hm-badge-tag">
                    <span class="material-symbols-outlined hm-badge-icon">
                        <?= match(strtolower($fh['category'] ?? 'farmhouse')) {
                            'guest house' => 'hotel',
                            'resort'      => 'holiday_village',
                            'villa'       => 'villa',
                            default       => 'cottage'
                        } ?>
                    </span>
                    <?= $category ?>
                </span>
                <?php if ($nego): ?>
                    <span class="hm-badge-nego">
                        <span class="material-symbols-outlined hm-badge-icon">chat</span>
                        Open to Offer
                    </span>
                <?php endif; ?>
            </div>

            <!-- Wishlist button -->
            <button
                class="card-wish-btn <?= $isWishlisted ? 'wishlisted' : '' ?>"
                data-enc-id="<?= htmlspecialchars($encId) ?>"
                data-logged="<?= $isLoggedIn ? '1' : '0' ?>"
                onclick="toggleWish(event, this)"
                aria-label="<?= $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' ?>"
                title="<?= $isWishlisted ? 'Remove from wishlist' : 'Save to wishlist' ?>"
            >
                <span class="material-symbols-outlined wish-icon">favorite</span>
            </button>
        </div>

        <div class="hm-card-body">
            <div class="hm-card-title-row">
                <h4 class="hm-card-name"><?= $title ?></h4>
                <div class="hm-card-rating">
                    <span class="material-symbols-outlined hm-star">star</span>
                    <strong><?= $rating ?></strong>
                </div>
            </div>

            <div class="hm-card-loc">
                <span class="material-symbols-outlined">location_on</span>
                <span><?= $location ?></span>
            </div>

            <div class="hm-card-specs">
                <span><span class="material-symbols-outlined hm-spec-icon">bed</span> <?= $bedrooms ?> BHK</span>
                <span><span class="material-symbols-outlined hm-spec-icon">groups</span> <?= $capacity ?> Guests</span>
                <span><span class="material-symbols-outlined hm-spec-icon"><?= $hasPool ? 'pool' : 'yard' ?></span> <?= $hasPool ? 'Pool' : 'Lawn' ?></span>
            </div>

            <div class="hm-card-price-row">
                <div class="hm-price-group">
                    <span class="hm-price-new"><?= fmtPrice($roomPrice) ?></span>
                    <span class="hm-price-unit">/ night</span>
                    <?php if ($priceOld > $roomPrice): ?>
                        <span class="hm-price-old"><?= fmtPrice($priceOld) ?></span>
                    <?php endif; ?>
                </div>
                <span class="hm-card-cta-btn">View Details</span>
            </div>
        </div>
    </a>
</div>
<?php
    return ob_get_clean();
}
?>

<!-- Google Fonts & Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

<!-- ── TOAST NOTIFICATION ── -->
<div class="wish-toast" id="wish-toast">
    <span class="material-symbols-outlined" id="wish-toast-icon" style="font-size:18px;">favorite</span>
    <span id="wish-toast-msg">Added to wishlist</span>
</div>

<!-- ── HERO SECTION WITH LUXURY POOL BACKGROUND ── -->
<section class="hm-hero-wrap">
    <div class="hm-hero-badge">
        <span class="material-symbols-outlined">verified</span>
        <span>Verified Private Estates &amp; Farmhouses Across India</span>
    </div>

    <h1 class="hm-hero-title">
        Discover &amp; Book Luxury <span>Private Pool Farmhouses</span> Near You
    </h1>

    <p class="hm-hero-subtitle">
        Handpicked luxury villas &amp; farmhouses for weekend parties, family getaways, weddings, and corporate offsites.
    </p>

    <!-- Multi-field Search Capsule -->
    <form method="GET" action="<?= url('farmhouses') ?>" id="main-search-form">
        <div class="hm-search-capsule">
            
            <!-- Category -->
            <div class="hm-search-field">
                <label class="hm-search-lbl">
                    <span class="material-symbols-outlined">category</span>
                    Category
                </label>
                <select class="hm-search-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach (['Guest House', 'Resort', 'Farmhouse', 'Villa'] as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Location -->
            <div class="hm-search-field">
                <label class="hm-search-lbl">
                    <span class="material-symbols-outlined">location_on</span>
                    Location
                </label>
                <select class="hm-search-select" name="location">
                    <option value="">All Locations</option>
                    <?php foreach ($availableLocations as $loc): ?>
                        <option value="<?= htmlspecialchars($loc) ?>" <?= ($activeLocation === $loc) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Check-in -->
            <div class="hm-search-field">
                <label class="hm-search-lbl">
                    <span class="material-symbols-outlined">calendar_today</span>
                    Check-In
                </label>
                <input type="date" class="hm-search-input" name="checkin" id="checkin"/>
            </div>

            <!-- Check-out -->
            <div class="hm-search-field">
                <label class="hm-search-lbl">
                    <span class="material-symbols-outlined">calendar_today</span>
                    Check-Out
                </label>
                <input type="date" class="hm-search-input" name="checkout" id="checkout"/>
            </div>

            <!-- Guests -->
            <div class="hm-search-field">
                <label class="hm-search-lbl">
                    <span class="material-symbols-outlined">group</span>
                    Guests
                </label>
                <div class="hm-guest-ctrl">
                    <button type="button" class="hm-guest-btn" onclick="chg(-1)">−</button>
                    <span id="g-count" style="font-size:15px;font-weight:800;min-width:18px;text-align:center;">2</span>
                    <input type="hidden" name="guests" id="g-hidden" value="2"/>
                    <button type="button" class="hm-guest-btn" onclick="chg(1)">+</button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="hm-search-submit">
                <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                <!-- <span>Search Farms</span> -->
            </button>
        </div>
    </form>

    <!-- Quick Location Chips -->
    <div class="hm-quick-pills">
        <span style="font-size:12px;color:#E2E8F0;font-weight:700;margin-right:4px;">Popular:</span>
        <?php 
          $popularPills = ['Indore', 'Surat', 'Delhi', 'Hyderabad', 'Daman', 'Ahmedabad', 'Mumbai'];
          foreach ($popularPills as $pill):
        ?>
          <a href="<?= url('farmhouses?location=' . urlencode($pill)) ?>" class="hm-pill-item <?= ($activeLocation === $pill) ? 'active' : '' ?>">
            <span class="material-symbols-outlined" style="font-size:14px;">location_on</span>
            <span><?= $pill ?></span>
          </a>
        <?php endforeach; ?>
    </div>
</section>



<!-- ── MAIN LISTINGS CONTENT ── -->
<div class="hm-main-container mt-8">

    <?php if ($isFiltered): ?>
        <!-- ══ FILTERED RESULTS MODE ══ -->
        <div style="margin-bottom:24px;">
            <div class="hm-sec-header">
                <div class="hm-sec-title-group">
                    <h2>Search Results</h2>
                    <p>Showing <?= count($farmhouses) ?> verified property<?= count($farmhouses) === 1 ? '' : 'ies' ?> matching your criteria</p>
                </div>
                <a href="<?= url('home') ?>" class="hm-view-all-link">✕ Clear Filters</a>
            </div>

            <?php if (empty($farmhouses)): ?>
                <div style="text-align:center;padding:60px 20px;background:#fff;border-radius:20px;border:1px solid #E2E8F0;">
                    <div style="margin-bottom:12px;">
                        <span class="material-symbols-outlined" style="font-size:48px;color:#94a3b8;">domain_disabled</span>
                    </div>
                    <h3 style="font-size:18px;font-weight:800;color:#0F172A;margin:0 0 6px;">No farmhouses found</h3>
                    <p style="font-size:13.5px;color:#64748B;margin:0 0 16px;">Try searching for another location or adjusting your date selection.</p>
                    <a href="<?= url('farmhouses') ?>" style="display:inline-block;background:#1D4ED8;color:#fff;padding:10px 20px;border-radius:12px;font-weight:700;text-decoration:none;font-size:13px;">Browse All Farmhouses</a>
                </div>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;">
                    <?php foreach ($farmhouses as $fh): echo renderCard($fh, $wishlistedIds, $isLoggedIn); endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- ══ FEATURED / TOP PICKS ══ -->
        <div style="margin-bottom:36px;">
            <div class="hm-sec-header">
                <div class="hm-sec-title-group">
                    <h2>
                        <span class="material-symbols-outlined" style="font-size:20px;color:#1E40AF;vertical-align:middle;">auto_awesome</span>
                        Featured Farmhouses &amp; Top Picks
                    </h2>
                    <p>Highest-rated private estates chosen for unforgettable weekend getaways</p>
                </div>
                <div class="hm-sec-actions">
                    <a href="<?= url('farmhouses') ?>" class="hm-view-all-link">Explore All (<?= count($farmhouses) ?>) →</a>
                    <div class="hm-nav-arrows">
                        <button type="button" class="hm-nav-btn" onclick="hmSlide('track-featured', -1)" aria-label="Slide Left">‹</button>
                        <button type="button" class="hm-nav-btn" onclick="hmSlide('track-featured', 1)" aria-label="Slide Right">›</button>
                    </div>
                </div>
            </div>

            <div class="hm-cards-scroll-wrap">
                <div class="hm-cards-scroll" id="track-featured">
                    <?php 
                      $featuredList = array_slice($farmhouses, 0, 8);
                      foreach ($featuredList as $fh): echo renderCard($fh, $wishlistedIds, $isLoggedIn); endforeach; 
                    ?>
                </div>
            </div>
        </div>

        <!-- ══ CITY-WISE HIGHLIGHT SECTIONS (MAX 8 PRODUCTS PER CITY) ══ -->
        <?php 
        $shownCities = [];

        // 1. Process Predefined Top City Highlights
        foreach ($citySections as $cityKey => $meta):
            $cityFarmhouses = array_filter($farmhouses, function($fh) use ($cityKey) {
                return stripos($fh['location'] ?? '', $cityKey) !== false;
            });
            if (empty($cityFarmhouses)) continue;

            $shownCities[] = strtolower($cityKey);
            $cityList = array_slice($cityFarmhouses, 0, 8);
            $trackId = 'track-city-' . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($cityKey));
        ?>
        <div style="margin-bottom:36px;">
            <div class="hm-sec-header">
                <div class="hm-sec-title-group">
                    <h2><?= htmlspecialchars($meta['label']) ?></h2>
                    <p><?= htmlspecialchars($meta['desc']) ?></p>
                </div>
                <div class="hm-sec-actions">
                    <a href="<?= url('farmhouses?location=' . urlencode($cityKey)) ?>" class="hm-view-all-link">View All in <?= htmlspecialchars($cityKey) ?> (<?= count($cityFarmhouses) ?>) →</a>
                    <div class="hm-nav-arrows">
                        <button type="button" class="hm-nav-btn" onclick="hmSlide('<?= $trackId ?>', -1)" aria-label="Slide Left">‹</button>
                        <button type="button" class="hm-nav-btn" onclick="hmSlide('<?= $trackId ?>', 1)" aria-label="Slide Right">›</button>
                    </div>
                </div>
            </div>

            <div class="hm-cards-scroll-wrap">
                <div class="hm-cards-scroll" id="<?= $trackId ?>">
                    <?php foreach ($cityList as $fh): echo renderCard($fh, $wishlistedIds, $isLoggedIn); endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- 2. Process All Other Cities in Database -->
        <?php
        foreach ($byCity as $cityName => $cityFarmhouses):
            $cityLower = strtolower(trim($cityName));
            // Skip if already covered in predefined city list
            $alreadyShown = false;
            foreach ($shownCities as $sc) {
                if (stripos($cityLower, $sc) !== false) {
                    $alreadyShown = true;
                    break;
                }
            }
            if ($alreadyShown || empty($cityFarmhouses)) continue;

            $cityList = array_slice($cityFarmhouses, 0, 8);
            $trackId = 'track-city-' . preg_replace('/[^a-zA-Z0-9]/', '', $cityLower);
        ?>
        <div style="margin-bottom:36px;">
            <div class="hm-sec-header">
                <div class="hm-sec-title-group">
                    <h2>Near <?= htmlspecialchars($cityName) ?></h2>
                    <p>Verified private farmhouses available near <?= htmlspecialchars($cityName) ?>.</p>
                </div>
                <div class="hm-sec-actions">
                    <a href="<?= url('farmhouses?location=' . urlencode($cityName)) ?>" class="hm-view-all-link">View All in <?= htmlspecialchars($cityName) ?> (<?= count($cityFarmhouses) ?>) →</a>
                    <div class="hm-nav-arrows">
                        <button type="button" class="hm-nav-btn" onclick="hmSlide('<?= $trackId ?>', -1)" aria-label="Slide Left">‹</button>
                        <button type="button" class="hm-nav-btn" onclick="hmSlide('<?= $trackId ?>', 1)" aria-label="Slide Right">›</button>
                    </div>
                </div>
            </div>

            <div class="hm-cards-scroll-wrap">
                <div class="hm-cards-scroll" id="<?= $trackId ?>">
                    <?php foreach ($cityList as $fh): echo renderCard($fh, $wishlistedIds, $isLoggedIn); endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <!-- ── GUEST GETAWAY BANNER ── -->
    <div class="hm-host-banner">
        <div class="hm-host-content">
            <h3>Looking for the Perfect Weekend Getaway?</h3>
            <p>Explore handpicked private farmhouses, luxury pool villas, and nature sanctuaries across India with verified direct pricing.</p>
        </div>
        <a href="<?= url('farmhouses') ?>" class="hm-host-btn">
            <span>Explore Farmhouses</span>
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
        </a>
    </div>

</div>

<script>
// ── UNIVERSAL CROSS-BROWSER SMOOTH SLIDER HANDLER ──
function hmSlide(trackId, direction) {
    const track = document.getElementById(trackId);
    if (!track) return;
    const scrollAmount = track.clientWidth * 0.85 || 500;

    if (track.scrollBy) {
        track.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    } else {
        track.scrollLeft += direction * scrollAmount;
    }
}

// ── DRAG-TO-SCROLL FOR DESKTOP & TOUCH ENHANCEMENT ──
document.querySelectorAll('.hm-cards-scroll').forEach(slider => {
    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', (e) => {
        // Avoid interfering with button clicks
        if (e.target.closest('button') || e.target.closest('.card-wish-btn')) return;
        isDown = true;
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
    });

    slider.addEventListener('mouseup', () => {
        isDown = false;
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 1.5;
        slider.scrollLeft = scrollLeft - walk;
    });
});

// ── WISHLIST TOGGLE ──
const USER_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
const LOGIN_URL = '<?= url("login") ?>';

function toggleWish(e, btn) {
    e.preventDefault();
    e.stopPropagation();

    if (!USER_LOGGED_IN) {
        window.location.href = LOGIN_URL;
        return;
    }

    if (btn.classList.contains('loading')) return;

    const encId = btn.dataset.encId;
    const isWishlisted = btn.classList.contains('wishlisted');

    btn.classList.add('loading');
    btn.classList.toggle('wishlisted', !isWishlisted);

    fetch('<?= url("toggle_wishlist") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ farmhouse_id: encId })
    })
    .then(res => res.json())
    .then(data => {
        btn.classList.remove('loading');
        if (data && data.success) {
            const added = data.action === 'added';
            btn.classList.toggle('wishlisted', added);
            showWishToast(added ? 'favorite' : 'heart_minus', added ? 'Saved to your Wishlist ❤️' : 'Removed from Wishlist');
        }
    })
    .catch(() => {
        btn.classList.remove('loading');
        btn.classList.toggle('wishlisted', isWishlisted);
        showWishToast('wifi_off', 'Network error. Please try again.');
    });
}

let toastTimer = null;
function showWishToast(icon, message) {
    const toast     = document.getElementById('wish-toast');
    const toastIcon = document.getElementById('wish-toast-icon');
    const toastMsg  = document.getElementById('wish-toast-msg');

    if (!toast) return;
    toastIcon.textContent = icon;
    toastMsg.textContent  = message;

    toast.classList.add('show');
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}

// ── GUEST COUNTER ──
let g = 2;
function chg(d) {
    g = Math.max(1, Math.min(50, g + d));
    const gc = document.getElementById('g-count');
    const gh = document.getElementById('g-hidden');
    if (gc) gc.textContent = g;
    if (gh) gh.value = g;
}

// ── DATE DEFAULTS ──
(function () {
    const fmt = d => d.toISOString().split('T')[0];
    const today    = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    const ci = document.getElementById('checkin');
    const co = document.getElementById('checkout');
    if (ci && !ci.value) ci.value = fmt(today);
    if (co && !co.value) co.value = fmt(tomorrow);

    if (ci) ci.min = fmt(today);
    if (co) co.min = fmt(tomorrow);

    if (ci) ci.addEventListener('change', () => {
        const next = new Date(ci.value);
        next.setDate(next.getDate() + 1);
        co.min   = fmt(next);
        co.value = fmt(next);
    });
})();
</script>

<?php include __DIR__ . "/Includes/footer.php"; ?>