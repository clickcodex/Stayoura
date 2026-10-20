<?php
$pageTitle   = "Admin Dashboard • Stayora Executive Console";
$currentPage = "dashboard";
$headerTitle = "Executive Dashboard";

// ── Shared Admin Layout Header (HTML Head, Sidebar, Top Navigation) ──
require_once __DIR__ . '/layouts/header.php';
?>

<!-- ══════════════════════════════════════════════
     1. WELCOME HERO BANNER
     ══════════════════════════════════════════════ -->
<section class="relative rounded-3xl bg-gradient-to-r from-slate-900 via-brand-900 to-slate-900 text-white p-6 sm:p-8 overflow-hidden shadow-xl shadow-slate-900/10 border border-slate-800">
  <!-- Abstract Glow Orb -->
  <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-brand-500/20 blur-3xl pointer-events-none"></div>
  <div class="absolute right-1/3 -bottom-20 w-64 h-64 rounded-full bg-sky-500/15 blur-3xl pointer-events-none"></div>

  <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="space-y-2 max-w-2xl">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-brand-200 text-xs font-bold border border-white/10">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span>All Stayora Platform Services Active &amp; Operational</span>
      </div>
      <h2 class="font-headline font-black text-2xl sm:text-3xl text-white tracking-tight">
        Welcome to Executive Operations, <?= $adminName ?>!
      </h2>
      <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
        Real-time platform metrics, farmhouse approvals, booking traffic, and guest management across India's top luxury private getaways.
      </p>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex flex-wrap items-center gap-3 shrink-0">
      <a href="<?= url('admin/properties') ?>" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all shadow-md flex items-center gap-2">
        <span class="material-symbols-outlined text-base">villa</span>
        <span>Manage Properties</span>
      </a>
      <button type="button" onclick="handleSyncLiveDB()" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
        <span class="material-symbols-outlined text-base">refresh</span>
        <span>Sync Live DB</span>
      </button>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════
     2. EXECUTIVE KPI CARDS (5 METRICS)
     ══════════════════════════════════════════════ -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5">
  
  <!-- Metric 1: Farmhouses -->
  <div class="dashboard-card bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
    <div class="flex items-start justify-between mb-3">
      <div class="w-11 h-11 rounded-xl bg-blue-50 text-brand-700 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">villa</span>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100/70 text-blue-800">
        Verified
      </span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Farmhouses</div>
      <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 mt-0.5">
        <?= number_format($kpis['total_farmhouses']) ?>
      </div>
      <div class="text-[11px] text-slate-400 font-medium mt-1 flex items-center gap-1">
        <span class="text-emerald-600 font-bold"><?= $kpis['active_farmhouses'] ?> Active</span>
        <span>•</span>
        <span><?= $kpis['pending_farmhouses'] ?> Pending</span>
      </div>
    </div>
  </div>

  <!-- Metric 2: Bookings -->
  <div class="dashboard-card bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
    <div class="flex items-start justify-between mb-3">
      <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">calendar_month</span>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100/70 text-purple-800">
        Real-time
      </span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Platform Bookings</div>
      <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 mt-0.5">
        <?= number_format($kpis['total_bookings']) ?>
      </div>
      <div class="text-[11px] text-slate-400 font-medium mt-1 flex items-center gap-1">
        <span class="text-emerald-600 font-bold"><?= $kpis['approved_bookings'] ?> Approved</span>
        <span>•</span>
        <span class="text-amber-600 font-bold"><?= $kpis['pending_bookings'] ?> Pending</span>
      </div>
    </div>
  </div>

  <!-- Metric 3: Gross Volume / Revenue -->
  <div class="dashboard-card bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
    <div class="flex items-start justify-between mb-3">
      <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">payments</span>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100/70 text-emerald-800 flex items-center gap-0.5">
        <span class="material-symbols-outlined text-[12px]">trending_up</span> +14.2%
      </span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Estimated Volume</div>
      <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 mt-0.5">
        ₹<?= number_format((float)($kpis['total_revenue'] ?? $kpis['estimated_revenue'] ?? 0)) ?>
      </div>
      <div class="text-[11px] text-slate-400 font-medium mt-1">
        From completed guest stays
      </div>
    </div>
  </div>

  <!-- Metric 4: Registered Users -->
  <div class="dashboard-card bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
    <div class="flex items-start justify-between mb-3">
      <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">person</span>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100/70 text-amber-800">
        Accounts
      </span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Registered Guests</div>
      <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 mt-0.5">
        <?= number_format($kpis['total_users']) ?>
      </div>
      <div class="text-[11px] text-slate-400 font-medium mt-1">
        Active Stayora travelers
      </div>
    </div>
  </div>

  <!-- Metric 5: Review Ratings -->
  <div class="dashboard-card bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
    <div class="flex items-start justify-between mb-3">
      <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">hotel_class</span>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100/70 text-rose-800 flex items-center gap-1">
        <span class="material-symbols-outlined text-[12px]">star</span> Top Rated
      </span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Average Rating</div>
      <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 mt-0.5 flex items-baseline gap-1">
        <span><?= number_format($kpis['avg_rating'], 1) ?></span>
        <span class="text-sm font-normal text-slate-400">/ 5.0</span>
      </div>
      <div class="text-[11px] text-slate-400 font-medium mt-1">
        <?= $kpis['total_reviews'] ?> Verified customer reviews
      </div>
    </div>
  </div>

</section>

<!-- ══════════════════════════════════════════════
     3. CHART.JS ANALYTICS & VISUALIZATIONS
     ══════════════════════════════════════════════ -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  
  <!-- Line Chart: Monthly Booking Volume & Revenue -->
  <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h3 class="font-headline font-bold text-base sm:text-lg text-slate-900">
          Monthly Booking &amp; Revenue Trends
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
          Dynamic platform transaction velocity over past 6 calendar months
        </p>
      </div>

      <div class="flex items-center gap-3 self-start sm:self-center">
        <span class="inline-flex items-center gap-1.5 text-xs text-slate-600 font-semibold">
          <span class="w-3 h-3 rounded-full bg-brand-600"></span>
          Bookings
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs text-slate-600 font-semibold">
          <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
          Revenue (₹)
        </span>
      </div>
    </div>

    <!-- Canvas Container -->
    <div class="relative w-full h-72 sm:h-80">
      <canvas id="monthlyTrendChart"></canvas>
    </div>
  </div>

  <!-- Doughnut Chart: Portfolio Distribution -->
  <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
    <div>
      <h3 class="font-headline font-bold text-base sm:text-lg text-slate-900">
        Portfolio by Category
      </h3>
      <p class="text-xs text-slate-500 mt-0.5">
        Live inventory split across luxury property types
      </p>
    </div>

    <div class="relative w-full h-56 my-auto flex items-center justify-center">
      <canvas id="categoryChart"></canvas>
      <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
        <span class="text-2xl font-black text-slate-900"><?= $kpis['total_farmhouses'] ?></span>
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Stays</span>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-xs">
      <?php 
        $colors = ['#1d4ed8', '#0284c7', '#10b981', '#8b5cf6'];
        $idx = 0;
        foreach ($categoryDist as $catName => $count): 
          $col = $colors[$idx % count($colors)];
          $idx++;
      ?>
        <div class="flex items-center gap-2 truncate">
          <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: <?= $col ?>"></span>
          <span class="text-slate-600 truncate"><?= htmlspecialchars($catName) ?></span>
          <span class="font-bold text-slate-900 ml-auto"><?= $count ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</section>

<!-- ══════════════════════════════════════════════
     4. RECENT ACTIVITY & RECORDS TABLES
     ══════════════════════════════════════════════ -->
<section class="grid grid-cols-1 xl:grid-cols-2 gap-6">

  <!-- Table 1: Recent Bookings -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="font-headline font-bold text-base sm:text-lg text-slate-900">
          Recent Bookings &amp; Inquiries
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
          Latest customer reservations recorded in database
        </p>
      </div>
      <span class="text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
        <?= count($recentBookings) ?> Listed
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
          <tr>
            <th class="py-3 px-4 sm:px-6">Ref ID</th>
            <th class="py-3 px-4">Guest Details</th>
            <th class="py-3 px-4">Property</th>
            <th class="py-3 px-4">Dates</th>
            <th class="py-3 px-4 sm:px-6 text-right">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
          <?php if (!empty($recentBookings)): ?>
            <?php foreach ($recentBookings as $bk): ?>
              <?php 
                $refDisplay = $bk['booking_reference'] ?? ('#BK-' . str_pad((string)$bk['id'], 3, '0', STR_PAD_LEFT));
              ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">
                  <a href="<?= url('admin/bookings/view/' . $bk['id']) ?>" class="inline-flex items-center gap-1.5 font-mono text-brand-700 hover:text-brand-900 hover:underline transition-colors group" title="Open Stay Dossier">
                    <span><?= htmlspecialchars($refDisplay) ?></span>
                    <span class="material-symbols-outlined text-[13px] opacity-0 group-hover:opacity-100 transition-opacity text-slate-400">open_in_new</span>
                  </a>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900"><?= htmlspecialchars($bk['customer_name'] ?? 'Guest User') ?></div>
                  <div class="text-[11px] text-slate-400"><?= htmlspecialchars($bk['phone'] ?? 'N/A') ?></div>
                </td>
                <td class="py-3.5 px-4">
                  <a href="<?= url('admin/properties/view?id=' . encrypt_id($bk['farmhouse_id'] ?? 0)) ?>" class="font-bold text-slate-800 hover:text-brand-700 transition-colors truncate max-w-[150px] block"><?= htmlspecialchars($bk['farmhouse_title'] ?? 'Farmhouse #' . $bk['farmhouse_id']) ?></a>
                  <div class="text-[11px] text-slate-400 truncate max-w-[150px]"><?= htmlspecialchars($bk['farmhouse_location'] ?? 'India') ?></div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div><?= date('M d, Y', strtotime($bk['start_date'])) ?></div>
                  <div class="text-[11px] text-slate-400">to <?= date('M d, Y', strtotime($bk['end_date'])) ?></div>
                </td>
                <td class="py-3.5 px-4 sm:px-6 text-right">
                  <?php 
                    $st = strtolower((string)($bk['status'] ?? 'pending'));
                    $badgeClass = match($st) {
                      'approved', 'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                      'checked_in'            => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                      'checked_out', 'completed' => 'bg-slate-100 text-slate-700 border-slate-200',
                      'rejected', 'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                      default                 => 'bg-amber-50 text-amber-700 border-amber-200'
                    };
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeClass ?> capitalize">
                    <?= str_replace('_', ' ', $st) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                No customer bookings registered yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Table 2: Recent Farmhouses -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="font-headline font-bold text-base sm:text-lg text-slate-900">
          Verified Farmhouse Portfolio
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
          Current active listings under platform management
        </p>
      </div>
      <a href="<?= url('admin/properties') ?>" class="text-xs font-bold text-brand-700 hover:text-brand-800 transition-colors inline-flex items-center gap-1">
        <span>Manage All</span>
        <span class="material-symbols-outlined text-sm">arrow_forward</span>
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
          <tr>
            <th class="py-3 px-4 sm:px-6">Property Title</th>
            <th class="py-3 px-4">Location</th>
            <th class="py-3 px-4">Rate / Night</th>
            <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
          <?php if (!empty($recentFarmhouses)): ?>
            <?php foreach ($recentFarmhouses as $farm): ?>
              <?php $encFhId = encrypt_id($farm['id']); ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 sm:px-6">
                  <a href="<?= url('admin/properties/view?id=' . $encFhId) ?>" class="font-bold text-slate-900 hover:text-brand-700 transition-colors truncate max-w-[180px] block"><?= htmlspecialchars($farm['title']) ?></a>
                  <div class="text-[11px] text-slate-400"><?= htmlspecialchars($farm['category'] ?? 'Luxury Farm') ?></div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="text-slate-800 font-semibold truncate max-w-[130px]"><?= htmlspecialchars($farm['location'] ?? 'India') ?></div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-bold text-slate-900">₹<?= number_format((float)($farm['price'] ?? 0)) ?></div>
                  <div class="text-[10px] text-slate-400">per night</div>
                </td>
                <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <a href="<?= url('admin/properties/view?id=' . $encFhId) ?>" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-700 hover:bg-slate-100 transition-colors" title="View Property Details">
                      <span class="material-symbols-outlined text-[17px]">visibility</span>
                    </a>
                    <a href="<?= url('admin/properties/edit?id=' . $encFhId) ?>" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-700 hover:bg-slate-100 transition-colors" title="Edit Property Specifications">
                      <span class="material-symbols-outlined text-[17px]">edit</span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                No farmhouses found in database.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</section>

<!-- Page-Specific Chart.js & Sync Script -->
<script>
  // ── 1. Monthly Traffic & Revenue Chart ──
  const monthlyLabels = <?= json_encode($chartData['labels'] ?? []) ?>;
  const bookingCounts = <?= json_encode($chartData['bookings'] ?? $chartData['counts'] ?? []) ?>;
  const revenueData   = <?= json_encode($chartData['revenue'] ?? $chartData['revenues'] ?? []) ?>;

  const ctxTrend = document.getElementById('monthlyTrendChart');
  if (ctxTrend) {
    new Chart(ctxTrend, {
      type: 'line',
      data: {
        labels: monthlyLabels,
        datasets: [
          {
            label: 'Bookings',
            data: bookingCounts,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.08)',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            yAxisID: 'y',
            pointBackgroundColor: '#2563eb',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
          },
          {
            label: 'Revenue (₹)',
            data: revenueData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.04)',
            borderWidth: 2.5,
            borderDash: [5, 5],
            tension: 0.35,
            fill: true,
            yAxisID: 'y1',
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { family: 'Epilogue', size: 12, weight: 'bold' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
            padding: 10,
            cornerRadius: 10,
            callbacks: {
              label: function(context) {
                if (context.dataset.yAxisID === 'y1') {
                  return ' ' + context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                }
                return ' ' + context.dataset.label + ': ' + context.raw + ' stays';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { family: 'Plus Jakarta Sans', size: 11 },
              color: '#64748b'
            }
          },
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            beginAtZero: true,
            grid: { color: '#f1f5f9' },
            title: {
              display: true,
              text: 'Bookings',
              font: { family: 'Plus Jakarta Sans', size: 10, weight: 'bold' },
              color: '#2563eb'
            },
            ticks: {
              font: { family: 'Plus Jakarta Sans', size: 11 },
              color: '#64748b',
              stepSize: 2,
              precision: 0
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            title: {
              display: true,
              text: 'Revenue (₹)',
              font: { family: 'Plus Jakarta Sans', size: 10, weight: 'bold' },
              color: '#10b981'
            },
            ticks: {
              font: { family: 'Plus Jakarta Sans', size: 10 },
              color: '#10b981',
              callback: function(value) {
                if (value >= 100000) return '₹' + (value / 100000).toFixed(1) + 'L';
                if (value >= 1000) return '₹' + (value / 1000).toFixed(0) + 'k';
                return '₹' + value;
              }
            }
          }
        }
      }
    });
  }

  // ── 2. Category Distribution Doughnut Chart ──
  const catLabels = <?= json_encode(array_keys($categoryDist ?? [])) ?>;
  const catData   = <?= json_encode(array_values($categoryDist ?? [])) ?>;

  const ctxCat = document.getElementById('categoryChart');
  if (ctxCat) {
    new Chart(ctxCat, {
      type: 'doughnut',
      data: {
        labels: catLabels,
        datasets: [{
          data: catData,
          backgroundColor: ['#1d4ed8', '#0284c7', '#10b981', '#8b5cf6'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
            padding: 8,
            cornerRadius: 8
          }
        }
      }
    });
  }

  // ── 3. Page Sync Helper ──
  function handleSyncLiveDB() {
    if (window.adminToast) {
      adminToast.info('Synchronizing live platform metrics with MySQL database...', 'Database Sync in Progress', 2200);
    }
    setTimeout(() => {
      window.location.reload();
    }, 700);
  }
</script>

<!-- ── Shared Admin Layout Footer (System Audit Bar, Scripts, Toasts, Document Close) ── -->
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
