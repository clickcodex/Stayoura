<?php
$pageTitle   = "Guests & Members Hub • Stayora Executive Console";
$currentPage = "guests";
$headerTitle = "Guest & Member Management";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

// Safe variable defaults
$guests       = $guests ?? [];
$stats        = $stats ?? [];
$filters      = $filters ?? [];
$page         = $page ?? 1;
$totalPages   = $totalPages ?? 1;
$totalGuests  = $totalGuests ?? 0;
$perPage      = $perPage ?? 15;
$startItem    = $totalGuests > 0 ? (($page - 1) * $perPage) + 1 : 0;
$endItem      = min($page * $perPage, $totalGuests);

// Helper to resolve avatar
function resolveGuestAvatar(?string $img, ?string $avatarUrl = null): string {
    if (!empty($avatarUrl)) {
        return $avatarUrl;
    }
    if (empty($img) || $img === 'default_profile.png') {
        return '';
    }
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
        return $img;
    }
    return asset('assets/images/uploads/profiles/' . ltrim($img, '/'));
}
?>

<div class="space-y-6">

  <!-- ══════════════════════════════════════════════
       1. TOP ACTION BAR & BREADCRUMBS
       ══════════════════════════════════════════════ -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-brand-700 transition-colors">Console</a>
        <span>/</span>
        <span class="text-slate-800">Guests &amp; Members</span>
      </div>
      <h1 class="font-headline font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2">
        <span>Guest &amp; Member Directory</span>
        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
          <?= number_format($totalGuests) ?> Total
        </span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">
        Manage luxury travelers, VIP members, corporate accounts, verified KYC documents, and stay histories.
      </p>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
      <!-- Export CSV -->
      <a 
        href="<?= url('admin/guests/export?' . http_build_query($filters)) ?>" 
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
        <span class="material-symbols-outlined text-[17px] text-slate-500">download</span>
        <span>Export CSV</span>
      </a>

      <!-- Register Guest -->
      <a 
        href="<?= url('admin/guests/create') ?>" 
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs">
        <span class="material-symbols-outlined text-[17px]">person_add</span>
        <span>Register Guest</span>
      </a>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       2. SUMMARY KPI METRIC CARDS
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
    
    <!-- 1. Total Guests -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Guests</span>
        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">groups</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-slate-900">
        <?= number_format($stats['total_guests'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Registered travelers</div>
    </div>

    <!-- 2. Active Members -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Active Members</span>
        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">workspace_premium</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-slate-900">
        <?= number_format($stats['active_members'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Enrolled in perks</div>
    </div>

    <!-- 3. In-House Guests -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">In-House Now</span>
        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">hotel</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-slate-900">
        <?= number_format($stats['in_house_guests'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Active checked-in stays</div>
    </div>

    <!-- 4. Pending KYC -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pending KYC</span>
        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">badge</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-slate-900">
        <?= number_format($stats['pending_kyc'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Awaiting verification</div>
    </div>

    <!-- 5. Expiring Soon -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Expiring (30d)</span>
        <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">hourglass_bottom</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-slate-900">
        <?= number_format($stats['expiring_members'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Memberships due</div>
    </div>

    <!-- 6. Total Guest Spend -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Spend</span>
        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">payments</span>
        </span>
      </div>
      <div class="font-headline font-black text-xl text-slate-900 truncate">
        ₹<?= number_format($stats['total_spend_all'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Lifetime verified yield</div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       3. SEARCH & MULTI-DIMENSIONAL FILTERS
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <form method="GET" action="<?= url('admin/guests') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
      
      <!-- Keyword Search -->
      <div class="lg:col-span-2 relative">
        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search Directory</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
          <input 
            type="text" 
            name="search" 
            value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
            placeholder="Name, phone, email, guest ID, member #..." 
            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>
      </div>

      <!-- Account Status Filter -->
      <div>
        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Account Status</label>
        <select 
          name="status" 
          class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
          <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>All Statuses</option>
          <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
        </select>
      </div>

      <!-- Membership Tier Filter -->
      <div>
        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Membership Tier</label>
        <select 
          name="membership_tier" 
          class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
          <option value="all" <?= ($filters['membership_tier'] ?? '') === 'all' ? 'selected' : '' ?>>All Tiers</option>
          <option value="none" <?= ($filters['membership_tier'] ?? '') === 'none' ? 'selected' : '' ?>>No Tier (Standard)</option>
          <option value="silver" <?= ($filters['membership_tier'] ?? '') === 'silver' ? 'selected' : '' ?>>Silver Member</option>
          <option value="gold" <?= ($filters['membership_tier'] ?? '') === 'gold' ? 'selected' : '' ?>>Gold Member</option>
          <option value="platinum" <?= ($filters['membership_tier'] ?? '') === 'platinum' ? 'selected' : '' ?>>Platinum Member</option>
          <option value="vip" <?= ($filters['membership_tier'] ?? '') === 'vip' ? 'selected' : '' ?>>VIP Concierge</option>
        </select>
      </div>

      <!-- KYC Status Filter -->
      <div>
        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">KYC Status</label>
        <select 
          name="kyc_status" 
          class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
          <option value="all" <?= ($filters['kyc_status'] ?? '') === 'all' ? 'selected' : '' ?>>All Verification</option>
          <option value="verified" <?= ($filters['kyc_status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
          <option value="pending" <?= ($filters['kyc_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Review</option>
          <option value="unverified" <?= ($filters['kyc_status'] ?? '') === 'unverified' ? 'selected' : '' ?>>Unverified</option>
          <option value="rejected" <?= ($filters['kyc_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>

      <!-- Action Buttons Row -->
      <div class="sm:col-span-2 lg:col-span-5 flex items-center justify-between pt-2 border-t border-slate-100 flex-wrap gap-2">
        <div class="flex items-center gap-3">
          <!-- Quick Filter: In-House Stays -->
          <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-700">
            <input type="checkbox" name="in_house" value="1" <?= ($filters['in_house'] ?? '') === '1' ? 'checked' : '' ?> onchange="this.form.submit()" class="rounded border-slate-300 text-brand-700 focus:ring-brand-500">
            <span>Show Currently In-House Only</span>
          </label>

          <!-- Quick Filter: Expiring Memberships -->
          <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-700 ml-3">
            <input type="checkbox" name="expiring_soon" value="1" <?= ($filters['expiring_soon'] ?? '') === '1' ? 'checked' : '' ?> onchange="this.form.submit()" class="rounded border-slate-300 text-brand-700 focus:ring-brand-500">
            <span>Expiring in Next 30 Days</span>
          </label>
        </div>

        <div class="flex items-center gap-2">
          <?php if (!empty(array_filter($filters, fn($v) => $v !== '' && $v !== 'all'))): ?>
            <a href="<?= url('admin/guests') ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all">
              Reset Filters
            </a>
          <?php endif; ?>
          <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px]">filter_alt</span>
            <span>Apply Filters</span>
          </button>
        </div>
      </div>

    </form>
  </div>

  <!-- ══════════════════════════════════════════════
       4. DIRECTORY TABLE & MOBILE CARDS
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    
    <!-- Table Header Info Bar -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
      <div class="flex items-center gap-2">
        <h2 class="font-headline font-bold text-sm text-slate-900">Registered Guest Directory</h2>
        <span class="text-xs text-slate-400">• Showing <?= $startItem ?> to <?= $endItem ?> of <?= number_format($totalGuests) ?></span>
      </div>

      <!-- Sorting Options -->
      <div class="flex items-center gap-2 text-xs">
        <span class="text-slate-400 font-medium">Sort by:</span>
        <a href="<?= url('admin/guests?' . http_build_query(array_merge($filters, ['sort_by' => 'created_at', 'sort_order' => ($filters['sort_by'] ?? '') === 'created_at' && ($filters['sort_order'] ?? '') === 'DESC' ? 'ASC' : 'DESC']))) ?>" 
           class="px-2.5 py-1 rounded-lg border text-xs font-semibold <?= ($filters['sort_by'] ?? 'created_at') === 'created_at' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' ?>">
          Date
        </a>
        <a href="<?= url('admin/guests?' . http_build_query(array_merge($filters, ['sort_by' => 'total_spend', 'sort_order' => ($filters['sort_by'] ?? '') === 'total_spend' && ($filters['sort_order'] ?? '') === 'DESC' ? 'ASC' : 'DESC']))) ?>" 
           class="px-2.5 py-1 rounded-lg border text-xs font-semibold <?= ($filters['sort_by'] ?? '') === 'total_spend' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' ?>">
          Spend
        </a>
        <a href="<?= url('admin/guests?' . http_build_query(array_merge($filters, ['sort_by' => 'total_stays_count', 'sort_order' => ($filters['sort_by'] ?? '') === 'total_stays_count' && ($filters['sort_order'] ?? '') === 'DESC' ? 'ASC' : 'DESC']))) ?>" 
           class="px-2.5 py-1 rounded-lg border text-xs font-semibold <?= ($filters['sort_by'] ?? '') === 'total_stays_count' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' ?>">
          Stays
        </a>
      </div>
    </div>

    <!-- Desktop Table View (md and up) -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
            <th class="py-3.5 px-4">Guest Profile</th>
            <th class="py-3.5 px-4">Contacts &amp; City</th>
            <th class="py-3.5 px-4">Membership &amp; Category</th>
            <th class="py-3.5 px-4">KYC / Verification</th>
            <th class="py-3.5 px-4 text-center">Stays / Bookings</th>
            <th class="py-3.5 px-4 text-right">Total Spend</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          <?php if (empty($guests)): ?>
            <tr>
              <td colspan="8" class="py-12 text-center text-slate-400">
                <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">person_search</span>
                <p class="font-bold text-slate-600 text-sm">No guests found</p>
                <p class="text-xs text-slate-400 mt-0.5">Try adjusting your filters or register a new guest.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($guests as $g): 
              $avatarUrl = resolveGuestAvatar($g['profile_image'] ?? null, $g['avatar_url'] ?? null);
              $tier = $g['membership_tier'] ?? 'none';
              $kyc = $g['kyc_status'] ?? 'unverified';
            ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                
                <!-- Guest Profile -->
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3">
                    <?php if ($avatarUrl): ?>
                      <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($g['name']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200 shrink-0">
                    <?php else: ?>
                      <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 font-headline font-black text-sm flex items-center justify-center border border-brand-200 shrink-0">
                        <?= strtoupper(substr($g['name'], 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <div>
                      <a href="<?= url('admin/guests/view/' . $g['id']) ?>" class="font-bold text-slate-900 hover:text-brand-700 transition-colors">
                        <?= htmlspecialchars($g['name']) ?>
                      </a>
                      <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-0.5">
                        <span class="font-mono text-slate-500 font-semibold"><?= htmlspecialchars($g['guest_code'] ?: 'ID#' . $g['id']) ?></span>
                        <?php if (!empty($g['gender'])): ?>
                          <span>•</span>
                          <span><?= ucfirst($g['gender']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Contacts & City -->
                <td class="py-4 px-4">
                  <div class="space-y-0.5">
                    <div class="font-semibold text-slate-800 flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px] text-slate-400">call</span>
                      <a href="tel:<?= htmlspecialchars($g['phone']) ?>" class="hover:underline"><?= htmlspecialchars($g['phone']) ?></a>
                    </div>
                    <?php if (!empty($g['email'])): ?>
                      <div class="text-[11px] text-slate-500 truncate max-w-[180px] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] text-slate-400">mail</span>
                        <span><?= htmlspecialchars($g['email']) ?></span>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($g['city'])): ?>
                      <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                        <span class="material-symbols-outlined text-[12px] text-slate-400">location_on</span>
                        <span><?= htmlspecialchars($g['city']) ?><?= !empty($g['state']) ? ', ' . htmlspecialchars($g['state']) : '' ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Membership & Category -->
                <td class="py-4 px-4">
                  <div class="space-y-1">
                    <?php if ($tier === 'vip'): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-slate-900 text-amber-300 border border-slate-700 shadow-2xs">
                        <span class="material-symbols-outlined text-[13px] text-amber-400">diamond</span>
                        <span>VIP Concierge</span>
                      </span>
                    <?php elseif ($tier === 'platinum'): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <span class="material-symbols-outlined text-[13px]">military_tech</span>
                        <span>Platinum</span>
                      </span>
                    <?php elseif ($tier === 'gold'): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="material-symbols-outlined text-[13px] text-amber-600">star</span>
                        <span>Gold</span>
                      </span>
                    <?php elseif ($tier === 'silver'): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                        <span class="material-symbols-outlined text-[13px]">shield</span>
                        <span>Silver</span>
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-medium bg-slate-100 text-slate-500">
                        Standard Guest
                      </span>
                    <?php endif; ?>

                    <div class="text-[10px] text-slate-400">
                      <?= ucwords(str_replace('_', ' ', $g['guest_type'] ?? 'guest')) ?>
                      <?php if (!empty($g['membership_number'])): ?>
                        • <span class="font-mono text-slate-500"><?= htmlspecialchars($g['membership_number']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>

                <!-- KYC Status -->
                <td class="py-4 px-4">
                  <div>
                    <?php if ($kyc === 'verified'): ?>
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="material-symbols-outlined text-[13px]">verified_user</span>
                        <span>Verified</span>
                      </span>
                    <?php elseif ($kyc === 'pending'): ?>
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="material-symbols-outlined text-[13px]">hourglass_empty</span>
                        <span>Pending</span>
                      </span>
                    <?php elseif ($kyc === 'rejected'): ?>
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="material-symbols-outlined text-[13px]">cancel</span>
                        <span>Rejected</span>
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-slate-100 text-slate-500">
                        Unverified
                      </span>
                    <?php endif; ?>

                    <?php if (!empty($g['gov_id_type'])): ?>
                      <div class="text-[10px] text-slate-400 mt-1 uppercase">
                        <?= htmlspecialchars(str_replace('_', ' ', $g['gov_id_type'])) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Stays / Bookings -->
                <td class="py-4 px-4 text-center">
                  <div class="font-bold text-slate-900">
                    <?= number_format($g['real_stays_count'] ?? 0) ?> Stays
                  </div>
                  <div class="text-[10px] text-slate-400">
                    <?= number_format($g['real_bookings_count'] ?? 0) ?> Total Bookings
                  </div>
                </td>

                <!-- Total Spend -->
                <td class="py-4 px-4 text-right">
                  <div class="font-headline font-black text-slate-900">
                    ₹<?= number_format((float)($g['real_total_paid'] ?? 0)) ?>
                  </div>
                  <div class="text-[10px] text-slate-400">
                    Verified Paid
                  </div>
                </td>

                <!-- Account Status -->
                <td class="py-4 px-4 text-center">
                  <?php if ($g['status'] === 'active'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      Active
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                      Blocked
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Actions -->
                <td class="py-4 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <a href="<?= url('admin/guests/view/' . $g['id']) ?>" 
                       title="View 360° Dossier"
                       class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-brand-700 hover:bg-slate-50 transition-all">
                      <span class="material-symbols-outlined text-[17px]">visibility</span>
                    </a>
                    <a href="<?= url('admin/guests/edit/' . $g['id']) ?>" 
                       title="Edit Profile"
                       class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-brand-700 hover:bg-slate-50 transition-all">
                      <span class="material-symbols-outlined text-[17px]">edit</span>
                    </a>
                    <button 
                      type="button" 
                      onclick="openStatusModal(<?= $g['id'] ?>, '<?= htmlspecialchars(addslashes($g['name'])) ?>', '<?= $g['status'] ?>')"
                      title="Update Account Status"
                      class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-amber-700 hover:bg-slate-50 transition-all">
                      <span class="material-symbols-outlined text-[17px]">tune</span>
                    </button>
                  </div>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile Responsive Cards (visible under md) -->
    <div class="block md:hidden divide-y divide-slate-100">
      <?php if (empty($guests)): ?>
        <div class="p-8 text-center text-slate-400">
          <p class="font-bold text-slate-600">No guests found</p>
        </div>
      <?php else: ?>
        <?php foreach ($guests as $g): 
          $avatarUrl = resolveGuestAvatar($g['profile_image'] ?? null, $g['avatar_url'] ?? null);
          $tier = $g['membership_tier'] ?? 'none';
          $kyc = $g['kyc_status'] ?? 'unverified';
        ?>
          <div class="p-4 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <?php if ($avatarUrl): ?>
                  <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($g['name']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                <?php else: ?>
                  <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 font-headline font-black text-sm flex items-center justify-center border border-brand-200">
                    <?= strtoupper(substr($g['name'], 0, 1)) ?>
                  </div>
                <?php endif; ?>
                <div>
                  <a href="<?= url('admin/guests/view/' . $g['id']) ?>" class="font-bold text-sm text-slate-900">
                    <?= htmlspecialchars($g['name']) ?>
                  </a>
                  <div class="text-[11px] font-mono text-slate-500 font-semibold"><?= htmlspecialchars($g['guest_code'] ?: 'ID#' . $g['id']) ?></div>
                </div>
              </div>

              <!-- Status pill -->
              <?php if ($g['status'] === 'active'): ?>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
              <?php else: ?>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Blocked</span>
              <?php endif; ?>
            </div>

            <!-- Contacts info -->
            <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl">
              <div>
                <span class="text-[10px] text-slate-400 block">Phone</span>
                <a href="tel:<?= htmlspecialchars($g['phone']) ?>" class="font-semibold text-slate-800"><?= htmlspecialchars($g['phone']) ?></a>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block">City</span>
                <span class="font-semibold text-slate-800"><?= htmlspecialchars($g['city'] ?: 'N/A') ?></span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block">Stays / Bookings</span>
                <span class="font-bold text-slate-900"><?= (int)($g['real_stays_count'] ?? 0) ?> stays (<?= (int)($g['real_bookings_count'] ?? 0) ?> bks)</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block">Total Spend</span>
                <span class="font-bold text-slate-900">₹<?= number_format((float)($g['real_total_paid'] ?? 0)) ?></span>
              </div>
            </div>

            <!-- Tier & Actions -->
            <div class="flex items-center justify-between pt-1">
              <div>
                <?php if ($tier !== 'none'): ?>
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <?= ucfirst($tier) ?> Member
                  </span>
                <?php else: ?>
                  <span class="text-[11px] text-slate-400">Standard Guest</span>
                <?php endif; ?>
              </div>

              <div class="flex items-center gap-2">
                <a href="<?= url('admin/guests/view/' . $g['id']) ?>" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-bold">
                  Dossier
                </a>
                <a href="<?= url('admin/guests/edit/' . $g['id']) ?>" class="p-1.5 rounded-lg border border-slate-200 text-slate-600">
                  <span class="material-symbols-outlined text-[16px]">edit</span>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
      <div class="p-4 sm:p-5 border-t border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <span class="text-xs text-slate-500">
          Page <?= $page ?> of <?= $totalPages ?>
        </span>
        <div class="flex items-center gap-1.5">
          <?php if ($page > 1): ?>
            <a href="<?= url('admin/guests?' . http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all">
              Previous
            </a>
          <?php endif; ?>

          <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="<?= url('admin/guests?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $i === $page ? 'bg-slate-900 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="<?= url('admin/guests?' . http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all">
              Next
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

<!-- ══════════════════════════════════════════════
     5. STATUS MODAL (QUICK ACTIVATION/BLOCK)
     ══════════════════════════════════════════════ -->
<div id="statusModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden p-6 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <h3 class="font-headline font-bold text-base text-slate-900" id="statusModalTitle">Change Account Status</h3>
      <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <form id="statusForm" onsubmit="submitStatusChange(event)" class="space-y-4">
      <input type="hidden" name="id" id="modalGuestId">

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Target Account Status</label>
        <select name="status" id="modalStatusSelect" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
          <option value="active">Active (Full Portal &amp; Booking Access)</option>
          <option value="blocked">Blocked (Restricted / Suspended Access)</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Administrative Note</label>
        <textarea name="reason" id="modalStatusReason" rows="3" placeholder="Provide context for this status change..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="closeStatusModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
          Cancel
        </button>
        <button type="submit" id="modalStatusSubmitBtn" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-all shadow-xs">
          Update Status
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusModal(id, name, currentStatus) {
  document.getElementById('modalGuestId').value = id;
  document.getElementById('statusModalTitle').textContent = 'Update Status: ' + name;
  document.getElementById('modalStatusSelect').value = currentStatus;
  document.getElementById('modalStatusReason').value = '';
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

  fetch('<?= url('admin/guests/update-status') ?>', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    btn.textContent = 'Update Status';
    if (data.success) {
      closeStatusModal();
      window.location.reload();
    } else {
      alert(data.message || 'Could not update status.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.textContent = 'Update Status';
    alert('Network or server error.');
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
