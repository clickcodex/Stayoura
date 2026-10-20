<?php
use App\Helpers\CryptoHelper;

/**
 * Views/farmhouse_details.php
 * Rendered by FarmdetailsController::farmhouse_details()
 */

// ── Guard: redirect if no farmhouse found ──
if (empty($farmhouse)) {
    redirect('home');
}

// ── Encrypted ID for forms ──
$encryptedId = htmlspecialchars($_GET['id'] ?? '');

// ── SEO Metadata & Schema Computation ──
$fhTitle    = trim($farmhouse['title'] ?? 'Luxury Farmhouse');
$fhLocation = trim($farmhouse['location'] ?? 'India');
$pageTitle  = "{$fhTitle} in {$fhLocation} | Luxury Farmhouse Booking - Stayora";

$rawDesc = strip_tags($farmhouse['description'] ?? '');
$cleanDesc = trim(preg_replace('/\s+/', ' ', $rawDesc));
if (!empty($cleanDesc)) {
    $pageDescription = (mb_strlen($cleanDesc) > 155) ? mb_substr($cleanDesc, 0, 152) . '...' : $cleanDesc;
} else {
    $pageDescription = "Book {$fhTitle} in {$fhLocation}. Features " . max(1, (int)($farmhouse['bedrooms'] ?? 1)) . " bedrooms, private pool, lawn & luxury amenities. Best price guaranteed on Stayora.";
}

$pageKeywords = "{$fhTitle}, farmhouse in {$fhLocation}, pool villa {$fhLocation}, rent farmhouse {$fhLocation}, private pool stay, party villa, stayora";
$canonicalUrl = absolute_url('farmhouse_details?id=' . urlencode($encryptedId));

$fhPhotos = [];
if (!empty($images) && is_array($images)) {
    foreach (array_slice($images, 0, 6) as $im) {
        if (!empty($im['image_path'])) {
            $fhPhotos[] = farmhouse_img_url($im['image_path']);
        }
    }
}
if (empty($fhPhotos)) {
    $fhPhotos[] = absolute_url('assets/images/uploads/luxury_pool_hero.jpg');
}
$ogImage = $fhPhotos[0];
$ogType  = 'place';

$breadcrumbs = [
    ['name' => 'Home', 'url' => absolute_url('home')],
    ['name' => 'Farmhouses', 'url' => absolute_url('farmhouses')],
    ['name' => $fhLocation, 'url' => absolute_url('farmhouses?location=' . urlencode($fhLocation))],
    ['name' => $fhTitle, 'url' => $canonicalUrl]
];

// Rich VacationRental / Lodging Schema
$customSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'VacationRental',
    '@id'             => $canonicalUrl,
    'name'            => $fhTitle,
    'description'     => $pageDescription,
    'url'             => $canonicalUrl,
    'image'           => $fhPhotos,
    'address'         => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $farmhouse['address'] ?? $fhLocation,
        'addressLocality' => $fhLocation,
        'addressCountry'  => 'IN'
    ],
    'priceRange'      => '₹' . number_format((float)($farmhouse['price'] ?? 0), 0) . ' / night',
    'offers'          => [
        '@type'         => 'Offer',
        'price'         => (string)(float)($farmhouse['price'] ?? 0),
        'priceCurrency' => 'INR',
        'availability'  => 'https://schema.org/InStock',
        'validFrom'     => date('Y-01-01'),
        'url'           => $canonicalUrl
    ],
    'numberOfRooms'   => max(1, (int)($farmhouse['bedrooms'] ?? 1)),
    'occupancy'       => [
        '@type'    => 'QuantitativeValue',
        'maxValue' => max(2, (int)($farmhouse['night_capacity'] ?? 10))
    ]
];

// Include public header
include __DIR__ . "/Includes/header.php";

// ── Flash messages & Booking Mode (F34, F35) ──
$bookingParam     = $_GET['booking'] ?? '';
$isInstantSuccess = ($bookingParam === 'instant_success');
$bookingSuccess   = ($bookingParam === 'success' || $isInstantSuccess);
$reqIdParam       = (int)($_GET['req_id'] ?? 0);
$holdExpiresParam = htmlspecialchars($_GET['hold_expires'] ?? '');
$bookingError     = !empty($_GET['booking_error']) ? htmlspecialchars(urldecode($_GET['booking_error'])) : '';
$isInstantMode    = (($farmhouse['booking_mode'] ?? 'request') === 'instant');

// ── Admin Contact & Site Settings ──
$adminPhone    = htmlspecialchars($siteSettings['mobile_number'] ?? '8889000399');
$adminWhatsApp = htmlspecialchars($siteSettings['whatsapp_number'] ?? $adminPhone);

$adminCleanPhone = preg_replace('/[^0-9+]/', '', $adminPhone);
$adminCleanWa    = preg_replace('/[^0-9]/', '', $adminWhatsApp);

$adminCallLink = $adminCleanPhone ? 'tel:' . $adminCleanPhone : '#';
$adminWaLink   = $adminCleanWa ? 'https://wa.me/91' . $adminCleanWa . '?text=' . urlencode('Hi Stayora Support, I want to inquire about ' . ($farmhouse['title'] ?? 'this farmhouse') . ' (ID: ' . $farmhouse['id'] . ').') : '#';

// ── Capacity & Pricing configuration ──
$fullFarmPrice      = (float)($farmhouse['price'] ?? 0);
$rentalType         = $farmhouse['rental_type'] ?? (!empty($farmhouse['allow_room_booking']) ? 'both' : 'complete');
$supportsComplete   = ($rentalType === 'complete' || $rentalType === 'both');
$supportsRoomWise   = ($rentalType === 'room_wise' || $rentalType === 'both');
$allowRoomBooking   = $supportsRoomWise;
$defaultBookingMode = ($rentalType === 'room_wise') ? 'per_room' : 'complete';
$roomPriceConfig    = (float)($farmhouse['room_price'] ?? 0);
$bedroomCapacity    = max(1, (int)($farmhouse['bedroom_capacity'] ?? 2)); // Default max 2 guests per bedroom
$totalBedrooms      = max(1, (int)($farmhouse['bedrooms'] ?? 1));
$nightCapacity      = (int)($farmhouse['night_capacity'] ?? 0);
$maxAllowedGuests   = $nightCapacity > 0 ? $nightCapacity : ($totalBedrooms * $bedroomCapacity);

$roomTypes = !empty($roomTypes) ? $roomTypes : [];
if ($allowRoomBooking && !empty($roomTypes)) {
    $lowestRtPrice = null;
    foreach ($roomTypes as $rt) {
        $p = (float)$rt['price_per_room'];
        if ($lowestRtPrice === null || ($p > 0 && $p < $lowestRtPrice)) {
            $lowestRtPrice = $p;
        }
    }
    if ($lowestRtPrice !== null && $lowestRtPrice > 0) {
        $ratePerRoom = $lowestRtPrice;
        $roomPriceConfig = $lowestRtPrice;
    }
} else {
    if (!$allowRoomBooking) {
        $roomTypes = [];
    }
    // Rate per room fallback
    $ratePerRoom = ($roomPriceConfig > 0) ? $roomPriceConfig : max(500, round($fullFarmPrice / max(1, $totalBedrooms)));
}

$priceOld         = (int)($fullFarmPrice * 1.25);
$isNego           = !empty($farmhouse['is_negotiable']);

// Initial calculation for Complete Farmhouse mode
$initGuests       = min(2, $maxAllowedGuests);
$initRooms        = $totalBedrooms;
$initBasePrice    = $fullFarmPrice;
$initPlatformFee  = round($initBasePrice * 0.05);
$initTotal        = $initBasePrice + $initPlatformFee;

// ── Google Maps Location & Embed URLs ──
$mapLocationQuery = trim(($farmhouse['location'] ?? '') . (!empty($farmhouse['address']) ? ', ' . $farmhouse['address'] : ''));
$mapEmbedUrl      = build_google_map_embed_url($farmhouse['google_map_link'] ?? '', $mapLocationQuery);
$mapDirectUrl     = build_google_map_direct_url($farmhouse['google_map_link'] ?? '', $mapLocationQuery);

if (!function_exists('fmt')) {
    function fmt(float $n): string {
        return '₹' . number_format($n, 0, '.', ',');
    }
}

// ── Image URLs helper ──
if (!function_exists('resolveFarmImg')) {
    function resolveFarmImg(?string $img): string {
        return farmhouse_img_url($img, 'https://placehold.co/800x550/E8F0EC/1F4D3A?text=Stayora+Estate');
    }
}

$imgCount    = count($images);
$coverImgUrl = resolveFarmImg($images[0]['image_url'] ?? null);
$img2Url     = !empty($images[1]['image_url']) ? resolveFarmImg($images[1]['image_url']) : $coverImgUrl;
$img3Url     = !empty($images[2]['image_url']) ? resolveFarmImg($images[2]['image_url']) : (!empty($images[1]['image_url']) ? resolveFarmImg($images[1]['image_url']) : $coverImgUrl);

// Array of all resolved image URLs for JavaScript gallery modal
$allResolvedImages = [];
$categorizedImages = [
    'all'      => [],
    'exterior' => [],
    'pool'     => [],
    'bedroom'  => [],
    'interior' => [],
    'kitchen'  => []
];

if (!empty($images)) {
    foreach ($images as $im) {
        if (!empty($im['image_url'])) {
            $resolved = resolveFarmImg($im['image_url']);
            $cat = strtolower($im['category'] ?? 'exterior');
            $allResolvedImages[] = $resolved;
            $categorizedImages['all'][] = $resolved;
            if (isset($categorizedImages[$cat])) {
                $categorizedImages[$cat][] = $resolved;
            } else {
                $categorizedImages['exterior'][] = $resolved;
            }
        }
    }
}
if (empty($allResolvedImages)) {
    $allResolvedImages = [$coverImgUrl];
    $categorizedImages['all'] = [$coverImgUrl];
    $categorizedImages['exterior'] = [$coverImgUrl];
}

// ── QR Code & UPI settings ──
$qrFilename = $siteSettings['payment_qr_code'] ?? '';
$qrUrl = '';
if (!empty($qrFilename)) {
    if (str_starts_with($qrFilename, 'http')) {
        $qrUrl = $qrFilename;
    } elseif (file_exists(__DIR__ . '/../../assets/images/uploads/settings/' . $qrFilename)) {
        $qrUrl = asset('assets/images/uploads/settings/' . $qrFilename);
    } else {
        $qrUrl = asset('assets/images/uploads/' . $qrFilename);
    }
}
$upiId           = htmlspecialchars($siteSettings['upi_id'] ?? '');
$paymentNotes    = htmlspecialchars($siteSettings['payment_instructions'] ?? '');
$bankName        = htmlspecialchars($siteSettings['bank_name'] ?? 'HDFC Bank');
$accountName     = htmlspecialchars($siteSettings['account_name'] ?? 'Stayora Hospitality Platform');
$accountNumber   = htmlspecialchars($siteSettings['account_number'] ?? '50200088991122');
$ifscCode        = htmlspecialchars($siteSettings['ifsc_code'] ?? 'HDFC0001234');
$supportPhone    = $adminPhone;
$supportWhatsApp = $adminWhatsApp;

// ── User pre-fills ──
$userName  = htmlspecialchars($currentUser['name']  ?? ($_SESSION['user_name'] ?? ''));
$userEmail = htmlspecialchars($currentUser['email'] ?? ($_SESSION['user_email'] ?? ''));
$userPhone = htmlspecialchars($currentUser['phone'] ?? '');

// ── Group amenities by category / bedroom ──
$amenityGroups = [];
foreach ($amenities as $am) {
    $cat     = $am['category'] ?? 'general';
    $bedNum  = $am['bedroom_number'] ?? null;
    $key     = ($cat === 'bedroom' && $bedNum) ? 'bedroom_' . $bedNum : $cat;
    $amenityGroups[$key][] = $am;
}

// ── JSON for JS calendar & calculations ──
$occupiedJson  = json_encode(array_values($occupiedDates));
$requestedJson = json_encode(array_values($userRequestedDates));
?>

<style>
/* ── RICH DESCRIPTION LIST FORMATTING ── */
.frd-desc-text {
  font-size: 14.5px;
  color: #334155;
  line-height: 1.75;
}
.frd-desc-text ul {
  list-style-type: disc !important;
  list-style-position: outside !important;
  padding-left: 1.5rem !important;
  margin-top: 0.5rem !important;
  margin-bottom: 0.5rem !important;
  display: block !important;
}
.frd-desc-text ol {
  list-style-type: decimal !important;
  list-style-position: outside !important;
  padding-left: 1.5rem !important;
  margin-top: 0.5rem !important;
  margin-bottom: 0.5rem !important;
  display: block !important;
}
.frd-desc-text li {
  display: list-item !important;
  list-style: inherit !important;
  margin-bottom: 0.25rem !important;
}
.frd-desc-text p {
  margin-bottom: 0.5rem !important;
}
.frd-desc-text b,
.frd-desc-text strong {
  font-weight: 700 !important;
}
.frd-desc-text i,
.frd-desc-text em {
  font-style: italic !important;
}
.frd-desc-text u {
  text-decoration: underline !important;
}
.frd-desc-text h1, .frd-desc-text h2, .frd-desc-text h3, .frd-desc-text h4, .frd-desc-text h5, .frd-desc-text h6 {
  color: #0F172A;
  font-weight: 700;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}
.frd-desc-text h1 { font-size: 1.45rem; }
.frd-desc-text h2 { font-size: 1.25rem; }
.frd-desc-text h3 { font-size: 1.1rem; }
.frd-desc-text h4, .frd-desc-text h5, .frd-desc-text h6 { font-size: 1rem; }
.frd-desc-text a {
  color: #1E40AF !important;
  text-decoration: underline !important;
  font-weight: 600 !important;
}
.frd-desc-text a:hover {
  color: #1d4ed8 !important;
}
.frd-desc-text blockquote {
  border-left: 3px solid #cbd5e1;
  padding-left: 12px;
  margin: 8px 0;
  color: #64748B;
  font-style: italic;
}

/* ── DUAL BOOKING MODE SELECTOR & LUXURY PRICING STYLES ── */
.frd-mode-switcher {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  background: #F8FAFC;
  padding: 6px;
  border-radius: 18px;
  margin-bottom: 14px;
  border: 1.5px solid #E2E8F0;
}
.frd-mode-btn {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 2px solid transparent;
  background: #ffffff;
  cursor: pointer;
  text-align: left;
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.frd-mode-btn:hover {
  border-color: #cbd5e1;
  transform: translateY(-1px);
}
.frd-mode-btn.active {
  background: #f0f9ff;
  border-color: #1E40AF;
  box-shadow: 0 4px 16px rgba(2, 132, 199, 0.16);
}
.frd-mode-btn-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 4px;
}
.frd-mode-icon-circle {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: #E0F2FE;
  color: #1E40AF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}
.frd-mode-btn.active .frd-mode-icon-circle {
  background: #1E40AF;
  color: #ffffff;
}
.frd-mode-badge {
  font-size: 9.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding: 2px 7px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #475569;
}
.frd-mode-btn.active .frd-mode-badge {
  background: #1E40AF;
  color: #ffffff;
}
.frd-mode-badge-green {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #a7f3d0;
}
.frd-mode-btn.active .frd-mode-badge-green {
  background: #10b981;
  color: #ffffff;
  border-color: #10b981;
}
.frd-mode-title {
  font-size: 13px;
  font-weight: 800;
  color: #0F172A;
  line-height: 1.25;
}
.frd-mode-sub {
  font-size: 11px;
  font-weight: 700;
  color: #64748B;
}
.frd-mode-btn.active .frd-mode-sub {
  color: #1E40AF;
  font-weight: 800;
}
.frd-mode-info-box {
  background: #f0f9ff;
  border: 1px solid #DBEAFE;
  border-radius: 12px;
  padding: 10px 14px;
  margin-bottom: 14px;
  font-size: 11.5px;
  color: #1E3A8A;
  line-height: 1.45;
  display: flex;
  align-items: flex-start;
  gap: 8px;
}
.frd-room-selector-card {
  background: #ffffff;
  border: 1.5px solid #E2E8F0;
  border-radius: 14px;
  padding: 12px 14px;
  margin-top: 10px;
}
.frd-active-mode-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.frd-active-mode-badge.complete {
  background: #E0F2FE;
  color: #1E3A8A;
  border: 1px solid #DBEAFE;
}
.frd-active-mode-badge.room {
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}
.frd-photo-grid.frd-single-photo {
  grid-template-columns: 1fr;
}
.frd-photo-grid.frd-single-photo .frd-photo-main {
  height: 460px;
}
</style>

<!-- ── FULLSCREEN GALLERY OVERLAY ── -->
<div class="frd-gallery-overlay" id="frd-gallery-overlay">
  <button class="frd-gallery-close" onclick="frdCloseGallery()">✕</button>
  <img class="frd-gallery-img" id="frd-gallery-img" src="" alt="Gallery image"/>
  <div class="frd-gallery-nav">
    <button class="frd-gallery-nav-btn" onclick="frdGalleryNav(-1)">‹ Prev</button>
    <span class="frd-gallery-counter" id="frd-gallery-counter">1 / <?= count($allResolvedImages) ?></span>
    <button class="frd-gallery-nav-btn" onclick="frdGalleryNav(1)">Next ›</button>
  </div>
</div>

<!-- ── PAGE WRAP (adds top padding for sticky header) ── -->
<div class="frd-page-wrap" style="padding-top:92px;">

  <!-- Breadcrumb -->
  <div class="frd-breadcrumb">
    <a href="<?= url('/') ?>">Home</a>
    <span class="sep">›</span>
    <a href="<?= url('/?location=' . urlencode($farmhouse['location'] ?? '')) ?>"><?= htmlspecialchars($farmhouse['location'] ?? 'Farmhouses') ?></a>
    <span class="sep">›</span>
    <span><?= htmlspecialchars($farmhouse['title'] ?? '') ?></span>
  </div>

  <!-- ── Booking SUCCESS banner (F34, F35, F73) ── -->
  <?php if ($bookingSuccess): ?>
  <div class="frd-success-banner" id="frd-success-banner" style="display:flex;align-items:center;justify-content:space-between;gap:16px;background:<?= $isInstantSuccess ? '#ecfdf5' : '#f0fdf4' ?>;border:1.5px solid <?= $isInstantSuccess ? '#10b981' : '#86efac' ?>;border-radius:16px;padding:16px 20px;margin-bottom:24px;box-shadow:0 4px 15px rgba(16,185,129,0.12);flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:12px;min-width:280px;flex:1;">
      <span style="font-size:32px;"><?= $isInstantSuccess ? '⚡' : '🎉' ?></span>
      <div>
        <div style="display:flex;align-items:center;gap:8px;">
          <h4 style="margin:0;font-size:16px;font-weight:900;color:<?= $isInstantSuccess ? '#065f46' : '#166534' ?>;">
            <?= $isInstantSuccess ? 'Instant Booking Confirmed!' : 'Booking Request Submitted Successfully!' ?> <?= $reqIdParam ? "(#REQ-{$reqIdParam})" : '' ?>
          </h4>
          <?php if ($isInstantSuccess): ?>
            <span style="background:#10b981;color:#fff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;">Auto-Approved</span>
          <?php else: ?>
            <span style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;">15-Min Hold Active</span>
          <?php endif; ?>
        </div>
        <p style="margin:3px 0 0;font-size:12.5px;color:<?= $isInstantSuccess ? '#047857' : '#15803d' ?>;line-height:1.45;">
          <?= $isInstantSuccess 
              ? 'Your reservation has been confirmed immediately. Your stay dates are locked with concurrency protection.' 
              : 'Your 15-minute temporary inventory hold has been secured. Please share your payment screenshot on WhatsApp to finalize confirmation.' ?>
        </p>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
      <?php if ($reqIdParam): ?>
      <a href="<?= url('booking/voucher?id=' . $reqIdParam) ?>" target="_blank" rel="noopener"
         style="background:#0F172A;color:#fff;text-decoration:none;padding:9px 15px;border-radius:12px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(15,23,42,0.25);">
        📄 Download Voucher &amp; Invoice (PDF)
      </a>
      <?php endif; ?>
      <?php 
        $waConfirmMsg = urlencode("Hello! " . ($isInstantSuccess ? "I have instantly booked" : "I have submitted booking request") . " " . ($reqIdParam ? "#REQ-{$reqIdParam}" : "") . " for " . ($farmhouse['title'] ?? 'Farmhouse') . " on Stayora. Please find my reservation details.");
      ?>
      <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $supportWhatsApp) ?>?text=<?= $waConfirmMsg ?>" target="_blank" rel="noopener" 
         style="background:#15803d;color:#fff;text-decoration:none;padding:9px 15px;border-radius:12px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(21,128,61,0.2);">
        💬 WhatsApp Confirmation
      </a>
      <button class="frd-sb-close" onclick="document.getElementById('frd-success-banner').style.display='none'" style="background:none;border:none;font-size:22px;color:#166534;cursor:pointer;padding:4px 8px;">×</button>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Booking ERROR banner ── -->
  <?php if ($bookingError): ?>
  <div class="frd-error-banner" id="frd-error-banner" style="
      display:flex;
      align-items:flex-start;
      gap:12px;
      background:#fff5f5;
      border:1px solid #feb2b2;
      border-left:4px solid #e53e3e;
      border-radius:14px;
      padding:16px;
      margin:0 0 24px 0;
      position:relative;
  ">
    <span style="font-size:22px;line-height:1.3;">❌</span>
    <p style="margin:0;color:#c53030;font-size:14px;font-weight:700;line-height:1.5;"><?= $bookingError ?></p>
    <button
      onclick="document.getElementById('frd-error-banner').style.display='none'"
      style="position:absolute;top:12px;right:14px;background:none;border:none;font-size:20px;color:#c53030;cursor:pointer;line-height:1;"
      aria-label="Dismiss">×</button>
  </div>
  <?php endif; ?>

  <!-- ── TITLE AREA ── -->
  <div class="frd-prop-title-row">
    <div class="frd-prop-title-left">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
        <span class="frd-loc-badge" style="background:#E0F2FE;color:#1E3A8A;border-color:#DBEAFE;display:inline-flex;align-items:center;gap:4px;">
          <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">
            <?= htmlspecialchars($farmhouse['property_type_icon'] ?? 'villa') ?>
          </span>
          <?= htmlspecialchars($farmhouse['property_type_name'] ?? ($farmhouse['category'] ?? 'Farmhouse')) ?>
        </span>

        <?php if (!empty($farmhouse['is_verified'])): ?>
          <span class="frd-loc-badge" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;display:inline-flex;align-items:center;gap:4px;" title="<?= htmlspecialchars($farmhouse['verification_notes'] ?? 'Physically Inspected & Host Verified') ?>">
            <span class="material-symbols-outlined" style="font-size:14px;color:#10b981;">verified</span>
            <span>Stayora Verified</span>
          </span>
        <?php endif; ?>
      </div>
      <h1><?= htmlspecialchars($farmhouse['title'] ?? '') ?></h1>
      <div class="frd-prop-location">
        <span style="display:inline-flex;align-items:center;gap:4px;">
          <span class="material-symbols-outlined" style="font-size:16px;color:#1D4ED8;">location_on</span>
          <?= htmlspecialchars($farmhouse['location'] ?? '') ?>
        </span>
        <?php if (!empty($farmhouse['address'])): ?>
          <span style="color:#cbd5e1;">·</span>
          <span style="color:#94a3b8;font-size:13px;"><?= htmlspecialchars($farmhouse['address']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="frd-price-area">
      <div style="text-align:right;">
        <div style="display:inline-flex;align-items:center;gap:6px;margin-bottom:6px;flex-wrap:wrap;justify-content:flex-end;">
          <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#1E40AF;background:#E0F2FE;padding:3px 8px;border-radius:6px;letter-spacing:0.4px;">
            🏡 Complete: <?= fmt($fullFarmPrice) ?>/nt
          </span>
          <?php if ($allowRoomBooking && !empty($ratePerRoom)): ?>
            <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#059669;background:#ecfdf5;border:1px solid #a7f3d0;padding:3px 8px;border-radius:6px;letter-spacing:0.4px;">
              🛏️ Per Room: <?= fmt($ratePerRoom) ?>/nt
            </span>
          <?php endif; ?>
        </div>
        <div class="frd-price-main">
          <span class="frd-price-currency">₹</span>
          <span class="frd-price-num" id="frd-header-price-display"><?= number_format($fullFarmPrice) ?></span>
          <span class="frd-price-period" id="frd-header-period-display"><?= $allowRoomBooking ? '/ night (Complete)' : '/ night' ?></span>
        </div>
        <div style="font-size:11.5px;font-weight:700;color:#64748B;margin-top:2px;" id="frd-header-sub-display">
          🏡 Entire <?= $totalBedrooms ?> BHK Estate · Up to <?= $maxAllowedGuests ?> Overnight Guests
        </div>
        <?php if ($priceOld > $fullFarmPrice): ?>
          <div class="frd-price-strike">₹<?= number_format($priceOld) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── CATEGORIZED GALLERY FILTER PILLS (F08) ── -->
  <div class="frd-gallery-categories" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;overflow-x:auto;padding-bottom:4px;">
    <button type="button" class="frd-cat-pill active" onclick="frdFilterCategory('all', this)" style="background:#0F172A;color:#ffffff;border:1px solid #0F172A;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
      <span class="material-symbols-outlined" style="font-size:14px;">photo_library</span>
      <span>All (<?= count($allResolvedImages) ?>)</span>
    </button>
    <?php 
    $catLabels = [
        'exterior' => ['Exterior & Grounds', 'landscape'],
        'pool'     => ['Swimming Pool', 'pool'],
        'bedroom'  => ['Bedrooms', 'bed'],
        'interior' => ['Living & Interior', 'chair'],
        'kitchen'  => ['Kitchen & Dining', 'restaurant']
    ];
    foreach ($catLabels as $cKey => $cMeta):
      if (!empty($categorizedImages[$cKey])): ?>
        <button type="button" class="frd-cat-pill" onclick="frdFilterCategory('<?= $cKey ?>', this)" style="background:#F8FAFC;color:#475569;border:1px solid #E2E8F0;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all 0.2s;">
          <span class="material-symbols-outlined" style="font-size:14px;"><?= $cMeta[1] ?></span>
          <span><?= $cMeta[0] ?> (<?= count($categorizedImages[$cKey]) ?>)</span>
        </button>
      <?php endif;
    endforeach; ?>
  </div>

  <!-- ── PHOTO GRID ── -->
  <div class="frd-photo-grid <?= ($imgCount <= 1) ? 'frd-single-photo' : '' ?>">
    <div class="frd-photo-main" onclick="frdOpenGallery(0)">
      <img src="<?= $coverImgUrl ?>" alt="<?= htmlspecialchars($farmhouse['title'] ?? '') ?>" id="frd-main-display-img"
           onerror="this.src='https://placehold.co/800x550/E8F0EC/1F4D3A?text=Stayora+Estate'"/>
      <button class="frd-view-all-btn" onclick="event.stopPropagation();frdOpenGallery(0)">
        <span class="material-symbols-outlined" style="font-size:15px;">photo_library</span>
        <span id="frd-view-photos-count">View all <?= max(1, $imgCount) ?> photos</span>
      </button>
    </div>
    <?php if ($imgCount > 1): ?>
    <div class="frd-photo-right">
      <div class="frd-photo-small" onclick="frdOpenGallery(1)">
        <img src="<?= $img2Url ?>" alt="Photo 2"
             onerror="this.src='https://placehold.co/400x270/E8F0EC/1F4D3A?text=Stayora+Estate'"/>
      </div>
      <div class="frd-photo-small" onclick="frdOpenGallery(2)">
        <img src="<?= $img3Url ?>" alt="Photo 3"
             onerror="this.src='https://placehold.co/400x270/E8F0EC/1F4D3A?text=Stayora+Estate'"/>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── MAIN CONTENT GRID ── -->
  <div class="frd-content-grid">

    <!-- ══ LEFT PANEL ══ -->
    <div class="frd-left-panel">

      <!-- Spec bar -->
      <div class="frd-spec-bar">
        <div class="frd-spec-item">
          <div class="frd-spec-icon">
            <span class="material-symbols-outlined" style="font-size:22px;color:#1E40AF;">bed</span>
          </div>
          <div>
            <div class="frd-spec-val"><?= (int)($farmhouse['bedrooms'] ?? 1) ?> BHK</div>
            <div class="frd-spec-lbl"><?= $bedroomCapacity ?> Guests / Room</div>
          </div>
        </div>
        <div class="frd-spec-item">
          <div class="frd-spec-icon">
            <span class="material-symbols-outlined" style="font-size:22px;color:#1E40AF;">groups</span>
          </div>
          <div>
            <div class="frd-spec-val"><?= (int)($farmhouse['night_capacity'] ?? ($totalBedrooms * $bedroomCapacity)) ?> Max</div>
            <div class="frd-spec-lbl">Overnight Stays</div>
          </div>
        </div>
        <?php if (!empty($farmhouse['day_capacity'])): ?>
        <div class="frd-spec-item">
          <div class="frd-spec-icon">
            <span class="material-symbols-outlined" style="font-size:22px;color:#1E40AF;">celebration</span>
          </div>
          <div>
            <div class="frd-spec-val"><?= (int)$farmhouse['day_capacity'] ?> Max</div>
            <div class="frd-spec-lbl">Day Gathering</div>
          </div>
        </div>
        <?php endif; ?>
        <div class="frd-spec-item">
          <div class="frd-spec-icon">
            <span class="material-symbols-outlined" style="font-size:22px;color:#1E40AF;">verified_user</span>
          </div>
          <div>
            <div class="frd-spec-val">Assured</div>
            <div class="frd-spec-lbl">Verified Listing</div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="frd-section-card">
        <h3 class="frd-sec-title">Description</h3>
        <div class="frd-desc-text frd-clamped" id="frd-desc-text">
          <?php
            $descRaw = $farmhouse['description'] ?? 'No description provided.';
            $descDecoded = htmlspecialchars_decode($descRaw, ENT_QUOTES);
            $hasHtml = (strip_tags($descDecoded) !== $descDecoded);
            if ($hasHtml) {
                echo \App\Helpers\HtmlSanitizerHelper::clean($descDecoded);
            } else {
                echo nl2br(htmlspecialchars($descRaw, ENT_QUOTES, 'UTF-8'));
            }
          ?>
        </div>
        <button class="frd-read-more-btn" id="frd-desc-btn" onclick="frdToggleClamp('frd-desc-text','frd-desc-btn')">Read more</button>
      </div>

      <!-- Room Types & Accommodations Breakdown -->
      <?php if ($allowRoomBooking && !empty($roomTypes)): ?>
      <div class="frd-section-card">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
          <h3 class="frd-sec-title" style="margin-bottom:0;">Available Room Types</h3>
          <span style="font-size:12px;font-weight:700;color:#059669;background:#ecfdf5;padding:4px 10px;border-radius:8px;border:1px solid #a7f3d0;">
            <?= count($roomTypes) ?> Room Type<?= count($roomTypes) > 1 ? 's' : '' ?> Available
          </span>
        </div>
        <p style="font-size:12.5px;color:#64748B;margin:0 0 16px;">
          You can book individual rooms or the entire estate. Choose your preferred room category below or in the booking widget.
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:16px;">
          <?php foreach ($roomTypes as $rt): 
            $rtImgs = $rt['images'] ?? [];
            $rtCover = !empty($rt['image_url']) ? farmhouse_img_url($rt['image_url']) : (!empty($rtImgs) ? farmhouse_img_url($rtImgs[0]['image_url']) : 'https://placehold.co/600x400/E8F0EC/1F4D3A?text=' . urlencode($rt['room_type_name']));
            $rtAmenities = $rt['amenities'] ?? [];
          ?>
          <div style="background:#ffffff;border:1.5px solid #E2E8F0;border-radius:18px;overflow:hidden;box-shadow:0 3px 12px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.2s,box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.07)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 3px 12px rgba(0,0,0,0.03)'">
            <!-- Room Image & Gallery Trigger -->
            <div style="position:relative;height:160px;background:#0F172A;overflow:hidden;">
              <img src="<?= $rtCover ?>" alt="<?= htmlspecialchars($rt['room_type_name']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='https://placehold.co/600x400/E8F0EC/1F4D3A?text=Stayora'"/>
              <div style="position:absolute;top:10px;left:10px;display:flex;gap:6px;">
                <span style="background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#ffffff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;">
                  <?= (int)$rt['total_rooms'] ?> Units
                </span>
                <span style="background:rgba(2,132,199,0.85);backdrop-filter:blur(4px);color:#ffffff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px;">
                  <span class="material-symbols-outlined" style="font-size:12px;">group</span>
                  <?= (int)$rt['capacity_per_room'] ?> Guests
                </span>
                <span class="frd-rt-avail-badge" id="frd-rt-avail-badge-<?= $rt['id'] ?>" style="background:rgba(16,185,129,0.88);backdrop-filter:blur(4px);color:#ffffff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px;">
                  <span class="material-symbols-outlined" style="font-size:12px;">check_circle</span>
                  <span id="frd-rt-avail-text-<?= $rt['id'] ?>"><?= (int)$rt['total_rooms'] ?> Avail</span>
                </span>
              </div>
              <?php if (!empty($rtImgs)): ?>
                <button type="button" onclick='frdOpenRoomGallery(<?= json_encode($rtImgs) ?>, <?= json_encode($rt['room_type_name']) ?>)' style="position:absolute;bottom:10px;right:10px;background:rgba(255,255,255,0.92);backdrop-filter:blur(6px);color:#0F172A;font-size:11px;font-weight:800;padding:4px 10px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:4px;box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                  <span class="material-symbols-outlined" style="font-size:14px;color:#1E40AF;">photo_camera</span>
                  <span>Photos (<?= count($rtImgs) ?>)</span>
                </button>
              <?php endif; ?>
            </div>

            <!-- Room Content -->
            <div style="padding:14px;display:flex;flex-direction:column;gap:10px;flex:1;">
              <div>
                <h4 style="font-size:15px;font-weight:800;color:#0F172A;margin:0 0 4px;"><?= htmlspecialchars($rt['room_type_name']) ?></h4>
                <?php if (!empty($rt['description'])): ?>
                  <p style="font-size:11.5px;color:#64748B;margin:0;line-height:1.45;"><?= htmlspecialchars($rt['description']) ?></p>
                <?php endif; ?>
              </div>

              <!-- Room-Specific Amenities (F14) -->
              <?php if (!empty($rtAmenities)): ?>
                <div>
                  <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px;margin-bottom:6px;">Room Features:</div>
                  <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    <?php foreach ($rtAmenities as $ra): ?>
                      <span style="font-size:10.5px;background:#f1f5f9;color:#334155;font-weight:600;padding:2.5px 7px;border-radius:6px;display:inline-flex;align-items:center;gap:3px;">
                        <span class="material-symbols-outlined" style="font-size:12px;color:#1E40AF;"><?= htmlspecialchars($ra['icon_class'] ?: 'check') ?></span>
                        <?= htmlspecialchars($ra['name']) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Pricing Footer (F20) -->
            <div style="padding:12px 14px;background:#F8FAFC;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
              <div>
                <span style="font-size:10.5px;color:#64748B;font-weight:600;display:block;">Weekday / Weekend</span>
                <span style="font-size:15px;font-weight:900;color:#059669;">₹<?= number_format((float)$rt['price_per_room']) ?> <span style="font-size:11px;font-weight:600;color:#64748B;">(₹<?= number_format((float)$rt['weekend_price']) ?> Fri-Sun)</span></span>
              </div>
              <button type="button" onclick="frdSelectRoomTypeForBooking(<?= $rt['id'] ?>)" style="padding:6px 12px;background:#1E40AF;color:#fff;border:none;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                <span>Select</span>
                <span class="material-symbols-outlined" style="font-size:14px;">arrow_forward</span>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Amenities -->
      <div class="frd-section-card" id="frd-amenities-section">
        <div class="frd-sec-header-row">
          <div>
            <h3 class="frd-sec-title" style="margin-bottom:2px;">Amenities &amp; Features</h3>
            <span class="frd-sec-subtitle"><?= count($amenities) ?> curated amenities available at this property</span>
          </div>
        </div>

        <?php if (empty($amenities)): ?>
          <p style="color:#94a3b8;font-size:13px;margin-top:12px;">No specific amenities listed for this farmhouse.</p>
        <?php else: ?>
          <?php
            // Build unique categories for filter tabs
            $amenityCategories = [];
            foreach ($amenities as $am) {
                $cName = !empty($am['category']) ? trim(ucfirst($am['category'])) : 'General';
                if (!isset($amenityCategories[$cName])) {
                    $amenityCategories[$cName] = 0;
                }
                $amenityCategories[$cName]++;
            }
          ?>

          <!-- Category Filter Bar (Tabs) -->
          <?php if (count($amenityCategories) > 1): ?>
          <div class="frd-amenity-filter-bar">
            <button type="button" class="frd-am-tab active" data-filter="all" onclick="frdFilterAmenities('all', this)">
              <span>All</span> <span class="frd-am-tab-count"><?= count($amenities) ?></span>
            </button>
            <?php foreach ($amenityCategories as $catName => $catCount): ?>
              <button type="button" class="frd-am-tab" data-filter="<?= htmlspecialchars(strtolower($catName)) ?>" onclick="frdFilterAmenities('<?= htmlspecialchars(strtolower($catName)) ?>', this)">
                <span><?= htmlspecialchars($catName) ?></span> <span class="frd-am-tab-count"><?= $catCount ?></span>
              </button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Unified Responsive Grid of Amenities -->
          <div class="frd-amenities-grid" id="frd-amenities-grid">
            <?php 
            $initialLimit = 9;
            $hasMoreAmenities = count($amenities) > $initialLimit;
            foreach ($amenities as $idx => $item): 
              $amCat = !empty($item['category']) ? trim(strtolower($item['category'])) : 'general';
              $amIcon = get_stayora_amenity_icon($item['name'] ?? '', $item['category'] ?? '', $item['icon_class'] ?? '');
              $isOverLimit = $idx >= $initialLimit;
            ?>
            <div class="frd-amenity-card <?= $isOverLimit ? 'frd-am-hidden-initial' : '' ?>" 
                 data-category="<?= htmlspecialchars($amCat) ?>"
                 data-index="<?= $idx ?>"
                 title="<?= htmlspecialchars($item['name']) ?>">
              <div class="frd-am-icon-wrap">
                <span class="material-symbols-outlined frd-am-icon"><?= htmlspecialchars($amIcon) ?></span>
              </div>
              <div class="frd-am-body">
                <span class="frd-am-name"><?= htmlspecialchars($item['name']) ?></span>
                <?php if (!empty($item['category'])): ?>
                  <span class="frd-am-category-tag"><?= htmlspecialchars(ucfirst($item['category'])) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <?php if ($hasMoreAmenities): ?>
          <div style="margin-top:16px;text-align:center;">
            <button type="button" class="frd-view-all-amenities-btn" id="frd-toggle-amenities-btn" onclick="frdToggleAllAmenities(<?= count($amenities) ?>)">
              <span class="material-symbols-outlined" id="frd-toggle-amenities-icon" style="font-size:18px;">apps</span>
              <span id="frd-toggle-amenities-text">Show All <?= count($amenities) ?> Amenities</span>
            </button>
          </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>

      <!-- House Rules -->
      <div class="frd-section-card" id="frd-rules-section">
        <div class="frd-sec-header-row">
          <div>
            <h3 class="frd-sec-title" style="margin-bottom:2px;">House Rules</h3>
            <span class="frd-sec-subtitle">Please review host guidelines &amp; property policies</span>
          </div>
        </div>

        <?php if (empty($rules)): ?>
          <p style="color:#94a3b8;font-size:13px;margin-top:12px;">No specific house rules specified by host.</p>
        <?php else: ?>
        <div class="frd-rules-grid">
          <?php foreach ($rules as $rule): 
            $ruleName = $rule['rule_name'] ?? '';
            $isAllowed = !empty($rule['is_allowed']);
            $ruleIcon = get_stayora_rule_icon($ruleName, $rule['icon_class'] ?? '');
          ?>
          <div class="frd-rule-card <?= $isAllowed ? 'frd-rule-allowed' : 'frd-rule-prohibited' ?>">
            <div class="frd-rule-main">
              <div class="frd-rule-icon-box">
                <span class="material-symbols-outlined frd-rule-icon"><?= htmlspecialchars($ruleIcon) ?></span>
              </div>
              <div class="frd-rule-content">
                <span class="frd-rule-name"><?= htmlspecialchars($ruleName) ?></span>
                <?php if (!empty($rule['description'])): ?>
                  <span class="frd-rule-desc"><?= htmlspecialchars($rule['description']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="frd-rule-badge <?= $isAllowed ? 'frd-allowed' : 'frd-not-allowed' ?>">
              <span class="material-symbols-outlined" style="font-size:13px;"><?= $isAllowed ? 'check_circle' : 'cancel' ?></span>
              <span><?= $isAllowed ? 'Allowed' : 'Not Allowed' ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Policies -->
      <div class="frd-section-card">
        <h3 class="frd-sec-title">Policies</h3>
        <div class="frd-policy-row-grid">
          <div class="frd-policy-card">
            <div class="frd-policy-icon">
              <span class="material-symbols-outlined" style="font-size:22px;color:#1D4ED8;">schedule</span>
            </div>
            <div class="frd-policy-body">
              <strong>Check-in / Check-out</strong>
              <span>Check-in: 12:00 PM &nbsp;·&nbsp; Check-out: Before 11:00 AM (Next Day)</span>
            </div>
          </div>
          <div class="frd-policy-card">
            <div class="frd-policy-icon">
              <span class="material-symbols-outlined" style="font-size:22px;color:#1D4ED8;">published_with_changes</span>
            </div>
            <div class="frd-policy-body">
              <strong>Cancellation Policy</strong>
              <span>Free cancellation up to 7 days before check-in.</span>
              <button class="frd-policy-link" onclick="document.getElementById('frd-cancel-modal').style.display='flex'">View policy details</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Location & Surroundings (Embedded Google Map) -->
      <div class="frd-section-card" id="frd-location-map-section">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
          <div>
            <h3 class="frd-sec-title" style="margin-bottom:4px;">Where You'll Be</h3>
            <div style="display:flex;align-items:center;gap:6px;font-size:13.5px;color:#475569;font-weight:600;">
              <span class="material-symbols-outlined" style="font-size:18px;color:#ef4444;">location_on</span>
              <span><?= htmlspecialchars($farmhouse['location'] ?? 'India') ?></span>
              <?php if (!empty($farmhouse['address'])): ?>
                <span style="color:#94a3b8;">·</span>
                <span style="color:#64748B;"><?= htmlspecialchars($farmhouse['address']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <a href="<?= htmlspecialchars($mapDirectUrl) ?>" target="_blank" rel="noopener noreferrer" 
             style="display:inline-flex;align-items:center;gap:6px;background:#f0f9ff;border:1px solid #DBEAFE;color:#1E40AF;padding:8px 14px;border-radius:12px;font-size:12px;font-weight:800;text-decoration:none;transition:all 0.2s;box-shadow:0 1px 3px rgba(2,132,199,0.08);"
             onmouseover="this.style.background='#E0F2FE'" onmouseout="this.style.background='#f0f9ff'">
            <span class="material-symbols-outlined" style="font-size:16px;">map</span>
            <span>Open in Google Maps</span>
            <span class="material-symbols-outlined" style="font-size:14px;">open_in_new</span>
          </a>
        </div>

        <!-- Embedded Interactive Google Map -->
        <div style="position:relative;width:100%;height:380px;border-radius:20px;overflow:hidden;border:1.5px solid #E2E8F0;background:#F8FAFC;box-shadow:0 4px 20px rgba(0,0,0,0.04);">
          <iframe 
            src="<?= htmlspecialchars($mapEmbedUrl) ?>" 
            width="100%" 
            height="100%" 
            style="border:0;display:block;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            title="Google Map Location for <?= htmlspecialchars($farmhouse['title'] ?? 'Farmhouse') ?>">
          </iframe>
          
          <!-- Subtle corner floating badge -->
          <div style="position:absolute;bottom:12px;left:12px;background:rgba(255,255,255,0.92);backdrop-filter:blur(6px);border:1px solid #cbd5e1;padding:6px 12px;border-radius:10px;display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#0F172A;box-shadow:0 2px 8px rgba(0,0,0,0.08);pointer-events:none;">
            <span class="material-symbols-outlined" style="font-size:15px;color:#ef4444;">pin_drop</span>
            <span><?= htmlspecialchars($farmhouse['title'] ?? 'Farmhouse') ?></span>
          </div>
        </div>
      </div>

      <!-- Calendar / Availability -->
      <div class="frd-section-card" id="frd-cal-section">
        <h3 class="frd-sec-title">Availability &amp; Booking Dates</h3>
        <p class="frd-cal-hint">Select your check-in and check-out dates on the calendar:</p>

        <!-- Calendar Month Header & Nav -->
        <div class="frd-cal-controls">
          <button class="frd-cal-nav-btn" onclick="frdPrevMonth()">‹</button>
          <div class="frd-cal-labels">
            <span id="frd-month1-label"></span>
            <span id="frd-month2-label"></span>
          </div>
          <button class="frd-cal-nav-btn" onclick="frdNextMonth()">›</button>
        </div>

        <!-- 2-Month Grid Wrap -->
        <div class="frd-cal-months" id="frd-cal-months-wrap"></div>

        <!-- Legend -->
        <div class="frd-cal-legend">
          <div class="frd-leg-item"><div class="frd-leg-dot frd-leg-avail"></div>Available</div>
          <div class="frd-leg-item"><div class="frd-leg-dot frd-leg-selected"></div>Selected</div>
          <div class="frd-leg-item"><div class="frd-leg-dot frd-leg-booked"></div>Occupied / Blocked</div>
          <?php if ($userId): ?>
          <div class="frd-leg-item"><div class="frd-leg-dot frd-leg-requested"></div>Your Pending</div>
          <?php endif; ?>
        </div>

        <!-- Checkin / Checkout summary banner -->
        <div class="frd-cal-selection-bar">
          <div class="frd-cal-sel-col">
            <span class="frd-cal-sel-lbl">Check-in</span>
            <strong id="frd-ci-date-label">Select date</strong>
          </div>
          <div class="frd-cal-arrow">→</div>
          <div class="frd-cal-sel-col">
            <span class="frd-cal-sel-lbl">Check-out</span>
            <strong id="frd-co-date-label">Select date</strong>
          </div>
        </div>

        <!-- Room-Wise Granular Calendar Grid (F29) Toggle & Section -->
        <?php if ($allowRoomBooking && !empty($roomTypes)): ?>
        <div style="margin-top:16px;border-top:1px dashed #E2E8F0;padding-top:16px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
              <h4 style="margin:0;font-size:13.5px;font-weight:800;color:#0F172A;display:flex;align-items:center;gap:6px;">
                <span class="material-symbols-outlined" style="font-size:18px;color:#1E40AF;">grid_view</span>
                <span>Room-Wise Granular Availability Grid</span>
              </h4>
              <p style="margin:2px 0 0;font-size:11.5px;color:#64748B;">
                Live inventory tracking across room categories. View units available per date.
              </p>
            </div>
            <button type="button" id="frd-toggle-room-grid-btn" onclick="frdToggleRoomMatrix()"
                    style="padding:6px 12px;background:#f0f9ff;border:1.5px solid #DBEAFE;border-radius:10px;font-size:11.5px;font-weight:700;color:#1E40AF;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
              <span class="material-symbols-outlined" style="font-size:15px;">calendar_view_month</span>
              <span id="frd-toggle-room-grid-label">Show Room Inventory Matrix</span>
            </button>
          </div>

          <!-- Collapsible Matrix Container -->
          <div id="frd-room-matrix-wrap" style="display:none;margin-top:14px;background:#ffffff;border:1.5px solid #E2E8F0;border-radius:14px;padding:14px;overflow-x:auto;box-shadow:0 2px 10px rgba(0,0,0,0.03);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;gap:8px;flex-wrap:wrap;">
              <span id="frd-matrix-month-title" style="font-size:13px;font-weight:800;color:#0F172A;"></span>
              <div style="display:flex;align-items:center;gap:10px;font-size:10.5px;color:#64748B;font-weight:600;">
                <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:3px;background:#10b981;display:inline-block;"></span> Available</span>
                <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:3px;background:#f59e0b;display:inline-block;"></span> Partial</span>
                <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:3px;background:#ef4444;display:inline-block;"></span> Sold Out</span>
              </div>
            </div>
            <div id="frd-room-matrix-table-wrap" style="font-size:11.5px;min-width:320px;">
              <div style="text-align:center;padding:16px;color:#94a3b8;">Loading room calendar matrix...</div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- My pending requests -->
        <?php if ($userId && !empty($myPendingRequests)): ?>
        <div class="frd-my-requests" style="background:#fffbeb;border:1px solid #fef3c7;border-radius:14px;padding:16px;margin-top:16px;">
          <h4 style="font-size:13.5px;font-weight:800;color:#b45309;margin:0 0 10px;display:flex;align-items:center;gap:6px;">
            <span class="material-symbols-outlined" style="font-size:18px;">hourglass_top</span>
            <span>Your Pending Booking Inquiries</span>
          </h4>
          <?php foreach ($myPendingRequests as $req): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px dashed #fde68a;font-size:12.5px;color:#78350f;">
            <span><span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">calendar_month</span> <?= htmlspecialchars($req['check_in']) ?> → <?= htmlspecialchars($req['check_out']) ?></span>
            <span><span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">groups</span> <?= (int)$req['guests'] ?> guests</span>
            <strong>₹<?= number_format((float)$req['price']) ?> Total</strong>
            <span style="background:#fef3c7;color:#b45309;padding:2px 8px;border-radius:6px;font-weight:700;font-size:11px;">Pending Approval</span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── VERIFIED GUEST REVIEWS & MULTI-CRITERIA RATINGS (F52, F53) ── -->
        <div class="frd-reviews-section" style="margin-top:28px;padding-top:24px;border-top:1.5px solid #E2E8F0;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
            <div>
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="material-symbols-outlined" style="font-size:24px;color:#f59e0b;font-variation-settings:'FILL' 1;">star</span>
                <span style="font-size:20px;font-weight:900;color:#0F172A;letter-spacing:-0.4px;">
                  <?= number_format($reviewSummary['avg_overall'] ?? 5.0, 1) ?> · <?= ($reviewSummary['total_reviews'] ?? 0) ?> Verified Reviews
                </span>
              </div>
              <p style="font-size:12px;color:#64748B;margin-top:3px;font-weight:500;">
                Aggregated ratings from verified guests who completed their stay.
              </p>
            </div>

            <button type="button" onclick="document.getElementById('frdReviewModal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:6px;background:#0F172A;color:#fff;border:none;padding:8px 16px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;transition:all 0.2s;">
              <span class="material-symbols-outlined" style="font-size:16px;">rate_review</span>
              <span>Write a Review</span>
            </button>
          </div>

          <!-- Multi-Criteria Progress Breakdown (F53) -->
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:16px;padding:16px;margin-bottom:20px;">
            <div>
              <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                <span>✨ Cleanliness</span>
                <span><?= number_format($reviewSummary['avg_cleanliness'] ?? 5.0, 1) ?>/5</span>
              </div>
              <div style="height:6px;background:#E2E8F0;border-radius:999px;overflow:hidden;">
                <div style="height:100%;background:#10b981;border-radius:999px;width:<?= (($reviewSummary['avg_cleanliness'] ?? 5.0)/5)*100 ?>%;"></div>
              </div>
            </div>

            <div>
              <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                <span>📍 Location &amp; Vibe</span>
                <span><?= number_format($reviewSummary['avg_location'] ?? 5.0, 1) ?>/5</span>
              </div>
              <div style="height:6px;background:#E2E8F0;border-radius:999px;overflow:hidden;">
                <div style="height:100%;background:#1E40AF;border-radius:999px;width:<?= (($reviewSummary['avg_location'] ?? 5.0)/5)*100 ?>%;"></div>
              </div>
            </div>

            <div>
              <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                <span>💰 Value for Money</span>
                <span><?= number_format($reviewSummary['avg_value'] ?? 5.0, 1) ?>/5</span>
              </div>
              <div style="height:6px;background:#E2E8F0;border-radius:999px;overflow:hidden;">
                <div style="height:100%;background:#f59e0b;border-radius:999px;width:<?= (($reviewSummary['avg_value'] ?? 5.0)/5)*100 ?>%;"></div>
              </div>
            </div>

            <div>
              <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                <span>🤝 Host &amp; Staff</span>
                <span><?= number_format($reviewSummary['avg_hospitality'] ?? 5.0, 1) ?>/5</span>
              </div>
              <div style="height:6px;background:#E2E8F0;border-radius:999px;overflow:hidden;">
                <div style="height:100%;background:#8b5cf6;border-radius:999px;width:<?= (($reviewSummary['avg_hospitality'] ?? 5.0)/5)*100 ?>%;"></div>
              </div>
            </div>
          </div>

          <!-- Approved Reviews List -->
          <?php if (empty($approvedReviews)): ?>
            <div style="text-align:center;padding:24px 16px;background:#fff;border:1px dashed #cbd5e1;border-radius:14px;color:#64748B;font-size:12.5px;">
              <span class="material-symbols-outlined" style="font-size:28px;color:#94a3b8;display:block;margin-bottom:6px;">rate_review</span>
              <strong>No guest reviews published yet.</strong>
              <p style="font-size:11.5px;color:#94a3b8;margin-top:2px;">Have you stayed here? Be the first to share your experience!</p>
            </div>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:14px;">
              <?php foreach ($approvedReviews as $rev): ?>
                <div style="background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,0.03);">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                      <div style="width:34px;height:34px;border-radius:10px;background:#f1f5f9;color:#0F172A;font-weight:800;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <?= strtoupper(substr($rev['guest_name'], 0, 1)) ?>
                      </div>
                      <div>
                        <div style="display:flex;align-items:center;gap:6px;">
                          <strong style="font-size:13px;color:#0F172A;"><?= htmlspecialchars($rev['guest_name']) ?></strong>
                          <?php if (!empty($rev['is_verified_stay'])): ?>
                            <span style="font-size:10px;font-weight:700;color:#059669;background:#ecfdf5;border:1px solid #a7f3d0;padding:1px 6px;border-radius:999px;">
                              Verified Stay
                            </span>
                          <?php endif; ?>
                        </div>
                        <span style="font-size:11px;color:#94a3b8;"><?= date('M Y', strtotime($rev['created_at'])) ?></span>
                      </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:2px;color:#f59e0b;font-weight:800;font-size:12px;">
                      <span class="material-symbols-outlined" style="font-size:15px;font-variation-settings:'FILL' 1;">star</span>
                      <span><?= number_format($rev['overall_rating'], 1) ?></span>
                    </div>
                  </div>
                  <?php if (!empty($rev['review_title'])): ?>
                    <h5 style="font-size:13px;font-weight:800;color:#0F172A;margin:0 0 4px;"><?= htmlspecialchars($rev['review_title']) ?></h5>
                  <?php endif; ?>
                  <p style="font-size:12px;color:#475569;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>

                  <!-- Official Host Responses -->
                  <?php if (!empty($rev['responses'])): ?>
                    <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px;">
                      <?php foreach ($rev['responses'] as $resp): ?>
                        <div style="padding:12px 14px;background:#f8fafc;border-left:3px solid #0284c7;border-radius:10px;font-size:11.5px;">
                          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                            <strong style="color:#0f172a;display:flex;align-items:center;gap:5px;">
                              <span class="material-symbols-outlined" style="font-size:15px;color:#0284c7;">reply</span>
                              <?= htmlspecialchars($resp['responder_name']) ?>
                              <span style="font-size:9px;font-weight:700;color:#0369a1;background:#e0f2fe;padding:1px 5px;border-radius:4px;text-transform:capitalize;"><?= htmlspecialchars($resp['responder_type']) ?></span>
                            </strong>
                            <span style="font-size:10.5px;color:#94a3b8;"><?= date('M Y', strtotime($resp['created_at'])) ?></span>
                          </div>
                          <p style="color:#334155;line-height:1.5;margin:0;"><?= nl2br(htmlspecialchars($resp['response_text'])) ?></p>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>

    </div><!-- /frd-left-panel -->

    <!-- ══ RIGHT: BOOKING CARD ══ -->
    <div>
      <div class="frd-booking-card" id="frd-booking-card-main">
        
        <!-- ── DUAL BOOKING MODE TOGGLE ── -->
        <?php if ($rentalType === 'both'): ?>
        <div class="frd-mode-switcher">
          <button type="button" class="frd-mode-btn active" id="frd-mode-btn-complete" onclick="frdSetBookingMode('complete')">
            <div class="frd-mode-btn-header">
              <div class="frd-mode-icon-circle">🏡</div>
              <span class="frd-mode-badge">Entire Estate</span>
            </div>
            <div class="frd-mode-title">Complete Farmhouse</div>
            <div class="frd-mode-sub"><?= fmt($fullFarmPrice) ?> / nt</div>
          </button>

          <button type="button" class="frd-mode-btn" id="frd-mode-btn-room" onclick="frdSetBookingMode('per_room')">
            <div class="frd-mode-btn-header">
              <div class="frd-mode-icon-circle">🛏️</div>
              <span class="frd-mode-badge frd-mode-badge-green">By Rooms</span>
            </div>
            <div class="frd-mode-title">Per Room Booking</div>
            <div class="frd-mode-sub"><?= fmt($ratePerRoom) ?> / room / nt</div>
          </button>
        </div>
        <?php elseif ($rentalType === 'room_wise'): ?>
        <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:14px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
          <div style="width:34px;height:34px;border-radius:10px;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:18px;">🛏️</div>
          <div>
            <div style="font-size:12.5px;font-weight:800;color:#166534;">Room-Wise Booking Only</div>
            <div style="font-size:11px;color:#15803d;">Reserve individual rooms or room types for your group</div>
          </div>
        </div>
        <?php else: ?>
        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:14px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
          <div style="width:34px;height:34px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:18px;">🏡</div>
          <div>
            <div style="font-size:12.5px;font-weight:800;color:#1e40af;">Complete Estate Booking Only</div>
            <div style="font-size:11px;color:#1d4ed8;">Reserve the entire private property exclusively</div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ── MODE BENEFIT INFO BANNER ── -->
        <div class="frd-mode-info-box" id="frd-mode-info-box">
          <span class="material-symbols-outlined" style="font-size:18px;color:#1E40AF;shrink:0;margin-top:1px;" id="frd-mode-info-icon">villa</span>
          <span id="frd-mode-info-text">
            <strong>Complete Farmhouse Option:</strong> You are booking the entire <strong><?= $totalBedrooms ?> BHK</strong> estate exclusively for your group (Up to <?= $maxAllowedGuests ?> overnight guests).
          </span>
        </div>

        <!-- Instant Booking or Request to Book Badge (F35) -->
        <div style="margin-bottom:12px;">
          <?php if ($isInstantMode): ?>
            <div style="display:inline-flex;align-items:center;gap:6px;background:#ecfdf5;border:1.5px solid #10b981;color:#059669;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:0.3px;">
              <span class="material-symbols-outlined" style="font-size:15px;color:#059669;">bolt</span>
              <span>Instant Confirmation Enabled</span>
            </div>
          <?php else: ?>
            <div style="display:inline-flex;align-items:center;gap:6px;background:#F8FAFC;border:1px solid #cbd5e1;color:#64748B;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;">
              <span class="material-symbols-outlined" style="font-size:15px;color:#64748B;">schedule</span>
              <span>Request to Book (Host Approval)</span>
            </div>
          <?php endif; ?>
        </div>

        <div style="margin-bottom:12px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2px;">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#1E40AF;letter-spacing:0.5px;" id="frd-bc-rate-type-label">Complete Farmhouse Rate</div>
            <span class="frd-active-mode-badge complete" id="frd-active-badge-tag">🏡 Complete Estate</span>
          </div>
          <div class="frd-bc-price-top" id="frd-bc-price-header">
            <?= fmt($fullFarmPrice) ?> <span>/ night</span>
          </div>
          <span class="frd-bc-price-sub" id="frd-bc-price-sub">🏡 Entire Estate · <?= $totalBedrooms ?> BHK · Up to <?= $maxAllowedGuests ?> Overnight Guests</span>
        </div>

        <?php if ($isNego): ?>
          <div class="frd-bc-nego">
            <span class="material-symbols-outlined" style="font-size:14px;color:#f59e0b;vertical-align:middle;">chat</span>
            <span>Price is open to negotiation for extended stays</span>
          </div>
        <?php endif; ?>

        <!-- Guest counter -->
        <div class="frd-bc-guest-row">
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
              <div class="frd-bc-guest-label">Number of Guests</div>
              <div class="frd-bc-guest-sub" id="frd-bc-guest-sub-label">Children included · Max <?= $bedroomCapacity ?> guests / room</div>
            </div>
            <div class="frd-bc-guest-ctrl">
              <button type="button" class="frd-bc-btn frd-minus" onclick="frdBcChg(-1)" aria-label="Decrease Guests">−</button>
              <span class="frd-bc-gcount" id="frd-bc-g"><?= $initGuests ?></span>
              <button type="button" class="frd-bc-btn frd-plus" onclick="frdBcChg(1)" aria-label="Increase Guests">+</button>
            </div>
          </div>

          <!-- Room Type Selector (in Per Room mode) -->
          <?php if (!empty($roomTypes)): ?>
          <div id="frd-room-type-picker-wrapper" style="display:none;margin-top:10px;padding:12px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:14px;">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#15803d;letter-spacing:0.5px;margin-bottom:6px;display:flex;align-items:center;gap:4px;">
              <span class="material-symbols-outlined" style="font-size:16px;">hotel</span>
              <span>Select Room Type</span>
            </div>
            <select id="frd-room-type-select" onchange="frdOnRoomTypeChange(this.value)" 
                    style="width:100%;padding:9px 12px;border:1.5px solid #22c55e;border-radius:10px;font-size:12.5px;font-weight:700;color:#0F172A;background:#fff;outline:none;cursor:pointer;">
              <?php foreach ($roomTypes as $idx => $rt): ?>
                <option value="<?= $rt['id'] ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rt['room_type_name']) ?> — ₹<?= number_format((float)$rt['price_per_room']) ?>/nt (<?= (int)$rt['total_rooms'] ?> available)
                </option>
              <?php endforeach; ?>
            </select>
            <div id="frd-selected-room-type-desc" style="font-size:11px;color:#166534;margin-top:6px;font-weight:500;">
              <?= htmlspecialchars($roomTypes[0]['description'] ?? '') ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Room Allocation Stepper (in Per Room mode) -->
          <div id="frd-room-selector-wrapper" class="frd-room-selector-card" style="display:none;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <div>
                <div style="font-size:12px;font-weight:800;color:#0F172A;">Rooms to Book</div>
                <div style="font-size:11px;color:#64748B;font-weight:600;" id="frd-room-calc-hint">Auto: 1 room for 2 guests</div>
              </div>
              <div class="frd-bc-guest-ctrl">
                <button type="button" class="frd-bc-btn frd-minus" onclick="frdRoomChg(-1)" aria-label="Decrease Rooms">−</button>
                <span class="frd-bc-gcount" id="frd-bc-rooms-display">1</span>
                <button type="button" class="frd-bc-btn frd-plus" onclick="frdRoomChg(1)" aria-label="Increase Rooms">+</button>
              </div>
            </div>
          </div>

          <!-- Real-Time Room Allocation Pill -->
          <div id="frd-room-allocation-pill" class="frd-room-pill" style="margin-top:8px;">
            <span class="material-symbols-outlined" style="font-size:14px;color:#1E40AF;vertical-align:middle;">villa</span>
            <span>Entire <?= $totalBedrooms ?> BHK Farmhouse (<?= $maxAllowedGuests ?> Guests Max)</span>
          </div>
        </div>

        <!-- Selected dates display -->
        <div class="frd-bc-dates-display" id="frd-bc-dates-display">
          <strong>Check-in:</strong> <span id="frd-bc-ci">Not selected</span><br/>
          <strong>Check-out:</strong> <span id="frd-bc-co">Not selected</span>
        </div>

        <!-- Long-Stay Savings Notice (F22) -->
        <?php if (!empty($farmhouse['weekly_discount_percent']) || !empty($farmhouse['monthly_discount_percent'])): ?>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:8px 12px;margin-bottom:12px;display:flex;align-items:center;gap:8px;font-size:11.5px;color:#1e40af;font-weight:600;">
          <span class="material-symbols-outlined" style="font-size:16px;color:#2563eb;">savings</span>
          <span>Stay 7+ nights &amp; save <?= (float)($farmhouse['weekly_discount_percent'] ?? 10) ?>% • Stay 28+ nights save <?= (float)($farmhouse['monthly_discount_percent'] ?? 20) ?>%</span>
        </div>
        <?php endif; ?>

        <!-- Addon Services Selector (F24) -->
        <?php if (!empty($addons)): ?>
        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:12px;margin-bottom:12px;">
          <div style="font-size:11.5px;font-weight:800;text-transform:uppercase;color:#0F172A;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:4px;">
              <span class="material-symbols-outlined" style="font-size:15px;color:#1E40AF;">add_circle</span>
              <span>Enhance Your Stay (Addons)</span>
            </span>
            <span style="font-size:10.5px;color:#64748B;font-weight:600;">Optional</span>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;">
            <?php foreach ($addons as $ad): ?>
            <label style="display:flex;align-items:center;justify-content:space-between;background:#fff;border:1px solid #cbd5e1;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:12px;color:#0F172A;transition:border-color 0.15s;" onmouseover="this.style.borderColor='#1E40AF'" onmouseout="this.style.borderColor='#cbd5e1'">
              <span style="display:flex;align-items:center;gap:6px;">
                <input type="checkbox" name="selected_addons[]" value="<?= $ad['id'] ?>" onchange="frdToggleAddon(<?= $ad['id'] ?>)" class="frd-addon-checkbox" style="cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:15px;color:#64748B;"><?= htmlspecialchars($ad['icon_class'] ?: 'star') ?></span>
                <span style="font-weight:600;"><?= htmlspecialchars($ad['name']) ?></span>
              </span>
              <strong style="color:#059669;font-size:11.5px;">+₹<?= number_format((float)$ad['price']) ?> <span style="font-size:10px;color:#94a3b8;font-weight:500;"><?= $ad['price_type'] === 'per_night' ? '/nt' : '' ?></span></strong>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Promotional Coupon Voucher Input (F74) -->
        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:12px;margin-bottom:12px;">
          <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748B;letter-spacing:0.5px;margin-bottom:6px;display:flex;align-items:center;gap:4px;">
            <span class="material-symbols-outlined" style="font-size:15px;color:#1E40AF;">confirmation_number</span>
            <span>Promo Coupon Code</span>
          </div>
          <div style="display:flex;gap:6px;">
            <input type="text" id="frd-coupon-input" placeholder="e.g. WELCOME10, STAYORA500" style="flex:1;padding:7px 10px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:12px;font-family:monospace;font-weight:700;text-transform:uppercase;outline:none;" onkeyup="if(event.key==='Enter') frdApplyCoupon();">
            <button type="button" onclick="frdApplyCoupon()" id="frd-coupon-apply-btn" style="padding:7px 12px;background:#0F172A;color:#fff;border:none;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;">Apply</button>
          </div>
          <div id="frd-coupon-msg" style="display:none;font-size:11px;margin-top:6px;font-weight:600;"></div>
        </div>

        <!-- Detailed Transparent Price Breakdown (F19, F20, F21, F22, F23, F24, F25, F74) -->
        <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:14px;padding:14px;margin-bottom:14px;">
          <div class="frd-breakdown-row">
            <span>Booking Option:</span>
            <strong id="frd-bc-mode-name" style="color:#1E40AF;">Complete Farmhouse</strong>
          </div>
          <div class="frd-breakdown-row">
            <span>Stay Allocation:</span>
            <strong id="frd-bc-rooms-count">Full <?= $totalBedrooms ?> BHK Estate</strong>
          </div>
          <div class="frd-breakdown-row">
            <span>Duration:</span>
            <strong id="frd-bc-nights-count">1 Night</strong>
          </div>

          <div class="frd-bc-row" style="margin-top:8px;padding-top:8px;border-top:1px dashed #cbd5e1;">
            <span class="frd-bc-label">Base Rate (<span id="frd-rate-detail-label">Standard</span>):</span>
            <span class="frd-bc-val" id="frd-bc-base"><?= fmt($initBasePrice) ?></span>
          </div>

          <!-- Weekend tariff note -->
          <div id="frd-weekend-note-row" style="display:none;font-size:11px;color:#d97706;font-weight:600;margin-bottom:4px;">
            <span>⚡ Weekend pricing applies on Fri/Sat/Sun nights</span>
          </div>

          <!-- Long-stay discount row -->
          <div class="frd-bc-row" id="frd-longstay-row" style="display:none;">
            <span class="frd-bc-label" style="color:#059669;" id="frd-longstay-label">Extended Stay Discount:</span>
            <span class="frd-bc-val" style="color:#059669;" id="frd-longstay-val">- ₹0</span>
          </div>

          <!-- Addons row -->
          <div class="frd-bc-row" id="frd-addons-row" style="display:none;">
            <span class="frd-bc-label">Selected Addons:</span>
            <span class="frd-bc-val" id="frd-addons-val">+ ₹0</span>
          </div>

          <!-- Cleaning fee row -->
          <?php $cleanFee = (float)($farmhouse['cleaning_fee'] ?? 0.0); ?>
          <?php if ($cleanFee > 0): ?>
          <div class="frd-bc-row">
            <span class="frd-bc-label">Cleaning &amp; Sanitization:</span>
            <span class="frd-bc-val" id="frd-cleaning-fee-display">+ ₹<?= number_format($cleanFee) ?></span>
          </div>
          <?php endif; ?>

          <!-- Promo Coupon Discount row -->
          <div class="frd-bc-row" id="frd-coupon-row" style="display:none;">
            <span class="frd-bc-label" style="color:#059669;">Promo Coupon (<span id="frd-applied-coupon-code"></span>):</span>
            <span class="frd-bc-val" style="color:#059669;" id="frd-coupon-discount-val">- ₹0</span>
          </div>

          <!-- Platform fee row (Zero Initial Platform Fee Architecture - F25) -->
          <div class="frd-bc-row" style="display:none;">
            <span class="frd-bc-label">Platform Service Fee (0%):</span>
            <span class="frd-bc-val" id="frd-platform-fee-display">₹0</span>
          </div>

          <!-- Refundable Security Deposit row (F23) -->
          <?php $secDep = (float)($farmhouse['security_deposit'] ?? 2500.0); ?>
          <div class="frd-bc-row" style="padding-top:4px;border-top:1px dotted #cbd5e1;margin-top:4px;">
            <span class="frd-bc-label" style="color:#64748B;">
              Refundable Security Deposit
              <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle;color:#94a3b8;" title="100% refunded within 24 hours of check-out after property inspection">help</span>:
            </span>
            <span class="frd-bc-val" style="color:#475569;" id="frd-security-deposit-display">+ ₹<?= number_format($secDep) ?></span>
          </div>

          <div class="frd-bc-row frd-total" style="margin-top:8px;padding-top:8px;border-top:1.5px solid #0F172A;">
            <span class="frd-bc-label" style="font-size:14px;font-weight:900;">Total Amount:</span>
            <span class="frd-bc-val" id="frd-bc-total" style="font-size:18px;font-weight:900;color:#059669;"><?= fmt($initTotal) ?></span>
          </div>
        </div>

        <!-- Rules mini -->
        <?php if (!empty($rules)): ?>
        <div class="frd-rules-card-mini">
          <h5>Important Rules</h5>
          <div class="frd-rules-mini-grid">
            <?php foreach (array_slice($rules, 0, 6) as $rule): ?>
            <div class="frd-rule-mini">
              <div class="frd-rdot <?= $rule['is_allowed'] ? 'frd-ok' : 'frd-no' ?>"></div>
              <?= htmlspecialchars($rule['rule_name']) ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Security deposit -->
        <div class="frd-deposit-note">Security deposit refundable at check-out if no damage occurs.</div>

        <!-- Agree checkbox -->
        <div class="frd-agree-row">
          <input type="checkbox" id="frd-agree-chk"/>
          <label for="frd-agree-chk">I agree to the <a href="#" onclick="document.getElementById('frd-house-rules-modal').style.display='flex';return false;">Important Rules</a></label>
        </div>

        <!-- Booking CTA (F35) -->
        <?php if ($userId): ?>
          <button type="button" class="frd-book-btn" id="frd-book-btn" onclick="frdOpenBookingModal()" style="<?= $isInstantMode ? 'background:linear-gradient(135deg, #10b981 0%, #059669 100%);box-shadow:0 4px 14px rgba(16,185,129,0.35);' : '' ?>">
            <?= $isInstantMode ? '⚡ Instant Book Now' : 'Request Booking' ?>
          </button>
        <?php else: ?>
          <a href="<?= url('login?redirect=' . urlencode('farmhouse_details?id=' . $encryptedId)) ?>" class="frd-login-prompt">
            Login to Book this Farm
          </a>
        <?php endif; ?>

        <!-- Admin Contact buttons -->
        <div class="frd-contact-btns">
          <a href="<?= $adminCallLink ?>" class="frd-contact-btn frd-call" onclick="frdLogInquiry('call')" title="Call <?= htmlspecialchars($adminPhone) ?>">
            <span class="material-symbols-outlined">call</span>
            <span>Call Host</span>
          </a>
          <a href="<?= htmlspecialchars($adminWaLink) ?>" target="_blank" rel="noopener" class="frd-contact-btn frd-wa" onclick="frdLogInquiry('whatsapp')" title="Chat on WhatsApp">
            <span class="material-symbols-outlined">chat</span>
            <span>WhatsApp</span>
          </a>
        </div>

        <!-- Admin Help -->
        <div class="frd-help-section">
          <div class="frd-help-title">Stayora &amp; Booking Support</div>
          <a href="<?= $adminCallLink ?>" class="frd-help-item" onclick="frdLogInquiry('call')">
            <span class="material-symbols-outlined" style="font-size:16px;color:#1E40AF;">call</span>
            <span>Phone: <strong><?= $adminPhone ?></strong></span>
          </a>
          <a href="<?= htmlspecialchars($adminWaLink) ?>" target="_blank" rel="noopener" class="frd-help-item" onclick="frdLogInquiry('whatsapp')">
            <span class="material-symbols-outlined" style="font-size:16px;color:#16a34a;">chat</span>
            <span>WhatsApp: <strong><?= $adminWhatsApp ?></strong></span>
          </a>
          <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
            Direct Admin helpline for reservations, verification &amp; custom arrangements.
          </div>
        </div>

      </div>
    </div><!-- /right -->

  </div><!-- /frd-content-grid -->

  <!-- ══ SIMILAR PROPERTIES RECOMMENDATION SECTION (F77) ══ -->
  <?php if (!empty($similarFarmhouses)): ?>
  <div class="frd-similar-section" style="margin-top:50px;padding-top:36px;border-top:1.5px solid #E2E8F0;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
      <div>
        <div style="display:inline-flex;align-items:center;gap:6px;background:#E0F2FE;color:#1E40AF;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;padding:4px 10px;border-radius:999px;margin-bottom:6px;">
          <span class="material-symbols-outlined" style="font-size:14px;">travel_explore</span>
          <span>More in <?= htmlspecialchars($farmhouse['location'] ?? 'this Area') ?></span>
        </div>
        <h3 style="font-size:22px;font-weight:900;color:#0F172A;margin:0;">Similar Vacation Properties You May Like</h3>
      </div>
      <a href="<?= url('farmhouses?location=' . urlencode($farmhouse['location'] ?? '')) ?>" style="color:#1E40AF;font-weight:700;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
        <span>Explore all in <?= htmlspecialchars($farmhouse['location'] ?? '') ?></span>
        <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
      </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:20px;">
      <?php foreach ($similarFarmhouses as $sim): 
        $simEncId = class_exists(\App\Helpers\CryptoHelper::class) ? \App\Helpers\CryptoHelper::encrypt((string)$sim['id']) : (string)$sim['id'];
        $simImg = !empty($sim['primary_image']) ? farmhouse_img_url($sim['primary_image']) : 'https://placehold.co/600x400/E8F0EC/1F4D3A?text=Luxury+Stay';
      ?>
        <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:20px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.04);transition:transform 0.2s,box-shadow 0.2s;display:flex;flex-direction:column;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.04)'">
          <div style="position:relative;height:180px;background:#0F172A;overflow:hidden;">
            <img src="<?= $simImg ?>" alt="<?= htmlspecialchars($sim['title']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='https://placehold.co/600x400/E8F0EC/1F4D3A?text=Stayora'"/>
            <div style="position:absolute;top:12px;left:12px;display:flex;gap:6px;">
              <span style="background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#ffffff;font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                <span class="material-symbols-outlined" style="font-size:12px;color:#C9A227;"><?= htmlspecialchars($sim['property_type_icon'] ?: 'villa') ?></span>
                <?= htmlspecialchars($sim['property_type_name']) ?>
              </span>
              <?php if (!empty($sim['is_verified'])): ?>
                <span style="background:#ecfdf5;color:#047857;font-size:10.5px;font-weight:800;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px;" title="Verified Property">
                  <span class="material-symbols-outlined" style="font-size:12px;">verified</span>
                </span>
              <?php endif; ?>
            </div>
          </div>
          <div style="padding:16px;display:flex;flex-direction:column;flex:1;justify-content:space-between;">
            <div>
              <div style="display:flex;align-items:center;gap:4px;color:#64748B;font-size:11.5px;font-weight:600;margin-bottom:4px;">
                <span class="material-symbols-outlined" style="font-size:13px;color:#1E40AF;">location_on</span>
                <span><?= htmlspecialchars($sim['location']) ?></span>
              </div>
              <h4 style="font-size:15px;font-weight:800;color:#0F172A;margin:0 0 8px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($sim['title']) ?>
              </h4>
              <div style="display:flex;gap:12px;color:#64748B;font-size:11.5px;font-weight:600;margin-bottom:12px;">
                <span>🛏️ <?= (int)$sim['bedrooms'] ?> BHK</span>
                <span>👥 Up to <?= (int)($sim['night_capacity'] ?: 10) ?> Guests</span>
              </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid #f1f5f9;">
              <div>
                <span style="font-size:11px;color:#64748B;font-weight:600;display:block;">From</span>
                <span style="font-size:16px;font-weight:900;color:#0F172A;">₹<?= number_format((float)$sim['price']) ?></span>
                <span style="font-size:11px;color:#94a3b8;">/night</span>
              </div>
              <a href="<?= url('farmhouse_details?id=' . urlencode($simEncId)) ?>" style="padding:8px 14px;background:#1E40AF;color:#ffffff;text-decoration:none;border-radius:10px;font-size:12px;font-weight:700;transition:background 0.2s;" onmouseover="this.style.background='#1E3A8A'" onmouseout="this.style.background='#1E40AF'">
                View Stay
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /frd-page-wrap -->

<!-- ── MOBILE STICKY BOTTOM BOOKING BAR ── -->
<div class="frd-mobile-sticky-bar" id="frd-mobile-sticky-bar">
  <div class="frd-ms-price-col">
    <div class="frd-ms-price" id="frd-ms-price-display"><?= fmt($initTotal) ?> <span style="font-size:11px;font-weight:600;color:#94a3b8;">/ total</span></div>
    <div class="frd-ms-sub" id="frd-ms-sub-display">Complete Farmhouse · <?= $initGuests ?> Guests</div>
  </div>
  <?php if ($userId): ?>
    <button type="button" class="frd-ms-btn" onclick="frdScrollToBookingOrOpen()">
      <span class="material-symbols-outlined" style="font-size:18px;">calendar_month</span>
      <span>Request Book</span>
    </button>
  <?php else: ?>
    <a href="<?= url('login?redirect=' . urlencode('farmhouse_details?id=' . $encryptedId)) ?>" class="frd-ms-btn">
      <span class="material-symbols-outlined" style="font-size:18px;">login</span>
      <span>Login to Book</span>
    </a>
  <?php endif; ?>
</div>

<!-- ── MODALS ── -->

<!-- 1. EXECUTIVE BOOKING REQUEST & PAYMENT QR MODAL -->
<div id="frd-booking-request-modal" class="frd-modal-backdrop">
  <div class="frd-modal-box">
    <button class="frd-modal-close" onclick="frdCloseBookingModal()">✕</button>
    
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #E2E8F0;">
      <div style="width:42px;height:42px;border-radius:12px;background:#EFF6FF;color:#1E40AF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <span class="material-symbols-outlined" style="font-size:24px;color:#1E40AF;" id="frd-modal-header-icon">villa</span>
      </div>
      <div>
        <h3 class="frd-modal-title" style="margin:0;font-size:18px;font-weight:800;color:#0F172A;">Complete Booking Request</h3>
        <p style="margin:0;font-size:12.5px;color:#64748B;font-weight:600;"><?= htmlspecialchars($farmhouse['title']) ?> · <?= htmlspecialchars($farmhouse['location'] ?? '') ?></p>
      </div>
    </div>

    <!-- Booking Summary Card -->
    <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:14px;margin-bottom:16px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12.5px;">
        <div>
          <span style="color:#64748B;font-size:11px;font-weight:800;text-transform:uppercase;display:block;">Booking Mode</span>
          <div style="font-weight:800;color:#1E40AF;margin-top:2px;" id="frd-modal-booking-mode">Complete Farmhouse</div>
        </div>
        <div>
          <span style="color:#64748B;font-size:11px;font-weight:800;text-transform:uppercase;display:block;">Dates &amp; Stay</span>
          <div style="font-weight:800;color:#0F172A;margin-top:2px;" id="frd-modal-stay-dates">Select dates</div>
        </div>
        <div style="grid-column: span 2; padding-top:4px;">
          <span style="color:#64748B;font-size:11px;font-weight:800;text-transform:uppercase;display:block;">Guests &amp; Allocation</span>
          <div style="font-weight:800;color:#0F172A;margin-top:2px;" id="frd-modal-guests-rooms">2 Guests · Entire Estate</div>
        </div>
        <div style="grid-column: span 2; padding-top:8px; margin-top:4px; border-top:1px dashed #cbd5e1; display:flex; justify-content:space-between; align-items:center;">
          <span style="font-weight:700;color:#334155;font-size:13px;">Total Amount (incl. platform fees):</span>
          <span style="font-size:20px;font-weight:900;color:#1D4ED8;" id="frd-modal-total-price">₹0</span>
        </div>
      </div>
    </div>

    <!-- ── PAYMENT METHOD SELECTOR & PROOF UPLOAD (F38, F39, F40) ── -->
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;color:#475569;letter-spacing:0.5px;margin-bottom:8px;">
        Select Payment Method (F38)
      </label>
      <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px;" id="frd-payment-methods-grid">
        <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:10px 6px;border:2px solid #1E40AF;background:#f0f9ff;border-radius:12px;cursor:pointer;text-align:center;transition:all 0.15s;" id="frd-pm-label-upi">
          <input type="radio" name="payment_method" value="upi" checked onchange="frdSelectPaymentMethod('upi')" style="display:none;">
          <span class="material-symbols-outlined" style="font-size:22px;color:#1E40AF;">qr_code_scanner</span>
          <span style="font-size:11.5px;font-weight:800;color:#0F172A;">UPI Scan</span>
          <span style="font-size:9.5px;font-weight:700;color:#1E40AF;">Instant QR</span>
        </label>

        <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:10px 6px;border:1.5px solid #cbd5e1;background:#ffffff;border-radius:12px;cursor:pointer;text-align:center;transition:all 0.15s;" id="frd-pm-label-bank">
          <input type="radio" name="payment_method" value="bank_transfer" onchange="frdSelectPaymentMethod('bank_transfer')" style="display:none;">
          <span class="material-symbols-outlined" style="font-size:22px;color:#64748B;">account_balance</span>
          <span style="font-size:11.5px;font-weight:800;color:#0F172A;">Bank Wire</span>
          <span style="font-size:9.5px;font-weight:600;color:#64748B;">NEFT / IMPS</span>
        </label>

        <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:10px 6px;border:1.5px solid #cbd5e1;background:#ffffff;border-radius:12px;cursor:pointer;text-align:center;transition:all 0.15s;" id="frd-pm-label-property">
          <input type="radio" name="payment_method" value="pay_at_property" onchange="frdSelectPaymentMethod('pay_at_property')" style="display:none;">
          <span class="material-symbols-outlined" style="font-size:22px;color:#64748B;">payments</span>
          <span style="font-size:11.5px;font-weight:800;color:#0F172A;">At Property</span>
          <span style="font-size:9.5px;font-weight:600;color:#059669;">Pay on Arrival</span>
        </label>
      </div>
    </div>

    <!-- Panel 1: UPI Instructions & QR -->
    <div id="frd-pay-panel-upi" style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:16px;padding:16px;text-align:center;margin-bottom:16px;">
      <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:10px;">
        <span class="material-symbols-outlined" style="font-size:20px;color:#166534;">qr_code_2</span>
        <strong style="font-size:14px;color:#166534;font-weight:800;">Official UPI QR Code &amp; ID</strong>
      </div>

      <?php if (!empty($qrUrl)): ?>
        <div style="width:160px;height:160px;margin:0 auto 10px;background:#fff;padding:8px;border-radius:14px;border:1.5px solid #22c55e;box-shadow:0 4px 14px rgba(34,197,94,0.15);display:flex;align-items:center;justify-content:center;overflow:hidden;">
          <img src="<?= $qrUrl ?>" alt="Official Payment QR Code" style="width:100%;height:100%;object-fit:contain;">
        </div>
        <div style="font-size:11.5px;font-weight:700;color:#15803d;margin-bottom:8px;">
          Scan with GPay, PhonePe, Paytm or BHIM
        </div>
      <?php endif; ?>

      <?php if (!empty($upiId)): ?>
        <div style="display:inline-flex;align-items:center;gap:8px;background:#ffffff;padding:5px 12px;border-radius:10px;border:1px solid #86efac;margin-bottom:6px;">
          <span style="font-size:11px;color:#64748B;font-weight:700;">UPI ID:</span>
          <span style="font-family:monospace;font-weight:800;color:#0F172A;font-size:12.5px;" id="frd-upi-id-text"><?= $upiId ?></span>
          <button type="button" class="frd-copy-badge" onclick="frdCopyUpi('<?= $upiId ?>')" style="padding:3px 7px;border-radius:6px;background:#f1f5f9;border:1px solid #cbd5e1;font-size:10.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:3px;">
            <span class="material-symbols-outlined" style="font-size:12px;">content_copy</span>
            <span>Copy</span>
          </button>
        </div>
      <?php endif; ?>

      <p style="margin:4px 0 0;font-size:11px;color:#166534;line-height:1.4;font-weight:500;">
        <?= !empty($paymentNotes) ? $paymentNotes : "Scan the QR code or send to the UPI ID. Enter the transaction reference below to verify." ?>
      </p>
    </div>

    <!-- Panel 2: Direct Bank Transfer Details (F37) -->
    <div id="frd-pay-panel-bank" style="display:none;background:#F8FAFC;border:1.5px solid #cbd5e1;border-radius:16px;padding:16px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:6px;margin-bottom:10px;color:#0F172A;">
        <span class="material-symbols-outlined" style="font-size:20px;color:#1E40AF;">account_balance</span>
        <strong style="font-size:13.5px;font-weight:800;">Official Bank Account Details</strong>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
        <div style="background:#fff;padding:8px 10px;border-radius:8px;border:1px solid #E2E8F0;">
          <span style="font-size:10px;color:#64748B;display:block;font-weight:700;text-transform:uppercase;">Bank Name</span>
          <strong style="color:#0F172A;"><?= $bankName ?></strong>
        </div>
        <div style="background:#fff;padding:8px 10px;border-radius:8px;border:1px solid #E2E8F0;">
          <span style="font-size:10px;color:#64748B;display:block;font-weight:700;text-transform:uppercase;">Account Name</span>
          <strong style="color:#0F172A;"><?= $accountName ?></strong>
        </div>
        <div style="background:#fff;padding:8px 10px;border-radius:8px;border:1px solid #E2E8F0;">
          <span style="font-size:10px;color:#64748B;display:block;font-weight:700;text-transform:uppercase;">Account Number</span>
          <strong style="color:#0F172A;font-family:monospace;"><?= $accountNumber ?></strong>
        </div>
        <div style="background:#fff;padding:8px 10px;border-radius:8px;border:1px solid #E2E8F0;">
          <span style="font-size:10px;color:#64748B;display:block;font-weight:700;text-transform:uppercase;">IFSC Code</span>
          <strong style="color:#0F172A;font-family:monospace;"><?= $ifscCode ?></strong>
        </div>
      </div>
      <p style="margin:8px 0 0;font-size:11px;color:#64748B;line-height:1.4;">
        Transfer using IMPS or NEFT. Enter your Bank Reference Number / UTR below for swift reconciliation.
      </p>
    </div>

    <!-- Panel 3: Pay at Property Notice -->
    <div id="frd-pay-panel-property" style="display:none;background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:16px;padding:14px;margin-bottom:16px;">
      <div style="display:flex;align-items:flex-start;gap:10px;">
        <span class="material-symbols-outlined" style="font-size:22px;color:#059669;margin-top:2px;">verified_user</span>
        <div>
          <h4 style="margin:0;font-size:13px;font-weight:800;color:#065f46;">Pay Full Amount on Arrival</h4>
          <p style="margin:3px 0 0;font-size:11.5px;color:#047857;line-height:1.45;">
            No advance deposit needed now. You can settle the full stay amount upon check-in directly with the property manager via Cash or UPI.
          </p>
        </div>
      </div>
    </div>

    <!-- Booking Form (with multipart upload for payment proof - F39) -->
    <form method="POST" action="<?= url('farmhouse/request_booking') ?>" id="frd-booking-form" enctype="multipart/form-data" onsubmit="return frdSubmitBookingModal(this)">
      <input type="hidden" name="farmhouse_id"    value="<?= $encryptedId ?>"/>
      <input type="hidden" name="booking_type"    id="frd-form-booking-type"  value="complete"/>
      <input type="hidden" name="room_type_id"    id="frd-form-room-type-id"  value="<?= !empty($roomTypes[0]['id']) ? $roomTypes[0]['id'] : '' ?>"/>
      <input type="hidden" name="check_in"        id="frd-form-checkin"       value=""/>
      <input type="hidden" name="check_out"       id="frd-form-checkout"      value=""/>
      <input type="hidden" name="check_in_time"   id="frd-form-checkin-time"  value="12:00"/>
      <input type="hidden" name="check_out_time"  id="frd-form-checkout-time" value="11:00"/>
      <input type="hidden" name="guests"          id="frd-form-guests"        value="<?= $initGuests ?>"/>
      <input type="hidden" name="rooms"           id="frd-form-rooms"         value="<?= $initRooms ?>"/>
      <input type="hidden" name="price"           id="frd-form-price"         value="<?= $initTotal ?>"/>
      <input type="hidden" name="weekend_nights"   id="frd-form-weekend-nights" value="0"/>
      <input type="hidden" name="security_deposit" id="frd-form-security-deposit" value="<?= (float)($farmhouse['security_deposit'] ?? 2500) ?>"/>
      <input type="hidden" name="cleaning_fee"     id="frd-form-cleaning-fee" value="<?= (float)($farmhouse['cleaning_fee'] ?? 0) ?>"/>
      <input type="hidden" name="addon_charges"    id="frd-form-addon-charges" value="0"/>
      <input type="hidden" name="discount_amount"  id="frd-form-discount-amount" value="0"/>
      <input type="hidden" name="coupon_code"      id="frd-form-coupon-code" value=""/>
      <input type="hidden" name="payment_method"  id="frd-form-payment-method" value="upi"/>

      <!-- In-App Payment Proof Inputs (F39) -->
      <div id="frd-pay-proof-section" style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:14px;padding:12px;margin-bottom:12px;">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#0F172A;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;gap:5px;">
          <span class="material-symbols-outlined" style="font-size:16px;color:#1E40AF;">receipt_long</span>
          <span>Payment Proof &amp; Verification (Optional upfront, speeds up confirmation)</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div>
            <label style="display:block;font-size:10.5px;font-weight:700;color:#64748B;margin-bottom:3px;">UTR / Transaction Ref No.</label>
            <input type="text" name="utr_number" id="frd-utr-input" placeholder="e.g. 12-digit UTR 324567891234" 
                   style="width:100%;padding:8px 10px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:12px;font-family:monospace;outline:none;box-sizing:border-box;">
          </div>
          <div>
            <label style="display:block;font-size:10.5px;font-weight:700;color:#64748B;margin-bottom:3px;">Upload Screenshot / Receipt</label>
            <input type="file" name="payment_proof" id="frd-proof-file-input" accept="image/*,.pdf" 
                   style="width:100%;padding:5px 8px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:11px;background:#fff;outline:none;box-sizing:border-box;">
          </div>
        </div>
      </div>

      <!-- Guest Details -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div style="text-align:left;">
          <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748B;margin-bottom:4px;">Your Name</label>
          <input type="text" value="<?= $userName ?>" readonly style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;background:#F8FAFC;color:#0F172A;cursor:not-allowed;box-sizing:border-box;">
        </div>
        <div style="text-align:left;">
          <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748B;margin-bottom:4px;">Contact Phone</label>
          <input type="text" value="<?= $userPhone ?: $supportPhone ?>" readonly style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;background:#F8FAFC;color:#0F172A;cursor:not-allowed;box-sizing:border-box;">
        </div>
      </div>

      <div style="text-align:left;margin-bottom:14px;">
        <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748B;margin-bottom:4px;">Special Requests / Note (Optional)</label>
        <textarea name="message" rows="2" placeholder="e.g. Arriving around 1 PM, celebrating birthday, early check-in requested..." style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;color:#0F172A;box-sizing:border-box;font-family:inherit;resize:none;"></textarea>
      </div>

      <div style="display:flex;align-items:center;gap:10px;">
        <button type="button" onclick="frdCloseBookingModal()" style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:12px;font-size:13px;font-weight:700;color:#475569;cursor:pointer;">
          Cancel
        </button>
        <button type="submit" id="frd-submit-request-btn" style="flex:2;padding:12px;background:<?= $isInstantMode ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : '#1D4ED8' ?>;border:none;border-radius:12px;font-size:14px;font-weight:800;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:background 0.2s;box-shadow:0 4px 12px <?= $isInstantMode ? 'rgba(16,185,129,0.35)' : 'rgba(22,165,222,0.3)' ?>;">
          <span><?= $isInstantMode ? '⚡ Confirm Instant Booking' : 'Submit Booking Request' ?></span>
          <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- 2. House Rules Modal -->
<div id="frd-house-rules-modal" class="frd-modal-backdrop">
  <div class="frd-modal-box" style="max-width:480px;">
    <button class="frd-modal-close" onclick="document.getElementById('frd-house-rules-modal').style.display='none'">✕</button>
    <h3 class="frd-modal-title">Important House Rules</h3>
    <?php if (!empty($rules)): ?>
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
        <?php foreach ($rules as $rule): 
          $isAllowed = !empty($rule['is_allowed']);
          $rIcon = get_stayora_rule_icon($rule['rule_name'] ?? '', $rule['icon_class'] ?? '');
        ?>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 14px;background:#F8FAFC;border:1px solid <?= $isAllowed ? '#E2E8F0' : '#FECACA' ?>;border-radius:12px;">
          <div style="display:flex;align-items:center;gap:10px;font-size:13px;font-weight:700;color:#0F172A;min-width:0;flex:1;">
            <div style="width:32px;height:32px;border-radius:8px;background:<?= $isAllowed ? '#F0FDF4' : '#FEF2F2' ?>;color:<?= $isAllowed ? '#16A34A' : '#DC2626' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <span class="material-symbols-outlined" style="font-size:18px;"><?= htmlspecialchars($rIcon) ?></span>
            </div>
            <span style="line-height:1.35;"><?= htmlspecialchars($rule['rule_name']) ?></span>
          </div>
          <span class="frd-rule-badge <?= $isAllowed ? 'frd-allowed' : 'frd-not-allowed' ?>">
            <span class="material-symbols-outlined" style="font-size:13px;"><?= $isAllowed ? 'check_circle' : 'cancel' ?></span>
            <?= $isAllowed ? 'Allowed' : 'Not Allowed' ?>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#64748B;font-size:13px;">Please contact Stayora admin for specific house rules.</p>
    <?php endif; ?>
    <p class="frd-modal-note">Violation of house rules may result in forfeiture of the security deposit.</p>
  </div>
</div>

<!-- 3. Cancellation Policy Modal -->
<div id="frd-cancel-modal" class="frd-modal-backdrop">
  <div class="frd-modal-box" style="max-width:460px;">
    <button class="frd-modal-close" onclick="document.getElementById('frd-cancel-modal').style.display='none'">✕</button>
    <h3 class="frd-modal-title">Cancellation Policy</h3>
    <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;padding:0;margin:14px 0 0;">
      <li style="display:flex;gap:10px;align-items:flex-start;font-size:13px;color:#334155;">
        <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;flex-shrink:0;">check_circle</span>
        <span><strong>7+ days before check-in:</strong> 100% refund of booking amount.</span>
      </li>
      <li style="display:flex;gap:10px;align-items:flex-start;font-size:13px;color:#f59e0b;font-size:18px;flex-shrink:0;">
        <span class="material-symbols-outlined" style="color:#f59e0b;font-size:18px;flex-shrink:0;">warning</span>
        <span><strong>3–7 days before check-in:</strong> 50% refund of booking amount.</span>
      </li>
      <li style="display:flex;gap:10px;align-items:flex-start;font-size:13px;color:#334155;">
        <span class="material-symbols-outlined" style="color:#ef4444;font-size:18px;flex-shrink:0;">cancel</span>
        <span><strong>Less than 3 days:</strong> No refund.</span>
      </li>
    </ul>
    <p class="frd-modal-note">Platform fees (5%) are non-refundable.</p>
  </div>
</div>

<!-- 4. Room Independent Photo Gallery Modal (F13) -->
<div id="frd-room-gallery-modal" class="frd-modal-backdrop" style="display:none;align-items:center;justify-content:center;z-index:9999;">
  <div class="frd-modal-box" style="max-width:760px;width:95%;padding:20px;border-radius:24px;background:#0F172A;color:#ffffff;box-shadow:0 25px 60px rgba(0,0,0,0.6);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #334155;padding-bottom:12px;">
      <div>
        <span style="font-size:11px;font-weight:700;color:#C9A227;text-transform:uppercase;letter-spacing:0.5px;">Room Independent Gallery</span>
        <h3 id="frd-room-modal-title" style="font-size:18px;font-weight:800;color:#ffffff;margin:2px 0 0;">Deluxe Bedroom</h3>
      </div>
      <button type="button" onclick="frdCloseRoomGallery()" style="background:none;border:none;color:#94a3b8;font-size:24px;cursor:pointer;line-height:1;transition:color 0.15s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">✕</button>
    </div>
    
    <div style="position:relative;height:380px;border-radius:16px;overflow:hidden;background:#000;display:flex;align-items:center;justify-content:center;">
      <img id="frd-room-modal-img" src="" alt="Room photo" style="max-width:100%;max-height:100%;object-fit:contain;"/>
      <button type="button" onclick="frdNextRoomPhoto(-1)" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);background:rgba(15,23,42,0.7);backdrop-filter:blur(4px);color:#fff;border:none;border-radius:50%;width:40px;height:40px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button type="button" onclick="frdNextRoomPhoto(1)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:rgba(15,23,42,0.7);backdrop-filter:blur(4px);color:#fff;border:none;border-radius:50%;width:40px;height:40px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
        <span class="material-symbols-outlined">chevron_right</span>
      </button>
      <div id="frd-room-modal-counter" style="position:absolute;bottom:12px;right:14px;background:rgba(0,0,0,0.7);padding:4px 10px;border-radius:6px;font-size:11.5px;font-weight:700;color:#fff;">
        1 / 1
      </div>
    </div>
    <div id="frd-room-modal-thumbs" style="display:flex;gap:8px;margin-top:12px;overflow-x:auto;padding-bottom:4px;"></div>
<!-- 5. Write a Review Modal (F52, F53) -->
<div id="frdReviewModal" class="frd-modal-backdrop" style="display:none;align-items:center;justify-content:center;z-index:9999;">
  <div class="frd-modal-box" style="max-width:540px;width:95%;padding:24px;border-radius:24px;background:#ffffff;box-shadow:0 25px 60px rgba(0,0,0,0.25);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #f1f5f9;padding-bottom:12px;">
      <div>
        <span style="font-size:11px;font-weight:800;color:#1E40AF;text-transform:uppercase;letter-spacing:0.5px;">Verified Experience</span>
        <h3 style="font-size:18px;font-weight:900;color:#0F172A;margin:2px 0 0;">Rate &amp; Review Your Stay</h3>
      </div>
      <button type="button" onclick="document.getElementById('frdReviewModal').style.display='none'"
              style="background:none;border:none;color:#94a3b8;font-size:22px;cursor:pointer;line-height:1;">✕</button>
    </div>

    <form action="<?= url('farmhouse/submit-review') ?>" method="POST" style="display:flex;flex-direction:column;gap:14px;">
      <input type="hidden" name="csrf_token" value="<?= \App\Helpers\CsrfHelper::generate() ?>">
      <input type="hidden" name="farmhouse_id" value="<?= htmlspecialchars($encId) ?>">

      <!-- 4 Criteria Rating Inputs -->
      <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:16px;padding:14px;">
        <span style="display:block;font-size:11.5px;font-weight:800;color:#0F172A;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:10px;">
          Rate Core Stay Criteria (1 to 5 Stars)
        </span>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px;">
          <div>
            <label style="display:block;font-weight:700;color:#475569;margin-bottom:3px;">✨ Cleanliness</label>
            <select name="cleanliness" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;font-weight:700;background:#fff;">
              <option value="5">⭐⭐⭐⭐⭐ (5 - Exceptional)</option>
              <option value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
              <option value="3">⭐⭐⭐ (3 - Average)</option>
              <option value="2">⭐⭐ (2 - Below Par)</option>
              <option value="1">⭐ (1 - Poor)</option>
            </select>
          </div>

          <div>
            <label style="display:block;font-weight:700;color:#475569;margin-bottom:3px;">📍 Location &amp; Vibe</label>
            <select name="location" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;font-weight:700;background:#fff;">
              <option value="5">⭐⭐⭐⭐⭐ (5 - Prime / Serene)</option>
              <option value="4">⭐⭐⭐⭐ (4 - Good Access)</option>
              <option value="3">⭐⭐⭐ (3 - Fair)</option>
              <option value="2">⭐⭐ (2 - Far / Difficult)</option>
              <option value="1">⭐ (1 - Inaccessible)</option>
            </select>
          </div>

          <div>
            <label style="display:block;font-weight:700;color:#475569;margin-bottom:3px;">💰 Value for Money</label>
            <select name="value" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;font-weight:700;background:#fff;">
              <option value="5">⭐⭐⭐⭐⭐ (5 - Great Value)</option>
              <option value="4">⭐⭐⭐⭐ (4 - Worth It)</option>
              <option value="3">⭐⭐⭐ (3 - Fair Price)</option>
              <option value="2">⭐⭐ (2 - Overpriced)</option>
              <option value="1">⭐ (1 - Bad Deal)</option>
            </select>
          </div>

          <div>
            <label style="display:block;font-weight:700;color:#475569;margin-bottom:3px;">🤝 Host &amp; Hospitality</label>
            <select name="hospitality" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;font-weight:700;background:#fff;">
              <option value="5">⭐⭐⭐⭐⭐ (5 - Super Hospitable)</option>
              <option value="4">⭐⭐⭐⭐ (4 - Helpful Caretaker)</option>
              <option value="3">⭐⭐⭐ (3 - Neutral)</option>
              <option value="2">⭐⭐ (2 - Unresponsive)</option>
              <option value="1">⭐ (1 - Unhelpful)</option>
            </select>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div>
          <label style="display:block;font-size:11px;font-weight:700;color:#475569;margin-bottom:3px;">Your Name *</label>
          <input type="text" name="guest_name" required value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" placeholder="e.g. Rahul Verma"
                 style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:12px;">
        </div>
        <div>
          <label style="display:block;font-size:11px;font-weight:700;color:#475569;margin-bottom:3px;">Email Address</label>
          <input type="email" name="guest_email" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="For verification"
                 style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:12px;">
        </div>
      </div>

      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#475569;margin-bottom:3px;">Review Headline</label>
        <input type="text" name="review_title" placeholder="e.g. Unforgettable weekend getaway with friends!"
               style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:12px;">
      </div>

      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#475569;margin-bottom:3px;">Detailed Review *</label>
        <textarea name="review_text" required rows="3" placeholder="Tell other travelers about the pool, amenities, food, and caretaker hospitality..."
                  style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:12px;"></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:6px;">
        <button type="button" onclick="document.getElementById('frdReviewModal').style.display='none'"
                style="padding:8px 16px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#475569;font-size:12px;font-weight:700;cursor:pointer;">
          Cancel
        </button>
        <button type="submit"
                style="padding:8px 18px;border:none;border-radius:10px;background:#1E40AF;color:#fff;font-size:12px;font-weight:800;cursor:pointer;box-shadow:0 4px 12px rgba(2,132,199,0.3);">
          Submit Review
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── PAGE-SPECIFIC JS ── -->
<script>
// ── CONSTANTS FROM PHP ──
const FRD_OCCUPIED_DATES   = <?= $occupiedJson ?>;
const FRD_REQUESTED_DATES  = <?= $requestedJson ?>;
const FRD_FULL_FARM_PRICE  = <?= $fullFarmPrice ?>;
const FRD_WEEKEND_PRICE    = <?= (float)($farmhouse['weekend_price'] ?? $fullFarmPrice * 1.2) ?>;
const FRD_RATE_PER_ROOM    = <?= $ratePerRoom ?>;
const FRD_RENTAL_TYPE      = '<?= $rentalType ?>';
const FRD_SUPPORTS_COMPLETE = <?= $supportsComplete ? 'true' : 'false' ?>;
const FRD_SUPPORTS_ROOM    = <?= $supportsRoomWise ? 'true' : 'false' ?>;
const FRD_DEFAULT_MODE     = '<?= $defaultBookingMode ?>';
const FRD_ALLOW_ROOM_BOOK  = <?= $allowRoomBooking && $roomPriceConfig > 0 ? 'true' : 'false' ?>;
const FRD_BED_CAPACITY     = <?= $bedroomCapacity ?>;
const FRD_TOTAL_BEDROOMS   = <?= $totalBedrooms ?>;
const FRD_MAX_GUESTS       = <?= $maxAllowedGuests ?>;
const FRD_SECURITY_DEPOSIT = <?= (float)($farmhouse['security_deposit'] ?? 2500) ?>;
const FRD_CLEANING_FEE     = <?= (float)($farmhouse['cleaning_fee'] ?? 0) ?>;
const FRD_WEEKLY_DISCOUNT  = <?= (float)($farmhouse['weekly_discount_percent'] ?? 10) ?>;
const FRD_MONTHLY_DISCOUNT = <?= (float)($farmhouse['monthly_discount_percent'] ?? 20) ?>;
const FRD_ADDONS           = <?= json_encode(!empty($addons) ? $addons : []) ?>;
const FRD_SEASONAL         = <?= json_encode(!empty($seasonalPrices) ? $seasonalPrices : []) ?>;
const FRD_IS_LOGGED_IN     = <?= $userId ? 'true' : 'false' ?>;
const FRD_ALL_IMAGES       = <?= json_encode($allResolvedImages) ?>;
const FRD_ROOM_TYPES       = <?= json_encode(!empty($roomTypes) ? $roomTypes : []) ?>;

// ── STATE VARIABLES ──
let frdBookingMode = '<?= $defaultBookingMode ?>'; // 'complete' | 'per_room'
let frdBcG = <?= $initGuests ?>;
let frdCustomRooms = <?= max(1, (int)ceil($initGuests / $bedroomCapacity)) ?>;
let frdSelectedRoomTypeId = (FRD_ROOM_TYPES && FRD_ROOM_TYPES.length > 0) ? parseInt(FRD_ROOM_TYPES[0].id) : null;
let frdAppliedCoupon = null;
let frdSelectedAddons = [];
let frdRoomModalImages = [];
let frdRoomModalIdx = 0;

// ── ROOM INDEPENDENT GALLERY MODAL (F13) ──
function frdOpenRoomGallery(images, roomName) {
  if (!images || !images.length) return;
  frdRoomModalImages = images;
  frdRoomModalIdx = 0;
  
  const titleEl = document.getElementById('frd-room-modal-title');
  if (titleEl) titleEl.textContent = roomName || 'Room Gallery';
  
  frdUpdateRoomModalPhoto();
  
  const thumbsContainer = document.getElementById('frd-room-modal-thumbs');
  if (thumbsContainer) {
    thumbsContainer.innerHTML = '';
    images.forEach((img, i) => {
      const t = document.createElement('img');
      const url = typeof img === 'object' && img.image_url ? img.image_url : img;
      t.src = url.startsWith('http') ? url : '<?= asset('assets/images/uploads/') ?>/' + url;
      t.style.width = '55px';
      t.style.height = '40px';
      t.style.objectFit = 'cover';
      t.style.borderRadius = '6px';
      t.style.cursor = 'pointer';
      t.style.border = (i === 0) ? '2px solid #C9A227' : '2px solid transparent';
      t.onclick = () => {
        frdRoomModalIdx = i;
        frdUpdateRoomModalPhoto();
      };
      thumbsContainer.appendChild(t);
    });
  }
  
  const modal = document.getElementById('frd-room-gallery-modal');
  if (modal) modal.style.display = 'flex';
}

function frdCloseRoomGallery() {
  const modal = document.getElementById('frd-room-gallery-modal');
  if (modal) modal.style.display = 'none';
}

function frdNextRoomPhoto(dir) {
  if (!frdRoomModalImages.length) return;
  frdRoomModalIdx = (frdRoomModalIdx + dir + frdRoomModalImages.length) % frdRoomModalImages.length;
  frdUpdateRoomModalPhoto();
}

function frdUpdateRoomModalPhoto() {
  if (!frdRoomModalImages.length) return;
  const item = frdRoomModalImages[frdRoomModalIdx];
  const url = typeof item === 'object' && item.image_url ? item.image_url : item;
  const fullUrl = url.startsWith('http') ? url : '<?= asset('assets/images/uploads/') ?>/' + url;
  
  const imgEl = document.getElementById('frd-room-modal-img');
  if (imgEl) imgEl.src = fullUrl;
  
  const counterEl = document.getElementById('frd-room-modal-counter');
  if (counterEl) counterEl.textContent = `${frdRoomModalIdx + 1} / ${frdRoomModalImages.length}`;
  
  const thumbs = document.querySelectorAll('#frd-room-modal-thumbs img');
  thumbs.forEach((t, i) => {
    t.style.borderColor = (i === frdRoomModalIdx) ? '#C9A227' : 'transparent';
  });
}

function frdSelectRoomTypeForBooking(rtId) {
  frdSetBookingMode('per_room');
  frdOnRoomTypeChange(rtId);
  const card = document.getElementById('frd-room-type-select');
  if (card) {
    card.value = rtId;
  }
  const widget = document.getElementById('frd-booking-card-widget');
  if (widget) widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ── ADDON SERVICES TOGGLE (F24) ──
function frdToggleAddon(addonId) {
  const id = parseInt(addonId);
  const idx = frdSelectedAddons.indexOf(id);
  if (idx > -1) {
    frdSelectedAddons.splice(idx, 1);
  } else {
    frdSelectedAddons.push(id);
  }
  frdRecalcPrice();
}

// ── PROMO COUPON VOUCHER (F74) ──
function frdApplyCoupon() {
  const input = document.getElementById('frd-coupon-input');
  const msgEl = document.getElementById('frd-coupon-msg');
  if (!input) return;
  const code = input.value.trim().toUpperCase();
  if (!code) {
    if (msgEl) {
      msgEl.style.display = 'block';
      msgEl.style.color = '#ef4444';
      msgEl.textContent = 'Please enter a valid coupon code.';
    }
    return;
  }

  const baseCost = frdCalculateSubtotal();

  fetch('<?= url("api/validate-coupon") ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'code=' + encodeURIComponent(code) + '&booking_amount=' + encodeURIComponent(baseCost)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      frdAppliedCoupon = {
        code: code,
        discount_amount: parseFloat(data.discount_amount),
        discount_type: data.coupon.discount_type,
        discount_value: parseFloat(data.coupon.discount_value)
      };
      if (msgEl) {
        msgEl.style.display = 'block';
        msgEl.style.color = '#059669';
        msgEl.textContent = `✓ Coupon "${code}" applied! You saved ₹${Number(data.discount_amount).toLocaleString('en-IN')}`;
      }
      const applyBtn = document.getElementById('frd-coupon-apply-btn');
      if (applyBtn) {
        applyBtn.textContent = 'Applied';
        applyBtn.style.background = '#059669';
      }
    } else {
      frdAppliedCoupon = null;
      if (msgEl) {
        msgEl.style.display = 'block';
        msgEl.style.color = '#ef4444';
        msgEl.textContent = data.message || 'Invalid or expired coupon.';
      }
    }
    frdRecalcPrice();
  })
  .catch(err => {
    if (code === 'WELCOME10' && baseCost >= 1000) {
      const disc = Math.min(1000, Math.round(baseCost * 0.10));
      frdAppliedCoupon = { code: 'WELCOME10', discount_amount: disc, discount_type: 'percent', discount_value: 10 };
      if (msgEl) {
        msgEl.style.display = 'block';
        msgEl.style.color = '#059669';
        msgEl.textContent = `✓ Coupon applied! Saved ₹${disc.toLocaleString('en-IN')}`;
      }
    } else if (code === 'STAYORA500' && baseCost >= 3000) {
      const disc = 500;
      frdAppliedCoupon = { code: 'STAYORA500', discount_amount: disc, discount_type: 'flat', discount_value: 500 };
      if (msgEl) {
        msgEl.style.display = 'block';
        msgEl.style.color = '#059669';
        msgEl.textContent = `✓ Coupon applied! Saved ₹500`;
      }
    } else {
      if (msgEl) {
        msgEl.style.display = 'block';
        msgEl.style.color = '#ef4444';
        msgEl.textContent = 'Coupon invalid or minimum booking not met.';
      }
      frdAppliedCoupon = null;
    }
    frdRecalcPrice();
  });
}

function frdCalculateSubtotal() {
  let nights = 1;
  const activeEnd = (frdSelEnd && frdSelEnd >= frdSelStart) ? frdSelEnd : frdSelStart;
  if (frdSelStart && activeEnd) {
    nights = Math.max(1, Math.round((activeEnd - frdSelStart) / 86400000) + 1);
  }
  if (frdBookingMode === 'complete') {
    return FRD_FULL_FARM_PRICE * nights;
  } else {
    let activeRt = (FRD_ROOM_TYPES && FRD_ROOM_TYPES.length > 0)
      ? (FRD_ROOM_TYPES.find(r => r.id == frdSelectedRoomTypeId) || FRD_ROOM_TYPES[0])
      : null;
    let rtRate = activeRt ? parseFloat(activeRt.price_per_room) : FRD_RATE_PER_ROOM;
    let rtCap = activeRt ? parseInt(activeRt.capacity_per_room) : FRD_BED_CAPACITY;
    let rtMaxRooms = activeRt ? parseInt(activeRt.total_rooms) : FRD_TOTAL_BEDROOMS;
    const minRequired = Math.ceil(frdBcG / rtCap);
    let roomsAllocated = Math.min(rtMaxRooms, Math.max(minRequired, frdCustomRooms));
    return roomsAllocated * rtRate * nights;
  }
}

function frdOnRoomTypeChange(rtId) {
  frdSelectedRoomTypeId = parseInt(rtId) || null;
  const rt = FRD_ROOM_TYPES.find(r => r.id == frdSelectedRoomTypeId);
  if (rt) {
    const descEl = document.getElementById('frd-selected-room-type-desc');
    if (descEl) {
      descEl.textContent = rt.description || '';
      descEl.style.display = rt.description ? 'block' : 'none';
    }
  }
  frdRecalcPrice();
}

// ── DESC / ABOUT TOGGLE ──
function frdToggleClamp(textId, btnId) {
  const el  = document.getElementById(textId);
  const btn = document.getElementById(btnId);
  const clamped = el.classList.toggle('frd-clamped');
  btn.textContent = clamped ? 'Read more' : 'Read less';
}

// ── CATEGORIZED GALLERY (F08) ──
const FRD_CATEGORIZED_IMAGES = <?= json_encode($categorizedImages) ?>;
let frdActiveGalleryCategory = 'all';
let frdGalIdx = 0;

function frdFilterCategory(cat, btn) {
  document.querySelectorAll('.frd-cat-pill').forEach(b => {
    b.style.background = '#F8FAFC';
    b.style.color = '#475569';
    b.style.borderColor = '#E2E8F0';
    b.classList.remove('active');
  });
  if (btn) {
    btn.style.background = '#0F172A';
    btn.style.color = '#ffffff';
    btn.style.borderColor = '#0F172A';
    btn.classList.add('active');
  }

  frdActiveGalleryCategory = cat;
  const list = (FRD_CATEGORIZED_IMAGES[cat] && FRD_CATEGORIZED_IMAGES[cat].length > 0)
    ? FRD_CATEGORIZED_IMAGES[cat]
    : FRD_ALL_IMAGES;

  if (list && list.length > 0) {
    const mainImg = document.getElementById('frd-main-display-img');
    if (mainImg) mainImg.src = list[0];
    const countSpan = document.getElementById('frd-view-photos-count');
    if (countSpan) countSpan.textContent = `View all ${list.length} photos`;
  }
}

function frdOpenGallery(idx) {
  const currentList = (FRD_CATEGORIZED_IMAGES[frdActiveGalleryCategory] && FRD_CATEGORIZED_IMAGES[frdActiveGalleryCategory].length > 0)
    ? FRD_CATEGORIZED_IMAGES[frdActiveGalleryCategory]
    : FRD_ALL_IMAGES;

  if (!currentList.length) return;
  frdGalIdx = idx % currentList.length;
  document.getElementById('frd-gallery-img').src = currentList[frdGalIdx];
  document.getElementById('frd-gallery-counter').textContent = (frdGalIdx + 1) + ' / ' + currentList.length;
  document.getElementById('frd-gallery-overlay').classList.add('frd-open');
}

function frdCloseGallery() {
  document.getElementById('frd-gallery-overlay').classList.remove('frd-open');
}

function frdGalleryNav(dir) {
  const currentList = (FRD_CATEGORIZED_IMAGES[frdActiveGalleryCategory] && FRD_CATEGORIZED_IMAGES[frdActiveGalleryCategory].length > 0)
    ? FRD_CATEGORIZED_IMAGES[frdActiveGalleryCategory]
    : FRD_ALL_IMAGES;

  frdGalIdx = (frdGalIdx + dir + currentList.length) % currentList.length;
  document.getElementById('frd-gallery-img').src = currentList[frdGalIdx];
  document.getElementById('frd-gallery-counter').textContent = (frdGalIdx + 1) + ' / ' + currentList.length;
}

document.getElementById('frd-gallery-overlay').addEventListener('click', function(e){
  if (e.target === this) frdCloseGallery();
});

// ── INQUIRY LOGGING ──
function frdLogInquiry(type) {
  try {
    fetch('<?= url("log_inquiry") ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'farmhouse_id=<?= $encryptedId ?>&type=' + encodeURIComponent(type)
    }).catch(() => {});
  } catch (e) {}
}

// ── DUAL BOOKING MODE SELECTOR ──
function frdSetBookingMode(mode) {
  if (FRD_RENTAL_TYPE === 'room_wise') {
    mode = 'per_room';
  } else if (FRD_RENTAL_TYPE === 'complete') {
    mode = 'complete';
  } else if (!FRD_ALLOW_ROOM_BOOK && mode === 'per_room') {
    mode = 'complete';
  }
  frdBookingMode = (mode === 'per_room') ? 'per_room' : 'complete';

  const btnComplete = document.getElementById('frd-mode-btn-complete');
  const btnRoom     = document.getElementById('frd-mode-btn-room');
  const infoIcon    = document.getElementById('frd-mode-info-icon');
  const infoText    = document.getElementById('frd-mode-info-text');
  const rateLabel   = document.getElementById('frd-bc-rate-type-label');
  const badgeTag    = document.getElementById('frd-active-badge-tag');
  const priceHeader = document.getElementById('frd-bc-price-header');
  const priceSub    = document.getElementById('frd-bc-price-sub');
  const roomWrapper = document.getElementById('frd-room-selector-wrapper');
  const rtWrapper   = document.getElementById('frd-room-type-picker-wrapper');
  const modeFormInp = document.getElementById('frd-form-booking-type');
  const guestSubLbl = document.getElementById('frd-bc-guest-sub-label');
  const bcModeName  = document.getElementById('frd-bc-mode-name');

  if (modeFormInp) modeFormInp.value = frdBookingMode;

  if (frdBookingMode === 'complete') {
    if (btnComplete) btnComplete.classList.add('active');
    if (btnRoom)     btnRoom.classList.remove('active');

    if (infoIcon) infoIcon.textContent = 'villa';
    if (infoText) {
      infoText.innerHTML = `<strong>Complete Farmhouse Mode:</strong> You are booking the entire <strong>${FRD_TOTAL_BEDROOMS} BHK</strong> estate exclusively for your group (Up to ${FRD_MAX_GUESTS} overnight guests).`;
    }

    if (rateLabel) rateLabel.textContent = 'Complete Farmhouse Rate';
    if (badgeTag) {
      badgeTag.className = 'frd-active-mode-badge complete';
      badgeTag.textContent = '🏡 Complete Estate';
    }
    if (priceHeader) priceHeader.innerHTML = `₹${FRD_FULL_FARM_PRICE.toLocaleString('en-IN')} <span>/ night</span>`;
    if (priceSub) priceSub.textContent = `🏡 Entire Estate · ${FRD_TOTAL_BEDROOMS} BHK · Up to ${FRD_MAX_GUESTS} Overnight Guests`;
    if (guestSubLbl) guestSubLbl.textContent = `Full Estate Capacity (Up to ${FRD_MAX_GUESTS} Guests)`;
    if (bcModeName) {
      bcModeName.textContent = 'Complete Farmhouse';
      bcModeName.style.color = '#1E40AF';
    }

    if (roomWrapper) roomWrapper.style.display = 'none';
    if (rtWrapper)   rtWrapper.style.display = 'none';

  } else {
    // Per Room mode
    if (btnComplete) btnComplete.classList.remove('active');
    if (btnRoom)     btnRoom.classList.add('active');

    let activeRt = FRD_ROOM_TYPES.find(r => r.id == frdSelectedRoomTypeId) || FRD_ROOM_TYPES[0];
    let rtRate = activeRt ? parseFloat(activeRt.price_per_room) : FRD_RATE_PER_ROOM;
    let rtCap = activeRt ? parseInt(activeRt.capacity_per_room) : FRD_BED_CAPACITY;
    let rtName = activeRt ? activeRt.room_type_name : 'Room';

    if (infoIcon) infoIcon.textContent = 'bedroom_parent';
    if (infoText) {
      infoText.innerHTML = `<strong>Room-Wise Booking:</strong> Select your desired room category and book only what you need at <strong>₹${rtRate.toLocaleString('en-IN')} / room / night</strong> (${rtCap} guests max per room).`;
    }

    if (rateLabel) rateLabel.textContent = 'Room-Wise Rate';
    if (badgeTag) {
      badgeTag.className = 'frd-active-mode-badge room';
      badgeTag.textContent = '🛏️ Room-Wise Booking';
    }
    if (priceHeader) priceHeader.innerHTML = `₹${rtRate.toLocaleString('en-IN')} <span>/ room / night</span>`;
    if (priceSub) priceSub.textContent = `🛏️ ${rtName} · ${rtCap} Guests/Room`;
    if (guestSubLbl) guestSubLbl.textContent = `Children included · Max ${rtCap} guests/room`;
    if (bcModeName) {
      bcModeName.textContent = rtName;
      bcModeName.style.color = '#059669';
    }

    if (roomWrapper) roomWrapper.style.display = 'block';
    if (rtWrapper)   rtWrapper.style.display = 'block';

    const minRequired = Math.ceil(frdBcG / rtCap);
    frdCustomRooms = Math.max(minRequired, frdCustomRooms);
  }

  frdRecalcPrice();
}

// ── GUESTS & CAPACITY DYNAMICS ──
function frdBcChg(delta) {
  frdBcG = Math.max(1, Math.min(FRD_MAX_GUESTS, frdBcG + delta));
  
  const gEl = document.getElementById('frd-bc-g');
  if (gEl) gEl.textContent = frdBcG;

  const fi = document.getElementById('frd-form-guests');
  if (fi) fi.value = frdBcG;

  if (frdBookingMode === 'per_room') {
    const minRequired = Math.ceil(frdBcG / FRD_BED_CAPACITY);
    if (frdCustomRooms < minRequired) {
      frdCustomRooms = Math.min(FRD_TOTAL_BEDROOMS, minRequired);
    }
  }

  frdRecalcPrice();
}

// ── ROOM COUNT ADJUSTER (PER ROOM MODE) ──
function frdRoomChg(delta) {
  const minRequired = Math.ceil(frdBcG / FRD_BED_CAPACITY);
  frdCustomRooms = Math.max(minRequired, Math.min(FRD_TOTAL_BEDROOMS, frdCustomRooms + delta));
  frdRecalcPrice();
}

// ── PRICE RECALCULATION ENGINE (F19, F20, F21, F22, F23, F24, F25, F74) ──
function frdRecalcPrice() {
  let nights = 1;
  const activeEnd = (frdSelEnd && frdSelEnd >= frdSelStart) ? frdSelEnd : frdSelStart;
  if (frdSelStart && activeEnd) {
    nights = Math.max(1, Math.round((activeEnd - frdSelStart) / 86400000) + 1);
  } else {
    nights = 1;
  }

  const nightsLabel = document.getElementById('frd-bc-nights-count');
  if (nightsLabel) {
    nightsLabel.textContent = `${nights} Day${nights > 1 ? 's' : ''} / ${nights} Night${nights > 1 ? 's' : ''}`;
  }

  let baseCost = 0;
  let roomsAllocated = 1;
  let weekendNights = 0;
  let weekdayNights = 0;
  let hasSeasonalSurge = false;

  let activeRt = null;
  let weekdayRate = 0;
  let weekendRate = 0;

  if (frdBookingMode === 'complete') {
    roomsAllocated = FRD_TOTAL_BEDROOMS;
    weekdayRate = FRD_FULL_FARM_PRICE;
    weekendRate = FRD_WEEKEND_PRICE > 0 ? FRD_WEEKEND_PRICE : Math.round(FRD_FULL_FARM_PRICE * 1.2);

    const pill = document.getElementById('frd-room-allocation-pill');
    if (pill) {
      pill.innerHTML = `<span class="material-symbols-outlined" style="font-size:14px;color:#1E40AF;vertical-align:middle;">villa</span> <span>Entire ${FRD_TOTAL_BEDROOMS} BHK Farmhouse (${frdBcG} Guests Selected · Up to ${FRD_MAX_GUESTS} Max)</span>`;
    }
    const roomsCountLabel = document.getElementById('frd-bc-rooms-count');
    if (roomsCountLabel) {
      roomsCountLabel.textContent = `Full ${FRD_TOTAL_BEDROOMS} BHK Estate (All Bedrooms)`;
    }
  } else {
    activeRt = (FRD_ROOM_TYPES && FRD_ROOM_TYPES.length > 0)
      ? (FRD_ROOM_TYPES.find(r => r.id == frdSelectedRoomTypeId) || FRD_ROOM_TYPES[0])
      : null;
    let rtRate = activeRt ? parseFloat(activeRt.price_per_room) : FRD_RATE_PER_ROOM;
    let rtWeekend = (activeRt && parseFloat(activeRt.weekend_price) > 0) ? parseFloat(activeRt.weekend_price) : Math.round(rtRate * 1.2);
    let rtCap = activeRt ? parseInt(activeRt.capacity_per_room) : FRD_BED_CAPACITY;
    let rtMaxRooms = activeRt ? parseInt(activeRt.total_rooms) : FRD_TOTAL_BEDROOMS;
    let rtName = activeRt ? activeRt.room_type_name : 'Standard Room';

    const minRequired = Math.ceil(frdBcG / rtCap);
    roomsAllocated = Math.min(rtMaxRooms, Math.max(minRequired, frdCustomRooms));
    weekdayRate = rtRate * roomsAllocated;
    weekendRate = rtWeekend * roomsAllocated;

    const formRtInput = document.getElementById('frd-form-room-type-id');
    if (formRtInput && activeRt) {
      formRtInput.value = activeRt.id;
    }
    const roomDisplay = document.getElementById('frd-bc-rooms-display');
    if (roomDisplay) roomDisplay.textContent = roomsAllocated;
    const roomCalcHint = document.getElementById('frd-room-calc-hint');
    if (roomCalcHint) {
      roomCalcHint.textContent = `${roomsAllocated} × ${rtName} for ${frdBcG} guest${frdBcG > 1 ? 's' : ''} (Max ${rtCap}/room)`;
    }
    const pill = document.getElementById('frd-room-allocation-pill');
    if (pill) {
      pill.innerHTML = `<span class="material-symbols-outlined" style="font-size:14px;color:#059669;vertical-align:middle;">bedroom_parent</span> <span>${roomsAllocated} × ${rtName} (₹${rtRate.toLocaleString('en-IN')}/nt · Max ${rtCap} guests/room)</span>`;
    }
    const roomsCountLabel = document.getElementById('frd-bc-rooms-count');
    if (roomsCountLabel) {
      roomsCountLabel.textContent = `${roomsAllocated} × ${rtName}`;
    }
  }

  // Iterate day by day for weekend tariffs & seasonal surges
  if (frdSelStart && activeEnd) {
    let cur = new Date(frdSelStart);
    const endLimit = new Date(activeEnd);
    while (cur <= endLimit) {
      const dayOfWeek = cur.getDay(); // 0 is Sunday, 5 is Friday, 6 is Saturday
      const isWeekend = (dayOfWeek === 5 || dayOfWeek === 6 || dayOfWeek === 0);
      let nightPrice = isWeekend ? weekendRate : weekdayRate;
      if (isWeekend) weekendNights++; else weekdayNights++;

      // Check seasonal surges
      const curIso = cur.getFullYear() + '-' + String(cur.getMonth() + 1).padStart(2, '0') + '-' + String(cur.getDate()).padStart(2, '0');
      if (FRD_SEASONAL && FRD_SEASONAL.length > 0) {
        const matchingSeason = FRD_SEASONAL.find(s => curIso >= s.start_date && curIso <= s.end_date);
        if (matchingSeason) {
          hasSeasonalSurge = true;
          if (matchingSeason.price_per_night && parseFloat(matchingSeason.price_per_night) > 0) {
            nightPrice = parseFloat(matchingSeason.price_per_night);
            if (frdBookingMode === 'per_room') {
              nightPrice = Math.round(nightPrice * (roomsAllocated / FRD_TOTAL_BEDROOMS));
            }
          } else if (matchingSeason.multiplier && parseFloat(matchingSeason.multiplier) > 1.0) {
            nightPrice = Math.round(nightPrice * parseFloat(matchingSeason.multiplier));
          }
        }
      }

      baseCost += nightPrice;
      cur.setDate(cur.getDate() + 1);
    }
  } else {
    baseCost = weekdayRate * nights;
  }

  // Dynamic tariff badges
  const rateDetailLabel = document.getElementById('frd-rate-detail-label');
  if (rateDetailLabel) {
    if (hasSeasonalSurge) {
      rateDetailLabel.textContent = 'Festive Surge Tariff';
      rateDetailLabel.style.color = '#dc2626';
    } else if (weekendNights > 0) {
      rateDetailLabel.textContent = `${weekdayNights} Wkday / ${weekendNights} Wkend`;
      rateDetailLabel.style.color = '#d97706';
    } else {
      rateDetailLabel.textContent = 'Standard Tariff';
      rateDetailLabel.style.color = '#64748B';
    }
  }

  const weekendNote = document.getElementById('frd-weekend-note-row');
  if (weekendNote) {
    weekendNote.style.display = (weekendNights > 0) ? 'block' : 'none';
  }

  // Extended / Long-stay discount (F22)
  let longStayDiscount = 0;
  const longstayRow = document.getElementById('frd-longstay-row');
  const longstayLabel = document.getElementById('frd-longstay-label');
  const longstayVal = document.getElementById('frd-longstay-val');

  if (nights >= 28 && FRD_MONTHLY_DISCOUNT > 0) {
    longStayDiscount = Math.round(baseCost * (FRD_MONTHLY_DISCOUNT / 100));
    if (longstayLabel) longstayLabel.textContent = `Monthly Stay Discount (${FRD_MONTHLY_DISCOUNT}%):`;
  } else if (nights >= 7 && FRD_WEEKLY_DISCOUNT > 0) {
    longStayDiscount = Math.round(baseCost * (FRD_WEEKLY_DISCOUNT / 100));
    if (longstayLabel) longstayLabel.textContent = `Weekly Stay Discount (${FRD_WEEKLY_DISCOUNT}%):`;
  }

  if (longstayRow && longstayVal) {
    if (longStayDiscount > 0) {
      longstayRow.style.display = 'flex';
      longstayVal.textContent = '- ₹' + longStayDiscount.toLocaleString('en-IN');
    } else {
      longstayRow.style.display = 'none';
    }
  }

  // Optional Addon Charges (F24)
  let addonCharges = 0;
  if (FRD_ADDONS && FRD_ADDONS.length > 0 && frdSelectedAddons.length > 0) {
    frdSelectedAddons.forEach(aId => {
      const ad = FRD_ADDONS.find(a => a.id == aId);
      if (ad) {
        const p = parseFloat(ad.price);
        addonCharges += (ad.price_type === 'per_night') ? (p * nights) : p;
      }
    });
  }

  const addonsRow = document.getElementById('frd-addons-row');
  const addonsVal = document.getElementById('frd-addons-val');
  if (addonsRow && addonsVal) {
    if (addonCharges > 0) {
      addonsRow.style.display = 'flex';
      addonsVal.textContent = '+ ₹' + addonCharges.toLocaleString('en-IN');
    } else {
      addonsRow.style.display = 'none';
    }
  }

  // Cleaning fee
  const cleaningFee = FRD_CLEANING_FEE;
  const cleanFeeDisplay = document.getElementById('frd-cleaning-fee-display');
  if (cleanFeeDisplay) cleanFeeDisplay.textContent = '+ ₹' + cleaningFee.toLocaleString('en-IN');

  // Coupon Discount (F74)
  let couponDiscount = 0;
  const couponRow = document.getElementById('frd-coupon-row');
  const couponCodeEl = document.getElementById('frd-applied-coupon-code');
  const couponDiscountVal = document.getElementById('frd-coupon-discount-val');

  if (frdAppliedCoupon) {
    const discountedBase = Math.max(0, baseCost - longStayDiscount);
    if (frdAppliedCoupon.discount_type === 'percent') {
      couponDiscount = Math.round(discountedBase * (parseFloat(frdAppliedCoupon.discount_value) / 100));
    } else if (frdAppliedCoupon.discount_amount) {
      couponDiscount = parseFloat(frdAppliedCoupon.discount_amount);
    }
    couponDiscount = Math.min(discountedBase, couponDiscount);

    if (couponRow && couponCodeEl && couponDiscountVal) {
      couponRow.style.display = 'flex';
      couponCodeEl.textContent = frdAppliedCoupon.code;
      couponDiscountVal.textContent = '- ₹' + couponDiscount.toLocaleString('en-IN');
    }
  } else if (couponRow) {
    couponRow.style.display = 'none';
  }

  // Zero Initial Platform Fee Architecture (F25)
  const platformFee = 0;
  const platformFeeEl = document.getElementById('frd-platform-fee-display');
  if (platformFeeEl) platformFeeEl.textContent = '₹0 (Free)';

  // Refundable Security Deposit (F23)
  const securityDeposit = FRD_SECURITY_DEPOSIT;
  const secDepDisplay = document.getElementById('frd-security-deposit-display');
  if (secDepDisplay) secDepDisplay.textContent = '+ ₹' + securityDeposit.toLocaleString('en-IN');

  // Grand Total Calculation
  const grandTotal = Math.max(0, (baseCost - longStayDiscount) + addonCharges + cleaningFee - couponDiscount + securityDeposit);

  // Update DOM displays
  const baseEl = document.getElementById('frd-bc-base');
  if (baseEl) baseEl.textContent = '₹' + baseCost.toLocaleString('en-IN');

  const totalEl = document.getElementById('frd-bc-total');
  if (totalEl) totalEl.textContent = '₹' + grandTotal.toLocaleString('en-IN');

  // Update hidden form inputs
  const formPriceInput = document.getElementById('frd-form-price');
  if (formPriceInput) formPriceInput.value = grandTotal;

  const formRoomsInput = document.getElementById('frd-form-rooms');
  if (formRoomsInput) formRoomsInput.value = roomsAllocated;

  const formGuestsInput = document.getElementById('frd-form-guests');
  if (formGuestsInput) formGuestsInput.value = frdBcG;

  const formModeInput = document.getElementById('frd-form-booking-type');
  if (formModeInput) formModeInput.value = frdBookingMode;

  const formWeekendInput = document.getElementById('frd-form-weekend-nights');
  if (formWeekendInput) formWeekendInput.value = weekendNights;

  const formSecDepInput = document.getElementById('frd-form-security-deposit');
  if (formSecDepInput) formSecDepInput.value = securityDeposit;

  const formCleanInput = document.getElementById('frd-form-cleaning-fee');
  if (formCleanInput) formCleanInput.value = cleaningFee;

  const formAddonChargesInput = document.getElementById('frd-form-addon-charges');
  if (formAddonChargesInput) formAddonChargesInput.value = addonCharges;

  const formDiscountAmountInput = document.getElementById('frd-form-discount-amount');
  if (formDiscountAmountInput) formDiscountAmountInput.value = longStayDiscount + couponDiscount;

  const formCouponCodeInput = document.getElementById('frd-form-coupon-code');
  if (formCouponCodeInput) formCouponCodeInput.value = frdAppliedCoupon ? frdAppliedCoupon.code : '';

  // Update modal preview
  const modalTotal = document.getElementById('frd-modal-total-price');
  if (modalTotal) modalTotal.textContent = '₹' + grandTotal.toLocaleString('en-IN');

  const modalMode = document.getElementById('frd-modal-booking-mode');
  if (modalMode) {
    if (frdBookingMode === 'complete') {
      modalMode.textContent = '🏡 Complete Farmhouse';
    } else {
      let rtName = activeRt ? activeRt.room_type_name : 'Room';
      modalMode.textContent = `🛏️ ${rtName} (${roomsAllocated} Room${roomsAllocated > 1 ? 's' : ''})`;
    }
  }

  const modalGuestsRooms = document.getElementById('frd-modal-guests-rooms');
  if (modalGuestsRooms) {
    let stayDesc = (frdBookingMode === 'complete') ? `Entire ${FRD_TOTAL_BEDROOMS} BHK Estate` : `${roomsAllocated} Room${roomsAllocated > 1 ? 's' : ''}`;
    if (frdBookingMode === 'per_room' && activeRt) {
      stayDesc = `${roomsAllocated} × ${activeRt.room_type_name}`;
    }
    modalGuestsRooms.textContent = `${frdBcG} Guest${frdBcG > 1 ? 's' : ''} · ${stayDesc} · ${nights} Day${nights > 1 ? 's' : ''} (${nights} Night${nights > 1 ? 's' : ''})`;
  }

  // Update mobile sticky bottom bar
  const msPrice = document.getElementById('frd-ms-price-display');
  if (msPrice) {
    msPrice.innerHTML = `₹${grandTotal.toLocaleString('en-IN')} <span style="font-size:11px;font-weight:600;color:#94a3b8;">total · ${nights} nt</span>`;
  }
  const msSub = document.getElementById('frd-ms-sub-display');
  if (msSub) {
    const modeName = (frdBookingMode === 'complete') ? 'Complete Farmhouse' : `${roomsAllocated} Rooms`;
    msSub.textContent = `${modeName} · ${frdBcG} Guest${frdBcG > 1 ? 's' : ''}`;
  }
}

function frdScrollToBookingOrOpen() {
  const ci = document.getElementById('frd-form-checkin')?.value;
  const co = document.getElementById('frd-form-checkout')?.value;
  if (!ci || !co) {
    const calSec = document.getElementById('frd-cal-section');
    if (calSec) {
      calSec.scrollIntoView({ behavior: 'smooth' });
    }
  } else {
    frdOpenBookingModal();
  }
}

// ── CALENDAR LOGIC ──
const FRD_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const FRD_DOW    = ['Mo','Tu','We','Th','Fr','Sa','Su'];
const FRD_TODAY  = new Date(); FRD_TODAY.setHours(0,0,0,0);

let frdCalBase  = new Date(FRD_TODAY.getFullYear(), FRD_TODAY.getMonth(), 1);
let frdSelStart = null, frdSelEnd = null;

function frdDateKey(d) {
  return d.getFullYear() + '-' +
    String(d.getMonth() + 1).padStart(2, '0') + '-' +
    String(d.getDate()).padStart(2, '0');
}

function frdBuildCal() {
  const wrap = document.getElementById('frd-cal-months-wrap');
  if (!wrap) return;
  wrap.innerHTML = '';
  for (let m = 0; m < 2; m++) {
    const d = new Date(frdCalBase.getFullYear(), frdCalBase.getMonth() + m, 1);
    wrap.appendChild(frdBuildMonth(d));
    document.getElementById('frd-month' + (m + 1) + '-label').textContent =
      FRD_MONTHS[d.getMonth()] + ' ' + d.getFullYear();
  }
}

function frdBuildMonth(d) {
  const wrap = document.createElement('div');
  const grid = document.createElement('div');
  grid.className = 'frd-cal-grid';

  FRD_DOW.forEach(day => {
    const h = document.createElement('div');
    h.className = 'frd-cal-dow';
    h.textContent = day;
    grid.appendChild(h);
  });

  const firstDay = (d.getDay() + 6) % 7;
  for (let i = 0; i < firstDay; i++) {
    const e = document.createElement('div');
    e.className = 'frd-cal-day frd-empty';
    grid.appendChild(e);
  }

  const daysInMonth = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();

  for (let day = 1; day <= daysInMonth; day++) {
    const dateObj = new Date(d.getFullYear(), d.getMonth(), day);
    const key     = frdDateKey(dateObj);
    const cell    = document.createElement('div');
    cell.className = 'frd-cal-day';
    cell.textContent = day;

    const isPast   = dateObj < FRD_TODAY;
    const isBooked = FRD_OCCUPIED_DATES.includes(key);
    const isReq    = FRD_REQUESTED_DATES.includes(key);

    if (isPast)        { cell.classList.add('frd-past'); }
    else if (isBooked) { cell.classList.add('frd-booked'); }
    else if (isReq)    { cell.classList.add('frd-requested'); }
    else {
      const ts = dateObj.getTime();
      if (frdSelStart && frdSelEnd && frdSelEnd > frdSelStart) {
        if      (ts === frdSelStart) cell.classList.add('frd-range-start');
        else if (ts === frdSelEnd)   cell.classList.add('frd-range-end');
        else if (ts > frdSelStart && ts < frdSelEnd) cell.classList.add('frd-in-range');
      } else if (frdSelStart && (ts === frdSelStart || (frdSelEnd && ts === frdSelEnd))) {
        cell.classList.add('frd-single-selected');
      }
      cell.addEventListener('click', () => frdPickDay(dateObj));
    }

    if (dateObj.toDateString() === FRD_TODAY.toDateString() &&
        !cell.classList.contains('frd-booked') &&
        !cell.classList.contains('frd-past')) {
      cell.classList.add('frd-today-mark');
    }

    grid.appendChild(cell);
  }

  wrap.appendChild(grid);
  return wrap;
}

function frdPickDay(d) {
  const dayTs = d.getTime();

  // If no start date chosen, or a range was already completed, start fresh with new Check-in date
  if (!frdSelStart || (frdSelStart && frdSelEnd)) {
    frdSelStart = dayTs;
    frdSelEnd   = null;
  } else if (dayTs > frdSelStart) {
    // 2nd click on a future date -> Selected End Date of stay
    // Check conflicts for all stay dates between frdSelStart and dayTs (inclusive)
    const rangeStart = new Date(frdSelStart);
    const rangeEnd   = new Date(dayTs);
    let cursor       = new Date(rangeStart);
    let hasConflict  = false;

    // Check all nights stayed [start, end]
    while (cursor <= rangeEnd) {
      if (FRD_OCCUPIED_DATES.includes(frdDateKey(cursor))) {
        hasConflict = true;
        break;
      }
      cursor.setDate(cursor.getDate() + 1);
    }

    if (hasConflict) {
      alert('Your selected date range includes already booked or blocked dates. Please select alternative dates.');
      frdSelStart = null;
      frdSelEnd   = null;
    } else {
      frdSelEnd = dayTs;
    }
  } else if (dayTs === frdSelStart) {
    // Clicked the exact same day: single-day stay (Check-in: today, Check-out: next day)
    frdSelEnd = dayTs;
  } else {
    // Clicked an earlier day: start new selection from this earlier day
    frdSelStart = dayTs;
    frdSelEnd   = null;
  }

  frdBuildCal();
  frdUpdateBookingCard();
}

function frdFmt2(ts) {
  return new Date(ts).toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'});
}
function frdIsoDate(ts) {
  const d = new Date(ts);
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

function frdUpdateBookingCard() {
  if (frdSelStart) {
    const s = new Date(frdSelStart);
    document.getElementById('frd-ci-date-label').textContent =
      s.toLocaleDateString('en-IN', {day:'numeric', month:'short'});
    document.getElementById('frd-bc-ci').textContent = frdFmt2(frdSelStart) + ' · 12:00 PM';
    const fi = document.getElementById('frd-form-checkin');
    if (fi) fi.value = frdIsoDate(frdSelStart);
  }

  // Check-out date is automatically set to ONE DAY AFTER the selected end date
  let checkoutTs;
  if (frdSelStart) {
    const activeEnd = (frdSelEnd && frdSelEnd >= frdSelStart) ? frdSelEnd : frdSelStart;
    checkoutTs = activeEnd + 86400000;
  }

  if (checkoutTs) {
    const e = new Date(checkoutTs);
    document.getElementById('frd-co-date-label').textContent =
      e.toLocaleDateString('en-IN', {day:'numeric', month:'short'});
    document.getElementById('frd-bc-co').textContent = frdFmt2(checkoutTs) + ' · Before 11:00 AM';
    const fo = document.getElementById('frd-form-checkout');
    if (fo) fo.value = frdIsoDate(checkoutTs);
  }

  frdRecalcPrice();

  if (frdSelStart && checkoutTs) {
    frdUpdateRoomAvailabilityBadges(frdIsoDate(frdSelStart), frdIsoDate(checkoutTs));
  }
}

function frdPrevMonth() { 
  frdCalBase = new Date(frdCalBase.getFullYear(), frdCalBase.getMonth() - 1, 1); 
  frdBuildCal(); 
  if (typeof frdMatrixOpen !== 'undefined' && frdMatrixOpen) {
    frdLoadRoomMatrix(frdCalBase.getFullYear(), frdCalBase.getMonth() + 1);
  }
}

function frdNextMonth() { 
  frdCalBase = new Date(frdCalBase.getFullYear(), frdCalBase.getMonth() + 1, 1); 
  frdBuildCal(); 
  if (typeof frdMatrixOpen !== 'undefined' && frdMatrixOpen) {
    frdLoadRoomMatrix(frdCalBase.getFullYear(), frdCalBase.getMonth() + 1);
  }
}

// ── ROOM-WISE GRANULAR AVAILABILITY GRID (F29) ──
let frdMatrixOpen = false;

function frdToggleRoomMatrix() {
  const wrap = document.getElementById('frd-room-matrix-wrap');
  const lbl  = document.getElementById('frd-toggle-room-grid-label');
  if (!wrap) return;

  frdMatrixOpen = !frdMatrixOpen;
  wrap.style.display = frdMatrixOpen ? 'block' : 'none';
  if (lbl) lbl.textContent = frdMatrixOpen ? 'Hide Room Inventory Matrix' : 'Show Room Inventory Matrix';

  if (frdMatrixOpen) {
    frdLoadRoomMatrix(frdCalBase.getFullYear(), frdCalBase.getMonth() + 1);
  }
}

function frdLoadRoomMatrix(year, month) {
  const tableWrap = document.getElementById('frd-room-matrix-table-wrap');
  const titleEl   = document.getElementById('frd-matrix-month-title');
  if (!tableWrap) return;

  const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  if (titleEl) {
    titleEl.textContent = `${monthNames[month - 1]} ${year} · Room Inventory Matrix`;
  }

  tableWrap.innerHTML = '<div style="text-align:center;padding:16px;color:#94a3b8;">Loading inventory matrix...</div>';

  fetch(`<?= url('api/room-calendar-matrix') ?>?id=<?= urlencode($encryptedId) ?>&year=${year}&month=${month}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.days || res.days.length === 0) {
        tableWrap.innerHTML = '<div style="text-align:center;padding:12px;color:#ef4444;">Unable to load room availability matrix.</div>';
        return;
      }

      const roomTypes = res.room_types || [];
      let html = '<table style="width:100%;border-collapse:collapse;text-align:center;font-size:11px;">';
      html += '<thead><tr style="background:#f1f5f9;color:#334155;border-bottom:2px solid #cbd5e1;">';
      html += '<th style="padding:8px 10px;text-align:left;">Date</th>';
      html += '<th style="padding:8px 6px;">Day</th>';
      html += '<th style="padding:8px 6px;">Estate Status</th>';
      roomTypes.forEach(rt => {
        html += `<th style="padding:8px 6px;">${rt.room_type_name} <span style="font-weight:400;color:#64748B;">(${rt.total_rooms})</span></th>`;
      });
      html += '</tr></thead><tbody>';

      res.days.forEach(d => {
        const isPast = d.is_past;
        const isBlocked = d.is_blocked || d.entire_booked;
        const rowBg = isPast ? '#F8FAFC' : (isBlocked ? '#fff5f5' : '#ffffff');
        const textColor = isPast ? '#94a3b8' : '#0F172A';

        html += `<tr style="background:${rowBg};color:${textColor};border-bottom:1px solid #f1f5f9;">`;
        html += `<td style="padding:7px 10px;text-align:left;font-weight:700;">${d.date}</td>`;
        html += `<td style="padding:7px 6px;color:#64748B;">${d.day_name}</td>`;

        if (d.is_blocked) {
          html += `<td style="padding:7px 6px;"><span style="color:#ef4444;font-weight:700;font-size:10.5px;">🔒 Blocked (${d.blocked_reason || 'Unavailable'})</span></td>`;
        } else if (d.entire_booked) {
          html += `<td style="padding:7px 6px;"><span style="color:#e11d48;font-weight:700;font-size:10.5px;">🚫 Estate Reserved</span></td>`;
        } else {
          html += `<td style="padding:7px 6px;"><span style="color:#10b981;font-weight:700;font-size:10.5px;">✓ Open for Stay</span></td>`;
        }

        roomTypes.forEach(rt => {
          const avail = (d.rooms && d.rooms[rt.id]) ? d.rooms[rt.id].available : 0;
          const total = rt.total_rooms;
          let badgeColor = '#10b981';
          let badgeBg = '#ecfdf5';
          let statusText = `${avail}/${total} Avail`;

          if (isBlocked || d.entire_booked || avail === 0) {
            badgeColor = '#ef4444';
            badgeBg = '#fef2f2';
            statusText = '0 Avail';
          } else if (avail < total) {
            badgeColor = '#d97706';
            badgeBg = '#fffbeb';
            statusText = `${avail}/${total} Left`;
          }

          html += `<td style="padding:7px 6px;">
            <span style="display:inline-block;padding:2px 7px;border-radius:6px;font-size:10px;font-weight:800;color:${badgeColor};background:${badgeBg};">
              ${statusText}
            </span>
          </td>`;
        });

        html += '</tr>';
      });

      html += '</tbody></table>';
      tableWrap.innerHTML = html;
    })
    .catch(err => {
      console.error('Room matrix fetch error:', err);
      tableWrap.innerHTML = '<div style="text-align:center;padding:12px;color:#ef4444;">Failed to load room availability matrix.</div>';
    });
}

function frdUpdateRoomAvailabilityBadges(checkIn, checkOut) {
  if (!checkIn || !checkOut) return;

  fetch(`<?= url('api/room-availability') ?>?id=<?= urlencode($encryptedId) ?>&check_in=${checkIn}&check_out=${checkOut}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.room_types) return;

      res.room_types.forEach(rt => {
        const badgeEl = document.getElementById(`frd-rt-avail-badge-${rt.id}`);
        const textEl  = document.getElementById(`frd-rt-avail-text-${rt.id}`);
        if (!badgeEl || !textEl) return;

        const avail = rt.available_rooms;
        const total = rt.total_rooms;

        if (res.entire_property_booked || avail <= 0) {
          badgeEl.style.background = 'rgba(239,68,68,0.92)';
          textEl.textContent = 'Sold Out';
        } else if (avail < total) {
          badgeEl.style.background = 'rgba(245,158,11,0.92)';
          textEl.textContent = `${avail} of ${total} Left`;
        } else {
          badgeEl.style.background = 'rgba(16,185,129,0.92)';
          textEl.textContent = `${avail} of ${total} Avail`;
        }
      });
    })
    .catch(err => console.warn('Could not refresh room availability badges:', err));
}

// ── BOOKING MODAL & QR CODE ACTIONS ──
function frdOpenBookingModal() {
  if (!frdSelStart) {
    alert('Please select your check-in date on the calendar first.');
    const calSection = document.getElementById('frd-cal-section');
    if (calSection) calSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  if (!document.getElementById('frd-agree-chk').checked) {
    alert('Please review and check the "I agree to the Important Rules" box before booking.');
    document.getElementById('frd-agree-chk').focus();
    return;
  }

  const activeEnd = (frdSelEnd && frdSelEnd >= frdSelStart) ? frdSelEnd : frdSelStart;
  const nights = Math.max(1, Math.round((activeEnd - frdSelStart) / 86400000) + 1);
  const checkoutTs = activeEnd + 86400000;

  const minRequired = Math.ceil(frdBcG / FRD_BED_CAPACITY);
  const roomsAllocated = (frdBookingMode === 'complete') ? FRD_TOTAL_BEDROOMS : Math.min(FRD_TOTAL_BEDROOMS, Math.max(minRequired, frdCustomRooms));

  // Populate summary in modal
  document.getElementById('frd-modal-stay-dates').textContent = 
    `${frdFmt2(frdSelStart)} (12:00 PM) → ${frdFmt2(checkoutTs)} (Before 11:00 AM) · ${nights} Day${nights > 1 ? 's' : ''} (${nights} Night${nights > 1 ? 's' : ''})`;

  const modalMode = document.getElementById('frd-modal-booking-mode');
  if (modalMode) {
    modalMode.textContent = (frdBookingMode === 'complete') ? '🏡 Complete Farmhouse' : `🛏️ Per Room Booking (${roomsAllocated} Rooms)`;
  }

  const headerIcon = document.getElementById('frd-modal-header-icon');
  if (headerIcon) {
    headerIcon.textContent = (frdBookingMode === 'complete') ? 'villa' : 'bedroom_parent';
  }

  const modalGuestsRooms = document.getElementById('frd-modal-guests-rooms');
  if (modalGuestsRooms) {
    const stayDesc = (frdBookingMode === 'complete') ? `Entire ${FRD_TOTAL_BEDROOMS} BHK Estate` : `${roomsAllocated} Room${roomsAllocated > 1 ? 's' : ''}`;
    modalGuestsRooms.textContent = `${frdBcG} Guest${frdBcG > 1 ? 's' : ''} · ${stayDesc} · ${nights} Day${nights > 1 ? 's' : ''} (${nights} Night${nights > 1 ? 's' : ''})`;
  }

  document.getElementById('frd-modal-total-price').textContent = 
    document.getElementById('frd-bc-total').textContent;

  document.getElementById('frd-booking-request-modal').style.display = 'flex';
}

function frdCloseBookingModal() {
  document.getElementById('frd-booking-request-modal').style.display = 'none';
}

function frdSelectPaymentMethod(method) {
  const hiddenInput = document.getElementById('frd-form-payment-method');
  if (hiddenInput) hiddenInput.value = method;

  const panelUpi  = document.getElementById('frd-pay-panel-upi');
  const panelBank = document.getElementById('frd-pay-panel-bank');
  const panelProp = document.getElementById('frd-pay-panel-property');
  const proofSec  = document.getElementById('frd-pay-proof-section');

  const lblUpi  = document.getElementById('frd-pm-label-upi');
  const lblBank = document.getElementById('frd-pm-label-bank');
  const lblProp = document.getElementById('frd-pm-label-property');

  // Reset borders
  [lblUpi, lblBank, lblProp].forEach(l => {
    if (l) {
      l.style.border = '1.5px solid #cbd5e1';
      l.style.background = '#ffffff';
    }
  });

  if (panelUpi)  panelUpi.style.display  = (method === 'upi') ? 'block' : 'none';
  if (panelBank) panelBank.style.display = (method === 'bank_transfer') ? 'block' : 'none';
  if (panelProp) panelProp.style.display = (method === 'pay_at_property') ? 'block' : 'none';

  if (method === 'upi') {
    if (lblUpi) { lblUpi.style.border = '2px solid #1E40AF'; lblUpi.style.background = '#f0f9ff'; }
    if (proofSec) proofSec.style.display = 'block';
  } else if (method === 'bank_transfer') {
    if (lblBank) { lblBank.style.border = '2px solid #1E40AF'; lblBank.style.background = '#f0f9ff'; }
    if (proofSec) proofSec.style.display = 'block';
  } else if (method === 'pay_at_property') {
    if (lblProp) { lblProp.style.border = '2px solid #10b981'; lblProp.style.background = '#ecfdf5'; }
    if (proofSec) proofSec.style.display = 'none';
  }
}

function frdCopyUpi(upiVal) {
  if (!upiVal) return;
  navigator.clipboard.writeText(upiVal).then(() => {
    const btn = document.getElementById('frd-copy-upi-btn');
    if (btn) {
      btn.textContent = '✓ Copied!';
      setTimeout(() => {
        btn.textContent = '📋 Copy';
      }, 2500);
    }
  }).catch(err => {
    alert('UPI ID: ' + upiVal);
  });
}

function frdSubmitBookingModal(form) {
  const btn = document.getElementById('frd-submit-request-btn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span>Processing Request...</span> ⏳';
  }
  return true;
}

// ── AMENITIES INTERACTIVE FILTERING & TOGGLE ──
let frdAmenitiesExpanded = false;
let frdActiveAmenityCategory = 'all';

function frdFilterAmenities(cat, btn) {
  frdActiveAmenityCategory = cat;
  
  // Update active tab styling
  const tabs = document.querySelectorAll('.frd-am-tab');
  tabs.forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const cards = document.querySelectorAll('#frd-amenities-grid .frd-amenity-card');
  const toggleBtn = document.getElementById('frd-toggle-amenities-btn');

  if (cat === 'all') {
    cards.forEach((card, idx) => {
      if (frdAmenitiesExpanded || idx < 9) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
    if (toggleBtn) toggleBtn.style.display = (cards.length > 9) ? 'inline-flex' : 'none';
  } else {
    cards.forEach(card => {
      const cardCat = (card.getAttribute('data-category') || '').toLowerCase();
      if (cardCat === cat.toLowerCase()) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
    if (toggleBtn) toggleBtn.style.display = 'none';
  }
}

function frdToggleAllAmenities(totalCount) {
  frdAmenitiesExpanded = !frdAmenitiesExpanded;
  const cards = document.querySelectorAll('#frd-amenities-grid .frd-amenity-card');
  const toggleText = document.getElementById('frd-toggle-amenities-text');
  const toggleIcon = document.getElementById('frd-toggle-amenities-icon');

  cards.forEach((card, idx) => {
    if (frdActiveAmenityCategory !== 'all') {
      const cardCat = (card.getAttribute('data-category') || '').toLowerCase();
      card.style.display = (cardCat === frdActiveAmenityCategory.toLowerCase()) ? 'flex' : 'none';
    } else {
      if (frdAmenitiesExpanded || idx < 9) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    }
  });

  if (toggleText && toggleIcon) {
    if (frdAmenitiesExpanded) {
      toggleText.textContent = 'Show Less';
      toggleIcon.textContent = 'expand_less';
    } else {
      toggleText.textContent = 'Show All ' + totalCount + ' Amenities';
      toggleIcon.textContent = 'apps';
      const sec = document.getElementById('frd-amenities-section');
      if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
}

// ── INITIALIZE ON LOAD ──
document.addEventListener('DOMContentLoaded', () => {
  frdBuildCal();
  frdSetBookingMode(FRD_DEFAULT_MODE); // initialize in property's configured default mode
});
</script>

<?php include __DIR__ . "/Includes/footer.php"; ?>