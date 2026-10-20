<?php
$guest             = $guest ?? [];
$guestId           = (int)($guest['id'] ?? 0);
$activeTab         = $activeTab ?? 'overview';
$bookings          = $bookings ?? [];
$stays             = $stays ?? [];
$payments          = $payments ?? [];
$documents         = $documents ?? [];
$reviews           = $reviews ?? [];
$inquiries         = $inquiries ?? [];
$activityLogs      = $activityLogs ?? [];
$membershipHistory = $membershipHistory ?? [];

$pageTitle   = htmlspecialchars($guest['name'] ?? 'Guest') . " • Guest Dossier";
$currentPage = "guests";
$headerTitle = "Guest Dossier: " . htmlspecialchars($guest['name'] ?? '');

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

function resolveShowAvatar(?string $img, ?string $avatarUrl = null): string {
    if (!empty($avatarUrl)) return $avatarUrl;
    if (empty($img) || $img === 'default_profile.png') return '';
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) return $img;
    return asset('assets/images/uploads/profiles/' . ltrim($img, '/'));
}
$avatarUrl = resolveShowAvatar($guest['profile_image'] ?? null, $guest['avatar_url'] ?? null);

$st = strtolower((string)($guest['status'] ?? 'active'));
$statusBadge = ($st === 'active') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200';

$tier = strtolower((string)($guest['membership_tier'] ?? 'none'));
$tierBadge = match($tier) {
    'vip'      => ['text' => 'VIP Concierge', 'class' => 'bg-slate-900 text-amber-300 border-slate-700', 'icon' => 'diamond'],
    'platinum' => ['text' => 'Platinum Member', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'icon' => 'military_tech'],
    'gold'     => ['text' => 'Gold Member', 'class' => 'bg-amber-50 text-amber-800 border-amber-200', 'icon' => 'star'],
    'silver'   => ['text' => 'Silver Member', 'class' => 'bg-slate-100 text-slate-700 border-slate-300', 'icon' => 'shield'],
    default    => ['text' => 'Standard Guest', 'class' => 'bg-slate-50 text-slate-600 border-slate-200', 'icon' => 'person']
};

$kyc = strtolower((string)($guest['kyc_status'] ?? 'unverified'));
$kycBadge = match($kyc) {
    'verified' => ['text' => 'KYC Verified', 'class' => 'text-emerald-700 bg-emerald-50 border-emerald-200', 'icon' => 'verified_user'],
    'pending'  => ['text' => 'Pending Review', 'class' => 'text-amber-700 bg-amber-50 border-amber-200', 'icon' => 'hourglass_empty'],
    'rejected' => ['text' => 'KYC Rejected', 'class' => 'text-rose-700 bg-rose-50 border-rose-200', 'icon' => 'cancel'],
    default    => ['text' => 'Unverified', 'class' => 'text-slate-500 bg-slate-100 border-slate-200', 'icon' => 'help_outline']
};

// Compute aggregates
$totalSpendPaid = 0.00;
foreach ($payments as $p) {
    if (($p['payment_status'] ?? '') === 'paid') {
        $totalSpendPaid += (float)($p['amount'] ?? 0);
    }
}
?>

<div class="space-y-6 pb-12">

  <!-- ══════════════════════════════════════════════
       1. TOP HERO DOSSIER HEADER
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      
      <!-- Left: Identity & Badges -->
      <div class="flex items-start gap-4 sm:gap-5">
        
        <!-- Large Avatar -->
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-brand-50 border-2 border-brand-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-700 font-headline font-black text-2xl shadow-sm">
          <?php if (!empty($avatarUrl)): ?>
            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($guest['name']) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <span><?= strtoupper(substr($guest['name'], 0, 2)) ?></span>
          <?php endif; ?>
        </div>

        <div class="min-w-0">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
            <a href="<?= url('admin/guests') ?>" class="hover:text-brand-700 transition-colors">Guests &amp; Members</a>
            <span>/</span>
            <span class="text-slate-800">Dossier: <?= htmlspecialchars($guest['guest_code'] ?: 'ID#' . $guestId) ?></span>
          </div>

          <h1 class="font-headline font-black text-xl sm:text-2xl lg:text-3xl text-slate-900 tracking-tight flex items-center gap-2 flex-wrap">
            <span><?= htmlspecialchars($guest['name']) ?></span>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $statusBadge ?> capitalize">
              <?= $st ?>
            </span>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $kycBadge['class'] ?> flex items-center gap-1">
              <span class="material-symbols-outlined text-[13px]"><?= $kycBadge['icon'] ?></span>
              <span><?= $kycBadge['text'] ?></span>
            </span>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $tierBadge['class'] ?> flex items-center gap-1">
              <span class="material-symbols-outlined text-[13px]"><?= $tierBadge['icon'] ?></span>
              <span><?= $tierBadge['text'] ?></span>
            </span>
          </h1>

          <!-- Coordinates Strip -->
          <div class="flex items-center gap-4 text-xs text-slate-500 mt-2.5 flex-wrap">
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">call</span>
              <a href="tel:<?= htmlspecialchars($guest['phone']) ?>" class="hover:text-brand-700 font-semibold text-slate-700">
                <?= htmlspecialchars($guest['phone']) ?>
              </a>
            </div>
            <?php if (!empty($guest['email'])): ?>
              <div class="flex items-center gap-1">
                <span class="material-symbols-outlined text-sm text-slate-400">mail</span>
                <a href="mailto:<?= htmlspecialchars($guest['email']) ?>" class="hover:text-brand-700 font-semibold text-slate-700">
                  <?= htmlspecialchars($guest['email']) ?>
                </a>
              </div>
            <?php endif; ?>
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">location_on</span>
              <span class="text-slate-700 font-medium"><?= htmlspecialchars($guest['city'] ?: 'India') ?><?= !empty($guest['state']) ? ', ' . htmlspecialchars($guest['state']) : '' ?></span>
            </div>
            <div class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm text-slate-400">calendar_today</span>
              <span class="text-slate-400">Member since <?= date('M d, Y', strtotime($guest['created_at'])) ?></span>
            </div>
          </div>

        </div>
      </div>

      <!-- Right: Action Buttons -->
      <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
        
        <!-- Status Action -->
        <button 
          type="button" 
          onclick="openStatusModal(<?= $guestId ?>, '<?= htmlspecialchars(addslashes($guest['name'])) ?>', '<?= $st ?>')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
          <span class="material-symbols-outlined text-[17px] text-slate-500">tune</span>
          <span>Status</span>
        </button>

        <!-- Membership Upgrade/Renewal Action -->
        <button 
          type="button" 
          onclick="openMembershipModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-all shadow-2xs">
          <span class="material-symbols-outlined text-[17px]">workspace_premium</span>
          <span>Membership</span>
        </button>

        <!-- Upload Document -->
        <button 
          type="button" 
          onclick="openDocUploadModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
          <span class="material-symbols-outlined text-[17px] text-slate-500">upload_file</span>
          <span>Upload KYC</span>
        </button>

        <!-- Edit Profile -->
        <a 
          href="<?= url('admin/guests/edit/' . $guestId) ?>" 
          class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs">
          <span class="material-symbols-outlined text-[17px]">edit</span>
          <span>Edit Profile</span>
        </a>

        <!-- Safe Delete / Block Button -->
        <button 
          type="button" 
          onclick="openDeleteModal()"
          class="p-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition-all"
          title="Delete Account">
          <span class="material-symbols-outlined text-[17px]">delete</span>
        </button>

      </div>

    </div>

    <!-- ══════════════════════════════════════════════
         MINI STATS STRIP
         ══════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-6 pt-6 border-t border-slate-100">
      
      <div class="p-3 bg-slate-50 rounded-2xl">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Bookings</span>
        <span class="font-headline font-black text-lg text-slate-900"><?= count($bookings) ?></span>
      </div>

      <div class="p-3 bg-slate-50 rounded-2xl">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Completed Stays</span>
        <span class="font-headline font-black text-lg text-slate-900"><?= count($stays) ?></span>
      </div>

      <div class="p-3 bg-slate-50 rounded-2xl">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Verified Spend (INR)</span>
        <span class="font-headline font-black text-lg text-emerald-700">₹<?= number_format($totalSpendPaid) ?></span>
      </div>

      <div class="p-3 bg-slate-50 rounded-2xl">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Membership Expiry</span>
        <span class="font-headline font-bold text-sm text-slate-800">
          <?= !empty($guest['membership_expiry_date']) ? date('M d, Y', strtotime($guest['membership_expiry_date'])) : 'No Expiry' ?>
        </span>
      </div>

      <div class="p-3 bg-slate-50 rounded-2xl">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Last Stay Visit</span>
        <span class="font-headline font-bold text-sm text-slate-800">
          <?= !empty($guest['last_visit_at']) ? date('M d, Y', strtotime($guest['last_visit_at'])) : 'No visits yet' ?>
        </span>
      </div>

    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       2. TAB NAVIGATION
       ══════════════════════════════════════════════ -->
  <div class="flex items-center gap-1.5 border-b border-slate-200 overflow-x-auto pb-px">
    
    <!-- Tab 1: Overview & Preferences -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=overview") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'overview' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">account_circle</span>
      <span>Overview &amp; Preferences</span>
    </a>

    <!-- Tab 2: Bookings Portfolio -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=bookings") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'bookings' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">calendar_month</span>
      <span>Bookings Portfolio</span>
      <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $activeTab === 'bookings' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($bookings) ?>
      </span>
    </a>

    <!-- Tab 3: Stay & Check-in History -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=stays") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'stays' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">hotel</span>
      <span>Stay &amp; Check-in Logs</span>
      <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $activeTab === 'stays' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($stays) ?>
      </span>
    </a>

    <!-- Tab 4: Payments & Ledger -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=payments") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'payments' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">payments</span>
      <span>Financial Ledger</span>
      <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $activeTab === 'payments' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($payments) ?>
      </span>
    </a>

    <!-- Tab 5: KYC & Documents -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=documents") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'documents' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">id_card</span>
      <span>Legal &amp; KYC Docs</span>
      <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $activeTab === 'documents' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($documents) ?>
      </span>
    </a>

    <!-- Tab 6: Feedback & Inquiries -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=reviews") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'reviews' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">hotel_class</span>
      <span>Feedback &amp; Reviews</span>
      <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $activeTab === 'reviews' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600' ?>">
        <?= count($reviews) + count($inquiries) ?>
      </span>
    </a>

    <!-- Tab 7: Activity Timeline -->
    <a href="<?= url("admin/guests/view/{$guestId}?tab=activity") ?>" 
       class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap <?= $activeTab === 'activity' ? 'border-brand-700 text-brand-700 bg-brand-50/20' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
      <span class="material-symbols-outlined text-[17px]">history</span>
      <span>Activity &amp; Timeline</span>
    </a>

  </div>

  <!-- ══════════════════════════════════════════════
       3. TAB CONTENT CONTAINERS
       ══════════════════════════════════════════════ -->

  <?php if ($activeTab === 'overview'): ?>
    <!-- ── TAB 1: OVERVIEW & PREFERENCES ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Left 2 Cols: Details & Notes -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Personal Coordinates -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-brand-700">contacts</span>
              <span>Contact Coordinates</span>
            </h3>
            <a href="<?= url('admin/guests/edit/' . $guestId) ?>" class="text-xs font-bold text-brand-700 hover:underline">Edit</a>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <span class="text-slate-400 block text-[11px]">Primary Phone</span>
              <span class="font-bold text-slate-800"><?= htmlspecialchars($guest['phone']) ?></span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Alternate Phone</span>
              <span class="font-semibold text-slate-800"><?= htmlspecialchars($guest['alternate_phone'] ?: 'None provided') ?></span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Email Address</span>
              <span class="font-semibold text-slate-800"><?= htmlspecialchars($guest['email'] ?: 'None provided') ?></span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Date of Birth / Age</span>
              <span class="font-semibold text-slate-800">
                <?= !empty($guest['date_of_birth']) ? date('M d, Y', strtotime($guest['date_of_birth'])) : 'Not specified' ?>
              </span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Government ID</span>
              <span class="font-semibold text-slate-800 uppercase">
                <?= htmlspecialchars(str_replace('_', ' ', $guest['gov_id_type'] ?? 'N/A')) ?>: <?= htmlspecialchars($guest['gov_id_number'] ?: 'Pending') ?>
              </span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Membership Number</span>
              <span class="font-mono font-bold text-slate-900"><?= htmlspecialchars($guest['membership_number'] ?: 'None') ?></span>
            </div>
          </div>
        </div>

        <!-- Residential Address -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-brand-700">home_pin</span>
              <span>Residential Address</span>
            </h3>
          </div>

          <div class="text-xs text-slate-700 space-y-1">
            <p class="font-semibold text-slate-900"><?= htmlspecialchars($guest['address_line1'] ?: 'No street address recorded') ?></p>
            <?php if (!empty($guest['address_line2'])): ?>
              <p><?= htmlspecialchars($guest['address_line2']) ?></p>
            <?php endif; ?>
            <p>
              <?= htmlspecialchars($guest['city'] ?: '') ?><?= !empty($guest['state']) ? ', ' . htmlspecialchars($guest['state']) : '' ?> 
              <?= !empty($guest['pincode']) ? ' - ' . htmlspecialchars($guest['pincode']) : '' ?>
            </p>
            <p class="font-medium text-slate-500"><?= htmlspecialchars($guest['country'] ?: 'India') ?></p>
          </div>
        </div>

        <!-- Special Stay Requests & Preferences -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
          <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
            <span class="material-symbols-outlined text-brand-700">room_service</span>
            <h3 class="font-headline font-bold text-sm text-slate-900">Hospitality &amp; Stay Preferences</h3>
          </div>
          <?php if (!empty($guest['special_requests'])): ?>
            <div class="p-3.5 bg-brand-50/40 rounded-2xl border border-brand-100 text-xs text-slate-800 leading-relaxed font-medium">
              <?= nl2br(htmlspecialchars($guest['special_requests'])) ?>
            </div>
          <?php else: ?>
            <p class="text-xs text-slate-400 italic">No special dietary or stay preferences on file.</p>
          <?php endif; ?>
        </div>

      </div>

      <!-- Right 1 Col: Emergency Contact & Admin Staff Notes -->
      <div class="space-y-6">
        
        <!-- Emergency Contact Card -->
        <div class="bg-white p-6 rounded-3xl border border-rose-200 shadow-xs space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-rose-100">
            <span class="material-symbols-outlined text-rose-600">emergency</span>
            <h3 class="font-headline font-bold text-sm text-slate-900">Emergency Contact</h3>
          </div>

          <?php if (!empty($guest['emergency_contact_name']) || !empty($guest['emergency_contact_phone'])): ?>
            <div class="space-y-2 text-xs">
              <div>
                <span class="text-slate-400 block text-[11px]">Contact Person</span>
                <span class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($guest['emergency_contact_name']) ?></span>
                <?php if (!empty($guest['emergency_contact_relation'])): ?>
                  <span class="text-[11px] text-slate-500">(<?= htmlspecialchars($guest['emergency_contact_relation']) ?>)</span>
                <?php endif; ?>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Emergency Phone</span>
                <a href="tel:<?= htmlspecialchars($guest['emergency_contact_phone']) ?>" class="font-bold text-rose-700 hover:underline flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm">call</span>
                  <span><?= htmlspecialchars($guest['emergency_contact_phone']) ?></span>
                </a>
              </div>
            </div>
          <?php else: ?>
            <p class="text-xs text-slate-400 italic">No emergency contact recorded yet.</p>
          <?php endif; ?>
        </div>

        <!-- Confidential Admin Staff Notes -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-slate-700">lock</span>
              <span>Confidential Staff Notes</span>
            </h3>
            <button type="button" onclick="openNoteModal()" class="text-xs font-bold text-brand-700 hover:underline">+ Add Note</button>
          </div>

          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-700 max-h-60 overflow-y-auto whitespace-pre-line font-mono text-[11px]">
            <?= !empty($guest['admin_notes']) ? htmlspecialchars($guest['admin_notes']) : "No internal staff notes recorded." ?>
          </div>
        </div>

      </div>

    </div>

  <?php elseif ($activeTab === 'bookings'): ?>
    <!-- ── TAB 2: BOOKINGS PORTFOLIO ── -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-headline font-bold text-sm text-slate-900">Reservations &amp; Bookings Portfolio</h3>
        <span class="text-xs text-slate-400"><?= count($bookings) ?> Total records</span>
      </div>

      <?php if (empty($bookings)): ?>
        <div class="p-12 text-center text-slate-400">
          <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">calendar_month</span>
          <p class="font-bold text-slate-600 text-sm">No reservations on record</p>
          <p class="text-xs text-slate-400 mt-0.5">This guest has not placed any reservations yet.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                <th class="py-3 px-4">Booking Ref</th>
                <th class="py-3 px-4">Luxury Property</th>
                <th class="py-3 px-4">Stay Dates</th>
                <th class="py-3 px-4 text-center">Rental Mode</th>
                <th class="py-3 px-4 text-right">Total Tariff</th>
                <th class="py-3 px-4 text-center">Stay Status</th>
                <th class="py-3 px-4 text-center">Payment</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($bookings as $b): 
                $bSt = $b['status'];
                $pSt = $b['payment_status'];
              ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                    <?= htmlspecialchars($b['booking_reference'] ?: 'BK#' . $b['id']) ?>
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-slate-900">
                    <?= htmlspecialchars($b['property_title'] ?? 'Luxury Farmhouse') ?>
                    <span class="text-[10px] text-slate-400 block"><?= htmlspecialchars($b['property_location'] ?? '') ?></span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-700">
                    <div><?= date('M d, Y', strtotime($b['check_in'])) ?> &rarr; <?= date('M d, Y', strtotime($b['check_out'])) ?></div>
                    <span class="text-[10px] text-slate-400"><?= (int)$b['total_nights'] ?> Nights</span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                      <?= htmlspecialchars($b['booking_type']) ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right font-headline font-bold text-slate-900">
                    ₹<?= number_format((float)$b['price']) ?>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 capitalize">
                      <?= str_replace('_', ' ', $bSt) ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <?php if ($pSt === 'paid'): ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 capitalize"><?= str_replace('_', ' ', $pSt) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <a href="<?= url('admin/bookings/view/' . $b['id']) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs inline-flex items-center gap-1">
                      <span>View</span>
                      <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($activeTab === 'stays'): ?>
    <!-- ── TAB 3: STAY & CHECK-IN HISTORY ── -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-headline font-bold text-sm text-slate-900">Historical Check-In &amp; Stay Logs</h3>
        <span class="text-xs text-slate-400"><?= count($stays) ?> Stays</span>
      </div>

      <?php if (empty($stays)): ?>
        <div class="p-12 text-center text-slate-400">
          <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">hotel</span>
          <p class="font-bold text-slate-600 text-sm">No completed or active stays</p>
          <p class="text-xs text-slate-400 mt-0.5">Stay records are logged automatically upon front-desk guest check-in.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                <th class="py-3 px-4">Booking Ref</th>
                <th class="py-3 px-4">Property</th>
                <th class="py-3 px-4">Assigned Room / Unit</th>
                <th class="py-3 px-4">Actual Check-In</th>
                <th class="py-3 px-4">Actual Check-Out</th>
                <th class="py-3 px-4">Stay Status</th>
                <th class="py-3 px-4 text-right">Front Desk Staff</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($stays as $s): ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                    <a href="<?= url('admin/bookings/view/' . $s['id']) ?>" class="hover:underline text-brand-700">
                      <?= htmlspecialchars($s['booking_reference'] ?: 'BK#' . $s['id']) ?>
                    </a>
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-slate-900">
                    <?= htmlspecialchars($s['property_title'] ?? '') ?>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-800">
                    <?= htmlspecialchars($s['assigned_room_number'] ?: 'Entire Property') ?>
                  </td>
                  <td class="py-3.5 px-4 text-slate-700">
                    <?= !empty($s['actual_check_in_at']) ? date('M d, Y h:i A', strtotime($s['actual_check_in_at'])) : date('M d, Y', strtotime($s['check_in'])) ?>
                  </td>
                  <td class="py-3.5 px-4 text-slate-700">
                    <?= !empty($s['actual_check_out_at']) ? date('M d, Y h:i A', strtotime($s['actual_check_out_at'])) : date('M d, Y', strtotime($s['check_out'])) ?>
                  </td>
                  <td class="py-3.5 px-4">
                    <?php if ($s['status'] === 'checked_in'): ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 animate-pulse">In-House Now</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Completed</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-4 text-right text-slate-500">
                    <?= htmlspecialchars($s['checked_in_by_name'] ?? 'Front Desk') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($activeTab === 'payments'): ?>
    <!-- ── TAB 4: PAYMENTS & FINANCIAL LEDGER ── -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
          <h3 class="font-headline font-bold text-sm text-slate-900">Authoritative Financial Transaction Ledger</h3>
          <p class="text-xs text-slate-400 mt-0.5">Directly verified records from the payments ledger.</p>
        </div>
        <div class="text-right">
          <span class="text-xs text-slate-400 block">Total Verified Paid</span>
          <span class="font-headline font-black text-base text-emerald-700">₹<?= number_format($totalSpendPaid) ?></span>
        </div>
      </div>

      <?php if (empty($payments)): ?>
        <div class="p-12 text-center text-slate-400">
          <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">receipt_long</span>
          <p class="font-bold text-slate-600 text-sm">No transaction records found</p>
          <p class="text-xs text-slate-400 mt-0.5">Transactions are recorded upon advance or stay settlement payment.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                <th class="py-3 px-4">Receipt ID</th>
                <th class="py-3 px-4">Booking Ref</th>
                <th class="py-3 px-4">Payment Type</th>
                <th class="py-3 px-4">Method / UTR</th>
                <th class="py-3 px-4 text-right">Amount (INR)</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-right">Date &amp; Verified By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($payments as $p): ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                    #PAY-<?= $p['id'] ?>
                  </td>
                  <td class="py-3.5 px-4 font-mono text-brand-700 font-semibold">
                    <a href="<?= url('admin/bookings/view/' . $p['booking_id']) ?>" class="hover:underline">
                      <?= htmlspecialchars($p['booking_reference'] ?: 'BK#' . $p['booking_id']) ?>
                    </a>
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-slate-700 capitalize">
                    <?= htmlspecialchars(str_replace('_', ' ', $p['payment_type'])) ?>
                  </td>
                  <td class="py-3.5 px-4 text-slate-700">
                    <div class="uppercase font-bold"><?= htmlspecialchars($p['payment_method']) ?></div>
                    <?php if (!empty($p['utr_number'])): ?>
                      <span class="font-mono text-[10px] text-slate-400">UTR: <?= htmlspecialchars($p['utr_number']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-4 text-right font-headline font-black text-slate-900">
                    ₹<?= number_format((float)$p['amount']) ?>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <?php if ($p['payment_status'] === 'paid'): ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 capitalize"><?= str_replace('_', ' ', $p['payment_status']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-4 text-right text-slate-500">
                    <div><?= date('M d, Y h:i A', strtotime($p['created_at'])) ?></div>
                    <?php if (!empty($p['verified_by_name'])): ?>
                      <span class="text-[10px] text-slate-400">By <?= htmlspecialchars($p['verified_by_name']) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($activeTab === 'documents'): ?>
    <!-- ── TAB 5: LEGAL & KYC DOCUMENTS ── -->
    <div class="space-y-4">
      <div class="flex items-center justify-between bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        <div>
          <h3 class="font-headline font-bold text-sm text-slate-900">Identity &amp; KYC Verification Documents</h3>
          <p class="text-xs text-slate-400 mt-0.5">Government identification, passport copies, driving licenses, and corporate IDs.</p>
        </div>
        <button type="button" onclick="openDocUploadModal()" class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">upload_file</span>
          <span>Upload Document</span>
        </button>
      </div>

      <?php if (empty($documents)): ?>
        <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-slate-400">
          <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">id_card</span>
          <p class="font-bold text-slate-600 text-sm">No identity documents on file</p>
          <p class="text-xs text-slate-400 mt-0.5">Click "Upload Document" to attach an Aadhaar, Passport, or other official proof.</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($documents as $doc): 
            $dSt = $doc['status'];
            $fileUrl = asset($doc['file_path']);
          ?>
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                    <?= htmlspecialchars(str_replace('_', ' ', $doc['document_type'])) ?>
                  </span>
                  <?php if ($dSt === 'verified'): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                      <span class="material-symbols-outlined text-[12px]">verified</span>
                      <span>Verified</span>
                    </span>
                  <?php elseif ($dSt === 'rejected'): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                      Rejected
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                      Pending
                    </span>
                  <?php endif; ?>
                </div>

                <h4 class="font-bold text-sm text-slate-900"><?= htmlspecialchars($doc['document_name']) ?></h4>
                <?php if (!empty($doc['document_number'])): ?>
                  <p class="text-xs font-mono text-slate-500 mt-0.5">No: <?= htmlspecialchars($doc['document_number']) ?></p>
                <?php endif; ?>

                <div class="flex items-center gap-3 text-[11px] text-slate-400 mt-2">
                  <span><?= strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)) ?></span>
                  <span>•</span>
                  <span><?= round($doc['file_size'] / 1024) ?> KB</span>
                  <span>•</span>
                  <span><?= date('M d, Y', strtotime($doc['created_at'])) ?></span>
                </div>

                <?php if (!empty($doc['rejection_reason'])): ?>
                  <div class="mt-2.5 p-2 bg-rose-50 rounded-xl text-[11px] text-rose-700">
                    <strong>Rejection Note:</strong> <?= htmlspecialchars($doc['rejection_reason']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Document Actions Strip -->
              <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-3">
                <a href="<?= $fileUrl ?>" target="_blank" class="text-xs font-bold text-brand-700 hover:underline flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm">visibility</span>
                  <span>View Document</span>
                </a>

                <div class="flex items-center gap-1">
                  <?php if ($dSt !== 'verified'): ?>
                    <button type="button" onclick="verifyDoc(<?= $doc['id'] ?>, 'verified')" class="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50" title="Verify Document">
                      <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    </button>
                    <button type="button" onclick="promptRejectDoc(<?= $doc['id'] ?>)" class="p-1 rounded-lg text-rose-600 hover:bg-rose-50" title="Reject Document">
                      <span class="material-symbols-outlined text-[18px]">cancel</span>
                    </button>
                  <?php endif; ?>
                  <button type="button" onclick="deleteDoc(<?= $doc['id'] ?>)" class="p-1 rounded-lg text-slate-400 hover:text-rose-600" title="Delete File">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($activeTab === 'reviews'): ?>
    <!-- ── TAB 6: FEEDBACK & REVIEWS ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Verified Stay Reviews -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500">star</span>
            <span>Stay Reviews &amp; Ratings</span>
          </h3>
          <span class="text-xs text-slate-400"><?= count($reviews) ?> Reviews</span>
        </div>

        <?php if (empty($reviews)): ?>
          <p class="text-xs text-slate-400 italic py-6 text-center">No reviews submitted by this guest yet.</p>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($reviews as $rev): 
              $revStatus = strtolower($rev['status'] ?? 'approved');
              $badgeClass = match($revStatus) {
                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                'hidden'   => 'bg-slate-100 text-slate-600 border-slate-200',
                'flagged'  => 'bg-purple-50 text-purple-700 border-purple-200',
                default    => 'bg-slate-100 text-slate-600 border-slate-200'
              };
            ?>
              <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-slate-900"><?= htmlspecialchars($rev['property_title'] ?? 'Luxury Villa') ?></span>
                    <?php if (!empty($rev['is_verified_stay'])): ?>
                      <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        Verified Stay
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize <?= $badgeClass ?>">
                      <?= $revStatus ?>
                    </span>
                    <div class="flex items-center gap-0.5 text-amber-500 font-bold text-xs">
                      <span class="material-symbols-outlined text-sm">star</span>
                      <span><?= number_format((float)$rev['overall_rating'], 1) ?></span>
                    </div>
                  </div>
                </div>

                <?php if (!empty($rev['review_title'])): ?>
                  <p class="font-bold text-xs text-slate-800">"<?= htmlspecialchars($rev['review_title']) ?>"</p>
                <?php endif; ?>
                <p class="text-xs text-slate-600 leading-relaxed"><?= htmlspecialchars($rev['review_text']) ?></p>

                <!-- Multi-criteria micro scores -->
                <div class="text-[10px] text-slate-400 flex items-center gap-2 pt-0.5">
                  <span>Cleanliness: <?= $rev['cleanliness_rating'] ?? 5 ?>/5</span>
                  <span>•</span>
                  <span>Location: <?= $rev['location_rating'] ?? 5 ?>/5</span>
                  <span>•</span>
                  <span>Value: <?= $rev['value_rating'] ?? 5 ?>/5</span>
                  <span>•</span>
                  <span>Hospitality: <?= $rev['hospitality_rating'] ?? 5 ?>/5</span>
                </div>

                <?php if ($revStatus === 'rejected' && !empty($rev['rejection_reason'])): ?>
                  <div class="text-[10px] text-rose-600 bg-rose-50 p-2 rounded-xl">
                    <span class="font-bold">Rejection Reason:</span> <?= htmlspecialchars($rev['rejection_reason']) ?>
                  </div>
                <?php endif; ?>

                <div class="text-[10px] text-slate-400 pt-1 flex items-center justify-between border-t border-slate-200/60">
                  <span>Submitted <?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                  <a href="<?= url('admin/reviews?search=' . urlencode($rev['guest_name'])) ?>" class="text-brand-700 font-bold hover:underline">
                    Moderate Review &rarr;
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Inquiries Submitted -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-600">contact_support</span>
            <span>Pre-Stay Inquiries &amp; Messages</span>
          </h3>
          <span class="text-xs text-slate-400"><?= count($inquiries) ?> Inquiries</span>
        </div>

        <?php if (empty($inquiries)): ?>
          <p class="text-xs text-slate-400 italic py-6 text-center">No pre-stay inquiries submitted.</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($inquiries as $inq): ?>
              <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-1">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-900"><?= htmlspecialchars($inq['property_title'] ?? 'General Inquiry') ?></span>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 capitalize"><?= $inq['status'] ?></span>
                </div>
                <p class="text-slate-600"><?= htmlspecialchars($inq['message']) ?></p>
                <span class="text-[10px] text-slate-400 block mt-1"><?= date('M d, Y h:i A', strtotime($inq['created_at'])) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

  <?php elseif ($activeTab === 'activity'): ?>
    <!-- ── TAB 7: CHRONOLOGICAL ACTIVITY TIMELINE ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Activity Timeline -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-700">history</span>
            <span>Guest Activity Audit Trail</span>
          </h3>
        </div>

        <?php if (empty($activityLogs)): ?>
          <p class="text-xs text-slate-400 italic py-6 text-center">No activity entries logged yet.</p>
        <?php else: ?>
          <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
            <?php foreach ($activityLogs as $act): ?>
              <div class="relative">
                <div class="absolute -left-6 top-1 w-3 h-3 rounded-full bg-brand-700 border-2 border-white shadow-2xs"></div>
                <div class="text-xs">
                  <span class="font-bold text-slate-900 block"><?= htmlspecialchars($act['description']) ?></span>
                  <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                    <span><?= date('M d, Y • h:i A', strtotime($act['created_at'])) ?></span>
                    <span>•</span>
                    <span class="text-slate-600 font-medium"><?= htmlspecialchars($act['performed_by']) ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Membership History Log -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-600">workspace_premium</span>
            <span>Membership Lifecycle History</span>
          </h3>
        </div>

        <?php if (empty($membershipHistory)): ?>
          <p class="text-xs text-slate-400 italic py-6 text-center">No membership status changes on record.</p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase border-b border-slate-100">
                  <th class="py-2 px-3">Action</th>
                  <th class="py-2 px-3">Tiers</th>
                  <th class="py-2 px-3">Validity</th>
                  <th class="py-2 px-3">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php foreach ($membershipHistory as $mh): ?>
                  <tr>
                    <td class="py-2.5 px-3 font-bold text-slate-900 capitalize"><?= $mh['action'] ?></td>
                    <td class="py-2.5 px-3 uppercase text-[11px]">
                      <?= $mh['previous_tier'] ?> &rarr; <span class="font-bold text-amber-700"><?= $mh['new_tier'] ?></span>
                    </td>
                    <td class="py-2.5 px-3 text-[11px] text-slate-600">
                      Till <?= date('M d, Y', strtotime($mh['expiry_date'])) ?>
                    </td>
                    <td class="py-2.5 px-3 text-[10px] text-slate-400">
                      <?= date('M d, Y', strtotime($mh['created_at'])) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>

  <?php endif; ?>

</div>

<!-- ══════════════════════════════════════════════
     MODAL 1: STATUS CHANGE MODAL
     ══════════════════════════════════════════════ -->
<div id="statusModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden p-6 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <h3 class="font-headline font-bold text-base text-slate-900" id="statusModalTitle">Change Account Status</h3>
      <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <form onsubmit="submitStatusChange(event)" class="space-y-4">
      <input type="hidden" name="id" id="modalGuestId" value="<?= $guestId ?>">

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Target Account Status</label>
        <select name="status" id="modalStatusSelect" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
          <option value="active" <?= $st === 'active' ? 'selected' : '' ?>>Active (Full Booking Privileges)</option>
          <option value="blocked" <?= $st === 'blocked' ? 'selected' : '' ?>>Blocked (Restricted / Suspended)</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Administrative Note</label>
        <textarea name="reason" id="modalStatusReason" rows="3" placeholder="Context for this status change..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeStatusModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
        <button type="submit" id="modalStatusSubmitBtn" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-xs">Update Status</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL 2: MEMBERSHIP UPGRADE / RENEWAL MODAL
     ══════════════════════════════════════════════ -->
<div id="membershipModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-lg w-full border border-slate-200 shadow-xl overflow-hidden p-6 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <h3 class="font-headline font-bold text-base text-slate-900">Membership Tier &amp; Annual Renewal</h3>
      <button type="button" onclick="closeMembershipModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <form onsubmit="submitMembershipChange(event)" class="space-y-4">
      <input type="hidden" name="id" value="<?= $guestId ?>">

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Membership Tier</label>
          <select name="membership_tier" id="memModalTier" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
            <option value="none" <?= $tier === 'none' ? 'selected' : '' ?>>None (Standard)</option>
            <option value="silver" <?= $tier === 'silver' ? 'selected' : '' ?>>Silver Member</option>
            <option value="gold" <?= $tier === 'gold' ? 'selected' : '' ?>>Gold Member</option>
            <option value="platinum" <?= $tier === 'platinum' ? 'selected' : '' ?>>Platinum Member</option>
            <option value="vip" <?= $tier === 'vip' ? 'selected' : '' ?>>VIP Concierge</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Action Type</label>
          <select name="action" id="memModalAction" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
            <option value="renewed">Annual Renewal</option>
            <option value="upgraded">Tier Upgrade</option>
            <option value="downgraded">Tier Downgrade</option>
            <option value="enrolled">New Enrollment</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Membership Number</label>
          <input type="text" name="membership_number" value="<?= htmlspecialchars($guest['membership_number'] ?? '') ?>" placeholder="Auto-generated" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 font-mono">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Expiry Date</label>
          <input type="date" name="membership_expiry_date" value="<?= !empty($guest['membership_expiry_date']) ? $guest['membership_expiry_date'] : date('Y-m-d', strtotime('+1 year')) ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Audit Notes</label>
        <textarea name="notes" id="memModalNotes" rows="2" placeholder="Reason for tier change or renewal note..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeMembershipModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
        <button type="submit" id="memModalSubmitBtn" class="px-4 py-2 rounded-xl bg-brand-700 text-white text-xs font-bold hover:bg-brand-800 shadow-xs">Save Membership</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL 3: DOCUMENT UPLOAD MODAL
     ══════════════════════════════════════════════ -->
<div id="docUploadModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden p-6 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <h3 class="font-headline font-bold text-base text-slate-900">Upload Identity / KYC Document</h3>
      <button type="button" onclick="closeDocUploadModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <form method="POST" action="<?= url('admin/guests/upload-document') ?>" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="user_id" value="<?= $guestId ?>">

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Document Category</label>
        <select name="document_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
          <option value="aadhaar">Aadhaar Card</option>
          <option value="pan">Permanent Account Number (PAN)</option>
          <option value="passport">Passport</option>
          <option value="driving_license">Driving License</option>
          <option value="voter_id">Voter ID Card</option>
          <option value="visa">International Visa</option>
          <option value="corporate_id">Corporate Identity Card</option>
          <option value="other">Other Official Document</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Document Label / Name</label>
        <input type="text" name="document_name" required placeholder="e.g., Aadhaar Card Front &amp; Back" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Document Number (Optional)</label>
        <input type="text" name="document_number" placeholder="e.g., XXXX-XXXX-1234" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Select Document File <span class="text-rose-500">*</span></label>
        <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
        <span class="text-[10px] text-slate-400 mt-1 block">PDF, JPG, PNG or WEBP (Max 10MB)</span>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeDocUploadModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-xl bg-brand-700 text-white text-xs font-bold hover:bg-brand-800 shadow-xs">Upload File</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL 4: ADD STAFF NOTE MODAL
     ══════════════════════════════════════════════ -->
<div id="noteModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden p-6 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <h3 class="font-headline font-bold text-base text-slate-900">Add Administrative Staff Note</h3>
      <button type="button" onclick="closeNoteModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <form onsubmit="submitStaffNote(event)" class="space-y-4">
      <input type="hidden" name="user_id" value="<?= $guestId ?>">

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Administrative Note</label>
        <textarea name="note" id="noteInput" rows="4" required placeholder="Enter confidential staff observation or stay reminder..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeNoteModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
        <button type="submit" id="noteSubmitBtn" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-xs">Append Note</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL 5: DELETE MODAL (SAFE DELETION)
     ══════════════════════════════════════════════ -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden p-6 space-y-4">
    <div class="flex items-center gap-3 text-rose-600 pb-3 border-b border-slate-100">
      <span class="material-symbols-outlined text-3xl">warning</span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Delete Guest Account</h3>
        <p class="text-xs text-slate-400">Orphan prevention check</p>
      </div>
    </div>

    <p class="text-xs text-slate-600 leading-relaxed">
      Are you sure you want to delete <strong class="text-slate-900"><?= htmlspecialchars($guest['name']) ?></strong>?
      If this account has historical reservations, stay logs, or financial ledger entries, the system will block deletion and recommend deactivation instead.
    </p>

    <div class="flex items-center justify-end gap-2 pt-2">
      <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
      <form method="POST" action="<?= url('admin/guests/delete') ?>">
        <input type="hidden" name="id" value="<?= $guestId ?>">
        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 shadow-xs">Proceed with Deletion</button>
      </form>
    </div>
  </div>
</div>

<script>
// Status Modal
function openStatusModal(id, name, currentStatus) {
  document.getElementById('modalGuestId').value = id;
  document.getElementById('modalStatusSelect').value = currentStatus;
  document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() {
  document.getElementById('statusModal').classList.add('hidden');
}
function submitStatusChange(e) {
  e.preventDefault();
  const id = document.getElementById('modalGuestId').value;
  const status = document.getElementById('modalStatusSelect').value;
  const reason = document.getElementById('modalStatusReason').value;
  const btn = document.getElementById('modalStatusSubmitBtn');

  btn.disabled = true;
  btn.textContent = 'Updating...';

  const formData = new FormData();
  formData.append('id', id);
  formData.append('status', status);
  formData.append('reason', reason);

  fetch('<?= url('admin/guests/update-status') ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.textContent = 'Update Status';
      if (data.success) {
        closeStatusModal();
        window.location.reload();
      } else {
        alert(data.message || 'Error updating status.');
      }
    });
}

// Membership Modal
function openMembershipModal() {
  document.getElementById('membershipModal').classList.remove('hidden');
}
function closeMembershipModal() {
  document.getElementById('membershipModal').classList.add('hidden');
}
function submitMembershipChange(e) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('memModalSubmitBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';

  fetch('<?= url('admin/guests/update-membership') ?>', { method: 'POST', body: new FormData(form) })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.textContent = 'Save Membership';
      if (data.success) {
        closeMembershipModal();
        window.location.reload();
      } else {
        alert(data.message || 'Error updating membership.');
      }
    });
}

// Document Upload Modal
function openDocUploadModal() {
  document.getElementById('docUploadModal').classList.remove('hidden');
}
function closeDocUploadModal() {
  document.getElementById('docUploadModal').classList.add('hidden');
}

// Document Verification
function verifyDoc(docId, status) {
  if (!confirm('Mark this document as ' + status.toUpperCase() + '?')) return;
  const fd = new FormData();
  fd.append('doc_id', docId);
  fd.append('status', status);

  fetch('<?= url('admin/guests/verify-document') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) window.location.reload();
      else alert(data.message || 'Error verifying document.');
    });
}

function promptRejectDoc(docId) {
  const reason = prompt('Please specify reason for rejecting this document:');
  if (reason === null) return;
  const fd = new FormData();
  fd.append('doc_id', docId);
  fd.append('status', 'rejected');
  fd.append('reason', reason);

  fetch('<?= url('admin/guests/verify-document') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) window.location.reload();
      else alert(data.message || 'Error updating document.');
    });
}

function deleteDoc(docId) {
  if (!confirm('Are you sure you want to permanently delete this document?')) return;
  const fd = new FormData();
  fd.append('doc_id', docId);

  fetch('<?= url('admin/guests/delete-document') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) window.location.reload();
      else alert(data.message || 'Error deleting document.');
    });
}

// Staff Notes Modal
function openNoteModal() {
  document.getElementById('noteModal').classList.remove('hidden');
}
function closeNoteModal() {
  document.getElementById('noteModal').classList.add('hidden');
}
function submitStaffNote(e) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('noteSubmitBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';

  fetch('<?= url('admin/guests/add-note') ?>', { method: 'POST', body: new FormData(form) })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.textContent = 'Append Note';
      if (data.success) {
        closeNoteModal();
        window.location.reload();
      } else {
        alert(data.message || 'Error adding note.');
      }
    });
}

// Delete Modal
function openDeleteModal() {
  document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
