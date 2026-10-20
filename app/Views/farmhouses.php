<?php
use App\Helpers\CryptoHelper;
/**
 * Views/farmhouses.php
 * High-Conversion Luxury Farmhouse Explorer & Filter Page
 * Rendered by FarmhousesController::farmhouses()
 *
 * Variables injected:
 *  $farmhouses          – filtered array of farmhouse rows
 *  $availableLocations  – distinct location strings
 *  $availableAmenities  – array of ['id','name','category','icon_class']
 *  $wishlistedIds       – flat int array of farmhouse IDs in user's wishlist
 *  $filters             – active filter values
 *  $activeCheckin       – string
 *  $activeCheckout      – string
 *  $activeGuests        – int
 */

// ── Active filters ──
$activeSearch           = htmlspecialchars(trim($filters['search'] ?? ''));
$activeLocation         = htmlspecialchars(trim($filters['location'] ?? ''));
$activeCategory         = htmlspecialchars(trim($filters['category'] ?? ''));
$activeMinPrice         = isset($filters['min_price']) && $filters['min_price'] !== '' ? (int)$filters['min_price'] : '';
$activeMaxPrice         = isset($filters['max_price']) && $filters['max_price'] !== '' ? (int)$filters['max_price'] : '';
$activeBedrooms         = isset($filters['bedrooms']) && $filters['bedrooms'] !== '' ? (int)$filters['bedrooms'] : '';
$activeNego             = !empty($filters['is_negotiable']);
$activeRoomBooking      = !empty($filters['allow_room_booking']);
$activeSort             = htmlspecialchars($filters['sort'] ?? '');
$activeAmenities        = array_map('intval', $filters['amenities'] ?? []);

$isFiltered = $activeSearch || $activeLocation || $activeCategory || $activeMinPrice || $activeMaxPrice
           || $activeBedrooms || $activeNego || $activeRoomBooking || $activeSort || !empty($activeAmenities)
           || !empty($activeCheckin) || !empty($activeCheckout);

$totalCount = count($farmhouses);
$isLoggedIn = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
$wishlistedIds = $wishlistedIds ?? [];

if (!function_exists('fmtPriceFH')) {
    function fmtPriceFH(float $n): string {
        return '₹' . number_format($n, 0, '.', ',');
    }
}

if (!function_exists('resolveFarmImg')) {
    function resolveFarmImg(?string $img): string {
        return farmhouse_img_url($img, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=75');
    }
}

// Icon mapper for amenity icon_classes to Material Symbols
if (!function_exists('getAmenityIcon')) {
    function getAmenityIcon(string $iconClass): string {
        $map = [
            'fa-swimming-pool' => 'pool',
            'fa-wifi'          => 'wifi',
            'fa-utensils'      => 'restaurant',
            'fa-music'         => 'music_note',
            'fa-snowflake'     => 'ac_unit',
            'fa-fire'          => 'outdoor_grill',
            'fa-video'         => 'videocam',
            'fa-car'           => 'directions_car',
            'fa-parking'       => 'local_parking',
            'fa-seedling'      => 'yard',
            'fa-toilet'        => 'shower',
            'fa-bed'           => 'bed',
            'fa-fan'           => 'mode_fan',
            'fa-fire-burner'   => 'soup_kitchen',
        ];
        return $map[$iconClass] ?? 'check_circle';
    }
}

// Group amenities by category
$amenityByCategory = [];
foreach ($availableAmenities as $am) {
    $cat = ucfirst($am['category'] ?? 'General');
    $amenityByCategory[$cat][] = $am;
}
ksort($amenityByCategory);

// Page title computation
if ($activeSearch && $activeLocation) {
    $pageHeading = '"' . $activeSearch . '" in ' . $activeLocation;
    $pageTitle = "{$activeSearch} in {$activeLocation} - Farmhouses & Villas | Stayora";
    $pageDescription = "Find {$activeSearch} farmhouses and private villas in {$activeLocation}. Verified amenities, swimming pools, and best rates on Stayora.";
} elseif ($activeSearch) {
    $pageHeading = 'Search: "' . $activeSearch . '"';
    $pageTitle = "\"{$activeSearch}\" Farmhouses & Private Villas | Stayora";
    $pageDescription = "Search results for {$activeSearch} farmhouses and vacation villas across India on Stayora.";
} elseif ($activeLocation) {
    $pageHeading = 'Luxury Farmhouses in ' . $activeLocation;
    $pageTitle = "Top Farmhouses & Private Pool Villas in {$activeLocation} | Stayora";
    $pageDescription = "Explore handpicked luxury farmhouses and private pool villas in {$activeLocation} for weekend parties, family stays, and events. Book online on Stayora.";
} else {
    $pageHeading = 'Explore All Luxury Farmhouses';
    $pageTitle = 'Explore Verified Luxury Farmhouses & Pool Villas | Stayora';
    $pageDescription = "Browse India's top collection of private farmhouses, holiday villas, and event spaces for weekend parties, family getaways, and corporate stays.";
}

$canonicalUrl = absolute_url('farmhouses' . ($activeLocation ? '?location=' . urlencode($activeLocation) : ''));

// Include public header after computing SEO variables
include __DIR__ . "/Includes/header.php";
?>

<!-- Google Fonts & Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

<!-- ── TOAST NOTIFICATION ── -->
<div class="wish-toast" id="wish-toast">
    <span class="material-symbols-outlined" id="wish-toast-icon" style="font-size:18px;">favorite</span>
    <span id="wish-toast-msg">Saved to Wishlist</span>
</div>

<section class="fh-hero-banner" style="background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.70) 50%, rgba(15, 23, 42, 0.94) 100%), url('<?= asset('assets/images/uploads/luxury_pool_hero.jpg') ?>');">
    <div class="fh-hero-inner">
        <nav class="fh-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= url('home') ?>">
                <span class="material-symbols-outlined" style="font-size:14px;">home</span>
                Home
            </a>
            <span class="fh-bc-sep">/</span>
            <a href="<?= url('farmhouses') ?>">Farmhouses</a>
            <?php if ($activeLocation): ?>
                <span class="fh-bc-sep">/</span>
                <span class="fh-bc-active"><?= $activeLocation ?></span>
            <?php endif; ?>
            <?php if ($activeSearch): ?>
                <span class="fh-bc-sep">/</span>
                <span class="fh-bc-active">"<?= $activeSearch ?>"</span>
            <?php endif; ?>
        </nav>

        <div class="fh-hero-title-row">
            <div>
                <h1 class="fh-hero-title"><?= $pageHeading ?></h1>
                <p class="fh-hero-subtitle">Handpicked, verified private villas &amp; farmhouses for memorable gatherings.</p>
            </div>
            <div class="fh-count-pill">
                <span class="material-symbols-outlined" style="font-size:16px;color:#C9A227;">verified</span>
                <span><strong><?= $totalCount ?></strong> <?= $totalCount === 1 ? 'Farmhouse' : 'Farmhouses' ?> Available</span>
            </div>
        </div>

        <!-- Quick Location Filter Chips -->
        <div class="fh-quick-locations">
            <span class="fh-loc-label">Popular:</span>
            <a href="<?= url('farmhouses') ?>" class="fh-loc-pill <?= empty($activeLocation) ? 'active' : '' ?>">
                All Cities
            </a>
            <?php 
            $quickCities = ['Indore', 'Surat', 'Delhi', 'Hyderabad', 'Daman', 'Ahmedabad', 'Mumbai'];
            foreach ($quickCities as $c):
            ?>
                <a href="<?= url('farmhouses?location=' . urlencode($c)) ?>" class="fh-loc-pill <?= ($activeLocation === $c) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined" style="font-size:13px;">location_on</span>
                    <?= $c ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     MAIN EXPLORER LAYOUT
     ══════════════════════════════════════════════ -->
<div class="fh-explorer-container">

    <!-- Mobile Filter & Sort Action Bar (Side by Side) -->
    <div class="fh-mobile-filter-bar">
        <button type="button" class="fh-mobile-action-btn fh-mobile-filter-btn" onclick="openMobileFilter()">
            <span class="material-symbols-outlined" style="font-size:18px;">tune</span>
            <span>Filters</span>
            <?php
            $activeFilterCount = count(array_filter([$activeSearch, $activeLocation, $activeMinPrice, $activeMaxPrice, $activeBedrooms, $activeNego, $activeRoomBooking, $activeSort]))
                               + count($activeAmenities);
            if ($activeFilterCount > 0): ?>
                <span class="fh-filter-count-badge"><?= $activeFilterCount ?></span>
            <?php endif; ?>
        </button>

        <div class="fh-mobile-action-btn fh-mobile-sort-btn">
            <span class="material-symbols-outlined" style="font-size:18px;color:#64748B;">sort</span>
            <select onchange="applyQuickSort(this.value)" class="fh-mobile-sort-select">
                <option value="" <?= empty($activeSort) ? 'selected' : '' ?>>Sort: Recommended</option>
                <option value="low-high" <?= ($activeSort === 'low-high') ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="high-low" <?= ($activeSort === 'high-low') ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
        </div>
    </div>

    <!-- ══ SIDEBAR FILTER COMPONENT (Reused for desktop & mobile drawer) ══ -->
    <?php ob_start(); ?>
    <form method="GET" action="<?= url('farmhouses') ?>" id="filter-form" class="fh-sidebar-form">

        <div class="fh-sb-head">
            <div class="fh-sb-head-title">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:20px;">tune</span>
                <span>Filters</span>
            </div>
            <?php if ($isFiltered): ?>
                <a href="<?= url('farmhouses') ?>" class="fh-sb-reset-link">Reset All</a>
            <?php endif; ?>
        </div>

        <!-- 1. Keyword Search -->
        <div class="fh-filter-box">
            <label class="fh-filter-lbl">
                <span class="material-symbols-outlined">search</span>
                Keywords
            </label>
            <div class="fh-search-field">
                <input type="text" name="search" placeholder="e.g. Pool, Lawn, Villa..." value="<?= $activeSearch ?>"/>
                <?php if ($activeSearch): ?>
                    <button type="button" onclick="this.previousElementSibling.value=''; this.closest('form').submit();" class="fh-clear-field-btn">✕</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Property Category -->
        <div class="fh-filter-box">
            <label class="fh-filter-lbl">
                <span class="material-symbols-outlined">category</span>
                Property Category
            </label>
            <select name="category" class="fh-select-field">
                <option value="">All Property Categories</option>
                <?php 
                $categoryOptions = ['Guest House', 'Resort', 'Farmhouse', 'Villa'];
                foreach ($categoryOptions as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($activeCategory === $cat) ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 3. Location -->
        <div class="fh-filter-box">
            <label class="fh-filter-lbl">
                <span class="material-symbols-outlined">location_on</span>
                Location / City
            </label>
            <select name="location" class="fh-select-field">
                <option value="">All Destinations</option>
                <?php foreach ($availableLocations as $loc): ?>
                    <option value="<?= htmlspecialchars($loc) ?>" <?= ($activeLocation === $loc) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 3. Price Range -->
        <div class="fh-filter-box">
            <div class="fh-lbl-row">
                <label class="fh-filter-lbl">
                    <span class="material-symbols-outlined">payments</span>
                    Price per Night
                </label>
                <span class="fh-price-indicator" id="price-indicator">
                    <?= $activeMaxPrice ? 'Up to ' . fmtPriceFH($activeMaxPrice) : 'Any Price' ?>
                </span>
            </div>
            <div class="fh-price-inputs">
                <div class="fh-price-input-wrap">
                    <span class="fh-currency">₹</span>
                    <input type="number" name="min_price" placeholder="Min" value="<?= $activeMinPrice ?>" min="0" step="500" oninput="syncPriceNote()"/>
                </div>
                <span class="fh-price-to">to</span>
                <div class="fh-price-input-wrap">
                    <span class="fh-currency">₹</span>
                    <input type="number" name="max_price" id="max-price-input" placeholder="Max" value="<?= $activeMaxPrice ?>" min="0" step="500" oninput="syncPriceNote()"/>
                </div>
            </div>

            <input type="range" class="fh-range-slider" min="0" max="100000" step="1000"
                   value="<?= $activeMaxPrice ?: 100000 ?>"
                   oninput="document.getElementById('max-price-input').value=this.value; syncPriceNote();"/>

            <!-- Quick price chips -->
            <div class="fh-quick-price-chips">
                <button type="button" class="fh-qp-chip" onclick="setPriceRange(0, 5000)">&lt; ₹5k</button>
                <button type="button" class="fh-qp-chip" onclick="setPriceRange(5000, 15000)">₹5k - 15k</button>
                <button type="button" class="fh-qp-chip" onclick="setPriceRange(15000, 30000)">₹15k - 30k</button>
                <button type="button" class="fh-qp-chip" onclick="setPriceRange(30000, '')">₹30k+</button>
            </div>
        </div>

        <!-- 4. Bedrooms / BHK -->
        <div class="fh-filter-box">
            <label class="fh-filter-lbl">
                <span class="material-symbols-outlined">bed</span>
                Bedrooms (BHK)
            </label>
            <div class="fh-bhk-pills">
                <label class="fh-bhk-pill">
                    <input type="radio" name="bedrooms" value="" <?= empty($activeBedrooms) ? 'checked' : '' ?>/>
                    <span>Any</span>
                </label>
                <?php foreach ([1, 2, 3, 4] as $bhk): ?>
                    <label class="fh-bhk-pill">
                        <input type="radio" name="bedrooms" value="<?= $bhk ?>" <?= ($activeBedrooms === $bhk) ? 'checked' : '' ?>/>
                        <span><?= $bhk === 4 ? '4+ BHK' : $bhk . ' BHK' ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 5. Property Options / Features -->
        <div class="fh-filter-box">
            <label class="fh-filter-lbl">
                <span class="material-symbols-outlined">verified</span>
                Special Options
            </label>
            <div class="fh-checkbox-list">
                <label class="fh-checkbox-item">
                    <input type="checkbox" name="is_negotiable" value="1" <?= $activeNego ? 'checked' : '' ?>/>
                    <span class="fh-chk-custom"></span>
                    <span class="fh-chk-text">
                        <span class="material-symbols-outlined" style="font-size:15px;color:#f59e0b;">chat</span>
                        Open to Offers / Negotiable
                    </span>
                </label>
                <label class="fh-checkbox-item">
                    <input type="checkbox" name="allow_room_booking" value="1" <?= $activeRoomBooking ? 'checked' : '' ?>/>
                    <span class="fh-chk-custom"></span>
                    <span class="fh-chk-text">
                        <span class="material-symbols-outlined" style="font-size:15px;color:#1D4ED8;">bedroom_parent</span>
                        Room Booking Allowed
                    </span>
                </label>
            </div>
        </div>

        <!-- 6. Sort By -->
        <div class="fh-filter-box">
            <label class="fh-filter-lbl">
                <span class="material-symbols-outlined">sort</span>
                Sort Results
            </label>
            <select name="sort" class="fh-select-field">
                <option value="" <?= empty($activeSort) ? 'selected' : '' ?>>✨ Recommended / Newest</option>
                <option value="low-high" <?= ($activeSort === 'low-high') ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="high-low" <?= ($activeSort === 'high-low') ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
        </div>

        <!-- 7. Amenities Checklist -->
        <?php if (!empty($amenityByCategory)): ?>
        <div class="fh-filter-box">
            <div class="fh-lbl-row">
                <label class="fh-filter-lbl">
                    <span class="material-symbols-outlined">checklist</span>
                    Amenities
                </label>
                <span style="font-size:11px;color:#64748B;font-weight:700;">Filter with all</span>
            </div>

            <!-- Amenity Search Filter -->
            <input type="text" placeholder="Quick search amenities..." class="fh-am-search-input" onkeyup="filterAmenitiesInSidebar(this)"/>

            <div class="fh-am-scrollable">
                <?php foreach ($amenityByCategory as $cat => $ams): ?>
                    <div class="fh-am-category-block">
                        <div class="fh-am-cat-title"><?= htmlspecialchars($cat) ?></div>
                        <div class="fh-checkbox-list">
                            <?php foreach ($ams as $am): 
                                $amId = (int)$am['id'];
                                $amIcon = getAmenityIcon($am['icon_class'] ?? '');
                                $isAmActive = in_array($amId, $activeAmenities);
                            ?>
                                <label class="fh-checkbox-item fh-am-item" data-name="<?= strtolower(htmlspecialchars($am['name'])) ?>">
                                    <input type="checkbox" name="amenities[]" value="<?= $amId ?>" <?= $isAmActive ? 'checked' : '' ?>/>
                                    <span class="fh-chk-custom"></span>
                                    <span class="fh-chk-text">
                                        <span class="material-symbols-outlined fh-am-ico"><?= $amIcon ?></span>
                                        <?= htmlspecialchars($am['name']) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Submit Button -->
        <div class="fh-filter-actions">
            <button type="submit" class="fh-apply-cta-btn">
                <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                <span>Apply Filters</span>
            </button>
        </div>

    </form>
    <?php $sidebarHtml = ob_get_clean(); ?>

    <!-- ══ DESKTOP SIDEBAR ══ -->
    <aside class="fh-sidebar-desktop" id="desktop-sidebar">
        <?= $sidebarHtml ?>
    </aside>

    <!-- ══ MAIN RESULTS SECTION ══ -->
    <main class="fh-results-main">

        <!-- Results Toolbar -->
        <div class="fh-results-toolbar">
            <div class="fh-tb-left">
                <div class="fh-tb-count">
                    <strong><?= $totalCount ?></strong> <?= $totalCount === 1 ? 'Farmhouse' : 'Farmhouses' ?>
                </div>
                <?php if ($activeLocation || $activeSearch): ?>
                    <div class="fh-tb-sub">
                        <?= $activeLocation ? 'in <strong>' . $activeLocation . '</strong>' : '' ?>
                        <?= $activeSearch ? ' matching "<strong>' . $activeSearch . '</strong>"' : '' ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="fh-tb-right">
                <!-- Quick Sort Dropdown -->
                <div class="fh-quick-sort">
                    <span class="material-symbols-outlined" style="font-size:16px;color:#64748B;">sort</span>
                    <select onchange="applyQuickSort(this.value)" class="fh-tb-sort-select">
                        <option value="" <?= empty($activeSort) ? 'selected' : '' ?>>Sort: Recommended</option>
                        <option value="low-high" <?= ($activeSort === 'low-high') ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="high-low" <?= ($activeSort === 'high-low') ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>

                <!-- Grid / List Switcher -->
                <div class="fh-view-switcher">
                    <button type="button" class="fh-v-btn active" id="btn-grid" onclick="setView('grid')" title="Grid View">
                        <span class="material-symbols-outlined">grid_view</span>
                    </button>
                    <button type="button" class="fh-v-btn" id="btn-list" onclick="setView('list')" title="List View">
                        <span class="material-symbols-outlined">view_list</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Filter Pills Bar -->
        <?php if ($isFiltered): ?>
        <div class="fh-active-filter-chips">
            <span style="font-size:12px;font-weight:700;color:#64748B;align-self:center;">Active Filters:</span>
            <?php if ($activeSearch): ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined">search</span>
                    "<?= $activeSearch ?>"
                    <button type="button" onclick="removeQueryParam('search')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeCategory): ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined">category</span>
                    Category: <?= $activeCategory ?>
                    <button type="button" onclick="removeQueryParam('category')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeLocation): ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined">location_on</span>
                    <?= $activeLocation ?>
                    <button type="button" onclick="removeQueryParam('location')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeMinPrice): ?>
                <span class="fh-active-chip">
                    Min <?= fmtPriceFH($activeMinPrice) ?>
                    <button type="button" onclick="removeQueryParam('min_price')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeMaxPrice): ?>
                <span class="fh-active-chip">
                    Max <?= fmtPriceFH($activeMaxPrice) ?>
                    <button type="button" onclick="removeQueryParam('max_price')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeBedrooms): ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined">bed</span>
                    <?= $activeBedrooms === 4 ? '4+ BHK' : $activeBedrooms . ' BHK' ?>
                    <button type="button" onclick="removeQueryParam('bedrooms')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeNego): ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined">chat</span>
                    Negotiable
                    <button type="button" onclick="removeQueryParam('is_negotiable')">✕</button>
                </span>
            <?php endif; ?>
            <?php if ($activeRoomBooking): ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined">bedroom_parent</span>
                    Room Booking
                    <button type="button" onclick="removeQueryParam('allow_room_booking')">✕</button>
                </span>
            <?php endif; ?>
            <?php foreach ($activeAmenities as $aId): 
                $aName = '';
                $aIcon = 'check_circle';
                foreach ($availableAmenities as $a) {
                    if ((int)$a['id'] === $aId) {
                        $aName = $a['name'];
                        $aIcon = getAmenityIcon($a['icon_class'] ?? '');
                        break;
                    }
                }
                if ($aName):
            ?>
                <span class="fh-active-chip">
                    <span class="material-symbols-outlined"><?= $aIcon ?></span>
                    <?= htmlspecialchars($aName) ?>
                    <button type="button" onclick="removeAmenityParam(<?= $aId ?>)">✕</button>
                </span>
            <?php endif; endforeach; ?>

            <a href="<?= url('farmhouses') ?>" class="fh-clear-all-chip">Reset All ✕</a>
        </div>
        <?php endif; ?>

        <!-- ══ FARMHOUSE LISTINGS (Grid & List View) ══ -->
        <?php if (empty($farmhouses)): ?>
            <div class="fh-empty-state">
                <div class="fh-empty-icon-wrap">
                    <span class="material-symbols-outlined" style="font-size:52px;color:#94a3b8;">domain_disabled</span>
                </div>
                <h3>No Properties Found</h3>
                <p>We couldn't find any properties matching your current filter selection. Try removing some filters or exploring other locations.</p>
                <div style="margin-top:16px;">
                    <a href="<?= url('farmhouses') ?>" class="fh-apply-cta-btn" style="display:inline-flex;width:auto;padding:10px 24px;">
                        <span>View All Properties</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="fh-listings-grid" id="listings-container">
                <?php foreach ($farmhouses as $fh):
                    $fhId         = (int)$fh['id'];
                    $title        = htmlspecialchars($fh['title'] ?? 'Luxury Property');
                    $category     = htmlspecialchars($fh['category'] ?? 'Farmhouse');
                    $location     = htmlspecialchars($fh['location'] ?? 'India');
                    $price        = (float)($fh['price'] ?? 0);
                    $roomPrice    = !empty($fh['allow_room_booking']) && (float)($fh['room_price'] ?? 0) > 0 ? (float)$fh['room_price'] : $price;
                    $bedrooms     = (int)($fh['bedrooms'] ?? 2);
                    $capacity     = (int)($fh['night_capacity'] ?? $fh['day_capacity'] ?? 15);
                    $nego         = !empty($fh['is_negotiable']);
                    $hasRoomBook  = !empty($fh['allow_room_booking']);
                    $coverImg     = farmhouse_img_url($fh['cover_image'] ?? null);
                    $descSnip     = htmlspecialchars(mb_strimwidth(strip_tags($fh['description'] ?? ''), 0, 140, '…'));

                    $rating       = number_format(4.3 + ($fhId % 7) * 0.1, 1);
                    $priceOld     = (int)($roomPrice * 1.30);
                    $encId        = CryptoHelper::encrypt($fhId);
                    $isWishlisted = in_array($fhId, $wishlistedIds);

                    $amList = [];
                    if (!empty($fh['amenities'])) {
                        $amList = array_slice(array_map('trim', explode(',', $fh['amenities'])), 0, 4);
                    }
                ?>
                <div class="fh-card-wrapper">
                    <a href="<?= url('farmhouse_details?id=' . urlencode($encId)) ?>" class="fh-prop-card" title="<?= $title ?>">
                        
                        <!-- Card Image Box -->
                        <div class="fh-card-img-wrap">
                            <img
                                src="<?= $coverImg ?>"
                                alt="<?= $title ?>"
                                loading="lazy"
                                onerror="this.src='https://placehold.co/600x420/E8F0EC/1F4D3A?text=Stayora+Estate'"
                            />
                            
                            <div class="fh-card-badges">
                                <span class="fh-badge-tag">
                                    <span class="material-symbols-outlined fh-badge-icon">
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
                                    <span class="fh-badge-nego">
                                        <span class="material-symbols-outlined fh-badge-icon">chat</span>
                                        Open to Offer
                                    </span>
                                <?php endif; ?>
                                <?php if ($hasRoomBook): ?>
                                    <span class="fh-badge-room">
                                        <span class="material-symbols-outlined fh-badge-icon">bedroom_parent</span>
                                        Room Booking
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Wishlist Button -->
                            <button
                                type="button"
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

                        <!-- Card Content -->
                        <div class="fh-card-body">
                            <div class="fh-card-title-row">
                                <h3 class="fh-card-name"><?= $title ?></h3>
                                <div class="fh-card-rating">
                                    <span class="material-symbols-outlined fh-star">star</span>
                                    <strong><?= $rating ?></strong>
                                </div>
                            </div>

                            <div class="fh-card-loc">
                                <span class="material-symbols-outlined">location_on</span>
                                <span><?= $location ?></span>
                            </div>

                            <div class="fh-card-specs">
                                <span><span class="material-symbols-outlined fh-spec-icon">bed</span> <?= $bedrooms ?> BHK</span>
                                <span><span class="material-symbols-outlined fh-spec-icon">groups</span> <?= $capacity ?> Guests</span>
                                <span><span class="material-symbols-outlined fh-spec-icon">pool</span> Pool</span>
                            </div>

                            <!-- Extended List-View snippet (visible only in list mode) -->
                            <p class="fh-list-desc"><?= $descSnip ?></p>

                            <?php if (!empty($amList)): ?>
                            <div class="fh-list-amenities">
                                <?php foreach ($amList as $amItem): ?>
                                    <span class="fh-am-tag"><?= htmlspecialchars($amItem) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Price & CTA Row -->
                            <div class="fh-card-price-row">
                                <div class="fh-price-group">
                                    <span class="fh-price-new"><?= fmtPriceFH($roomPrice) ?></span>
                                    <span class="fh-price-unit">/ night</span>
                                    <?php if ($priceOld > $roomPrice): ?>
                                        <span class="fh-price-old"><?= fmtPriceFH($priceOld) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="fh-card-cta-btn">
                                    <span>View Details</span>
                                    <span class="material-symbols-outlined" style="font-size:14px;">arrow_forward</span>
                                </span>
                            </div>
                        </div>

                    </a>
                </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

</div>

<!-- ══════════════════════════════════════════════
     MOBILE FILTER DRAWER OVERLAY
     ══════════════════════════════════════════════ -->
<div class="fh-mob-overlay" id="mob-overlay" onclick="closeMobileFilter()"></div>
<div class="fh-mob-drawer" id="mob-drawer">
    <div class="fh-mob-drawer-header">
        <div style="display:flex;align-items:center;gap:6px;font-size:16px;font-weight:800;color:#0F172A;">
            <span class="material-symbols-outlined" style="color:#1D4ED8;">tune</span>
            <span>Filters &amp; Options</span>
        </div>
        <button type="button" class="fh-mob-close-btn" onclick="closeMobileFilter()">✕</button>
    </div>
    <div class="fh-mob-drawer-body" id="mob-filter-target">
        <?= $sidebarHtml ?>
    </div>
</div>

<script>
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

// ── VIEW TOGGLE (Grid vs List) ──
function setView(mode) {
    const container = document.getElementById('listings-container');
    if (!container) return;

    const isList = (mode === 'list');
    container.classList.toggle('list-view', isList);

    // Desktop switcher
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');
    if (btnGrid && btnList) {
        btnGrid.classList.toggle('active', !isList);
        btnList.classList.toggle('active', isList);
    }

    // Mobile switcher
    const btnGridM = document.getElementById('btn-grid-m');
    const btnListM = document.getElementById('btn-list-m');
    if (btnGridM && btnListM) {
        btnGridM.classList.toggle('active', !isList);
        btnListM.classList.toggle('active', isList);
    }

    localStorage.setItem('fh_view_pref', mode);
}

(function () {
    const saved = localStorage.getItem('fh_view_pref');
    if (saved === 'list') setView('list');
})();

// ── QUICK SORT HANDLER ──
function applyQuickSort(val) {
    const p = new URLSearchParams(window.location.search);
    if (val) {
        p.set('sort', val);
    } else {
        p.delete('sort');
    }
    window.location.href = '<?= url("farmhouses") ?>?' + p.toString();
}

// ── PRICE NOTE SYNC ──
function syncPriceNote() {
    const max = document.getElementById('max-price-input');
    const note = document.getElementById('price-indicator');
    if (!max || !note) return;
    const val = parseInt(max.value) || 0;
    note.textContent = val ? 'Up to ₹' + val.toLocaleString('en-IN') : 'Any Price';
}

function setPriceRange(min, max) {
    const minInput = document.querySelector('[name=min_price]');
    const maxInput = document.getElementById('max-price-input');
    if (minInput) minInput.value = min || '';
    if (maxInput) maxInput.value = max || '';
    syncPriceNote();
}

// ── REMOVE QUERY PARAMS ──
function removeQueryParam(key) {
    const p = new URLSearchParams(window.location.search);
    p.delete(key);
    window.location.href = '<?= url("farmhouses") ?>?' + p.toString();
}

function removeAmenityParam(id) {
    const p = new URLSearchParams(window.location.search);
    const list = p.getAll('amenities[]').filter(v => parseInt(v) !== id);
    p.delete('amenities[]');
    list.forEach(v => p.append('amenities[]', v));
    window.location.href = '<?= url("farmhouses") ?>?' + p.toString();
}

// ── AMENITY SEARCH IN SIDEBAR ──
function filterAmenitiesInSidebar(input) {
    const filter = input.value.toLowerCase().trim();
    const items = input.closest('.fh-filter-box').querySelectorAll('.fh-am-item');
    items.forEach(item => {
        const name = item.getAttribute('data-name') || '';
        item.style.display = (name.indexOf(filter) > -1) ? '' : 'none';
    });
}

// ── MOBILE FILTER DRAWER OPEN / CLOSE ──
function openMobileFilter() {
    const overlay = document.getElementById('mob-overlay');
    const drawer = document.getElementById('mob-drawer');
    if (overlay && drawer) {
        overlay.classList.add('open');
        drawer.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeMobileFilter() {
    const overlay = document.getElementById('mob-overlay');
    const drawer = document.getElementById('mob-drawer');
    if (overlay && drawer) {
        overlay.classList.remove('open');
        drawer.classList.remove('open');
        document.body.style.overflow = '';
    }
}
</script>

<?php include __DIR__ . "/Includes/footer.php"; ?>