<?php
$pageTitle   = $pageTitle ?? "Booking & Financial Reports • Stayora Executive Console";
$currentPage = "bookings";
$headerTitle = "Financial & Stay Analytics";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

$summary   = $reports['summary'] ?? [];
$trends    = $reports['trends'] ?? [];
$props     = $reports['property_breakdown'] ?? [];
$farmhouses = $farmhouses ?? [];

$grossRevenue    = (float)($summary['total_gross'] ?? 0);
$cashCollected   = (float)($summary['total_collected'] ?? 0);
$outstandingDue  = (float)($summary['total_outstanding'] ?? 0);
$totalBookings   = (int)($summary['total_bookings'] ?? 0);
$totalCancelled  = (int)($summary['total_cancelled'] ?? 0);
$collectionRate  = $grossRevenue > 0 ? round(($cashCollected / $grossRevenue) * 100, 1) : 0;
?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER
     ═══════════════════════════════════════════════════════════════════════════ -->
<main class="flex-1 overflow-y-auto space-y-6 mt-0 bg-slate-50/50 pb-16">

  <!-- Breadcrumb & Top Bar -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1.5" aria-label="Breadcrumb">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-slate-600 transition-colors">Admin</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="<?= url('admin/bookings') ?>" class="hover:text-slate-600 transition-colors">Bookings</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-brand-700 font-bold">Analytics &amp; Reports</span>
      </nav>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-700/20">
          <span class="material-symbols-outlined text-xl">insights</span>
        </div>
        <div>
          <h1 class="font-headline font-bold text-xl sm:text-2xl text-slate-900 tracking-tight leading-none">
            Financial &amp; Occupancy Reports
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Revenue reconciliation, cashflow collection velocity, cancellation rates, and property yield metrics.
          </p>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs & Action Button -->
    <div class="flex flex-wrap items-center gap-2.5">
      <div class="inline-flex p-1 bg-white border border-slate-200 rounded-xl shadow-xs">
        <a href="<?= url('admin/bookings') ?>" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">
          <span class="material-symbols-outlined text-sm align-middle mr-1">table_rows</span>
          Bookings List
        </a>
        <a href="<?= url('admin/bookings/calendar') ?>" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">
          <span class="material-symbols-outlined text-sm align-middle mr-1">calendar_view_month</span>
          Calendar Matrix
        </a>
        <a href="<?= url('admin/bookings/reports') ?>" 
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-brand-700 text-white shadow-xs">
          <span class="material-symbols-outlined text-sm align-middle mr-1">insights</span>
          Reports
        </a>
      </div>

      <button type="button" onclick="window.print()" 
              class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-xs cursor-pointer">
        <span class="material-symbols-outlined text-base">print</span>
        <span>Print Statement</span>
      </button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       DATE RANGE & PROPERTY FILTER BAR
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs">
    <form method="GET" action="<?= url('admin/bookings/reports') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
      
      <!-- Start Date -->
      <div>
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" 
               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
      </div>

      <!-- End Date -->
      <div>
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" 
               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
      </div>

      <!-- Property Filter -->
      <div>
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Estate / Property</label>
        <select name="farmhouse_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
          <option value="">All Properties</option>
          <?php foreach ($farmhouses as $fh): ?>
            <option value="<?= $fh['id'] ?>" <?= ($farmId === (int)$fh['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($fh['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Submit & Clear -->
      <div class="flex items-center gap-2">
        <button type="submit" class="flex-1 py-2 px-4 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">filter_alt</span>
          <span>Generate</span>
        </button>
        <a href="<?= url('admin/bookings/reports') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all" title="Reset">
          <span class="material-symbols-outlined text-base">refresh</span>
        </a>
      </div>

      <!-- Preset Quick Pills -->
      <div class="hidden lg:flex items-center gap-1 justify-end">
        <?php
        $thisMonthStart = date('Y-m-01');
        $thisMonthEnd = date('Y-m-d');
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
        ?>
        <a href="<?= url('admin/bookings/reports?start_date=' . $thisMonthStart . '&end_date=' . $thisMonthEnd) ?>" 
           class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition-all">
          This Month
        </a>
        <a href="<?= url('admin/bookings/reports?start_date=' . $lastMonthStart . '&end_date=' . $lastMonthEnd) ?>" 
           class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition-all">
          Last Month
        </a>
      </div>

    </form>
  </div>

  <!-- ══════════════════════════════════════════════
       FINANCIAL KPI CARDS
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- Gross Revenue -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Gross Tariff Booked</span>
        <div class="w-9 h-9 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">trending_up</span>
        </div>
      </div>
      <div>
        <div class="text-2xl sm:text-3xl font-black text-slate-900 font-headline">
          ₹<?= number_format($grossRevenue) ?>
        </div>
        <p class="text-xs text-slate-400 mt-1">
          From <?= number_format($totalBookings - $totalCancelled) ?> active reservations
        </p>
      </div>
    </div>

    <!-- Cash Realized -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Cash Realized</span>
        <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
        </div>
      </div>
      <div>
        <div class="text-2xl sm:text-3xl font-black text-emerald-600 font-headline">
          ₹<?= number_format($cashCollected) ?>
        </div>
        <div class="flex items-center gap-1.5 mt-1">
          <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full" style="width: <?= min(100, $collectionRate) ?>%"></div>
          </div>
          <span class="text-[11px] font-extrabold text-emerald-700"><?= $collectionRate ?>%</span>
        </div>
      </div>
    </div>

    <!-- Outstanding Balance -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Balance Receivable</span>
        <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">pending_actions</span>
        </div>
      </div>
      <div>
        <div class="text-2xl sm:text-3xl font-black text-amber-700 font-headline">
          ₹<?= number_format($outstandingDue) ?>
        </div>
        <p class="text-xs text-slate-400 mt-1">
          Due at or prior to check-in
        </p>
      </div>
    </div>

    <!-- Cancellations & Rejections -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Cancellations</span>
        <div class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">cancel</span>
        </div>
      </div>
      <div>
        <div class="text-2xl sm:text-3xl font-black text-slate-900 font-headline">
          <?= number_format($totalCancelled) ?>
        </div>
        <p class="text-xs text-rose-600 font-semibold mt-1">
          <?= $totalBookings > 0 ? round(($totalCancelled / $totalBookings) * 100, 1) : 0 ?>% of total requests
        </p>
      </div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       PROPERTY PERFORMANCE TABLE
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-brand-700">holiday_village</span>
        <h3 class="font-headline font-bold text-base text-slate-900">Estate &amp; Property Revenue Yield</h3>
      </div>
      <span class="text-xs text-slate-400 font-semibold"><?= count($props) ?> Properties Analyzed</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 bg-slate-50/75">
            <th class="py-3 px-6">Property</th>
            <th class="py-3 px-4 text-center">Bookings Count</th>
            <th class="py-3 px-4 text-right">Gross Booked</th>
            <th class="py-3 px-4 text-right">Cash Collected</th>
            <th class="py-3 px-6 text-right">Collection Rate</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($props)): ?>
            <tr>
              <td colspan="5" class="py-8 text-center text-slate-400 italic">No revenue activity recorded for the selected date window.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($props as $p): ?>
              <?php
              $pGross = (float)($p['gross_revenue'] ?? 0);
              $pPaid  = (float)($p['collected_revenue'] ?? 0);
              $pRate  = $pGross > 0 ? round(($pPaid / $pGross) * 100) : 0;
              ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3.5 px-6 font-bold text-slate-900">
                  <?= htmlspecialchars($p['farmhouse_title']) ?>
                </td>
                <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                  <?= number_format((int)($p['bookings_count'] ?? 0)) ?>
                </td>
                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                  ₹<?= number_format($pGross) ?>
                </td>
                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                  ₹<?= number_format($pPaid) ?>
                </td>
                <td class="py-3.5 px-6 text-right">
                  <div class="inline-flex items-center gap-2 justify-end">
                    <span class="font-extrabold text-slate-800 text-[11px]"><?= $pRate ?>%</span>
                    <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                      <div class="h-full bg-brand-600 rounded-full" style="width: <?= min(100, $pRate) ?>%"></div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       DAILY REVENUE TREND SUMMARY
       ══════════════════════════════════════════════ -->
  <?php if (!empty($trends)): ?>
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">query_stats</span>
        <h3 class="font-headline font-bold text-base text-slate-900">Daily Revenue Timeline</h3>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
        <?php foreach ($trends as $tr): ?>
          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
              <?= date('d M', strtotime($tr['booking_date'])) ?>
            </span>
            <span class="font-mono font-black text-xs text-slate-900 block">
              ₹<?= number_format((float)$tr['daily_revenue']) ?>
            </span>
            <span class="text-[10px] font-semibold text-slate-500 block">
              <?= (int)$tr['bookings_count'] ?> stay<?= (int)$tr['bookings_count'] > 1 ? 's' : '' ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</main>

<?php 
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php'; 
?>
