<?php
$owner            = $owner ?? [];
$ownerId          = (int)($owner['id'] ?? 0);
$properties       = $properties ?? [];
$bookings         = $bookings ?? [];
$financials       = $financials ?? [];
$documents        = $documents ?? [];
$ownershipHistory = $ownershipHistory ?? [];
$activeOwners     = $activeOwners ?? [];
$ownerReviews     = $ownerReviews ?? [];
$ownerReviewStats = $ownerReviewStats ?? [];
$tab              = $tab ?? 'properties';

$pageTitle   = htmlspecialchars($owner['name'] ?? 'Host') . " • Owner Dossier";
$currentPage = "owners";
$headerTitle = "Owner Dossier: " . htmlspecialchars($owner['name'] ?? '');

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

// Helper for avatars
function resolveDossierAvatar(?string $img): string {
    if (empty($img)) return '';
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) return $img;
    return asset('assets/images/uploads/avatars/' . ltrim($img, '/'));
}
$avatarUrl = resolveDossierAvatar($owner['profile_image'] ?? null);

$st = strtolower((string)($owner['status'] ?? 'active'));
$statusBadge = match($st) {
    'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'suspended' => 'bg-rose-50 text-rose-700 border-rose-200',
    'inactive'  => 'bg-slate-100 text-slate-700 border-slate-200',
    default     => 'bg-amber-50 text-amber-700 border-amber-200',
};

$kyc = strtolower((string)($owner['kyc_status'] ?? 'pending'));
$kycBadge = match($kyc) {
    'verified' => ['text' => 'KYC Verified', 'class' => 'text-teal-700 bg-teal-50 border-teal-200', 'icon' => 'verified'],
    'rejected' => ['text' => 'KYC Rejected', 'class' => 'text-rose-700 bg-rose-50 border-rose-200', 'icon' => 'cancel'],
    default    => ['text' => 'Pending Audit', 'class' => 'text-amber-700 bg-amber-50 border-amber-200', 'icon' => 'schedule'],
};
?>

<div class="space-y-6 pb-12">

  <!-- ══════════════════════════════════════════════
       1. TOP HERO DOSSIER HEADER
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      
      <!-- Left: Identity & Badges -->
      <div class="flex items-start gap-4 sm:gap-5">
        
        <!-- Large Avatar/Logo -->
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-brand-50 border-2 border-brand-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-700 font-headline font-black text-2xl shadow-sm">
          <?php if (!empty($avatarUrl)): ?>
            <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($owner['name']) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <span><?= strtoupper(substr($owner['name'], 0, 2)) ?></span>
          <?php endif; ?>
        </div>

        <div class="min-w-0">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
            <a href="<?= url('admin/owners') ?>" class="hover:text-brand-700 transition-colors">Property Owners</a>
            <span>/</span>
            <span class="text-slate-800">Dossier #<?= $ownerId ?></span>
          </div>

          <h1 class="font-headline font-black text-xl sm:text-2xl lg:text-3xl text-slate-900 tracking-tight flex items-center gap-2 flex-wrap">
            <span><?= htmlspecialchars($owner['name']) ?></span>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $statusBadge ?> capitalize">
              <?= $st ?>
            </span>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $kycBadge['class'] ?> flex items-center gap-1">
              <span class="material-symbols-outlined text-[13px]"><?= $kycBadge['icon'] ?></span>
              <span><?= $kycBadge['text'] ?></span>
            </span>
          </h1>

          <?php if (!empty($owner['business_name'])): ?>
            <div class="font-bold text-sm text-slate-600 mt-1 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-base text-slate-400">domain</span>
              <span><?= htmlspecialchars($owner['business_name']) ?></span>
            </div>
          <?php endif; ?>

          <!-- Coordinates Strip -->
          <div class="flex items-center gap-4 text-xs text-slate-500 mt-2.5 flex-wrap">
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">mail</span>
              <a href="mailto:<?= htmlspecialchars($owner['email']) ?>" class="hover:text-brand-700 font-semibold text-slate-700">
                <?= htmlspecialchars($owner['email']) ?>
              </a>
            </div>
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">call</span>
              <a href="tel:<?= htmlspecialchars($owner['phone']) ?>" class="hover:text-brand-700 font-semibold text-slate-700">
                <?= htmlspecialchars($owner['phone']) ?>
              </a>
            </div>
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">location_on</span>
              <span class="text-slate-700 font-medium"><?= htmlspecialchars($owner['city'] ?? 'India') ?><?= !empty($owner['state']) ? ', ' . htmlspecialchars($owner['state']) : '' ?></span>
            </div>
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">calendar_today</span>
              <span class="text-slate-400">Registered <?= date('M d, Y', strtotime($owner['created_at'])) ?></span>
            </div>
          </div>

        </div>
      </div>

      <!-- Right: Action Buttons -->
      <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
        
        <!-- Status Action -->
        <button 
          type="button" 
          onclick="openStatusModal(<?= $ownerId ?>, '<?= htmlspecialchars(addslashes($owner['name'])) ?>', '<?= $st ?>')"
          class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5 shadow-2xs">
          <span class="material-symbols-outlined text-[17px] text-slate-500">tune</span>
          <span>Status</span>
        </button>

        <!-- Upload Doc Trigger -->
        <button 
          type="button" 
          onclick="openUploadDocModal()"
          class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5 shadow-2xs">
          <span class="material-symbols-outlined text-[17px] text-slate-500">upload_file</span>
          <span>Upload Doc</span>
        </button>

        <!-- Edit Profile -->
        <a 
          href="<?= url('admin/owners/edit/' . $ownerId) ?>" 
          class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[17px]">edit</span>
          <span>Edit Profile</span>
        </a>

      </div>

    </div>

    <!-- ══════════════════════════════════════════════
         2. EXECUTIVE MINI METRICS STRIP
         ══════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 pt-6 mt-6 border-t border-slate-100">
      
      <!-- 1. Total Properties -->
      <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-100">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Properties Portfolio</div>
        <div class="font-headline font-black text-xl text-indigo-700 mt-1">
          <?= count($properties) ?>
        </div>
        <div class="text-[10px] text-slate-500 mt-0.5">Estates in management</div>
      </div>

      <!-- 2. Gross Booking Turnover -->
      <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-100">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Booking Turnover</div>
        <div class="font-headline font-black text-xl text-slate-900 mt-1">
          ₹<?= number_format((float)($financials['gross_booking_value'] ?? 0)) ?>
        </div>
        <div class="text-[10px] text-slate-500 mt-0.5"><?= (int)($financials['total_bookings'] ?? 0) ?> total bookings</div>
      </div>

      <!-- 3. Payments Collected -->
      <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-100">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Collected Revenue</div>
        <div class="font-headline font-black text-xl text-emerald-600 mt-1">
          ₹<?= number_format((float)($financials['total_collected'] ?? 0)) ?>
        </div>
        <div class="text-[10px] text-slate-500 mt-0.5">Guest payments received</div>
      </div>

      <!-- 4. Outstanding Guest Balances -->
      <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-100">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending Balance</div>
        <div class="font-headline font-black text-xl text-amber-600 mt-1">
          ₹<?= number_format((float)($financials['outstanding_balance'] ?? 0)) ?>
        </div>
        <div class="text-[10px] text-slate-500 mt-0.5">Uncollected guest fees</div>
      </div>

      <!-- 5. Completed Stays Hosted -->
      <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-100 col-span-2 lg:col-span-1">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Stays Completed</div>
        <div class="font-headline font-black text-xl text-teal-600 mt-1">
          <?= (int)($financials['completed_stays'] ?? 0) ?>
        </div>
        <div class="text-[10px] text-slate-500 mt-0.5">Successful check-outs</div>
      </div>

    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       3. TAB NAVIGATION BAR
       ══════════════════════════════════════════════ -->
  <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
    
    <!-- Tab 1: Properties Portfolio -->
    <a 
      href="<?= url('admin/owners/view/' . $ownerId . '?tab=properties') ?>" 
      class="inline-flex items-center gap-2 px-5 py-3 rounded-t-2xl text-xs sm:text-sm font-bold transition-all border-b-2 whitespace-nowrap <?= $tab === 'properties' ? 'border-brand-700 text-brand-700 bg-white shadow-2xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' ?>">
      <span class="material-symbols-outlined text-lg">villa</span>
      <span>Properties Portfolio</span>
      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'properties' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($properties) ?>
      </span>
    </a>

    <!-- Tab 2: Bookings & Stays -->
    <a 
      href="<?= url('admin/owners/view/' . $ownerId . '?tab=bookings') ?>" 
      class="inline-flex items-center gap-2 px-5 py-3 rounded-t-2xl text-xs sm:text-sm font-bold transition-all border-b-2 whitespace-nowrap <?= $tab === 'bookings' ? 'border-brand-700 text-brand-700 bg-white shadow-2xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' ?>">
      <span class="material-symbols-outlined text-lg">hotel</span>
      <span>Bookings &amp; Stays</span>
      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'bookings' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($bookings) ?>
      </span>
    </a>

    <!-- Tab 3: Financial Yield & Settlements -->
    <a 
      href="<?= url('admin/owners/view/' . $ownerId . '?tab=financials') ?>" 
      class="inline-flex items-center gap-2 px-5 py-3 rounded-t-2xl text-xs sm:text-sm font-bold transition-all border-b-2 whitespace-nowrap <?= $tab === 'financials' ? 'border-brand-700 text-brand-700 bg-white shadow-2xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' ?>">
      <span class="material-symbols-outlined text-lg">payments</span>
      <span>Financial Yield</span>
    </a>

    <!-- Tab 4: Legal & KYC Documents -->
    <a 
      href="<?= url('admin/owners/view/' . $ownerId . '?tab=documents') ?>" 
      class="inline-flex items-center gap-2 px-5 py-3 rounded-t-2xl text-xs sm:text-sm font-bold transition-all border-b-2 whitespace-nowrap <?= $tab === 'documents' ? 'border-brand-700 text-brand-700 bg-white shadow-2xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' ?>">
      <span class="material-symbols-outlined text-lg">folder_shared</span>
      <span>Legal &amp; KYC Documents</span>
      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'documents' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($documents) ?>
      </span>
    </a>

    <!-- Tab 5: Ownership Transfer History -->
    <a 
      href="<?= url('admin/owners/view/' . $ownerId . '?tab=history') ?>" 
      class="inline-flex items-center gap-2 px-5 py-3 rounded-t-2xl text-xs sm:text-sm font-bold transition-all border-b-2 whitespace-nowrap <?= $tab === 'history' ? 'border-brand-700 text-brand-700 bg-white shadow-2xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' ?>">
      <span class="material-symbols-outlined text-lg">history</span>
      <span>Transfer History</span>
      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'history' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($ownershipHistory) ?>
      </span>
    </a>

    <!-- Tab 6: Guest Reviews & Reputation -->
    <a 
      href="<?= url('admin/owners/view/' . $ownerId . '?tab=reviews') ?>" 
      class="inline-flex items-center gap-2 px-5 py-3 rounded-t-2xl text-xs sm:text-sm font-bold transition-all border-b-2 whitespace-nowrap <?= $tab === 'reviews' ? 'border-brand-700 text-brand-700 bg-white shadow-2xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' ?>">
      <span class="material-symbols-outlined text-lg">hotel_class</span>
      <span>Reviews &amp; Reputation</span>
      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'reviews' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($ownerReviews) ?>
      </span>
    </a>

  </div>

  <!-- ══════════════════════════════════════════════
       4. TAB 1: PROPERTIES PORTFOLIO
       ══════════════════════════════════════════════ -->
  <?php if ($tab === 'properties'): ?>
    <div class="space-y-4">
      
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="font-headline font-bold text-base text-slate-900">Associated Luxury Properties</h2>
          <p class="text-xs text-slate-500">Estates, farmhouses, and villas legally linked to this owner</p>
        </div>

        <a 
          href="<?= url('admin/properties/create') ?>" 
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs">
          <span class="material-symbols-outlined text-[17px]">add_circle</span>
          <span>Add New Property</span>
        </a>
      </div>

      <?php if (!empty($properties)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($properties as $prop): ?>
            <?php 
              $thumb = !empty($prop['primary_image']) ? asset($prop['primary_image']) : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=600&q=80';
              $pst = strtolower((string)($prop['status'] ?? 'pending'));
              $pBadge = match($pst) {
                'active'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                default    => 'bg-amber-50 text-amber-700 border-amber-200',
              };
            ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between hover:shadow-xs transition-shadow">
              
              <!-- Card Image & Header -->
              <div>
                <div class="relative h-44 bg-slate-100 overflow-hidden">
                  <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($prop['title']) ?>" class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>
                  
                  <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/90 text-slate-800 backdrop-blur-xs">
                      <?= htmlspecialchars($prop['property_type_name'] ?? 'Luxury Villa') ?>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $pBadge ?> bg-white/95 backdrop-blur-xs capitalize">
                      <?= $pst ?>
                    </span>
                  </div>

                  <div class="absolute bottom-3 left-3 right-3 text-white">
                    <div class="text-[11px] font-medium opacity-90 flex items-center gap-1">
                      <span class="material-symbols-outlined text-[13px]">location_on</span>
                      <span><?= htmlspecialchars($prop['location'] ?? 'India') ?></span>
                    </div>
                    <div class="font-headline font-bold text-base line-clamp-1">
                      <?= htmlspecialchars($prop['title']) ?>
                    </div>
                  </div>
                </div>

                <!-- Specs Strip -->
                <div class="p-4 space-y-3">
                  <div class="grid grid-cols-3 gap-2 text-center text-[11px] bg-slate-50 p-2.5 rounded-2xl border border-slate-100">
                    <div>
                      <span class="text-slate-400 block text-[10px]">Bedrooms</span>
                      <span class="font-bold text-slate-800"><?= (int)$prop['bedrooms'] ?> Beds</span>
                    </div>
                    <div>
                      <span class="text-slate-400 block text-[10px]">Guests</span>
                      <span class="font-bold text-slate-800"><?= (int)$prop['day_capacity'] ?> Max</span>
                    </div>
                    <div>
                      <span class="text-slate-400 block text-[10px]">Nightly</span>
                      <span class="font-bold text-slate-900">₹<?= number_format((float)$prop['price']) ?></span>
                    </div>
                  </div>

                  <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                    <span class="flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm text-slate-400">book_online</span>
                      <span><?= (int)$prop['total_bookings'] ?> Bookings</span>
                    </span>
                    <?php if (!empty($prop['is_verified'])): ?>
                      <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-brand-700">
                        <span class="material-symbols-outlined text-[13px]">verified</span>
                        <span>Verified Listing</span>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Action Bar -->
              <div class="p-4 pt-0 flex items-center gap-2">
                <a 
                  href="<?= url('admin/properties/edit/' . encrypt_id($prop['id'])) ?>" 
                  class="flex-1 py-2 text-center rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                  Edit Property
                </a>

                <button 
                  type="button" 
                  onclick="openReassignModal(<?= $prop['id'] ?>, '<?= htmlspecialchars(addslashes($prop['title'])) ?>')"
                  class="px-3 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors flex items-center gap-1"
                  title="Reassign ownership to another host">
                  <span class="material-symbols-outlined text-sm">swap_horiz</span>
                  <span>Reassign</span>
                </button>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">villa</span>
          </div>
          <div class="font-headline font-bold text-base text-slate-800">No Properties Linked</div>
          <p class="text-xs text-slate-400 max-w-sm mx-auto">
            This owner does not have any luxury properties assigned yet. You can create a new property or assign an existing one.
          </p>
          <a href="<?= url('admin/properties/create') ?>" class="inline-block px-4 py-2 rounded-xl bg-brand-700 text-white text-xs font-bold">
            Create Property
          </a>
        </div>
      <?php endif; ?>

    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════
       5. TAB 2: BOOKINGS & STAYS
       ══════════════════════════════════════════════ -->
  <?php if ($tab === 'bookings'): ?>
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="font-headline font-bold text-base text-slate-900">Reservations &amp; Stays</h2>
          <p class="text-xs text-slate-500 mt-0.5">Guest bookings across all properties owned by <?= htmlspecialchars($owner['name']) ?></p>
        </div>

        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-slate-400"><?= count($bookings) ?> reservations recorded</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[750px]">
          <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
            <tr>
              <th class="py-3 px-4">Booking Ref</th>
              <th class="py-3 px-4">Property</th>
              <th class="py-3 px-4">Guest Information</th>
              <th class="py-3 px-4">Stay Dates</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Turnover</th>
              <th class="py-3 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
            <?php if (!empty($bookings)): ?>
              <?php foreach ($bookings as $b): ?>
                <?php 
                  $bst = strtolower((string)($b['status'] ?? 'pending'));
                  $bBadge = match($bst) {
                    'checked_in'  => 'bg-purple-50 text-purple-700 border-purple-200',
                    'checked_out', 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'confirmed'   => 'bg-blue-50 text-blue-700 border-blue-200',
                    'cancelled', 'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default       => 'bg-amber-50 text-amber-700 border-amber-200',
                  };
                ?>
                <tr class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                    <?= htmlspecialchars($b['booking_reference'] ?? ('#' . $b['id'])) ?>
                  </td>
                  <td class="py-3.5 px-4">
                    <div class="font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($b['property_title'] ?? 'Estate') ?></div>
                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($b['property_location'] ?? '') ?></div>
                  </td>
                    <?php if (!empty($b['user_id'])): ?>
                      <a href="<?= url('admin/guests/view/' . (int)$b['user_id']) ?>" class="font-bold text-slate-900 hover:text-brand-700 hover:underline"><?= htmlspecialchars($b['guest_name'] ?? 'Guest') ?></a>
                    <?php else: ?>
                      <div class="font-bold text-slate-900"><?= htmlspecialchars($b['guest_name'] ?? 'Guest') ?></div>
                    <?php endif; ?>
                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($b['guest_phone'] ?? $b['guest_email'] ?? '') ?></div>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <div class="font-semibold text-slate-800">
                      <?= date('d M', strtotime($b['check_in'])) ?> &rarr; <?= date('d M Y', strtotime($b['check_out'])) ?>
                    </div>
                    <div class="text-[10px] text-slate-400"><?= (int)($b['nights'] ?? 1) ?> Night(s)</div>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $bBadge ?> capitalize">
                      <?= str_replace('_', ' ', $bst) ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <div class="font-headline font-black text-slate-900">₹<?= number_format((float)($b['price'] ?? 0)) ?></div>
                    <div class="text-[10px] text-emerald-600 font-semibold">Paid: ₹<?= number_format((float)($b['amount_paid'] ?? 0)) ?></div>
                  </td>
                  <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <a href="<?= url('admin/bookings/view?id=' . encrypt_id($b['id'])) ?>" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 inline-flex items-center">
                      <span class="material-symbols-outlined text-base">visibility</span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                  No stay reservations found for properties associated with this owner.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════
       6. TAB 3: FINANCIAL YIELD & SETTLEMENTS
       ══════════════════════════════════════════════ -->
  <?php if ($tab === 'financials'): ?>
    <div class="space-y-6">
      
      <!-- Financial Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gross Booking Turnover</span>
            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
              <span class="material-symbols-outlined text-lg">receipt_long</span>
            </span>
          </div>
          <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900">
            ₹<?= number_format((float)($financials['gross_booking_value'] ?? 0)) ?>
          </div>
          <p class="text-xs text-slate-400 mt-1">Total revenue generated by owner's estates</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Collected Receipts</span>
            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
              <span class="material-symbols-outlined text-lg">payments</span>
            </span>
          </div>
          <div class="font-headline font-black text-2xl sm:text-3xl text-emerald-600">
            ₹<?= number_format((float)($financials['total_collected'] ?? 0)) ?>
          </div>
          <p class="text-xs text-slate-400 mt-1">Confirmed guest deposits &amp; advance payments</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Disbursed Settlements</span>
            <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
              <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
            </span>
          </div>
          <div class="font-headline font-black text-2xl sm:text-3xl text-purple-700">
            ₹<?= number_format((float)($financials['total_settled'] ?? 0)) ?>
          </div>
          <p class="text-xs text-slate-400 mt-1">Administrative payouts logged to owner</p>
        </div>

      </div>

      <!-- Compliance & Financial Guidance Notice -->
      <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl flex items-start gap-3 text-xs text-blue-900 leading-relaxed">
        <span class="material-symbols-outlined text-blue-600 shrink-0 text-lg">info</span>
        <div>
          <strong>Operational Financial Note:</strong> Gross booking turnover represents customer contract values. In accordance with Stayora governance, owner disbursements are recorded through authorized administrative settlements and bank transfers according to individual partnership agreements.
        </div>
      </div>

      <!-- Bank Account Details Card -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
          <span class="material-symbols-outlined text-amber-600">account_balance</span>
          <span>Settlement Coordinates on File</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl">
            <span class="text-slate-400 block text-[10px]">Bank Name</span>
            <span class="font-bold text-slate-800"><?= htmlspecialchars($owner['bank_name'] ?? 'Not Provided') ?></span>
          </div>
          <div class="p-3 bg-slate-50 rounded-xl">
            <span class="text-slate-400 block text-[10px]">Account Holder</span>
            <span class="font-bold text-slate-800"><?= htmlspecialchars($owner['bank_account_holder'] ?? 'Not Provided') ?></span>
          </div>
          <div class="p-3 bg-slate-50 rounded-xl">
            <span class="text-slate-400 block text-[10px]">Account Number</span>
            <span class="font-bold text-slate-800 font-mono"><?= htmlspecialchars($owner['bank_account_number'] ?? 'Not Provided') ?></span>
          </div>
          <div class="p-3 bg-slate-50 rounded-xl">
            <span class="text-slate-400 block text-[10px]">IFSC Code</span>
            <span class="font-bold text-slate-800 font-mono"><?= htmlspecialchars($owner['bank_ifsc'] ?? 'Not Provided') ?></span>
          </div>
        </div>
      </div>

      <!-- Historical Settlement Logs -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-headline font-bold text-base text-slate-900">Administrative Settlement Logs</h3>
          <span class="text-xs text-slate-400 font-bold"><?= count($financials['payouts_list'] ?? []) ?> records</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[650px]">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
              <tr>
                <th class="py-3 px-4">Payout Ref</th>
                <th class="py-3 px-4">Gross Amount</th>
                <th class="py-3 px-4">Commission</th>
                <th class="py-3 px-4">Net Payout</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Reference / UTR</th>
                <th class="py-3 px-4 text-right">Processed At</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
              <?php if (!empty($financials['payouts_list'])): ?>
                <?php foreach ($financials['payouts_list'] as $po): ?>
                  <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="py-3 px-4 font-bold text-slate-900">#PO-<?= $po['id'] ?></td>
                    <td class="py-3 px-4">₹<?= number_format((float)$po['gross_amount']) ?></td>
                    <td class="py-3 px-4 text-slate-500">₹<?= number_format((float)$po['platform_commission']) ?></td>
                    <td class="py-3 px-4 font-headline font-bold text-emerald-700">₹<?= number_format((float)$po['net_payout']) ?></td>
                    <td class="py-3 px-4">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $po['payout_status'] === 'processed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>">
                        <?= ucfirst($po['payout_status']) ?>
                      </span>
                    </td>
                    <td class="py-3 px-4 font-mono text-slate-600"><?= htmlspecialchars($po['payment_reference'] ?? 'Pending') ?></td>
                    <td class="py-3 px-4 text-right text-slate-400"><?= $po['processed_at'] ? date('d M Y', strtotime($po['processed_at'])) : '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-400">
                    No payout settlements have been logged for this owner yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════
       7. TAB 4: LEGAL & KYC DOCUMENTS
       ══════════════════════════════════════════════ -->
  <?php if ($tab === 'documents'): ?>
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="font-headline font-bold text-base text-slate-900">Legal, KYC &amp; Title Documents</h2>
          <p class="text-xs text-slate-500 mt-0.5">Verified title deeds, identification proofs, and signed agreements</p>
        </div>

        <button 
          type="button" 
          onclick="openUploadDocModal()"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs shrink-0">
          <span class="material-symbols-outlined text-[17px]">upload_file</span>
          <span>Upload Document</span>
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[750px]">
          <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
            <tr>
              <th class="py-3 px-4">Document</th>
              <th class="py-3 px-4">Type</th>
              <th class="py-3 px-4">Related Property</th>
              <th class="py-3 px-4">Uploaded</th>
              <th class="py-3 px-4">Verification</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
            <?php if (!empty($documents)): ?>
              <?php foreach ($documents as $doc): ?>
                <?php 
                  $dst = strtolower((string)($doc['status'] ?? 'pending'));
                  $dBadge = match($dst) {
                    'verified' => 'bg-teal-50 text-teal-700 border-teal-200',
                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default    => 'bg-amber-50 text-amber-700 border-amber-200',
                  };
                  $fileUrl = asset(ltrim($doc['file_path'], '/'));
                ?>
                <tr class="hover:bg-slate-50/70 transition-colors">
                  
                  <!-- Document Name -->
                  <td class="py-3.5 px-4">
                    <div class="font-bold text-slate-900"><?= htmlspecialchars($doc['document_name'] ?? 'Owner Document') ?></div>
                    <?php if (!empty($doc['document_number'])): ?>
                      <div class="text-[10px] text-slate-400 font-mono">Ref: <?= htmlspecialchars($doc['document_number']) ?></div>
                    <?php endif; ?>
                  </td>

                  <!-- Document Type -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wider">
                      <?= str_replace('_', ' ', $doc['document_type']) ?>
                    </span>
                  </td>

                  <!-- Related Property -->
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-700">
                    <?= htmlspecialchars($doc['property_title'] ?? 'General Entity Document') ?>
                  </td>

                  <!-- Uploaded Date & Size -->
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                    <div><?= date('d M Y', strtotime($doc['created_at'])) ?></div>
                    <?php if (!empty($doc['file_size'])): ?>
                      <div class="text-[10px] text-slate-400"><?= round($doc['file_size'] / 1024, 1) ?> KB</div>
                    <?php endif; ?>
                  </td>

                  <!-- Verification Status -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $dBadge ?> capitalize">
                      <?= $dst ?>
                    </span>
                    <?php if ($dst === 'rejected' && !empty($doc['rejection_reason'])): ?>
                      <div class="text-[10px] text-rose-600 mt-0.5 italic max-w-xs truncate" title="<?= htmlspecialchars($doc['rejection_reason']) ?>">
                        <?= htmlspecialchars($doc['rejection_reason']) ?>
                      </div>
                    <?php endif; ?>
                  </td>

                  <!-- Actions -->
                  <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                      
                      <!-- View / Download -->
                      <a 
                        href="<?= $fileUrl ?>" 
                        target="_blank" 
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" 
                        title="Download / View File">
                        <span class="material-symbols-outlined text-base">download</span>
                      </a>

                      <!-- Verify -->
                      <?php if ($dst !== 'verified'): ?>
                        <button 
                          type="button" 
                          onclick="verifyDocAction(<?= $doc['id'] ?>, 'verified')"
                          class="p-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 transition-colors" 
                          title="Mark Document Verified">
                          <span class="material-symbols-outlined text-base">check</span>
                        </button>
                      <?php endif; ?>

                      <!-- Reject -->
                      <?php if ($dst !== 'rejected'): ?>
                        <button 
                          type="button" 
                          onclick="rejectDocAction(<?= $doc['id'] ?>)"
                          class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" 
                          title="Reject Document">
                          <span class="material-symbols-outlined text-base">close</span>
                        </button>
                      <?php endif; ?>

                      <!-- Delete -->
                      <button 
                        type="button" 
                        onclick="deleteDocAction(<?= $doc['id'] ?>)"
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors" 
                        title="Delete Document">
                        <span class="material-symbols-outlined text-base">delete</span>
                      </button>

                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="py-12 text-center text-slate-400">
                  No legal or verification documents uploaded yet. Click <strong>Upload Document</strong> above to add title deeds, IDs, or contracts.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════
       8. TAB 5: OWNERSHIP TRANSFER HISTORY
       ══════════════════════════════════════════════ -->
  <?php if ($tab === 'history'): ?>
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 sm:p-6 border-b border-slate-100">
        <h2 class="font-headline font-bold text-base text-slate-900">Ownership Transfer &amp; Reassignment History</h2>
        <p class="text-xs text-slate-500 mt-0.5">Audit log of all properties registered, assigned, or transferred to/from <?= htmlspecialchars($owner['name']) ?></p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[700px]">
          <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
            <tr>
              <th class="py-3 px-4">Timestamp</th>
              <th class="py-3 px-4">Property</th>
              <th class="py-3 px-4">Transfer Action</th>
              <th class="py-3 px-4">Previous Owner</th>
              <th class="py-3 px-4">New Owner</th>
              <th class="py-3 px-4">Authorized By</th>
              <th class="py-3 px-4">Reason / Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
            <?php if (!empty($ownershipHistory)): ?>
              <?php foreach ($ownershipHistory as $h): ?>
                <?php 
                  $isNewOwner = ((int)$h['new_owner_id'] === $ownerId);
                ?>
                <tr class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                    <?= date('d M Y, h:i A', strtotime($h['created_at'])) ?>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">
                    <div><?= htmlspecialchars($h['property_title'] ?? ('Estate #' . $h['farmhouse_id'])) ?></div>
                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($h['property_location'] ?? '') ?></div>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <?php if (empty($h['previous_owner_id'])): ?>
                      <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">Initial Registration</span>
                    <?php elseif ($isNewOwner): ?>
                      <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Acquired Ownership</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700">Transferred Out</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-700">
                    <?= htmlspecialchars($h['prev_owner_name'] ?? 'Stayora / None') ?>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-900">
                    <?= htmlspecialchars($h['new_owner_name'] ?? '') ?>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                    <?= htmlspecialchars($h['transferred_by_admin'] ?? 'Administrator') ?>
                  </td>
                  <td class="py-3.5 px-4 max-w-xs text-slate-600">
                    <div class="font-semibold text-slate-800"><?= htmlspecialchars($h['transfer_reason'] ?? 'Ownership change') ?></div>
                    <?php if (!empty($h['notes'])): ?>
                      <div class="text-[10px] text-slate-400 mt-0.5"><?= htmlspecialchars($h['notes']) ?></div>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                  No ownership transfer records found for this partner.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════
       9. TAB 6: GUEST REVIEWS & REPUTATION
       ══════════════════════════════════════════════ -->
  <?php if ($tab === 'reviews'): ?>
    <div class="space-y-6">
      
      <!-- Portfolio Review KPIs -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Portfolio Average</span>
          <div class="flex items-center gap-1.5 mt-1 font-headline font-black text-xl text-slate-900">
            <span><?= number_format((float)($ownerReviewStats['average_rating'] ?? 0), 1) ?></span>
            <span class="text-amber-500 text-base">★</span>
          </div>
          <span class="text-[10px] text-slate-400">Scale 1-5</span>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Reviews</span>
          <div class="font-headline font-black text-xl text-slate-900 mt-1">
            <?= number_format($ownerReviewStats['total_reviews'] ?? 0) ?>
          </div>
          <span class="text-[10px] text-teal-700 font-semibold"><?= number_format($ownerReviewStats['verified_stays'] ?? 0) ?> verified stays</span>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Published Live</span>
          <div class="font-headline font-black text-xl text-emerald-700 mt-1">
            <?= number_format($ownerReviewStats['approved_reviews'] ?? 0) ?>
          </div>
          <span class="text-[10px] text-slate-400">Active social proof</span>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Host Responses</span>
          <div class="font-headline font-black text-xl text-blue-700 mt-1">
            <?= number_format($ownerReviewStats['with_responses'] ?? 0) ?>
          </div>
          <span class="text-[10px] text-slate-400">Official replies</span>
        </div>

      </div>

      <!-- Reviews Listing -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="font-headline font-bold text-sm text-slate-900">Guest Feedback &amp; Ratings</h3>
            <p class="text-[11px] text-slate-400">All guest reviews received across this partner's estate properties</p>
          </div>
          <a href="<?= url('admin/reviews?owner_id=' . $ownerId) ?>" class="text-xs font-bold text-brand-700 hover:underline inline-flex items-center gap-1">
            <span>Open in Moderation Console</span>
            <span class="material-symbols-outlined text-[14px]">open_in_new</span>
          </a>
        </div>

        <?php if (empty($ownerReviews)): ?>
          <div class="p-10 text-center space-y-2">
            <span class="material-symbols-outlined text-3xl text-slate-300">rate_review</span>
            <p class="text-xs text-slate-400 italic">No guest reviews recorded across this owner's properties yet.</p>
          </div>
        <?php else: ?>
          <div class="divide-y divide-slate-100">
            <?php foreach ($ownerReviews as $rev): 
              $st = strtolower($rev['status'] ?? 'approved');
              $stClass = match($st) {
                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                'hidden'   => 'bg-slate-100 text-slate-600 border-slate-200',
                default    => 'bg-purple-50 text-purple-700 border-purple-200'
              };
            ?>
              <div class="p-5 space-y-2 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-slate-900"><?= htmlspecialchars($rev['property_title'] ?? 'Estate') ?></span>
                    <span class="text-slate-300">•</span>
                    <span class="text-xs text-slate-600"><?= htmlspecialchars($rev['guest_name']) ?></span>
                    <?php if (!empty($rev['is_verified_stay'])): ?>
                      <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-teal-50 text-teal-700">Verified Stay</span>
                    <?php endif; ?>
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize <?= $stClass ?>">
                      <?= $st ?>
                    </span>
                    <div class="flex items-center gap-0.5 text-amber-500 font-bold text-xs">
                      <span class="material-symbols-outlined text-[15px]">star</span>
                      <span><?= number_format((float)$rev['overall_rating'], 1) ?></span>
                    </div>
                  </div>
                </div>

                <?php if (!empty($rev['review_title'])): ?>
                  <div class="font-bold text-xs text-slate-800">"<?= htmlspecialchars($rev['review_title']) ?>"</div>
                <?php endif; ?>
                <p class="text-xs text-slate-600 leading-relaxed"><?= htmlspecialchars($rev['review_text']) ?></p>

                <div class="pt-1 flex items-center justify-between text-[10px] text-slate-400">
                  <span>Submitted on <?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                  <a href="<?= url('admin/reviews?search=' . urlencode($rev['guest_name'])) ?>" class="text-brand-700 font-bold hover:underline">
                    Manage Review in Console &rarr;
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  <?php endif; ?>

</div>

<!-- ══════════════════════════════════════════════
     MODAL 1: UPLOAD DOCUMENT MODAL
     ══════════════════════════════════════════════ -->
<div id="upload-doc-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">upload_file</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Upload Owner Document</h3>
          <p class="text-[11px] text-slate-400">Attach verification proofs or title deeds</p>
        </div>
      </div>
      <button type="button" onclick="closeUploadDocModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <form id="upload-doc-form" onsubmit="submitDocUpload(event)" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="owner_id" value="<?= $ownerId ?>">

      <!-- Document Type -->
      <div>
        <label for="doc-type-select" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Document Type <span class="text-rose-500">*</span>
        </label>
        <select 
          name="document_type" 
          id="doc-type-select" 
          required 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="pan">PAN Card</option>
          <option value="aadhaar">Aadhaar Card</option>
          <option value="gst_certificate">GST Certificate / Corporate CIN</option>
          <option value="property_registry">Property Registry / Title Deed</option>
          <option value="electricity_bill">Electricity / Utility Bill</option>
          <option value="agreement">Host Partnership Agreement</option>
          <option value="bank_passbook">Bank Passbook / Cancelled Cheque</option>
          <option value="noc">NOC (No Objection Certificate)</option>
          <option value="other" selected>Other Supporting Document</option>
        </select>
      </div>

      <!-- Document Name -->
      <div>
        <label for="doc-name-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Document Title / Name <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="document_name" 
          id="doc-name-input" 
          required 
          placeholder="e.g., North Goa Villa Title Registry 2024" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Document Number (Optional) -->
      <div>
        <label for="doc-number-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Document / Registration Ref Number
        </label>
        <input 
          type="text" 
          name="document_number" 
          id="doc-number-input" 
          placeholder="e.g., DEED-8492-2024" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Linked Property (Optional) -->
      <div>
        <label for="doc-prop-select" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Associated Property <span class="text-slate-400 font-normal">(Optional)</span>
        </label>
        <select 
          name="farmhouse_id" 
          id="doc-prop-select" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="">General Entity Document (Applies to all properties)</option>
          <?php foreach ($properties as $p): ?>
            <option value="<?= $p['id'] ?>">Property #<?= $p['id'] ?>: <?= htmlspecialchars($p['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- File Input -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Select Document File <span class="text-rose-500">*</span>
        </label>
        <input 
          type="file" 
          name="document_file" 
          id="doc-file-input" 
          required 
          accept="application/pdf, image/jpeg, image/png, image/webp" 
          class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
        />
        <p class="text-[10px] text-slate-400 mt-1">Accepts PDF, JPG, PNG, WEBP up to 10 MB.</p>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeUploadDocModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
          Cancel
        </button>
        <button type="submit" id="doc-upload-btn" class="px-5 py-2.5 rounded-xl bg-brand-700 text-white text-xs font-bold hover:bg-brand-800 transition-colors shadow-xs">
          Upload &amp; Attach
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL 2: REASSIGN PROPERTY OWNERSHIP MODAL
     ══════════════════════════════════════════════ -->
<div id="reassign-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">swap_horiz</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Reassign Property Ownership</h3>
          <p class="text-[11px] text-slate-400">Atomically transfer estate to another active owner</p>
        </div>
      </div>
      <button type="button" onclick="closeReassignModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <form id="reassign-form" onsubmit="submitReassignForm(event)" class="space-y-4">
      <input type="hidden" name="farmhouse_id" id="reassign-farmhouse-id" value="">

      <!-- Current Property -->
      <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700">
        <span class="text-slate-400 text-[10px] block">Selected Property</span>
        <strong id="reassign-property-title" class="text-slate-900 block font-headline">...</strong>
      </div>

      <!-- Target Owner Select -->
      <div>
        <label for="reassign-target-owner" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Transfer To New Owner <span class="text-rose-500">*</span>
        </label>
        <select 
          name="new_owner_id" 
          id="reassign-target-owner" 
          required 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3.5 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="">Select Target Owner...</option>
          <?php foreach ($activeOwners as $ao): ?>
            <?php if ((int)$ao['id'] !== $ownerId): ?>
              <option value="<?= $ao['id'] ?>">
                <?= htmlspecialchars($ao['name']) ?><?= !empty($ao['business_name']) ? ' (' . htmlspecialchars($ao['business_name']) . ')' : '' ?><?= !empty($ao['city']) ? ' • ' . htmlspecialchars($ao['city']) : '' ?>
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Mandatory Transfer Reason -->
      <div>
        <label for="reassign-reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Transfer Reason <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="transfer_reason" 
          id="reassign-reason" 
          required 
          placeholder="e.g., Title deed sold, host partnership migration" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Transfer Notes -->
      <div>
        <label for="reassign-notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Administrative Audit Notes
        </label>
        <textarea 
          name="notes" 
          id="reassign-notes" 
          rows="2" 
          placeholder="Optional supporting notes or legal agreement reference..." 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"></textarea>
      </div>

      <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/70 text-[11px] text-amber-800 leading-relaxed">
        <strong>Audit Trail Notice:</strong> This operation writes an irreversible entry into the property ownership transfer audit ledger. Existing reservations remain preserved.
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeReassignModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
          Cancel
        </button>
        <button type="submit" id="reassign-submit-btn" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-colors shadow-xs">
          Confirm Reassignment
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL 3: STATUS CHANGE MODAL
     ══════════════════════════════════════════════ -->
<div id="status-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">tune</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Update Host Status</h3>
          <p class="text-[11px] text-slate-400"><?= htmlspecialchars($owner['name']) ?></p>
        </div>
      </div>
      <button type="button" onclick="closeStatusModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <form id="status-update-form" onsubmit="submitStatusUpdate(event)" class="space-y-4">
      <input type="hidden" name="id" value="<?= $ownerId ?>">

      <div>
        <label for="status-modal-select" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Operational Status <span class="text-rose-500">*</span>
        </label>
        <select 
          name="status" 
          id="status-modal-select" 
          required 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3.5 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="active" <?= $st === 'active' ? 'selected' : '' ?>>Active (Fully operational &amp; assignable)</option>
          <option value="inactive" <?= $st === 'inactive' ? 'selected' : '' ?>>Inactive (Prevent new property assignments)</option>
          <option value="pending" <?= $st === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
          <option value="suspended" <?= $st === 'suspended' ? 'selected' : '' ?>>Suspended (Temporarily restricted)</option>
        </select>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeStatusModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
          Cancel
        </button>
        <button type="submit" id="status-submit-btn" class="px-5 py-2.5 rounded-xl bg-brand-700 text-white text-xs font-bold hover:bg-brand-800 transition-colors shadow-xs">
          Save Status
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     DOSSIER JAVASCRIPT LOGIC
     ══════════════════════════════════════════════ -->
<script>
  // ── Status Modal Logic ──
  function openStatusModal() {
    document.getElementById('status-modal').classList.remove('hidden');
  }

  function closeStatusModal() {
    document.getElementById('status-modal').classList.add('hidden');
  }

  function submitStatusUpdate(e) {
    e.preventDefault();
    const btn = document.getElementById('status-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Updating...';

    const form = document.getElementById('status-update-form');
    const formData = new FormData(form);

    fetch('<?= url('admin/owners/update-status') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (window.adminToast) adminToast.success(data.message || 'Status updated.');
        setTimeout(() => location.reload(), 500);
      } else {
        btn.disabled = false;
        btn.textContent = 'Save Status';
        if (window.adminToast) adminToast.error(data.message || 'Failed.');
        else alert(data.message || 'Failed.');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Save Status';
      if (window.adminToast) adminToast.error('Network error.');
    });
  }

  // ── Document Upload Modal ──
  function openUploadDocModal() {
    document.getElementById('upload-doc-modal').classList.remove('hidden');
  }

  function closeUploadDocModal() {
    document.getElementById('upload-doc-modal').classList.add('hidden');
  }

  function submitDocUpload(e) {
    e.preventDefault();
    const btn = document.getElementById('doc-upload-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Uploading...';

    const form = document.getElementById('upload-doc-form');
    const formData = new FormData(form);

    fetch('<?= url('admin/owners/upload-document?format=json') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeUploadDocModal();
        if (window.adminToast) adminToast.success(data.message || 'Document uploaded.');
        setTimeout(() => {
          window.location.href = '<?= url('admin/owners/view/' . $ownerId . '?tab=documents') ?>';
        }, 500);
      } else {
        btn.disabled = false;
        btn.textContent = 'Upload & Attach';
        if (window.adminToast) adminToast.error(data.message || 'Upload failed.');
        else alert(data.message || 'Upload failed.');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Upload & Attach';
      if (window.adminToast) adminToast.error('Network error during upload.');
    });
  }

  // ── Verify / Reject Document Actions ──
  function verifyDocAction(docId, status) {
    const formData = new FormData();
    formData.append('document_id', docId);
    formData.append('status', status);

    fetch('<?= url('admin/owners/verify-document') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (window.adminToast) adminToast.success(data.message || 'Verified.');
        setTimeout(() => location.reload(), 500);
      } else {
        if (window.adminToast) adminToast.error(data.message || 'Failed.');
      }
    });
  }

  function rejectDocAction(docId) {
    const reason = prompt('Please enter the reason for rejecting this document:');
    if (reason === null) return;

    const formData = new FormData();
    formData.append('document_id', docId);
    formData.append('status', 'rejected');
    formData.append('rejection_reason', reason);

    fetch('<?= url('admin/owners/verify-document') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (window.adminToast) adminToast.success(data.message || 'Rejected.');
        setTimeout(() => location.reload(), 500);
      } else {
        if (window.adminToast) adminToast.error(data.message || 'Failed.');
      }
    });
  }

  function deleteDocAction(docId) {
    if (!confirm('Are you sure you want to permanently delete this document?')) return;

    const formData = new FormData();
    formData.append('document_id', docId);

    fetch('<?= url('admin/owners/delete-document') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (window.adminToast) adminToast.success(data.message || 'Deleted.');
        setTimeout(() => location.reload(), 500);
      } else {
        if (window.adminToast) adminToast.error(data.message || 'Failed.');
      }
    });
  }

  // ── Property Reassignment Modal ──
  function openReassignModal(propId, propTitle) {
    document.getElementById('reassign-farmhouse-id').value = propId;
    document.getElementById('reassign-property-title').textContent = propTitle;
    document.getElementById('reassign-modal').classList.remove('hidden');
  }

  function closeReassignModal() {
    document.getElementById('reassign-modal').classList.add('hidden');
  }

  function submitReassignForm(e) {
    e.preventDefault();
    const btn = document.getElementById('reassign-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Reassigning...';

    const form = document.getElementById('reassign-form');
    const formData = new FormData(form);

    fetch('<?= url('admin/owners/reassign-property') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeReassignModal();
        if (window.adminToast) adminToast.success(data.message || 'Property reassigned.');
        setTimeout(() => {
          window.location.href = '<?= url('admin/owners/view/' . $ownerId . '?tab=history') ?>';
        }, 500);
      } else {
        btn.disabled = false;
        btn.textContent = 'Confirm Reassignment';
        if (window.adminToast) adminToast.error(data.message || 'Reassignment failed.');
        else alert(data.message || 'Reassignment failed.');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Confirm Reassignment';
      if (window.adminToast) adminToast.error('Network error.');
    });
  }
</script>

<!-- ── Shared Admin Layout Footer ── -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
