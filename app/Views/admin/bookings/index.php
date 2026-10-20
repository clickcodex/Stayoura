<?php
$pageTitle   = $pageTitle ?? "Bookings & Stays Operations • Stayora Executive Console";
$currentPage = "bookings";
$headerTitle = "Bookings & Stay Management";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

$bookings     = $bookings ?? [];
$kpis         = $kpis ?? [];
$farmhouses   = $farmhouses ?? [];
$filters      = $filters ?? [];
$page         = $page ?? 1;
$totalPages   = $totalPages ?? 1;
$totalRecords = $totalRecords ?? 0;
$startItem    = $startItem ?? 0;
$endItem      = $endItem ?? 0;

// Status Badge Styling Helper
$statusStyles = [
    'pending'     => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500', 'icon' => 'schedule', 'label' => 'Pending Approval'],
    'approved'    => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500', 'icon' => 'verified', 'label' => 'Confirmed'],
    'confirmed'   => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500', 'icon' => 'verified', 'label' => 'Confirmed'],
    'checked_in'  => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500', 'icon' => 'hotel', 'label' => 'Active Stay (In-House)'],
    'checked_out' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500', 'icon' => 'task_alt', 'label' => 'Checked Out'],
    'completed'   => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500', 'icon' => 'task_alt', 'label' => 'Completed'],
    'cancelled'   => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500', 'icon' => 'cancel', 'label' => 'Cancelled'],
    'rejected'    => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'dot' => 'bg-red-500', 'icon' => 'block', 'label' => 'Rejected'],
    'no_show'     => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'dot' => 'bg-purple-500', 'icon' => 'person_off', 'label' => 'No-Show'],
    'expired'     => ['bg' => 'bg-gray-100 text-gray-600 border-gray-200', 'dot' => 'bg-gray-400', 'icon' => 'timer_off', 'label' => 'Expired Hold'],
];

// Active tab determination
$currentStatusFilter = $filters['status'] ?? 'all';
?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER
     ═══════════════════════════════════════════════════════════════════════════ -->
<main class="flex-1 overflow-y-auto space-y-6 mt-0 bg-slate-50/50 pb-16">

  <!-- Top Navigation & Action Bar -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1.5" aria-label="Breadcrumb">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-slate-600 transition-colors">Admin</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-brand-700 font-bold">Bookings &amp; Stays</span>
      </nav>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-700/20">
          <span class="material-symbols-outlined text-xl">calendar_month</span>
        </div>
        <div>
          <h1 class="font-headline font-bold text-xl sm:text-2xl text-slate-900 tracking-tight leading-none">
            Bookings &amp; Stay Operations
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Real-time reservations ledger, guest front-desk check-in/out, stay extensions, and financial reconciliation.
          </p>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs & Create Button -->
    <div class="flex flex-wrap items-center gap-2.5">
      <div class="inline-flex p-1 bg-white border border-slate-200 rounded-xl shadow-xs">
        <a href="<?= url('admin/bookings') ?>" 
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-brand-700 text-white shadow-xs">
          <span class="material-symbols-outlined text-sm align-middle mr-1">table_rows</span>
          Bookings List
        </a>
        <a href="<?= url('admin/bookings/calendar') ?>" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">
          <span class="material-symbols-outlined text-sm align-middle mr-1">calendar_view_month</span>
          Calendar Matrix
        </a>
        <a href="<?= url('admin/bookings/reports') ?>" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">
          <span class="material-symbols-outlined text-sm align-middle mr-1">insights</span>
          Reports
        </a>
      </div>

      <a href="<?= url('admin/bookings/create') ?>" 
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md shadow-brand-700/20 transition-all">
        <span class="material-symbols-outlined text-base">add_circle</span>
        <span>New Booking</span>
      </a>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       KPI CARDS GRID
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
    
    <!-- Total Bookings -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-500 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Bookings</span>
        <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">book_online</span>
        </div>
      </div>
      <div>
        <div class="text-2xl font-black text-slate-900 font-headline">
          <?= number_format($kpis['total_bookings'] ?? 0) ?>
        </div>
        <p class="text-[11px] text-slate-400 mt-0.5">
          ₹<?= number_format($kpis['gross_revenue'] ?? 0) ?> gross booked
        </p>
      </div>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between <?= ($kpis['pending_requests'] ?? 0) > 0 ? 'ring-2 ring-amber-400/50' : '' ?>">
      <div class="flex items-center justify-between text-slate-500 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pending Review</span>
        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">pending_actions</span>
        </div>
      </div>
      <div>
        <div class="text-2xl font-black text-amber-600 font-headline flex items-center gap-2">
          <?= number_format($kpis['pending_requests'] ?? 0) ?>
          <?php if (($kpis['pending_requests'] ?? 0) > 0): ?>
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
            </span>
          <?php endif; ?>
        </div>
        <p class="text-[11px] text-amber-700 font-medium mt-0.5">
          Awaiting confirmation
        </p>
      </div>
    </div>

    <!-- Active In-House Stays -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-500 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Stays</span>
        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">hotel</span>
        </div>
      </div>
      <div>
        <div class="text-2xl font-black text-emerald-600 font-headline">
          <?= number_format($kpis['active_stays'] ?? 0) ?>
        </div>
        <p class="text-[11px] text-slate-400 mt-0.5">
          Checked in guests in-house
        </p>
      </div>
    </div>

    <!-- Today's Movement -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-500 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Today's Moves</span>
        <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">swap_horiz</span>
        </div>
      </div>
      <div>
        <div class="text-base font-bold text-slate-900 font-headline flex items-center gap-2">
          <span class="text-emerald-700">↓ <?= (int)($kpis['today_check_ins'] ?? 0) ?> In</span>
          <span class="text-slate-300">•</span>
          <span class="text-rose-700">↑ <?= (int)($kpis['today_check_outs'] ?? 0) ?> Out</span>
        </div>
        <p class="text-[11px] text-slate-400 mt-0.5">
          <?= (int)($kpis['confirmed_upcoming'] ?? 0) ?> confirmed upcoming
        </p>
      </div>
    </div>

    <!-- Financial Health -->
    <div class="col-span-2 sm:col-span-2 lg:col-span-1 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-500 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Balance Due</span>
        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">payments</span>
        </div>
      </div>
      <div>
        <div class="text-xl font-black text-slate-900 font-headline">
          ₹<?= number_format($kpis['total_outstanding'] ?? 0) ?>
        </div>
        <p class="text-[11px] text-emerald-600 font-medium mt-0.5">
          ₹<?= number_format($kpis['total_collected'] ?? 0) ?> collected
        </p>
      </div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       STATUS FILTER TABS
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-1 overflow-x-auto no-scrollbar">
    <?php
    $statusTabs = [
        'all'         => ['label' => 'All Reservations', 'count' => $kpis['total_bookings'] ?? 0],
        'pending'     => ['label' => 'Pending', 'count' => $kpis['pending_requests'] ?? 0, 'alert' => true],
        'confirmed'   => ['label' => 'Confirmed', 'count' => $kpis['confirmed_upcoming'] ?? 0],
        'checked_in'  => ['label' => 'Active Stays', 'count' => $kpis['active_stays'] ?? 0],
        'checked_out' => ['label' => 'Checked Out', 'count' => $kpis['completed_stays'] ?? 0],
        'cancelled'   => ['label' => 'Cancelled / Rejected', 'count' => $kpis['cancelled_bookings'] ?? 0],
    ];
    ?>
    <?php foreach ($statusTabs as $tabKey => $tabInfo): ?>
      <?php 
      $isActive = ($currentStatusFilter === $tabKey); 
      // Keep other filters when switching tab
      $tabQuery = array_merge($filters, ['status' => $tabKey, 'page' => 1]);
      $tabUrl = url('admin/bookings') . '?' . http_build_query($tabQuery);
      ?>
      <a href="<?= $tabUrl ?>" 
         class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?= $isActive ? 'bg-brand-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
        <span><?= $tabInfo['label'] ?></span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold <?= $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' ?>">
          <?= number_format($tabInfo['count']) ?>
        </span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ══════════════════════════════════════════════
       ADVANCED FILTER & SEARCH FORM
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
    <form method="GET" action="<?= url('admin/bookings') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
      <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatusFilter) ?>">

      <!-- Search Query -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search Booking / Guest</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-lg">search</span>
          <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                 placeholder="Ref #, Guest Name, Phone, Email..." 
                 class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>
      </div>

      <!-- Property Dropdown -->
      <div>
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Property</label>
        <select name="farmhouse_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
          <option value="">All Properties</option>
          <?php foreach ($farmhouses as $fh): ?>
            <option value="<?= $fh['id'] ?>" <?= (!empty($filters['farmhouse_id']) && (int)$filters['farmhouse_id'] === (int)$fh['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($fh['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Payment Status -->
      <div>
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Status</label>
        <select name="payment_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
          <option value="all" <?= ($filters['payment_status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Payments</option>
          <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Fully Paid (₹0 Due)</option>
          <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partially Paid</option>
          <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Unpaid / Pending</option>
        </select>
      </div>

      <!-- Check-In Date Range -->
      <div>
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Check-in From</label>
        <input type="date" name="check_in_from" value="<?= htmlspecialchars($filters['check_in_from'] ?? '') ?>" 
               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
      </div>

      <!-- Actions -->
      <div class="flex items-end gap-2">
        <button type="submit" class="flex-1 px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">filter_list</span>
          <span>Apply</span>
        </button>
        <a href="<?= url('admin/bookings?status=' . $currentStatusFilter) ?>" 
           class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-all flex items-center justify-center" title="Reset Filters">
          <span class="material-symbols-outlined text-base">refresh</span>
        </a>
      </div>
    </form>
  </div>

  <!-- ══════════════════════════════════════════════
       BOOKINGS DATA DISPLAY (DESKTOP TABLE & MOBILE CARDS)
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    
    <!-- Header Summary Bar -->
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-slate-500">Showing</span>
        <span class="text-xs font-extrabold text-slate-900"><?= $startItem ?> - <?= $endItem ?></span>
        <span class="text-xs font-bold text-slate-500">of <?= number_format($totalRecords) ?> reservations</span>
      </div>
      
      <!-- Sort Options -->
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-slate-400">Sort By:</span>
        <select onchange="window.location.href=this.value" class="text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg px-2.5 py-1 focus:outline-none">
          <?php
          $sortOptions = [
              'newest'          => 'Newest First',
              'oldest'          => 'Oldest First',
              'checkin_soonest' => 'Check-in (Earliest)',
              'amount_high'     => 'Amount (High to Low)',
              'amount_low'      => 'Amount (Low to High)',
          ];
          foreach ($sortOptions as $sKey => $sVal):
              $sQuery = array_merge($filters, ['sort' => $sKey]);
              $sUrl = url('admin/bookings') . '?' . http_build_query($sQuery);
          ?>
            <option value="<?= $sUrl ?>" <?= ($filters['sort'] ?? 'newest') === $sKey ? 'selected' : '' ?>><?= $sVal ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <?php if (empty($bookings)): ?>
      <!-- Empty State -->
      <div class="p-12 text-center">
        <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
          <span class="material-symbols-outlined text-3xl">event_busy</span>
        </div>
        <h3 class="text-base font-bold text-slate-900 font-headline">No Reservations Found</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
          No bookings match the selected status tab or filters. Try adjusting your search query, property selector, or date range.
        </p>
        <div class="mt-4">
          <a href="<?= url('admin/bookings') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
            <span class="material-symbols-outlined text-sm">refresh</span>
            <span>Clear All Filters</span>
          </a>
        </div>
      </div>
    <?php else: ?>

      <!-- ── Desktop Table (Hidden on Mobile) ── -->
      <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 bg-slate-50/75">
              <th class="py-3 px-5">Booking Ref</th>
              <th class="py-3 px-4">Property &amp; Mode</th>
              <th class="py-3 px-4">Guest Info</th>
              <th class="py-3 px-4">Stay Dates</th>
              <th class="py-3 px-4">Financial Ledger</th>
              <th class="py-3 px-4 text-center">Stay Status</th>
              <th class="py-3 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <?php foreach ($bookings as $b): ?>
              <?php
              $st = $b['status'] ?? 'pending';
              $style = $statusStyles[$st] ?? $statusStyles['pending'];
              $refCode = $b['booking_reference'] ?? ('STAY-BK-' . str_pad($b['id'], 5, '0', STR_PAD_LEFT));
              $nights = max(1, (int)round((strtotime($b['check_out']) - strtotime($b['check_in'])) / 86400));
              $totalPrice = (float)($b['price'] ?? 0);
              $paid = (float)($b['amount_paid'] ?? 0);
              $balance = (float)($b['balance_amount'] ?? max(0, $totalPrice - $paid));
              $isFullyPaid = ($balance <= 0 && $totalPrice > 0);
              ?>
              <tr class="hover:bg-slate-50/80 transition-colors group">
                
                <!-- Ref & Timestamp -->
                <td class="py-3.5 px-5">
                  <a href="<?= url('admin/bookings/view/' . $b['id']) ?>" class="font-mono font-bold text-brand-700 hover:text-brand-900 hover:underline flex items-center gap-1">
                    <span>#<?= htmlspecialchars($refCode) ?></span>
                  </a>
                  <span class="text-[10.5px] text-slate-400 block mt-0.5">
                    <?= date('d M Y, h:i A', strtotime($b['created_at'])) ?>
                  </span>
                </td>

                <!-- Property & Booking Mode -->
                <td class="py-3.5 px-4 max-w-xs">
                  <div class="font-bold text-slate-900 truncate">
                    <?= htmlspecialchars($b['farmhouse_title'] ?? 'Estate #' . $b['farmhouse_id']) ?>
                  </div>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <?php if (($b['booking_type'] ?? '') === 'complete'): ?>
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Entire Property
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200">
                        <?= (int)($b['rooms'] ?? 1) ?>x <?= htmlspecialchars($b['room_type_name'] ?? 'Room') ?>
                      </span>
                      <?php if (!empty($b['assigned_room_number'])): ?>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1 py-0.5 rounded">
                          Rm #<?= htmlspecialchars($b['assigned_room_number']) ?>
                        </span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Guest Info -->
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 flex items-center gap-1.5">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px]">
                      <?= strtoupper(substr($b['name'] ?? 'G', 0, 1)) ?>
                    </span>
                    <span><?= htmlspecialchars($b['name'] ?? 'Guest') ?></span>
                  </div>
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    <?= htmlspecialchars($b['mobile'] ?? '') ?>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-600 font-semibold"><?= (int)($b['guests'] ?? 1) ?> Guests</span>
                  </div>
                </td>

                <!-- Dates & Nights -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-semibold text-slate-800">
                    <?= date('d M', strtotime($b['check_in'])) ?> → <?= date('d M Y', strtotime($b['check_out'])) ?>
                  </div>
                  <span class="inline-flex items-center gap-1 text-[10.5px] font-bold text-slate-400">
                    <span class="material-symbols-outlined text-[12px]">night_shelter</span>
                    <span><?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?></span>
                  </span>
                </td>

                <!-- Financial Ledger -->
                <td class="py-3.5 px-4">
                  <div class="font-black text-slate-900">
                    ₹<?= number_format($totalPrice) ?>
                  </div>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <?php if ($isFullyPaid): ?>
                      <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="material-symbols-outlined text-[11px]">check</span> Paid
                      </span>
                    <?php elseif ($paid > 0): ?>
                      <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                        Due: ₹<?= number_format($balance) ?>
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                        Unpaid
                      </span>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Stay Status Badge -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold border <?= $style['bg'] ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $style['dot'] ?>"></span>
                    <span><?= $style['label'] ?></span>
                  </span>
                </td>

                <!-- Action Dropdown & Buttons -->
                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1 justify-end">
                    
                    <!-- Quick View Details -->
                    <a href="<?= url('admin/bookings/view/' . $b['id']) ?>" 
                       class="p-1.5 rounded-lg text-slate-500 hover:text-brand-700 hover:bg-slate-100 transition-colors" 
                       title="View 360° Booking Dossier">
                      <span class="material-symbols-outlined text-[19px]">visibility</span>
                    </a>

                    <!-- Front Desk Quick Action based on status -->
                    <?php if (in_array($st, ['confirmed', 'approved'])): ?>
                      <button type="button" 
                              onclick="openCheckInModal(<?= htmlspecialchars(json_encode($b)) ?>)"
                              class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition-colors flex items-center gap-1"
                              title="Check-In Guest">
                        <span class="material-symbols-outlined text-sm">hotel</span>
                        <span>Check-In</span>
                      </button>
                    <?php elseif ($st === 'checked_in'): ?>
                      <button type="button" 
                              onclick="openCheckOutModal(<?= htmlspecialchars(json_encode($b)) ?>)"
                              class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-bold text-[11px] shadow-xs transition-colors flex items-center gap-1"
                              title="Check-Out Guest">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        <span>Check-Out</span>
                      </button>
                    <?php elseif ($st === 'pending'): ?>
                      <button type="button" 
                              onclick="openStatusModal(<?= $b['id'] ?>, 'confirmed', '<?= htmlspecialchars($refCode) ?>')"
                              class="px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] shadow-xs transition-colors flex items-center gap-1"
                              title="Confirm Booking">
                        <span class="material-symbols-outlined text-sm">check</span>
                        <span>Confirm</span>
                      </button>
                    <?php endif; ?>

                    <!-- Action Menu Trigger -->
                    <div class="relative inline-block text-left" id="action-dropdown-<?= $b['id'] ?>">
                      <button type="button" 
                              onclick="toggleActionMenu(<?= $b['id'] ?>)" 
                              class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[19px]">more_vert</span>
                      </button>
                      
                      <!-- Floating Menu -->
                      <div id="menu-box-<?= $b['id'] ?>" class="hidden absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-xl border border-slate-200 z-30 py-1.5 text-left text-xs font-semibold">
                        <a href="<?= url('admin/bookings/view/' . $b['id']) ?>" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50">
                          <span class="material-symbols-outlined text-base text-slate-400">info</span>
                          <span>Full Stay Details</span>
                        </a>

                        <button type="button" onclick="openPaymentModal(<?= htmlspecialchars(json_encode($b)) ?>)" class="w-full flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 text-left">
                          <span class="material-symbols-outlined text-base text-emerald-500">payments</span>
                          <span>Record Payment</span>
                        </button>

                        <a href="<?= url('admin/bookings/invoice/' . $b['id']) ?>" target="_blank" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50">
                          <span class="material-symbols-outlined text-base text-slate-400">receipt_long</span>
                          <span>Print Voucher / Invoice</span>
                        </a>

                        <div class="my-1 border-t border-slate-100"></div>

                        <button type="button" onclick="openStatusModal(<?= $b['id'] ?>, 'cancelled', '<?= htmlspecialchars($refCode) ?>')" class="w-full flex items-center gap-2 px-3 py-2 text-rose-600 hover:bg-rose-50 text-left">
                          <span class="material-symbols-outlined text-base">cancel</span>
                          <span>Cancel Booking</span>
                        </button>

                        <button type="button" onclick="openStatusModal(<?= $b['id'] ?>, 'no_show', '<?= htmlspecialchars($refCode) ?>')" class="w-full flex items-center gap-2 px-3 py-2 text-purple-600 hover:bg-purple-50 text-left">
                          <span class="material-symbols-outlined text-base">person_off</span>
                          <span>Mark as No-Show</span>
                        </button>
                      </div>
                    </div>

                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ── Mobile Card View (Visible only on < lg screens) ── -->
      <div class="block lg:hidden divide-y divide-slate-100">
        <?php foreach ($bookings as $b): ?>
          <?php
          $st = $b['status'] ?? 'pending';
          $style = $statusStyles[$st] ?? $statusStyles['pending'];
          $refCode = $b['booking_reference'] ?? ('STAY-BK-' . str_pad($b['id'], 5, '0', STR_PAD_LEFT));
          $nights = max(1, (int)round((strtotime($b['check_out']) - strtotime($b['check_in'])) / 86400));
          $totalPrice = (float)($b['price'] ?? 0);
          $paid = (float)($b['amount_paid'] ?? 0);
          $balance = (float)($b['balance_amount'] ?? max(0, $totalPrice - $paid));
          $isFullyPaid = ($balance <= 0 && $totalPrice > 0);
          ?>
          <div class="p-4 space-y-3">
            
            <!-- Card Header: Ref & Status -->
            <div class="flex items-start justify-between gap-2">
              <div>
                <a href="<?= url('admin/bookings/view/' . $b['id']) ?>" class="font-mono font-bold text-sm text-brand-700">
                  #<?= htmlspecialchars($refCode) ?>
                </a>
                <span class="text-[11px] text-slate-400 block">
                  <?= date('d M Y, h:i A', strtotime($b['created_at'])) ?>
                </span>
              </div>
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold border <?= $style['bg'] ?>">
                <span class="w-1.5 h-1.5 rounded-full <?= $style['dot'] ?>"></span>
                <span><?= $style['label'] ?></span>
              </span>
            </div>

            <!-- Property & Room -->
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
              <div class="font-bold text-slate-900 text-xs">
                <?= htmlspecialchars($b['farmhouse_title'] ?? 'Estate #' . $b['farmhouse_id']) ?>
              </div>
              <div class="text-[11px] text-slate-500 mt-0.5">
                <?= ($b['booking_type'] ?? '') === 'complete' ? 'Entire Farmhouse Estate' : ((int)($b['rooms'] ?? 1) . 'x ' . htmlspecialchars($b['room_type_name'] ?? 'Room')) ?>
                <?php if (!empty($b['assigned_room_number'])): ?>
                  • <span class="font-bold text-slate-700">Rm #<?= htmlspecialchars($b['assigned_room_number']) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Guest & Stay Dates Grid -->
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Guest</span>
                <span class="font-bold text-slate-800"><?= htmlspecialchars($b['name'] ?? 'Guest') ?></span>
                <span class="text-[11px] text-slate-500 block"><?= htmlspecialchars($b['mobile'] ?? '') ?></span>
              </div>
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Stay (<?= $nights ?>N)</span>
                <span class="font-bold text-slate-800"><?= date('d M', strtotime($b['check_in'])) ?> → <?= date('d M', strtotime($b['check_out'])) ?></span>
                <span class="text-[11px] text-slate-500 block"><?= (int)($b['guests'] ?? 1) ?> Guests</span>
              </div>
            </div>

            <!-- Financials & Quick Action -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total / Balance</span>
                <div class="text-xs font-black text-slate-900">
                  ₹<?= number_format($totalPrice) ?>
                  <?php if (!$isFullyPaid): ?>
                    <span class="text-[10.5px] font-bold text-amber-600">(Due: ₹<?= number_format($balance) ?>)</span>
                  <?php else: ?>
                    <span class="text-[10.5px] font-bold text-emerald-600">(Paid)</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="flex items-center gap-1.5">
                <a href="<?= url('admin/bookings/view/' . $b['id']) ?>" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                  View
                </a>
                <?php if (in_array($st, ['confirmed', 'approved'])): ?>
                  <button type="button" onclick="openCheckInModal(<?= htmlspecialchars(json_encode($b)) ?>)" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs">
                    Check-In
                  </button>
                <?php elseif ($st === 'checked_in'): ?>
                  <button type="button" onclick="openCheckOutModal(<?= htmlspecialchars(json_encode($b)) ?>)" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs">
                    Check-Out
                  </button>
                <?php endif; ?>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

    <!-- ══════════════════════════════════════════════
         PAGINATION CONTROLS
         ══════════════════════════════════════════════ -->
    <?php if ($totalPages > 1): ?>
      <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/30">
        <div class="text-xs text-slate-500 font-semibold">
          Page <span class="font-bold text-slate-800"><?= $page ?></span> of <span class="font-bold text-slate-800"><?= $totalPages ?></span>
        </div>
        <div class="inline-flex items-center gap-1">
          <?php if ($page > 1): ?>
            <a href="<?= url('admin/bookings?' . http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all flex items-center gap-1">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
              <span>Prev</span>
            </a>
          <?php endif; ?>

          <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
            <a href="<?= url('admin/bookings?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $p === $page ? 'bg-brand-700 text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' ?>">
              <?= $p ?>
            </a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="<?= url('admin/bookings?' . http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all flex items-center gap-1">
              <span>Next</span>
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     FRONT-DESK OPERATIONAL MODALS
     ═══════════════════════════════════════════════════════════════════════════ -->

<!-- 1. CHECK-IN MODAL -->
<div id="checkInModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeCheckInModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
      
      <!-- Modal Header -->
      <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">hotel</span>
          </div>
          <div>
            <h3 class="font-headline font-bold text-lg leading-tight">Digital Guest Check-In</h3>
            <p class="text-xs text-emerald-100" id="checkInRefSubtitle">Assign room and verify identification</p>
          </div>
        </div>
        <button type="button" onclick="closeCheckInModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Form Body -->
      <form id="checkInForm" onsubmit="handleCheckInSubmit(event)" enctype="multipart/form-data" class="p-6 space-y-4">
        <input type="hidden" name="id" id="checkInBookingId">

        <!-- Booking Details Banner -->
        <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-100 text-xs text-emerald-900 flex items-center justify-between">
          <div>
            <span class="font-bold block" id="checkInGuestName">Guest Name</span>
            <span class="text-[11px] text-emerald-700" id="checkInPropertySummary">Property • Dates</span>
          </div>
          <div class="text-right">
            <span class="text-[10px] uppercase font-bold text-emerald-600 block">Balance Due</span>
            <span class="font-mono font-bold text-sm text-emerald-900" id="checkInBalanceDue">₹0</span>
          </div>
        </div>

        <!-- Room Assignment -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">
            Assigned Room / Unit Number <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="assigned_room_number" id="checkInRoomNumber" required 
                 placeholder="e.g., Suite 101, Villa East, Rm 2B" 
                 class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>

        <!-- ID Verification -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">ID Document Type</label>
            <select name="guest_id_type" id="checkInIdType" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
              <option value="Aadhaar Card">Aadhaar Card</option>
              <option value="Driving License">Driving License</option>
              <option value="Passport">Passport</option>
              <option value="Voter ID">Voter ID</option>
              <option value="PAN Card">PAN Card</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">ID Document Number</label>
            <input type="text" name="guest_id_number" id="checkInIdNumber" placeholder="XXXX-XXXX-XXXX" 
                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
          </div>
        </div>

        <!-- Optional ID File Attachment -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Upload ID Proof (Optional photo/scan)</label>
          <input type="file" name="guest_id_document" accept="image/*,.pdf" 
                 class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
        </div>

        <!-- Check-in Notes -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Front Desk Notes</label>
          <textarea name="notes" rows="2" placeholder="Luggage stored, special arrival requests noted..." 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"></textarea>
        </div>

        <!-- Modal Footer -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
          <button type="button" onclick="closeCheckInModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
            Cancel
          </button>
          <button type="submit" id="btnSubmitCheckIn" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <span>Complete Check-In</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- 2. CHECK-OUT MODAL -->
<div id="checkOutModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeCheckOutModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
      
      <!-- Modal Header -->
      <div class="px-6 py-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">logout</span>
          </div>
          <div>
            <h3 class="font-headline font-bold text-lg leading-tight">Digital Guest Check-Out</h3>
            <p class="text-xs text-slate-300" id="checkOutRefSubtitle">Folio reconciliation and room release</p>
          </div>
        </div>
        <button type="button" onclick="closeCheckOutModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Form Body -->
      <form id="checkOutForm" onsubmit="handleCheckOutSubmit(event)" class="p-6 space-y-4">
        <input type="hidden" name="id" id="checkOutBookingId">

        <!-- Financial Summary Card -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-semibold">Total Stay Tariff:</span>
            <span class="font-mono font-bold text-slate-900" id="checkOutTotalTariff">₹0</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-semibold">Total Paid to Date:</span>
            <span class="font-mono font-bold text-emerald-600" id="checkOutTotalPaid">₹0</span>
          </div>
          <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-900">Outstanding Balance:</span>
            <span class="font-mono font-black text-sm" id="checkOutOutstandingBalance">₹0</span>
          </div>
        </div>

        <!-- Deposit Status -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Security Deposit Status</label>
          <select name="deposit_status" id="checkOutDepositStatus" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:bg-white">
            <option value="refunded">100% Refunded to Guest (No damages)</option>
            <option value="retained">Retained / Deducted for Damages/Extras</option>
            <option value="not_applicable">Not Applicable / No Deposit Taken</option>
          </select>
        </div>

        <!-- Check-out Notes -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Departure Notes / Inspection Remarks</label>
          <textarea name="notes" rows="2" placeholder="Keys returned, property inspected, guest satisfied..." 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:bg-white"></textarea>
        </div>

        <!-- Modal Footer -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
          <button type="button" onclick="closeCheckOutModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
            Cancel
          </button>
          <button type="submit" id="btnSubmitCheckOut" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">task_alt</span>
            <span>Settle &amp; Complete Check-Out</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- 3. RECORD PAYMENT MODAL -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closePaymentModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
      
      <!-- Modal Header -->
      <div class="px-6 py-5 bg-gradient-to-r from-brand-700 to-brand-800 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">payments</span>
          </div>
          <div>
            <h3 class="font-headline font-bold text-lg leading-tight">Record Payment Installment</h3>
            <p class="text-xs text-brand-100" id="paymentRefSubtitle">Post payment to booking folio</p>
          </div>
        </div>
        <button type="button" onclick="closePaymentModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Form Body -->
      <form id="paymentForm" onsubmit="handlePaymentSubmit(event)" enctype="multipart/form-data" class="p-6 space-y-4">
        <input type="hidden" name="id" id="paymentBookingId">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Amount -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">
              Payment Amount (₹) <span class="text-rose-500">*</span>
            </label>
            <input type="number" step="0.01" name="amount" id="paymentAmountInput" required min="1" 
                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method</label>
            <select name="payment_method" id="paymentMethodInput" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
              <option value="cash">Cash at Property</option>
              <option value="upi">UPI / QR Code</option>
              <option value="card">Credit / Debit Card</option>
              <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
              <option value="pay_at_property">Pay at Property</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Payment Type -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Installment Category</label>
            <select name="payment_type" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
              <option value="balance">Remaining Balance Settlement</option>
              <option value="advance">Advance Booking Deposit</option>
              <option value="security_deposit">Security Deposit</option>
              <option value="incidental">Extra / Incidental Charges</option>
            </select>
          </div>

          <!-- UTR / Ref Number -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">UTR / Transaction Ref</label>
            <input type="text" name="utr_number" placeholder="Optional transaction ID" 
                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
          </div>
        </div>

        <!-- Receipt Proof -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Upload Receipt / Screenshot (Optional)</label>
          <input type="file" name="payment_proof" accept="image/*,.pdf" 
                 class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Internal Note</label>
          <input type="text" name="notes" placeholder="Collected at front desk, receipt issued..." 
                 class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
        </div>

        <!-- Modal Footer -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
          <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
            Cancel
          </button>
          <button type="submit" id="btnSubmitPayment" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-md shadow-brand-700/20 transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">save</span>
            <span>Record Payment</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- 4. STATUS CHANGE MODAL -->
<div id="statusModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeStatusModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
      
      <!-- Modal Header -->
      <div class="px-6 py-5 bg-slate-900 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">sync_alt</span>
          </div>
          <div>
            <h3 class="font-headline font-bold text-lg leading-tight">Update Stay Status</h3>
            <p class="text-xs text-slate-300" id="statusModalSubtitle">Transition reservation state</p>
          </div>
        </div>
        <button type="button" onclick="closeStatusModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Form Body -->
      <form id="statusForm" onsubmit="handleStatusSubmit(event)" class="p-6 space-y-4">
        <input type="hidden" name="id" id="statusBookingId">

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Select New Status</label>
          <select name="status" id="statusSelectInput" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white">
            <option value="confirmed">Confirmed (Approved)</option>
            <option value="cancelled">Cancelled by Guest / Admin</option>
            <option value="rejected">Rejected (Property Unavailable)</option>
            <option value="no_show">Mark as No-Show</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Notes</label>
          <textarea name="notes" id="statusNotesInput" rows="3" required placeholder="Provide reason or operational notes for this status change..." 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white"></textarea>
        </div>

        <!-- Modal Footer -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
          <button type="button" onclick="closeStatusModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
            Cancel
          </button>
          <button type="submit" id="btnSubmitStatus" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">check</span>
            <span>Update Status</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT INTERACTIVITY FOR MODALS & AJAX
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
  // Close open action menus when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="action-dropdown-"]')) {
      document.querySelectorAll('[id^="menu-box-"]').forEach(el => el.classList.add('hidden'));
    }
  });

  function toggleActionMenu(id) {
    const menu = document.getElementById('menu-box-' + id);
    const isHidden = menu.classList.contains('hidden');
    document.querySelectorAll('[id^="menu-box-"]').forEach(el => el.classList.add('hidden'));
    if (isHidden) {
      menu.classList.remove('hidden');
    }
  }

  // ── CHECK-IN MODAL LOGIC ──
  function openCheckInModal(booking) {
    document.getElementById('checkInBookingId').value = booking.id;
    const ref = booking.booking_reference || ('STAY-BK-' + String(booking.id).padStart(5, '0'));
    document.getElementById('checkInRefSubtitle').innerText = 'Booking #' + ref;
    document.getElementById('checkInGuestName').innerText = booking.name || 'Guest';
    document.getElementById('checkInPropertySummary').innerText = (booking.farmhouse_title || 'Estate') + ' • ' + booking.check_in + ' to ' + booking.check_out;
    
    const balance = Math.max(0, parseFloat(booking.balance_amount || (booking.price - (booking.amount_paid || 0))));
    document.getElementById('checkInBalanceDue').innerText = '₹' + balance.toLocaleString('en-IN');
    
    document.getElementById('checkInRoomNumber').value = booking.assigned_room_number || (booking.booking_type === 'complete' ? 'Entire Estate' : (booking.room_type_name || 'Room 1'));
    if (booking.guest_id_type) document.getElementById('checkInIdType').value = booking.guest_id_type;
    if (booking.guest_id_number) document.getElementById('checkInIdNumber').value = booking.guest_id_number;

    document.getElementById('checkInModal').classList.remove('hidden');
  }

  function closeCheckInModal() {
    document.getElementById('checkInModal').classList.add('hidden');
  }

  function handleCheckInSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('checkInForm');
    const formData = new FormData(form);
    const btn = document.getElementById('btnSubmitCheckIn');

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Processing...';

    fetch('<?= url('admin/bookings/check-in') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">check_circle</span> Complete Check-In';
      if (data.success) {
        showToast(data.message || 'Guest checked in successfully!', 'success');
        closeCheckInModal();
        setTimeout(() => window.location.reload(), 400);
      } else {
        showToast(data.message || 'Failed to check in guest', 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">check_circle</span> Complete Check-In';
      showToast('A network error occurred while checking in.', 'error');
    });
  }

  // ── CHECK-OUT MODAL LOGIC ──
  function openCheckOutModal(booking) {
    document.getElementById('checkOutBookingId').value = booking.id;
    const ref = booking.booking_reference || ('STAY-BK-' + String(booking.id).padStart(5, '0'));
    document.getElementById('checkOutRefSubtitle').innerText = 'Booking #' + ref;

    const total = parseFloat(booking.price || 0);
    const paid = parseFloat(booking.amount_paid || 0);
    const balance = Math.max(0, parseFloat(booking.balance_amount || (total - paid)));

    document.getElementById('checkOutTotalTariff').innerText = '₹' + total.toLocaleString('en-IN');
    document.getElementById('checkOutTotalPaid').innerText = '₹' + paid.toLocaleString('en-IN');
    const balEl = document.getElementById('checkOutOutstandingBalance');
    balEl.innerText = '₹' + balance.toLocaleString('en-IN');
    balEl.className = balance > 0 ? 'font-mono font-black text-sm text-rose-600' : 'font-mono font-black text-sm text-emerald-600';

    document.getElementById('checkOutModal').classList.remove('hidden');
  }

  function closeCheckOutModal() {
    document.getElementById('checkOutModal').classList.add('hidden');
  }

  function handleCheckOutSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('checkOutForm');
    const formData = new FormData(form);
    const btn = document.getElementById('btnSubmitCheckOut');

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Processing...';

    fetch('<?= url('admin/bookings/check-out') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">task_alt</span> Settle & Complete Check-Out';
      if (data.success) {
        showToast(data.message || 'Guest checked out successfully!', 'success');
        closeCheckOutModal();
        setTimeout(() => window.location.reload(), 400);
      } else {
        showToast(data.message || 'Failed to check out guest', 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">task_alt</span> Settle & Complete Check-Out';
      showToast('A network error occurred while checking out.', 'error');
    });
  }

  // ── RECORD PAYMENT MODAL LOGIC ──
  function openPaymentModal(booking) {
    document.getElementById('paymentBookingId').value = booking.id;
    const ref = booking.booking_reference || ('STAY-BK-' + String(booking.id).padStart(5, '0'));
    document.getElementById('paymentRefSubtitle').innerText = 'Folio #' + ref;
    
    const balance = Math.max(0, parseFloat(booking.balance_amount || (booking.price - (booking.amount_paid || 0))));
    document.getElementById('paymentAmountInput').value = balance > 0 ? balance : '';

    document.getElementById('paymentModal').classList.remove('hidden');
  }

  function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
  }

  function handlePaymentSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    const btn = document.getElementById('btnSubmitPayment');

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Recording...';

    fetch('<?= url('admin/bookings/payment') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">save</span> Record Payment';
      if (data.success) {
        showToast(data.message || 'Payment recorded successfully!', 'success');
        closePaymentModal();
        setTimeout(() => window.location.reload(), 400);
      } else {
        showToast(data.message || 'Failed to record payment', 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">save</span> Record Payment';
      showToast('A network error occurred while posting payment.', 'error');
    });
  }

  // ── STATUS TRANSITION MODAL LOGIC ──
  function openStatusModal(id, targetStatus, refCode) {
    document.getElementById('statusBookingId').value = id;
    document.getElementById('statusModalSubtitle').innerText = 'Booking #' + refCode;
    if (targetStatus) {
      document.getElementById('statusSelectInput').value = targetStatus;
    }
    document.getElementById('statusNotesInput').value = '';
    document.getElementById('statusModal').classList.remove('hidden');
  }

  function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
  }

  function handleStatusSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('statusForm');
    const formData = new FormData(form);
    const btn = document.getElementById('btnSubmitStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Updating...';

    fetch('<?= url('admin/bookings/status') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">check</span> Update Status';
      if (data.success) {
        showToast(data.message || 'Booking status updated successfully!', 'success');
        closeStatusModal();
        setTimeout(() => window.location.reload(), 400);
      } else {
        showToast(data.message || 'Failed to update booking status', 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">check</span> Update Status';
      showToast('A network error occurred while updating status.', 'error');
    });
  }

  // Escape key handler
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeCheckInModal();
      closeCheckOutModal();
      closePaymentModal();
      closeStatusModal();
    }
  });
</script>

<?php 
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php'; 
?>
