<?php
$pageTitle   = "Property Owners Hub • Stayora Executive Console";
$currentPage = "owners";
$headerTitle = "Property Owners Management";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

// Safe variable defaults
$owners       = $owners ?? [];
$kpis         = $kpis ?? [];
$cities       = $cities ?? [];
$filters      = $filters ?? [];
$page         = $page ?? 1;
$totalPages   = $totalPages ?? 1;
$totalRecords = $totalRecords ?? 0;
$startItem    = $startItem ?? 0;
$endItem      = $endItem ?? 0;

// Helper to resolve avatar
function resolveOwnerAvatar(?string $img): string {
    if (empty($img)) {
        return '';
    }
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
        return $img;
    }
    return asset('assets/images/uploads/avatars/' . ltrim($img, '/'));
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
        <span class="text-slate-800">Property Owners</span>
      </div>
      <h1 class="font-headline font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2">
        <span>Estate Hosts &amp; Property Owners</span>
        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
          <?= number_format($totalRecords) ?> Total
        </span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">
        Manage luxury estate owners, business partnerships, KYC records, property portfolios, and booking yields.
      </p>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
      <!-- Export CSV -->
      <a 
        href="<?= url('admin/owners/export?' . http_build_query($filters)) ?>" 
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
        <span class="material-symbols-outlined text-[17px] text-slate-500">download</span>
        <span>Export CSV</span>
      </a>

      <!-- Register Owner -->
      <a 
        href="<?= url('admin/owners/create') ?>" 
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs">
        <span class="material-symbols-outlined text-[17px]">person_add</span>
        <span>Register Owner</span>
      </a>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       2. SUMMARY KPI METRIC CARDS
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
    
    <!-- 1. Total Owners -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Hosts</span>
        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">groups</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-slate-900">
        <?= number_format($kpis['total_owners'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Registered partners</div>
    </div>

    <!-- 2. Active Owners -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Active</span>
        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">check_circle</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-emerald-600">
        <?= number_format($kpis['active_owners'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">In operational status</div>
    </div>

    <!-- 3. Inactive / Suspended -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Inactive</span>
        <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">pause_circle</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-rose-600">
        <?= number_format(($kpis['inactive_owners'] ?? 0) + ($kpis['suspended_owners'] ?? 0)) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Inactive or suspended</div>
    </div>

    <!-- 4. Total Properties Linked -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Properties</span>
        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">villa</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-indigo-600">
        <?= number_format($kpis['total_properties_linked'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Under management</div>
    </div>

    <!-- 5. KYC Verified -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">KYC Verified</span>
        <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">verified_user</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-teal-600">
        <?= number_format($kpis['verified_kyc'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">Documents audited</div>
    </div>

    <!-- 6. Multi-Property Owners -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Multi-Estate</span>
        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">domain_add</span>
        </span>
      </div>
      <div class="font-headline font-black text-2xl text-amber-600">
        <?= number_format($kpis['multi_property_owners'] ?? 0) ?>
      </div>
      <div class="text-[10px] text-slate-400 mt-0.5">&gt; 1 property held</div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       3. SEARCH, FILTERS & CONTROLS BAR
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-xs">
    <form method="GET" action="<?= url('admin/owners') ?>" class="space-y-4">
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
        
        <!-- Search Keyword -->
        <div class="lg:col-span-4 relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <span class="material-symbols-outlined text-lg">search</span>
          </span>
          <input 
            type="text" 
            name="search" 
            value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
            placeholder="Search by name, business, email, phone, city..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Status Filter -->
        <div class="lg:col-span-2">
          <select 
            name="status" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>All Statuses</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active Only</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
            <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
          </select>
        </div>

        <!-- KYC Status Filter -->
        <div class="lg:col-span-2">
          <select 
            name="kyc_status" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="all" <?= ($filters['kyc_status'] ?? '') === 'all' ? 'selected' : '' ?>>All KYC States</option>
            <option value="verified" <?= ($filters['kyc_status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified KYC</option>
            <option value="pending" <?= ($filters['kyc_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending KYC</option>
            <option value="rejected" <?= ($filters['kyc_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected KYC</option>
          </select>
        </div>

        <!-- Owner Type Filter -->
        <div class="lg:col-span-2">
          <select 
            name="owner_type" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="all" <?= ($filters['owner_type'] ?? '') === 'all' ? 'selected' : '' ?>>All Entity Types</option>
            <option value="individual" <?= ($filters['owner_type'] ?? '') === 'individual' ? 'selected' : '' ?>>Individual</option>
            <option value="organization" <?= ($filters['owner_type'] ?? '') === 'organization' ? 'selected' : '' ?>>Organization</option>
          </select>
        </div>

        <!-- City Filter -->
        <div class="lg:col-span-2">
          <select 
            name="city" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="all" <?= ($filters['city'] ?? '') === 'all' ? 'selected' : '' ?>>All Cities</option>
            <?php foreach ($cities as $city): ?>
              <option value="<?= htmlspecialchars($city) ?>" <?= strtolower($filters['city'] ?? '') === strtolower($city) ? 'selected' : '' ?>>
                <?= htmlspecialchars($city) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>

      <!-- Second Row: Sort + Actions -->
      <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100 flex-wrap">
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-slate-500">Sort by:</span>
          <select 
            name="sort" 
            onchange="this.form.submit()"
            class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
            <option value="created_desc" <?= ($filters['sort'] ?? '') === 'created_desc' ? 'selected' : '' ?>>Recently Added</option>
            <option value="created_asc" <?= ($filters['sort'] ?? '') === 'created_asc' ? 'selected' : '' ?>>Oldest First</option>
            <option value="name_asc" <?= ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
            <option value="name_desc" <?= ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
            <option value="properties_desc" <?= ($filters['sort'] ?? '') === 'properties_desc' ? 'selected' : '' ?>>Most Properties</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <?php if (!empty($filters['search']) || ($filters['status'] ?? 'all') !== 'all' || ($filters['kyc_status'] ?? 'all') !== 'all' || ($filters['owner_type'] ?? 'all') !== 'all' || ($filters['city'] ?? 'all') !== 'all'): ?>
            <a href="<?= url('admin/owners') ?>" class="text-xs font-bold text-slate-500 hover:text-rose-600 transition-colors px-2 py-1">
              Reset Filters
            </a>
          <?php endif; ?>
          <button type="submit" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
            Apply Filters
          </button>
        </div>
      </div>

    </form>
  </div>

  <!-- ══════════════════════════════════════════════
       4. OWNERS MASTER DIRECTORY TABLE (Desktop: >= md)
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    
    <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">
          Owners Directory
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
          Showing <?= $startItem ?> to <?= $endItem ?> of <?= number_format($totalRecords) ?> total partners
        </p>
      </div>

      <div class="text-xs font-bold text-slate-400">
        Page <?= $page ?> of <?= $totalPages ?>
      </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-xs min-w-[850px]">
        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
          <tr>
            <th class="py-3.5 px-4 sm:px-6">Owner / Entity</th>
            <th class="py-3.5 px-4">Contact Coordinates</th>
            <th class="py-3.5 px-4">Location</th>
            <th class="py-3.5 px-4 text-center">Portfolio</th>
            <th class="py-3.5 px-4">Yield / Turnover</th>
            <th class="py-3.5 px-4">Status &amp; KYC</th>
            <th class="py-3.5 px-4 sm:px-6 text-right">Quick Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
          <?php if (!empty($owners)): ?>
            <?php foreach ($owners as $ow): ?>
              <?php 
                $st = strtolower((string)($ow['status'] ?? 'active'));
                $statusBadge = match($st) {
                  'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                  'suspended' => 'bg-rose-50 text-rose-700 border-rose-200',
                  'inactive'  => 'bg-slate-100 text-slate-700 border-slate-200',
                  default     => 'bg-amber-50 text-amber-700 border-amber-200',
                };
                $kyc = strtolower((string)($ow['kyc_status'] ?? 'pending'));
                $kycBadge = match($kyc) {
                  'verified' => ['text' => 'KYC Verified', 'class' => 'text-teal-700 bg-teal-50 border-teal-200', 'icon' => 'verified'],
                  'rejected' => ['text' => 'KYC Rejected', 'class' => 'text-rose-700 bg-rose-50 border-rose-200', 'icon' => 'cancel'],
                  default    => ['text' => 'Pending Audit', 'class' => 'text-amber-700 bg-amber-50 border-amber-200', 'icon' => 'schedule'],
                };
                $avatarUrl = resolveOwnerAvatar($ow['profile_image'] ?? null);
              ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                
                <!-- Owner / Entity -->
                <td class="py-4 px-4 sm:px-6">
                  <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-brand-50 border border-brand-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-700 font-headline font-bold text-sm shadow-2xs">
                      <?php if (!empty($avatarUrl)): ?>
                        <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($ow['name']) ?>" class="w-full h-full object-cover">
                      <?php else: ?>
                        <span><?= strtoupper(substr($ow['name'], 0, 2)) ?></span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <a href="<?= url('admin/owners/view/' . $ow['id']) ?>" class="font-headline font-bold text-slate-900 text-sm hover:text-brand-700 transition-colors">
                        <?= htmlspecialchars($ow['name']) ?>
                      </a>
                      <?php if (!empty($ow['business_name'])): ?>
                        <div class="text-[11px] font-semibold text-slate-500 truncate max-w-[200px]" title="<?= htmlspecialchars($ow['business_name']) ?>">
                          <?= htmlspecialchars($ow['business_name']) ?>
                        </div>
                      <?php endif; ?>
                      <div class="flex items-center gap-1.5 mt-1">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider <?= $ow['owner_type'] === 'organization' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600' ?>">
                          <?= $ow['owner_type'] ?>
                        </span>
                        <span class="text-slate-400 text-[10px]">#<?= $ow['id'] ?></span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Contact Coordinates -->
                <td class="py-4 px-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-[13px] text-slate-400">mail</span>
                      <a href="mailto:<?= htmlspecialchars($ow['email']) ?>" class="hover:text-brand-700">
                        <?= htmlspecialchars($ow['email']) ?>
                      </a>
                    </div>
                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-[13px] text-slate-400">call</span>
                      <a href="tel:<?= htmlspecialchars($ow['phone']) ?>" class="hover:text-brand-700">
                        <?= htmlspecialchars($ow['phone']) ?>
                      </a>
                    </div>
                    <?php if (!empty($ow['alternate_phone'])): ?>
                      <div class="text-[10px] text-slate-400 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[12px] text-slate-300">phone_iphone</span>
                        <span>Alt: <?= htmlspecialchars($ow['alternate_phone']) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Location -->
                <td class="py-4 px-4 whitespace-nowrap">
                  <div class="font-bold text-slate-800 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-slate-400">location_city</span>
                    <span><?= htmlspecialchars($ow['city'] ?? 'India') ?></span>
                  </div>
                  <div class="text-[10px] text-slate-400 mt-0.5">
                    <?= htmlspecialchars($ow['state'] ?? 'India') ?>
                  </div>
                </td>

                <!-- Associated Properties -->
                <td class="py-4 px-4 text-center whitespace-nowrap">
                  <a href="<?= url('admin/owners/view/' . $ow['id'] . '?tab=properties') ?>" 
                     class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition-all <?= (int)$ow['properties_count'] > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' : 'bg-slate-100 text-slate-400' ?>"
                     title="View assigned properties">
                    <span class="material-symbols-outlined text-sm">villa</span>
                    <span><?= (int)$ow['properties_count'] ?></span>
                  </a>
                </td>

                <!-- Yield / Turnover -->
                <td class="py-4 px-4 whitespace-nowrap">
                  <div class="font-headline font-black text-slate-900 text-sm">
                    ₹<?= number_format((float)($ow['gross_booking_value'] ?? 0)) ?>
                  </div>
                  <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                    Collected: ₹<?= number_format((float)($ow['total_collected'] ?? 0)) ?>
                  </div>
                </td>

                <!-- Status & KYC -->
                <td class="py-4 px-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $statusBadge ?> capitalize">
                        <span class="w-1.5 h-1.5 rounded-full <?= $st === 'active' ? 'bg-emerald-500' : ($st === 'suspended' ? 'bg-rose-500' : 'bg-slate-400') ?>"></span>
                        <span><?= $st ?></span>
                      </span>
                    </div>
                    <div>
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold border <?= $kycBadge['class'] ?>">
                        <span class="material-symbols-outlined text-[11px]"><?= $kycBadge['icon'] ?></span>
                        <span><?= $kycBadge['text'] ?></span>
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Quick Actions -->
                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    
                    <!-- View 360° Dossier -->
                    <a 
                      href="<?= url('admin/owners/view/' . $ow['id']) ?>" 
                      class="p-2 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-700 transition-colors" 
                      title="Open 360° Profile Dossier">
                      <span class="material-symbols-outlined text-lg">badge</span>
                    </a>

                    <!-- Edit Owner -->
                    <a 
                      href="<?= url('admin/owners/edit/' . $ow['id']) ?>" 
                      class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-colors" 
                      title="Edit Profile Information">
                      <span class="material-symbols-outlined text-lg">edit</span>
                    </a>

                    <!-- Quick Status Trigger -->
                    <button 
                      type="button" 
                      onclick="openStatusModal(<?= $ow['id'] ?>, '<?= htmlspecialchars(addslashes($ow['name'])) ?>', '<?= $st ?>')" 
                      class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-colors cursor-pointer" 
                      title="Update Account Status">
                      <span class="material-symbols-outlined text-lg">tune</span>
                    </button>

                    <!-- Delete Dialog Trigger -->
                    <button 
                      type="button" 
                      onclick="confirmDeleteOwner(<?= $ow['id'] ?>, '<?= htmlspecialchars(addslashes($ow['name'])) ?>', <?= (int)$ow['properties_count'] ?>)" 
                      class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" 
                      title="Delete Owner">
                      <span class="material-symbols-outlined text-lg">delete</span>
                    </button>

                  </div>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="py-16 text-center text-slate-400">
                <div class="max-w-xs mx-auto space-y-3">
                  <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">person_search</span>
                  </div>
                  <div class="font-headline font-bold text-slate-700 text-sm">No Property Owners Found</div>
                  <p class="text-xs text-slate-400 leading-relaxed">
                    No owners matched your current filter criteria. Try adjusting or clearing search parameters.
                  </p>
                  <a href="<?= url('admin/owners') ?>" class="inline-block px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
                    Reset All Filters
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ══════════════════════════════════════════════
         5. MOBILE RESPONSIVE CARDS VIEW (< md)
         ══════════════════════════════════════════════ -->
    <div class="md:hidden p-3.5 sm:p-4 space-y-3.5 bg-slate-50/50">
      <?php if (!empty($owners)): ?>
        <?php foreach ($owners as $ow): ?>
          <?php 
            $st = strtolower((string)($ow['status'] ?? 'active'));
            $statusBadge = match($st) {
              'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
              'suspended' => 'bg-rose-50 text-rose-700 border-rose-200',
              'inactive'  => 'bg-slate-100 text-slate-700 border-slate-200',
              default     => 'bg-amber-50 text-amber-700 border-amber-200',
            };
            $kyc = strtolower((string)($ow['kyc_status'] ?? 'pending'));
            $kycBadge = match($kyc) {
              'verified' => ['text' => 'Verified', 'class' => 'text-teal-700 bg-teal-50 border-teal-200', 'icon' => 'verified'],
              'rejected' => ['text' => 'Rejected', 'class' => 'text-rose-700 bg-rose-50 border-rose-200', 'icon' => 'cancel'],
              default    => ['text' => 'Pending', 'class' => 'text-amber-700 bg-amber-50 border-amber-200', 'icon' => 'schedule'],
            };
            $avatarUrl = resolveOwnerAvatar($ow['profile_image'] ?? null);
          ?>
          <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
            
            <!-- Top Row: Avatar + Name + Badges -->
            <div class="flex items-start gap-3">
              <div class="w-12 h-12 rounded-xl bg-brand-50 border border-brand-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-700 font-headline font-bold text-sm shadow-2xs">
                <?php if (!empty($avatarUrl)): ?>
                  <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($ow['name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <span><?= strtoupper(substr($ow['name'], 0, 2)) ?></span>
                <?php endif; ?>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-1 mb-0.5">
                  <a href="<?= url('admin/owners/view/' . $ow['id']) ?>" class="font-headline font-bold text-slate-900 text-sm hover:text-brand-700 transition-colors truncate">
                    <?= htmlspecialchars($ow['name']) ?>
                  </a>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $statusBadge ?> capitalize shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full <?= $st === 'active' ? 'bg-emerald-500' : ($st === 'suspended' ? 'bg-rose-500' : 'bg-slate-400') ?>"></span>
                    <span><?= $st ?></span>
                  </span>
                </div>
                
                <?php if (!empty($ow['business_name'])): ?>
                  <div class="text-[11px] font-semibold text-slate-500 truncate" title="<?= htmlspecialchars($ow['business_name']) ?>">
                    <?= htmlspecialchars($ow['business_name']) ?>
                  </div>
                <?php endif; ?>

                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                  <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider <?= $ow['owner_type'] === 'organization' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600' ?>">
                    <?= $ow['owner_type'] ?>
                  </span>
                  <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold border <?= $kycBadge['class'] ?>">
                    <span class="material-symbols-outlined text-[10px]"><?= $kycBadge['icon'] ?></span>
                    <span><?= $kycBadge['text'] ?></span>
                  </span>
                  <span class="text-[10px] text-slate-400">#<?= $ow['id'] ?></span>
                </div>
              </div>
            </div>

            <!-- Contact & Location Row -->
            <div class="grid grid-cols-2 gap-2 text-[11px] bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
              <div>
                <span class="text-slate-400 block text-[10px]">Phone</span>
                <a href="tel:<?= htmlspecialchars($ow['phone']) ?>" class="font-semibold text-slate-700 hover:text-brand-700 truncate block">
                  <?= htmlspecialchars($ow['phone']) ?>
                </a>
              </div>
              <div>
                <span class="text-slate-400 block text-[10px]">Location</span>
                <span class="font-semibold text-slate-700 truncate block">
                  <?= htmlspecialchars($ow['city'] ?? 'India') ?>
                </span>
              </div>
              <div class="col-span-2">
                <span class="text-slate-400 block text-[10px]">Email</span>
                <a href="mailto:<?= htmlspecialchars($ow['email']) ?>" class="font-semibold text-slate-700 hover:text-brand-700 truncate block">
                  <?= htmlspecialchars($ow['email']) ?>
                </a>
              </div>
            </div>

            <!-- Stats & Quick Actions Row -->
            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
              <div class="flex items-center gap-3">
                <div>
                  <span class="text-[10px] text-slate-400 block">Estates</span>
                  <a href="<?= url('admin/owners/view/' . $ow['id'] . '?tab=properties') ?>" class="font-headline font-bold text-xs text-indigo-600 hover:underline">
                    <?= (int)$ow['properties_count'] ?> Properties
                  </a>
                </div>
                <div class="h-6 w-px bg-slate-100"></div>
                <div>
                  <span class="text-[10px] text-slate-400 block">Turnover</span>
                  <span class="font-headline font-bold text-xs text-slate-900">
                    ₹<?= number_format((float)($ow['gross_booking_value'] ?? 0)) ?>
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-1.5">
                <a href="<?= url('admin/owners/view/' . $ow['id']) ?>" class="p-2 rounded-xl bg-brand-50 text-brand-700 font-bold hover:bg-brand-100 transition-colors" title="View Dossier">
                  <span class="material-symbols-outlined text-base">badge</span>
                </a>
                <a href="<?= url('admin/owners/edit/' . $ow['id']) ?>" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors" title="Edit Profile">
                  <span class="material-symbols-outlined text-base">edit</span>
                </a>
                <button type="button" onclick="openStatusModal(<?= $ow['id'] ?>, '<?= htmlspecialchars(addslashes($ow['name'])) ?>', '<?= $st ?>')" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors" title="Change Status">
                  <span class="material-symbols-outlined text-base">tune</span>
                </button>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 p-6 space-y-2">
          <span class="material-symbols-outlined text-3xl text-slate-300">person_search</span>
          <div class="font-bold text-slate-700 text-sm">No Property Owners Found</div>
          <p class="text-xs text-slate-400">Try adjusting your filters.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════
         6. PAGINATION CONTROLS
         ══════════════════════════════════════════════ -->
    <?php if ($totalPages > 1): ?>
      <?php $queryArgs = $filters; ?>
      <div class="p-4 sm:p-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-xs text-slate-500 font-medium order-2 sm:order-1">
          Showing <?= $startItem ?> to <?= $endItem ?> of <?= number_format($totalRecords) ?> total owners
        </div>

        <div class="flex items-center gap-1.5 order-1 sm:order-2">
          
          <!-- Previous Page -->
          <?php if ($page > 1): ?>
            <?php $queryArgs['page'] = $page - 1; ?>
            <a href="<?= url('admin/owners?' . http_build_query($queryArgs)) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
              <span>Prev</span>
            </a>
          <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg border border-slate-100 text-slate-300 text-xs font-bold cursor-not-allowed">
              Prev
            </span>
          <?php endif; ?>

          <!-- Numeric Pages -->
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 1 && $p <= $page + 1)): ?>
              <?php $queryArgs['page'] = $p; ?>
              <a href="<?= url('admin/owners?' . http_build_query($queryArgs)) ?>" 
                 class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all <?= $p === $page ? 'bg-brand-700 text-white shadow-xs' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' ?>">
                <?= $p ?>
              </a>
            <?php elseif ($p == $page - 2 || $p == $page + 2): ?>
              <span class="px-1 text-slate-400">...</span>
            <?php endif; ?>
          <?php endfor; ?>

          <!-- Next Page -->
          <?php if ($page < $totalPages): ?>
            <?php $queryArgs['page'] = $page + 1; ?>
            <a href="<?= url('admin/owners?' . http_build_query($queryArgs)) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1">
              <span>Next</span>
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </a>
          <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg border border-slate-100 text-slate-300 text-xs font-bold cursor-not-allowed">
              Next
            </span>
          <?php endif; ?>

        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

<!-- ══════════════════════════════════════════════
     7. QUICK STATUS CHANGE MODAL
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
          <p class="text-[11px] text-slate-400" id="status-modal-owner-name">Select account state</p>
        </div>
      </div>
      <button type="button" onclick="closeStatusModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <form id="status-update-form" onsubmit="submitStatusUpdate(event)" class="space-y-4">
      <input type="hidden" name="id" id="status-modal-owner-id" value="">

      <div>
        <label for="status-modal-select" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Operational Status <span class="text-rose-500">*</span>
        </label>
        <select 
          name="status" 
          id="status-modal-select" 
          required 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3.5 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="active">Active (Fully operational &amp; assignable)</option>
          <option value="inactive">Inactive (Prevent new property assignments)</option>
          <option value="pending">Pending Verification (Under review)</option>
          <option value="suspended">Suspended (Temporarily restricted)</option>
        </select>
      </div>

      <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/70 text-[11px] text-amber-800 leading-relaxed">
        <strong>Consequence Notice:</strong> Deactivating or suspending an owner prevents new property assignments, but preserves existing properties and historical booking integrity.
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
     8. DELETE CONFIRMATION MODAL (Orphan Prevention)
     ══════════════════════════════════════════════ -->
<div id="delete-owner-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 text-center space-y-4">
    
    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center shadow-xs">
      <span class="material-symbols-outlined text-3xl">person_remove</span>
    </div>
    
    <div>
      <h3 class="font-headline font-bold text-lg text-slate-900">Delete Property Owner?</h3>
      <p class="text-xs text-slate-500 mt-1 leading-relaxed" id="delete-owner-msg">
        Are you sure you want to remove <strong id="delete-owner-name" class="text-slate-900">this owner</strong>?
      </p>
    </div>

    <!-- Warning block if properties exist -->
    <div id="delete-warning-box" class="hidden p-3 bg-rose-50 rounded-xl border border-rose-200 text-[11px] text-rose-800 text-left">
      <div class="font-bold mb-0.5 flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">warning</span> Action Blocked
      </div>
      <p id="delete-warning-text">This owner currently has active properties linked. Reassign or archive properties first.</p>
    </div>

    <form id="delete-owner-form" onsubmit="submitDeleteOwner(event)" class="pt-2 flex items-center gap-2 justify-center">
      <input type="hidden" name="id" id="delete-owner-id" value="">
      
      <button type="button" onclick="closeDeleteOwnerModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
        Cancel
      </button>

      <button type="submit" id="delete-owner-btn" class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors shadow-xs flex items-center justify-center gap-1">
        <span class="material-symbols-outlined text-sm">delete</span>
        <span>Delete</span>
      </button>
    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     9. INTERACTIVE JAVASCRIPT LOGIC
     ══════════════════════════════════════════════ -->
<script>
  // ── Status Modal Logic ──
  function openStatusModal(id, name, currentStatus) {
    document.getElementById('status-modal-owner-id').value = id;
    document.getElementById('status-modal-owner-name').textContent = name;
    document.getElementById('status-modal-select').value = currentStatus;
    document.getElementById('status-modal').classList.remove('hidden');
  }

  function closeStatusModal() {
    document.getElementById('status-modal').classList.add('hidden');
  }

  function submitStatusUpdate(e) {
    e.preventDefault();
    const btn = document.getElementById('status-submit-btn');
    const origText = btn.innerHTML;
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
      btn.disabled = false;
      btn.innerHTML = origText;
      if (data.success) {
        closeStatusModal();
        if (window.adminToast) {
          adminToast.success(data.message || 'Status updated successfully.');
        }
        setTimeout(() => location.reload(), 600);
      } else {
        if (window.adminToast) {
          adminToast.error(data.message || 'Failed to update status.');
        } else {
          alert(data.message || 'Failed to update status.');
        }
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = origText;
      if (window.adminToast) adminToast.error('Network error updating owner status.');
    });
  }

  // ── Delete Confirmation Dialog ──
  function confirmDeleteOwner(id, name, propCount) {
    document.getElementById('delete-owner-id').value = id;
    document.getElementById('delete-owner-name').textContent = name;
    
    const warnBox = document.getElementById('delete-warning-box');
    const warnText = document.getElementById('delete-warning-text');
    const delBtn = document.getElementById('delete-owner-btn');

    if (propCount > 0) {
      warnBox.classList.remove('hidden');
      warnText.textContent = `This owner has ${propCount} linked property/properties. Stayora orphan prevention prevents deleting this owner. Please reassign the properties first.`;
      delBtn.disabled = true;
      delBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      warnBox.classList.add('hidden');
      delBtn.disabled = false;
      delBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    document.getElementById('delete-owner-modal').classList.remove('hidden');
  }

  function closeDeleteOwnerModal() {
    document.getElementById('delete-owner-modal').classList.add('hidden');
  }

  function submitDeleteOwner(e) {
    e.preventDefault();
    const btn = document.getElementById('delete-owner-btn');
    if (btn.disabled) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Deleting...';

    const form = document.getElementById('delete-owner-form');
    const formData = new FormData(form);

    fetch('<?= url('admin/owners/delete') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeDeleteOwnerModal();
        if (window.adminToast) {
          adminToast.success(data.message || 'Owner deleted successfully.');
        }
        setTimeout(() => location.reload(), 600);
      } else {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-sm">delete</span> <span>Delete</span>';
        if (window.adminToast) {
          adminToast.error(data.message || 'Failed to delete owner.');
        } else {
          alert(data.message || 'Failed to delete owner.');
        }
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-sm">delete</span> <span>Delete</span>';
      if (window.adminToast) adminToast.error('Network error deleting owner.');
    });
  }
</script>

<!-- ── Shared Admin Layout Footer ── -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
