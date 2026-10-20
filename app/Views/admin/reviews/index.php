<?php
$pageTitle   = "Guest Reviews & Moderation • Stayora Executive Console";
$currentPage = "reviews";
$headerTitle = "Guest Reviews Management";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

// Safe variable defaults
$reviews      = $reviews ?? [];
$kpis         = $kpis ?? [];
$properties   = $properties ?? [];
$filters      = $filters ?? [];
$page         = $page ?? 1;
$totalPages   = $totalPages ?? 1;
$totalRecords = $totalRecords ?? 0;
$startItem    = $startItem ?? 0;
$endItem      = $endItem ?? 0;

require_once __DIR__ . '/../../../Helpers/CsrfHelper.php';
$csrfToken = \App\Helpers\CsrfHelper::generate();

// Helper to resolve user avatar
function resolveReviewGuestAvatar(?string $img, ?string $avatarUrl = null): string {
    if (!empty($avatarUrl)) return $avatarUrl;
    if (empty($img) || $img === 'default_profile.png') return '';
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) return $img;
    return asset('assets/images/uploads/profiles/' . ltrim($img, '/'));
}

// Helper to resolve property thumbnail
function resolvePropertyThumb(?string $img): string {
    if (empty($img)) return asset('assets/images/placeholder-property.jpg');
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) return $img;
    return asset('assets/images/uploads/properties/' . ltrim($img, '/'));
}
?>

<div class="space-y-6 pb-12">

  <!-- ══════════════════════════════════════════════
       1. TOP ACTION BAR & BREADCRUMBS
       ══════════════════════════════════════════════ -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-brand-700 transition-colors">Console</a>
        <span>/</span>
        <span class="text-slate-800">Guest Reviews</span>
      </div>
      <h1 class="font-headline font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2">
        <span>Guest Review Moderation &amp; Social Proof</span>
        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
          <?= number_format($totalRecords) ?> Reviews
        </span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">
        Moderate verified stay ratings, inspect multi-criteria feedback, manage host responses, and safeguard reputation integrity.
      </p>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex items-center gap-2.5 flex-wrap">
      <a href="<?= url('admin/reviews/export?' . http_build_query($filters)) ?>" 
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-2xs transition-all hover:border-slate-400">
        <span class="material-symbols-outlined text-[18px] text-slate-500">download</span>
        <span>Export CSV</span>
      </a>

      <button type="button" onclick="location.reload()" 
              class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-2xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all">
        <span class="material-symbols-outlined text-[18px]">refresh</span>
      </button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       2. FLASH SESSION NOTIFICATIONS
       ══════════════════════════════════════════════ -->
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-xs">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
      </div>
      <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <div class="flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold shadow-xs">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
      </div>
      <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════
       3. EXECUTIVE KPI CARDS (Real Database Records)
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-5">
    
    <!-- Card 1: Total Reviews -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Feedback</span>
        <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
          <span class="material-symbols-outlined text-[18px]">rate_review</span>
        </div>
      </div>
      <div class="mt-3">
        <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
          <?= number_format($kpis['total_reviews'] ?? 0) ?>
        </div>
        <div class="flex items-center gap-1.5 mt-1 text-[11px] font-semibold text-emerald-600">
          <span class="material-symbols-outlined text-[14px]">verified</span>
          <span><?= number_format($kpis['verified_stays'] ?? 0) ?> verified stays</span>
        </div>
      </div>
    </div>

    <!-- Card 2: Published Reviews -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Published Live</span>
        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">public</span>
        </div>
      </div>
      <div class="mt-3">
        <div class="font-headline font-black text-2xl sm:text-3xl text-emerald-700 tracking-tight">
          <?= number_format($kpis['approved_reviews'] ?? 0) ?>
        </div>
        <div class="flex items-center gap-1.5 mt-1 text-[11px] font-semibold text-slate-500">
          <span>Active in property ratings</span>
        </div>
      </div>
    </div>

    <!-- Card 3: Pending Moderation (Queue) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between <?= ($kpis['pending_reviews'] ?? 0) > 0 ? 'ring-2 ring-amber-400/30' : '' ?>">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Awaiting Audit</span>
        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px] <?= ($kpis['pending_reviews'] ?? 0) > 0 ? 'animate-bounce' : '' ?>">hourglass_top</span>
        </div>
      </div>
      <div class="mt-3">
        <div class="font-headline font-black text-2xl sm:text-3xl text-amber-600 tracking-tight flex items-center gap-2">
          <span><?= number_format($kpis['pending_reviews'] ?? 0) ?></span>
          <?php if (($kpis['pending_reviews'] ?? 0) > 0): ?>
            <span class="text-[9px] uppercase px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-extrabold animate-pulse">Action Required</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-1.5 mt-1 text-[11px] font-semibold text-slate-500">
          <span>Needs admin moderation</span>
        </div>
      </div>
    </div>

    <!-- Card 4: Platform Average Rating -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Platform Score</span>
        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">star</span>
        </div>
      </div>
      <div class="mt-3">
        <div class="font-headline font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-baseline gap-1">
          <span><?= number_format((float)($kpis['average_rating'] ?? 0), 1) ?></span>
          <span class="text-sm font-semibold text-slate-400">/ 5.0</span>
        </div>
        <div class="flex items-center gap-1 mt-1 text-[11px] text-amber-500 font-bold">
          <?php
          $avgStars = round((float)($kpis['average_rating'] ?? 0));
          for ($i = 1; $i <= 5; $i++):
          ?>
            <span class="material-symbols-outlined text-[14px] <?= $i <= $avgStars ? 'text-amber-500 fill-amber-500' : 'text-slate-300' ?>">star</span>
          <?php endfor; ?>
          <span class="text-slate-400 text-[10px] ml-1">(<?= number_format($kpis['approved_reviews'] ?? 0) ?> scored)</span>
        </div>
      </div>
    </div>

    <!-- Card 5: Moderation Exceptions (Rejected / Hidden / Flagged) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Exceptions &amp; Hidden</span>
        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">shield</span>
        </div>
      </div>
      <div class="mt-3">
        <div class="font-headline font-black text-2xl sm:text-3xl text-slate-800 tracking-tight">
          <?= number_format(($kpis['rejected_reviews'] ?? 0) + ($kpis['hidden_reviews'] ?? 0) + ($kpis['flagged_reviews'] ?? 0)) ?>
        </div>
        <div class="flex items-center gap-2 mt-1 text-[10px] font-semibold text-slate-500">
          <span class="text-rose-600"><?= $kpis['rejected_reviews'] ?? 0 ?> rej</span>
          <span>•</span>
          <span class="text-slate-600"><?= $kpis['hidden_reviews'] ?? 0 ?> hide</span>
          <span>•</span>
          <span class="text-purple-600"><?= $kpis['flagged_reviews'] ?? 0 ?> flag</span>
        </div>
      </div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       4. RATING BREAKDOWN & MULTI-CRITERIA SENTIMENT
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    
    <!-- Left 2 Cols: Star Distribution Meter -->
    <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <div>
          <h2 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500">hotel_class</span>
            <span>Star Rating Distribution</span>
          </h2>
          <p class="text-[11px] text-slate-400">Breakdown of published guest reviews by satisfaction tier</p>
        </div>
        <span class="text-xs font-bold text-slate-600"><?= number_format($kpis['approved_reviews'] ?? 0) ?> Verified Ratings</span>
      </div>

      <div class="space-y-3 mt-4">
        <?php for ($s = 5; $s >= 1; $s--): 
          $cnt = $kpis['star_counts'][$s] ?? 0;
          $pct = $kpis['star_percentages'][$s] ?? 0;
        ?>
          <a href="<?= url('admin/reviews?rating=' . $s) ?>" 
             class="flex items-center gap-3 group hover:bg-slate-50 p-1.5 rounded-xl transition-all">
            <div class="flex items-center gap-1 w-14 shrink-0 text-xs font-bold text-slate-700 group-hover:text-amber-600">
              <span><?= $s ?></span>
              <span class="material-symbols-outlined text-[15px] text-amber-500">star</span>
            </div>
            
            <div class="flex-1 bg-slate-100 h-2.5 rounded-full overflow-hidden">
              <div class="bg-amber-400 h-full rounded-full transition-all duration-500 group-hover:bg-amber-500" 
                   style="width: <?= $pct ?>%;"></div>
            </div>

            <div class="w-14 text-right text-xs font-semibold text-slate-500 shrink-0">
              <?= $pct ?>%
            </div>

            <div class="w-12 text-right text-[11px] font-extrabold text-slate-400 group-hover:text-slate-800 shrink-0">
              (<?= number_format($cnt) ?>)
            </div>
          </a>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Right Col: Multi-Criteria Quality Pillars & Response Rate -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h2 class="font-headline font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-700">award_star</span>
            <span>Quality Criteria Averages</span>
          </h2>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Scale 1-5</span>
        </div>

        <div class="space-y-3.5 mt-4">
          
          <!-- Cleanliness -->
          <div>
            <div class="flex items-center justify-between text-xs font-semibold mb-1">
              <span class="text-slate-600 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-teal-600">sanitizer</span>
                <span>Cleanliness &amp; Hygiene</span>
              </span>
              <span class="font-bold text-slate-900"><?= number_format((float)($kpis['avg_cleanliness'] ?? 0), 1) ?></span>
            </div>
            <div class="bg-slate-100 h-1.5 rounded-full overflow-hidden">
              <div class="bg-teal-500 h-full rounded-full" style="width: <?= min(100, (($kpis['avg_cleanliness'] ?? 0) / 5) * 100) ?>%;"></div>
            </div>
          </div>

          <!-- Location -->
          <div>
            <div class="flex items-center justify-between text-xs font-semibold mb-1">
              <span class="text-slate-600 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-blue-600">location_on</span>
                <span>Location &amp; Surroundings</span>
              </span>
              <span class="font-bold text-slate-900"><?= number_format((float)($kpis['avg_location'] ?? 0), 1) ?></span>
            </div>
            <div class="bg-slate-100 h-1.5 rounded-full overflow-hidden">
              <div class="bg-blue-500 h-full rounded-full" style="width: <?= min(100, (($kpis['avg_location'] ?? 0) / 5) * 100) ?>%;"></div>
            </div>
          </div>

          <!-- Value -->
          <div>
            <div class="flex items-center justify-between text-xs font-semibold mb-1">
              <span class="text-slate-600 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-emerald-600">payments</span>
                <span>Value for Money</span>
              </span>
              <span class="font-bold text-slate-900"><?= number_format((float)($kpis['avg_value'] ?? 0), 1) ?></span>
            </div>
            <div class="bg-slate-100 h-1.5 rounded-full overflow-hidden">
              <div class="bg-emerald-500 h-full rounded-full" style="width: <?= min(100, (($kpis['avg_value'] ?? 0) / 5) * 100) ?>%;"></div>
            </div>
          </div>

          <!-- Hospitality -->
          <div>
            <div class="flex items-center justify-between text-xs font-semibold mb-1">
              <span class="text-slate-600 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-indigo-600">room_service</span>
                <span>Host Hospitality &amp; Service</span>
              </span>
              <span class="font-bold text-slate-900"><?= number_format((float)($kpis['avg_hospitality'] ?? 0), 1) ?></span>
            </div>
            <div class="bg-slate-100 h-1.5 rounded-full overflow-hidden">
              <div class="bg-indigo-500 h-full rounded-full" style="width: <?= min(100, (($kpis['avg_hospitality'] ?? 0) / 5) * 100) ?>%;"></div>
            </div>
          </div>

        </div>
      </div>

      <!-- Host Response Rate pill -->
      <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
        <span class="text-slate-500 flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px] text-brand-600">forum</span>
          <span>Official Responses:</span>
        </span>
        <span class="font-bold text-slate-900">
          <?= number_format($kpis['with_responses'] ?? 0) ?> of <?= number_format($kpis['approved_reviews'] ?? 0) ?> published
        </span>
      </div>

    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       5. FILTER & SEARCH TOOLBAR
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 space-y-4">
    
    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
      <?php
      $statusTabs = [
        'all'      => ['label' => 'All Reviews', 'count' => $kpis['total_reviews'] ?? 0, 'icon' => 'list_alt'],
        'pending'  => ['label' => 'Pending Audit', 'count' => $kpis['pending_reviews'] ?? 0, 'icon' => 'hourglass_empty', 'badge' => 'amber'],
        'approved' => ['label' => 'Published Live', 'count' => $kpis['approved_reviews'] ?? 0, 'icon' => 'check_circle', 'badge' => 'emerald'],
        'flagged'  => ['label' => 'Flagged for Review', 'count' => $kpis['flagged_reviews'] ?? 0, 'icon' => 'flag', 'badge' => 'purple'],
        'rejected' => ['label' => 'Rejected', 'count' => $kpis['rejected_reviews'] ?? 0, 'icon' => 'cancel', 'badge' => 'rose'],
        'hidden'   => ['label' => 'Hidden / Unpublished', 'count' => $kpis['hidden_reviews'] ?? 0, 'icon' => 'visibility_off', 'badge' => 'slate'],
      ];
      $currentStatus = $filters['status'] ?? 'all';
      ?>
      <?php foreach ($statusTabs as $k => $tab): 
        $isActive = ($currentStatus === $k);
      ?>
        <a href="<?= url('admin/reviews?' . http_build_query(array_merge($filters, ['status' => $k, 'page' => 1]))) ?>" 
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= $isActive ? 'bg-brand-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
          <span class="material-symbols-outlined text-[16px]"><?= $tab['icon'] ?></span>
          <span><?= $tab['label'] ?></span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $isActive ? 'bg-white/20 text-white' : 'bg-white text-slate-700 border border-slate-200' ?>">
            <?= number_format($tab['count']) ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="<?= url('admin/reviews') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
      
      <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">

      <!-- Search Input -->
      <div class="lg:col-span-2 relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
               placeholder="Search guest, email, property, text, or ref..." 
               class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all outline-none">
      </div>

      <!-- Property Dropdown Filter -->
      <div>
        <select name="farmhouse_id" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 outline-none">
          <option value="">All Properties</option>
          <?php foreach ($properties as $prop): ?>
            <option value="<?= $prop['id'] ?>" <?= ((int)($filters['farmhouse_id'] ?? 0) === (int)$prop['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($prop['title']) ?> (<?= htmlspecialchars($prop['city'] ?? '') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Star Rating Filter -->
      <div>
        <select name="rating" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 outline-none">
          <option value="">All Ratings</option>
          <option value="5" <?= ($filters['rating'] ?? '') == '5' ? 'selected' : '' ?>>5 Stars (★★★★★)</option>
          <option value="4" <?= ($filters['rating'] ?? '') == '4' ? 'selected' : '' ?>>4 Stars (★★★★☆)</option>
          <option value="3" <?= ($filters['rating'] ?? '') == '3' ? 'selected' : '' ?>>3 Stars (★★★☆☆)</option>
          <option value="2" <?= ($filters['rating'] ?? '') == '2' ? 'selected' : '' ?>>2 Stars (★★☆☆☆)</option>
          <option value="1" <?= ($filters['rating'] ?? '') == '1' ? 'selected' : '' ?>>1 Star  (★☆☆☆☆)</option>
        </select>
      </div>

      <!-- Verification Status -->
      <div>
        <select name="verified" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 outline-none">
          <option value="">All Stay Types</option>
          <option value="1" <?= ($filters['verified'] ?? '') === 1 || ($filters['verified'] ?? '') === '1' ? 'selected' : '' ?>>Verified Stays Only</option>
          <option value="0" <?= ($filters['verified'] ?? '') === 0 || ($filters['verified'] ?? '') === '0' ? 'selected' : '' ?>>Unverified Feedback</option>
        </select>
      </div>

      <!-- Sort & Submit -->
      <div class="flex items-center gap-2">
        <select name="sort" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 outline-none">
          <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
          <option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
          <option value="rating_high" <?= ($filters['sort'] ?? '') === 'rating_high' ? 'selected' : '' ?>>Highest Rating</option>
          <option value="rating_low" <?= ($filters['sort'] ?? '') === 'rating_low' ? 'selected' : '' ?>>Lowest Rating</option>
          <option value="pending_first" <?= ($filters['sort'] ?? '') === 'pending_first' ? 'selected' : '' ?>>Pending First</option>
        </select>

        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs transition-all shadow-xs">
          Filter
        </button>

        <?php if (!empty($filters['search']) || !empty($filters['farmhouse_id']) || !empty($filters['rating']) || ($filters['verified'] !== '') || ($currentStatus !== 'all')): ?>
          <a href="<?= url('admin/reviews') ?>" class="px-3 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-all" title="Clear Filters">
            <span class="material-symbols-outlined text-[16px]">close</span>
          </a>
        <?php endif; ?>
      </div>

    </form>

    <!-- Bulk Moderation Action Bar (Visible when checkboxes checked) -->
    <div id="bulkActionBar" class="hidden items-center justify-between p-3.5 bg-brand-50 border border-brand-200 rounded-2xl text-xs">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-brand-700 text-[20px]">select_check_box</span>
        <span class="font-bold text-brand-900"><span id="selectedCount">0</span> review(s) selected</span>
      </div>

      <div class="flex items-center gap-2">
        <button type="button" onclick="openBulkModal('approve')" 
                class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-2xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">check_circle</span>
          <span>Approve Selected</span>
        </button>

        <button type="button" onclick="openBulkModal('reject')" 
                class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-all shadow-2xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">cancel</span>
          <span>Reject Selected</span>
        </button>

        <button type="button" onclick="openBulkModal('hide')" 
                class="px-3 py-1.5 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-2xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">visibility_off</span>
          <span>Hide Selected</span>
        </button>

        <button type="button" onclick="clearBulkSelection()" class="px-2.5 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs">
          Cancel
        </button>
      </div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       6. REVIEWS TABLE (Desktop) & CARDS (Mobile)
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    
    <?php if (empty($reviews)): ?>
      <div class="p-12 text-center space-y-4">
        <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
          <span class="material-symbols-outlined text-3xl">rate_review</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-800">No Guest Reviews Found</h3>
          <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
            No review records match your current filter criteria. Try adjusting your search query, status tab, or date range.
          </p>
        </div>
        <a href="<?= url('admin/reviews') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-brand-700 text-white font-bold text-xs hover:bg-brand-800 transition-all shadow-xs">
          <span>Reset All Filters</span>
        </a>
      </div>
    <?php else: ?>

      <!-- Desktop Table -->
      <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/75 text-[10px] font-bold uppercase tracking-wider text-slate-400">
              <th class="py-3.5 pl-6 pr-3 w-10">
                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" 
                       class="rounded border-slate-300 text-brand-700 focus:ring-brand-500 cursor-pointer">
              </th>
              <th class="py-3.5 px-3">Review ID &amp; Date</th>
              <th class="py-3.5 px-3">Guest &amp; Stay Context</th>
              <th class="py-3.5 px-3">Property</th>
              <th class="py-3.5 px-3">Rating Score</th>
              <th class="py-3.5 px-3 min-w-[220px]">Feedback Content</th>
              <th class="py-3.5 px-3 text-center">Status</th>
              <th class="py-3.5 pl-3 pr-6 text-right">Moderation Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <?php foreach ($reviews as $rev): 
              $revId = (int)$rev['id'];
              $rating = (float)$rev['overall_rating'];
              $avatarUrl = resolveReviewGuestAvatar($rev['profile_image'] ?? null, $rev['avatar_url'] ?? null);
              $propThumb = resolvePropertyThumb($rev['property_image'] ?? null);
              
              // Status Badge Config
              $status = strtolower($rev['status']);
              $statusBadge = match($status) {
                'approved' => ['label' => 'Approved', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'check_circle'],
                'pending'  => ['label' => 'Pending Audit', 'class' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'hourglass_empty'],
                'rejected' => ['label' => 'Rejected', 'class' => 'bg-rose-50 text-rose-700 border-rose-200', 'icon' => 'cancel'],
                'hidden'   => ['label' => 'Hidden', 'class' => 'bg-slate-100 text-slate-600 border-slate-200', 'icon' => 'visibility_off'],
                'flagged'  => ['label' => 'Flagged', 'class' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'flag'],
                default    => ['label' => ucfirst($status), 'class' => 'bg-slate-100 text-slate-600 border-slate-200', 'icon' => 'help_outline']
              };
            ?>
              <tr class="hover:bg-slate-50/60 transition-colors group">
                
                <!-- Checkbox -->
                <td class="py-4 pl-6 pr-3">
                  <input type="checkbox" name="selected_reviews[]" value="<?= $revId ?>" onchange="updateSelectedCount()" 
                         class="review-checkbox rounded border-slate-300 text-brand-700 focus:ring-brand-500 cursor-pointer">
                </td>

                <!-- ID & Timestamp -->
                <td class="py-4 px-3 whitespace-nowrap">
                  <div class="font-bold text-slate-900 flex items-center gap-1.5">
                    <span>#REV-<?= str_pad((string)$revId, 5, '0', STR_PAD_LEFT) ?></span>
                  </div>
                  <div class="text-[10px] text-slate-400 mt-0.5" title="<?= htmlspecialchars($rev['created_at']) ?>">
                    <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                  </div>
                  <div class="text-[10px] text-slate-400">
                    <?= date('h:i A', strtotime($rev['created_at'])) ?>
                  </div>
                </td>

                <!-- Guest & Stay Context -->
                <td class="py-4 px-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-brand-50 border border-brand-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-700 font-bold text-xs">
                      <?php if (!empty($avatarUrl)): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($rev['guest_name']) ?>" class="w-full h-full object-cover">
                      <?php else: ?>
                        <span><?= strtoupper(substr($rev['guest_name'], 0, 2)) ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 truncate flex items-center gap-1">
                        <?php if (!empty($rev['user_id'])): ?>
                          <a href="<?= url('admin/guests/view/' . (int)$rev['user_id'] . '?tab=reviews') ?>" 
                             class="hover:text-brand-700 transition-colors" title="Open Guest Dossier">
                            <?= htmlspecialchars($rev['guest_name']) ?>
                          </a>
                        <?php else: ?>
                          <span><?= htmlspecialchars($rev['guest_name']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($rev['membership_tier']) && $rev['membership_tier'] !== 'none'): ?>
                          <span class="text-[9px] px-1 py-0.2 rounded font-extrabold uppercase bg-amber-100 text-amber-800">
                            <?= htmlspecialchars($rev['membership_tier']) ?>
                          </span>
                        <?php endif; ?>
                      </div>
                      
                      <div class="text-[10px] text-slate-400 truncate">
                        <?= htmlspecialchars($rev['guest_email'] ?? 'No email provided') ?>
                      </div>

                      <!-- Stay Verification Badge -->
                      <div class="mt-1 flex items-center gap-1">
                        <?php if (!empty($rev['is_verified_stay'])): ?>
                          <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                            <span class="material-symbols-outlined text-[11px]">verified_user</span>
                            <span>Verified Stay</span>
                          </span>
                          <?php if (!empty($rev['booking_reference'])): ?>
                            <span class="text-[9.5px] text-slate-400">Ref: <?= htmlspecialchars($rev['booking_reference']) ?></span>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9.5px] font-medium bg-slate-100 text-slate-500">
                            <span>General Review</span>
                          </span>
                        <?php endif; ?>
                      </div>

                    </div>
                  </div>
                </td>

                <!-- Property Info -->
                <td class="py-4 px-3">
                  <div class="flex items-center gap-2 max-w-[180px]">
                    <img src="<?= htmlspecialchars($propThumb) ?>" alt="Property" class="w-8 h-8 rounded-lg object-cover shrink-0 border border-slate-100">
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 truncate" title="<?= htmlspecialchars($rev['property_title'] ?? '') ?>">
                        <?= htmlspecialchars($rev['property_title'] ?? 'Estate Property') ?>
                      </div>
                      <div class="text-[10px] text-slate-400 truncate">
                        <?= htmlspecialchars($rev['property_location'] ?? 'Location N/A') ?>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Rating Score & Multi-Criteria Tooltip -->
                <td class="py-4 px-3 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <div class="flex items-center gap-0.5 text-amber-500 font-bold">
                      <span class="material-symbols-outlined text-[16px]">star</span>
                      <span><?= number_format($rating, 1) ?></span>
                    </div>
                  </div>

                  <!-- Multi-criteria micro score summary -->
                  <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                    <span title="Cleanliness">C: <?= $rev['cleanliness_rating'] ?></span>
                    <span>•</span>
                    <span title="Location">L: <?= $rev['location_rating'] ?></span>
                    <span>•</span>
                    <span title="Value">V: <?= $rev['value_rating'] ?></span>
                    <span>•</span>
                    <span title="Hospitality">H: <?= $rev['hospitality_rating'] ?></span>
                  </div>
                </td>

                <!-- Review Content Snippet -->
                <td class="py-4 px-3">
                  <div class="max-w-[320px]">
                    <?php if (!empty($rev['review_title'])): ?>
                      <div class="font-bold text-slate-900 text-xs mb-0.5 line-clamp-1">
                        "<?= htmlspecialchars($rev['review_title']) ?>"
                      </div>
                    <?php endif; ?>
                    <p class="text-slate-600 line-clamp-2 text-[11px] leading-relaxed">
                      <?= htmlspecialchars($rev['review_text']) ?>
                    </p>

                    <!-- Official Host Response Preview if present -->
                    <?php if (!empty($rev['response_count']) && (int)$rev['response_count'] > 0): ?>
                      <div class="mt-1.5 flex items-center gap-1 text-[10px] font-semibold text-brand-700 bg-brand-50/70 px-2 py-0.5 rounded-md w-fit">
                        <span class="material-symbols-outlined text-[13px]">reply</span>
                        <span>1 Host Response</span>
                      </div>
                    <?php endif; ?>

                    <!-- Rejection Reason if rejected -->
                    <?php if ($status === 'rejected' && !empty($rev['rejection_reason'])): ?>
                      <div class="mt-1 text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                        Reason: <?= htmlspecialchars($rev['rejection_reason']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Moderation Status Badge -->
                <td class="py-4 px-3 text-center whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold border <?= $statusBadge['class'] ?>">
                    <span class="material-symbols-outlined text-[13px]"><?= $statusBadge['icon'] ?></span>
                    <span><?= $statusBadge['label'] ?></span>
                  </span>
                </td>

                <!-- Moderation Actions -->
                <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    
                    <!-- View Details Modal -->
                    <button type="button" onclick="openReviewModal(<?= $revId ?>)" 
                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-700 transition-colors" 
                            title="Inspect 360° Review Dossier">
                      <span class="material-symbols-outlined text-[18px]">visibility</span>
                    </button>

                    <!-- Quick Approve (if not approved) -->
                    <?php if ($status !== 'approved'): ?>
                      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline" onsubmit="return confirm('Publish and approve review #<?= $revId ?>? This will make it live and include it in property ratings.')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="review_id" value="<?= $revId ?>">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="p-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors" title="Quick Approve &amp; Publish">
                          <span class="material-symbols-outlined text-[18px]">check</span>
                        </button>
                      </form>
                    <?php endif; ?>

                    <!-- Quick Reject (opens reject reason modal) -->
                    <?php if ($status !== 'rejected'): ?>
                      <button type="button" onclick="openRejectModal(<?= $revId ?>, '<?= htmlspecialchars(addslashes($rev['guest_name'])) ?>')" 
                              class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" 
                              title="Reject Review">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                      </button>
                    <?php endif; ?>

                    <!-- Quick Hide / Unpublish Toggle -->
                    <?php if ($status === 'approved'): ?>
                      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline" onsubmit="return confirm('Hide this review from public view?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="review_id" value="<?= $revId ?>">
                        <input type="hidden" name="status" value="hidden">
                        <button type="submit" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Hide / Unpublish">
                          <span class="material-symbols-outlined text-[18px]">visibility_off</span>
                        </button>
                      </form>
                    <?php elseif ($status === 'hidden'): ?>
                      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="review_id" value="<?= $revId ?>">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="p-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors" title="Restore to Published">
                          <span class="material-symbols-outlined text-[18px]">publish</span>
                        </button>
                      </form>
                    <?php endif; ?>

                    <!-- Post Host Response -->
                    <button type="button" onclick="openResponseModal(<?= $revId ?>, '<?= htmlspecialchars(addslashes($rev['guest_name'])) ?>', '<?= htmlspecialchars(addslashes($rev['property_title'] ?? '')) ?>')" 
                            class="p-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors" 
                            title="Add Official Host Response">
                      <span class="material-symbols-outlined text-[18px]">reply</span>
                    </button>

                  </div>
                </td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card Grid -->
      <div class="block lg:hidden divide-y divide-slate-100">
        <?php foreach ($reviews as $rev): 
          $revId = (int)$rev['id'];
          $status = strtolower($rev['status']);
        ?>
          <div class="p-5 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">#REV-<?= str_pad((string)$revId, 5, '0', STR_PAD_LEFT) ?></span>
              <span class="text-[10px] text-slate-400"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
            </div>

            <div class="flex items-center gap-2">
              <div class="font-bold text-xs text-slate-900"><?= htmlspecialchars($rev['guest_name']) ?></div>
              <?php if (!empty($rev['is_verified_stay'])): ?>
                <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-teal-50 text-teal-700">Verified</span>
              <?php endif; ?>
            </div>

            <div class="text-xs text-slate-500 font-medium">
              Property: <span class="text-slate-800 font-semibold"><?= htmlspecialchars($rev['property_title'] ?? 'Estate') ?></span>
            </div>

            <div class="flex items-center gap-1 text-amber-500 font-bold text-xs">
              <span class="material-symbols-outlined text-sm">star</span>
              <span><?= number_format((float)$rev['overall_rating'], 1) ?></span>
            </div>

            <?php if (!empty($rev['review_title'])): ?>
              <div class="font-bold text-xs text-slate-800">"<?= htmlspecialchars($rev['review_title']) ?>"</div>
            <?php endif; ?>
            <p class="text-xs text-slate-600 line-clamp-3"><?= htmlspecialchars($rev['review_text']) ?></p>

            <div class="pt-2 flex items-center justify-between border-t border-slate-100">
              <span class="text-[10px] font-bold capitalize px-2 py-0.5 rounded-full bg-slate-100 text-slate-700"><?= $status ?></span>
              <button type="button" onclick="openReviewModal(<?= $revId ?>)" class="text-xs font-bold text-brand-700">
                View Full Details &rarr;
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination Footer -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 border-t border-slate-100 bg-slate-50/50">
        <div class="text-xs text-slate-500 font-medium">
          Showing <span class="font-bold text-slate-800"><?= $startItem ?></span> to <span class="font-bold text-slate-800"><?= $endItem ?></span> of <span class="font-bold text-slate-800"><?= number_format($totalRecords) ?></span> total reviews
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="flex items-center gap-1.5">
            <?php if ($page > 1): ?>
              <a href="<?= url('admin/reviews?' . http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>" 
                 class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
                &larr; Prev
              </a>
            <?php endif; ?>

            <?php 
            $startPage = max(1, $page - 2);
            $endPage   = min($totalPages, $page + 2);
            for ($p = $startPage; $p <= $endPage; $p++):
            ?>
              <a href="<?= url('admin/reviews?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>" 
                 class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold transition-all <?= $p === $page ? 'bg-brand-700 text-white shadow-xs' : 'border border-slate-200 bg-white hover:bg-slate-50 text-slate-700' ?>">
                <?= $p ?>
              </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
              <a href="<?= url('admin/reviews?' . http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>" 
                 class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
                Next &rarr;
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>

    <?php endif; ?>

  </div>

</div>

<!-- ══════════════════════════════════════════════
     7. MODAL: 360° REVIEW DOSSIER & AUDIT TRAIL
     ══════════════════════════════════════════════ -->
<div id="reviewDetailModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white w-full max-w-3xl rounded-3xl border border-slate-200 shadow-2xl overflow-hidden my-8 transform transition-all flex flex-col max-h-[90vh]">
    
    <!-- Modal Header -->
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/75 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-700 font-headline font-black text-sm">
          ★
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 id="modalReviewId" class="font-headline font-black text-base text-slate-900">#REV-00000</h3>
            <span id="modalReviewStatus" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Approved</span>
            <span id="modalVerifiedBadge" class="hidden text-[10px] font-bold text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-full">
              Verified Stay
            </span>
          </div>
          <p id="modalCreatedDate" class="text-[11px] text-slate-400 mt-0.5">Submitted date</p>
        </div>
      </div>

      <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-all">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>

    <!-- Modal Scrollable Content -->
    <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
      
      <!-- Top Grid: Guest & Stay Context -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Guest Details -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
          <div class="flex items-center justify-between text-slate-400 font-bold text-[10px] uppercase tracking-wider">
            <span>Guest Profile</span>
            <span id="modalGuestTier" class="text-amber-800 bg-amber-100 px-1.5 py-0.2 rounded font-extrabold">VIP</span>
          </div>
          <div class="font-bold text-slate-900 text-sm" id="modalGuestName">Guest Name</div>
          <div class="text-slate-500" id="modalGuestEmail">guest@stayora.com</div>
          <div class="text-[11px] text-slate-500" id="modalGuestPhone">Phone</div>
          <div class="pt-1">
            <a id="modalGuestLink" href="#" class="text-brand-700 font-bold hover:underline inline-flex items-center gap-1">
              <span>View Full Guest Dossier</span>
              <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
          </div>
        </div>

        <!-- Property & Stay Context -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
          <div class="flex items-center justify-between text-slate-400 font-bold text-[10px] uppercase tracking-wider">
            <span>Property &amp; Booking</span>
            <span id="modalBookingStatus" class="capitalize font-bold text-slate-700">Checked Out</span>
          </div>
          <div class="font-bold text-slate-900 text-sm" id="modalPropertyTitle">Villa Name</div>
          <div class="text-slate-500" id="modalPropertyLocation">City, State</div>
          <div class="text-[11px] text-slate-500" id="modalStayDates">Stay: Check-in &rarr; Check-out</div>
          <div class="pt-1 text-slate-400 text-[10px]" id="modalBookingRef">Booking Ref: -</div>
        </div>

      </div>

      <!-- Rating Breakdown Card -->
      <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-500/10 via-amber-50/20 to-transparent border border-amber-200/60 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="font-headline font-black text-3xl text-slate-900 flex items-center gap-1">
              <span id="modalOverallRating">5.0</span>
              <span class="text-amber-500 text-2xl">★</span>
            </div>
            <div class="text-xs text-slate-600 font-semibold">Overall Guest Satisfaction</div>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-amber-200/40 text-center">
          <div class="p-2 bg-white rounded-xl border border-slate-100">
            <span class="block text-[10px] font-bold text-slate-400 uppercase">Cleanliness</span>
            <span id="modalCleanliness" class="font-headline font-black text-sm text-teal-700">5 / 5</span>
          </div>
          <div class="p-2 bg-white rounded-xl border border-slate-100">
            <span class="block text-[10px] font-bold text-slate-400 uppercase">Location</span>
            <span id="modalLocation" class="font-headline font-black text-sm text-blue-700">5 / 5</span>
          </div>
          <div class="p-2 bg-white rounded-xl border border-slate-100">
            <span class="block text-[10px] font-bold text-slate-400 uppercase">Value</span>
            <span id="modalValue" class="font-headline font-black text-sm text-emerald-700">5 / 5</span>
          </div>
          <div class="p-2 bg-white rounded-xl border border-slate-100">
            <span class="block text-[10px] font-bold text-slate-400 uppercase">Hospitality</span>
            <span id="modalHospitality" class="font-headline font-black text-sm text-indigo-700">5 / 5</span>
          </div>
        </div>
      </div>

      <!-- Review Body -->
      <div class="p-5 rounded-2xl bg-white border border-slate-200 space-y-2">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Guest Feedback</div>
        <h4 id="modalReviewTitle" class="font-headline font-bold text-sm text-slate-900"></h4>
        <p id="modalReviewText" class="text-slate-700 leading-relaxed text-xs whitespace-pre-line"></p>
      </div>

      <!-- Host / Staff Official Responses Section -->
      <div class="p-5 rounded-2xl bg-white border border-slate-200 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
          <h4 class="font-headline font-bold text-xs text-slate-900 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-brand-700">forum</span>
            <span>Official Management &amp; Host Responses</span>
          </h4>
          <button type="button" onclick="showAddResponseForm()" class="text-xs font-bold text-brand-700 hover:underline">
            + Add Response
          </button>
        </div>

        <div id="modalResponsesList" class="space-y-2.5">
          <!-- Dynamic responses inserted here -->
        </div>

        <!-- Add Response Sub-form -->
        <div id="modalAddResponseContainer" class="hidden pt-3 border-t border-slate-100 space-y-2">
          <form method="POST" action="<?= url('admin/reviews/add-response') ?>" class="space-y-2">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="review_id" id="responseFormReviewId" value="">
            
            <div class="grid grid-cols-2 gap-2">
              <input type="text" name="responder_name" placeholder="Responder Name (e.g. Host Kabir / Concierge)" required 
                     class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none">
              <select name="responder_type" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none">
                <option value="admin">Stayora Executive Management</option>
                <option value="owner">Property Owner / Host</option>
                <option value="staff">Villa Butler / Staff</option>
              </select>
            </div>

            <textarea name="response_text" rows="3" placeholder="Compose official response to the guest..." required 
                      class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none"></textarea>

            <div class="flex justify-end gap-2">
              <button type="button" onclick="hideAddResponseForm()" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 font-semibold">Cancel</button>
              <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold shadow-2xs">Publish Response</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Private Administrative Notes -->
      <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Internal Admin Notes (Private)</span>
          <span class="text-[10px] text-slate-400">Never visible to guests</span>
        </div>
        <p id="modalAdminNotes" class="text-slate-600 italic text-xs">No administrative notes recorded.</p>
      </div>

      <!-- Audit Trail Log -->
      <div class="space-y-2">
        <h4 class="font-headline font-bold text-xs text-slate-900 flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px] text-slate-500">history</span>
          <span>Moderation Audit Trail</span>
        </h4>
        <div id="modalAuditLogs" class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
          <!-- Dynamic audit logs inserted here -->
        </div>
      </div>

    </div>

    <!-- Modal Footer Actions -->
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/75 shrink-0 flex items-center justify-between flex-wrap gap-2">
      <div class="flex items-center gap-2">
        <button type="button" onclick="openEditModalFromDetail()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold transition-all flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">edit</span>
          <span>Edit Content</span>
        </button>

        <form id="modalDeleteForm" method="POST" action="<?= url('admin/reviews/delete') ?>" onsubmit="return confirm('Permanently remove this review? This action cannot be undone.')">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
          <input type="hidden" name="review_id" id="modalDeleteReviewId" value="">
          <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px]">delete</span>
            <span>Delete</span>
          </button>
        </form>
      </div>

      <div class="flex items-center gap-2" id="modalActionButtons">
        <!-- Moderation status buttons updated via JS -->
      </div>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     8. MODAL: STRUCTURED REJECT WITH REASONS
     ══════════════════════════════════════════════ -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-3xl border border-slate-200 shadow-2xl overflow-hidden transform transition-all p-6 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">cancel</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-sm text-slate-900">Reject Guest Review</h3>
          <p class="text-[11px] text-slate-400">Review #<span id="rejectReviewIdText"></span> by <span id="rejectGuestNameText"></span></p>
        </div>
      </div>
      <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>

    <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="space-y-4 text-xs">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="review_id" id="rejectReviewId" value="">
      <input type="hidden" name="status" value="rejected">

      <div>
        <label class="block font-bold text-slate-700 mb-1.5">Standard Policy Rejection Reason</label>
        <select name="predefined_reason" onchange="handleRejectReasonChange(this)" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 outline-none">
          <option value="Contains inappropriate, defamatory, or abusive language.">Inappropriate or abusive language</option>
          <option value="Irrelevant claims or not based on actual completed stay.">Irrelevant claims or false stay experience</option>
          <option value="Commercial solicitation, advertising, or spam.">Commercial spam or marketing solicitation</option>
          <option value="Contains confidential personal contact information or privacy violation.">Privacy policy or personal data breach</option>
          <option value="Conflict of interest or competitor extortion.">Conflict of interest or verified malicious extortion</option>
          <option value="custom">Other / Custom specific reason...</option>
        </select>
      </div>

      <div id="customReasonContainer">
        <label class="block font-bold text-slate-700 mb-1.5">Specific Rejection Reason</label>
        <textarea name="rejection_reason" id="rejectionReasonInput" rows="3" required 
                  class="w-full p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none focus:bg-white focus:border-rose-500">Contains inappropriate, defamatory, or abusive language.</textarea>
      </div>

      <div>
        <label class="block font-bold text-slate-700 mb-1.5">Internal Moderator Notes (Optional)</label>
        <textarea name="admin_notes" rows="2" placeholder="Private internal record..." 
                  class="w-full p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none focus:bg-white"></textarea>
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-xs">
          Confirm Rejection
        </button>
      </div>

    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     9. MODAL: EDIT REVIEW CONTENT (With Audit Log)
     ══════════════════════════════════════════════ -->
<div id="editContentModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-lg rounded-3xl border border-slate-200 shadow-2xl overflow-hidden p-6 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">edit_note</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-sm text-slate-900">Edit Review Content</h3>
          <p class="text-[11px] text-slate-400">All modifications are immutably logged to the audit trail.</p>
        </div>
      </div>
      <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>

    <form method="POST" action="<?= url('admin/reviews/edit-content') ?>" class="space-y-4 text-xs">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="review_id" id="editReviewId" value="">

      <div>
        <label class="block font-bold text-slate-700 mb-1.5">Review Title</label>
        <input type="text" name="review_title" id="editReviewTitle" required 
               class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-800 outline-none focus:bg-white focus:border-brand-500">
      </div>

      <div>
        <label class="block font-bold text-slate-700 mb-1.5">Review Content Text</label>
        <textarea name="review_text" id="editReviewText" rows="5" required 
                  class="w-full p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none focus:bg-white focus:border-brand-500 leading-relaxed"></textarea>
      </div>

      <div>
        <label class="block font-bold text-slate-700 mb-1.5">Audit Note for Edit</label>
        <input type="text" name="admin_notes" placeholder="e.g. Redacted private phone number as per privacy policy" required 
               class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none">
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold shadow-xs">
          Save Changes &amp; Log
        </button>
      </div>

    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     10. MODAL: POST OFFICIAL HOST / STAFF RESPONSE
     ══════════════════════════════════════════════ -->
<div id="responseModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-lg rounded-3xl border border-slate-200 shadow-2xl overflow-hidden p-6 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">reply</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-sm text-slate-900">Post Host / Management Response</h3>
          <p class="text-[11px] text-slate-400">Responding to <span id="responseGuestName" class="font-bold text-slate-700"></span></p>
        </div>
      </div>
      <button type="button" onclick="closeResponseModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>

    <form method="POST" action="<?= url('admin/reviews/add-response') ?>" class="space-y-4 text-xs">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="review_id" id="responseReviewId" value="">

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-bold text-slate-700 mb-1.5">Official Responder Name</label>
          <input type="text" name="responder_name" value="Stayora Concierge &amp; Host Management" required 
                 class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-800 outline-none">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1.5">Role / Capacity</label>
          <select name="responder_type" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 outline-none">
            <option value="admin">Platform Management</option>
            <option value="owner">Estate Owner / Host</option>
            <option value="staff">Villa Butler / Manager</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block font-bold text-slate-700 mb-1.5">Official Response Content</label>
        <textarea name="response_text" rows="4" placeholder="Dear Guest, thank you so much for your kind words and for choosing to stay with us..." required 
                  class="w-full p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none focus:bg-white focus:border-brand-500 leading-relaxed"></textarea>
        <p class="text-[10px] text-slate-400 mt-1">This response will be visibly published underneath the guest's review on public villa pages.</p>
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
        <button type="button" onclick="closeResponseModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs">
          Publish Official Response
        </button>
      </div>

    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     11. MODAL: BULK MODERATION CONFIRMATION
     ══════════════════════════════════════════════ -->
<div id="bulkModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-3xl border border-slate-200 shadow-2xl overflow-hidden p-6 space-y-4">
    
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">done_all</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-sm text-slate-900">Bulk Moderation Action</h3>
          <p class="text-[11px] text-slate-400">Action on <span id="bulkModalCount" class="font-bold text-slate-700"></span> review(s)</p>
        </div>
      </div>
      <button type="button" onclick="closeBulkModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>

    <form id="bulkActionForm" method="POST" action="<?= url('admin/reviews/bulk-update') ?>" class="space-y-4 text-xs">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="bulk_action" id="bulkActionType" value="">
      <div id="bulkIdsContainer"></div>

      <p class="text-slate-600 text-xs" id="bulkActionPrompt">
        Are you sure you want to proceed with this bulk action?
      </p>

      <div id="bulkReasonField" class="hidden">
        <label class="block font-bold text-slate-700 mb-1.5">Reason for Bulk Action</label>
        <input type="text" name="bulk_reason" placeholder="e.g. Batch moderation during weekly audit" 
               class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 outline-none">
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
        <button type="button" onclick="closeBulkModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
          Cancel
        </button>
        <button type="submit" id="bulkConfirmBtn" class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold shadow-xs">
          Confirm Action
        </button>
      </div>

    </form>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     12. JAVASCRIPT LOGIC & ASYNC CONTROLS
     ══════════════════════════════════════════════ -->
<script>
let activeLoadedReview = null;

// Bulk Selection Handlers
function toggleSelectAll(master) {
  const checkboxes = document.querySelectorAll('.review-checkbox');
  checkboxes.forEach(cb => cb.checked = master.checked);
  updateSelectedCount();
}

function updateSelectedCount() {
  const checked = document.querySelectorAll('.review-checkbox:checked');
  const count = checked.length;
  const bar = document.getElementById('bulkActionBar');
  const countEl = document.getElementById('selectedCount');
  
  countEl.innerText = count;
  if (count > 0) {
    bar.classList.remove('hidden');
    bar.classList.add('flex');
  } else {
    bar.classList.add('hidden');
    bar.classList.remove('flex');
    const master = document.getElementById('selectAllCheckbox');
    if (master) master.checked = false;
  }
}

function clearBulkSelection() {
  document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = false);
  const master = document.getElementById('selectAllCheckbox');
  if (master) master.checked = false;
  updateSelectedCount();
}

function openBulkModal(action) {
  const checked = document.querySelectorAll('.review-checkbox:checked');
  if (checked.length === 0) return;

  document.getElementById('bulkActionType').value = action;
  document.getElementById('bulkModalCount').innerText = checked.length;

  const container = document.getElementById('bulkIdsContainer');
  container.innerHTML = '';
  checked.forEach(cb => {
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'review_ids[]';
    hidden.value = cb.value;
    container.appendChild(hidden);
  });

  const prompt = document.getElementById('bulkActionPrompt');
  const reasonField = document.getElementById('bulkReasonField');
  const btn = document.getElementById('bulkConfirmBtn');

  if (action === 'approve') {
    prompt.innerText = `You are about to publish and approve ${checked.length} reviews. They will become immediately visible on public property pages.`;
    reasonField.classList.add('hidden');
    btn.className = "px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs";
    btn.innerText = "Confirm Bulk Approval";
  } else if (action === 'reject') {
    prompt.innerText = `You are about to reject ${checked.length} reviews.`;
    reasonField.classList.remove('hidden');
    btn.className = "px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-xs";
    btn.innerText = "Confirm Bulk Rejection";
  } else if (action === 'hide') {
    prompt.innerText = `You are about to hide ${checked.length} reviews from public views.`;
    reasonField.classList.add('hidden');
    btn.className = "px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold shadow-xs";
    btn.innerText = "Confirm Bulk Hide";
  }

  document.getElementById('bulkModal').classList.remove('hidden');
}

function closeBulkModal() {
  document.getElementById('bulkModal').classList.add('hidden');
}

// 360 Details Modal Handler
function openReviewModal(reviewId) {
  const modal = document.getElementById('reviewDetailModal');
  modal.classList.remove('hidden');

  // Fetch JSON details
  fetch(`<?= url('admin/reviews/details/') ?>${reviewId}`)
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert(data.message || 'Error fetching review details.');
        closeReviewModal();
        return;
      }
      activeLoadedReview = data.review;
      populateReviewModal(data.review);
    })
    .catch(err => {
      console.error(err);
      alert('Network error while retrieving review details.');
      closeReviewModal();
    });
}

function closeReviewModal() {
  document.getElementById('reviewDetailModal').classList.add('hidden');
  activeLoadedReview = null;
  hideAddResponseForm();
}

function populateReviewModal(r) {
  document.getElementById('modalReviewId').innerText = `#REV-${String(r.id).padStart(5, '0')}`;
  
  const statusEl = document.getElementById('modalReviewStatus');
  statusEl.innerText = r.status.toUpperCase();
  statusEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase ';
  if (r.status === 'approved') statusEl.className += 'bg-emerald-100 text-emerald-800';
  else if (r.status === 'pending') statusEl.className += 'bg-amber-100 text-amber-800';
  else if (r.status === 'rejected') statusEl.className += 'bg-rose-100 text-rose-800';
  else if (r.status === 'hidden') statusEl.className += 'bg-slate-100 text-slate-800';
  else if (r.status === 'flagged') statusEl.className += 'bg-purple-100 text-purple-800';

  const vBadge = document.getElementById('modalVerifiedBadge');
  if (parseInt(r.is_verified_stay) === 1) {
    vBadge.classList.remove('hidden');
  } else {
    vBadge.classList.add('hidden');
  }

  document.getElementById('modalCreatedDate').innerText = `Submitted on ${r.created_at}`;

  // Guest Details
  document.getElementById('modalGuestName').innerText = r.guest_name;
  document.getElementById('modalGuestEmail').innerText = r.guest_email || 'No email provided';
  document.getElementById('modalGuestPhone').innerText = r.user_phone ? `Phone: ${r.user_phone}` : '';
  document.getElementById('modalGuestTier').innerText = (r.membership_tier && r.membership_tier !== 'none') ? r.membership_tier.toUpperCase() : 'STANDARD';

  const gLink = document.getElementById('modalGuestLink');
  if (r.user_id) {
    gLink.href = `<?= url('admin/guests/view/') ?>${r.user_id}?tab=reviews`;
    gLink.classList.remove('hidden');
  } else {
    gLink.classList.add('hidden');
  }

  // Property Details
  document.getElementById('modalPropertyTitle').innerText = r.property_title || 'Estate';
  document.getElementById('modalPropertyLocation').innerText = r.property_location || 'Location N/A';
  
  if (r.check_in && r.check_out) {
    document.getElementById('modalStayDates').innerText = `Stay Dates: ${r.check_in} to ${r.check_out}`;
  } else {
    document.getElementById('modalStayDates').innerText = `Stay Dates: Not linked to booking`;
  }
  document.getElementById('modalBookingRef').innerText = r.booking_reference ? `Booking Ref: ${r.booking_reference}` : (r.booking_id ? `Booking ID: #${r.booking_id}` : 'General Feedback');
  document.getElementById('modalBookingStatus').innerText = r.booking_status ? r.booking_status.replace('_', ' ') : 'N/A';

  // Ratings
  document.getElementById('modalOverallRating').innerText = parseFloat(r.overall_rating).toFixed(1);
  document.getElementById('modalCleanliness').innerText = `${r.cleanliness_rating} / 5`;
  document.getElementById('modalLocation').innerText = `${r.location_rating} / 5`;
  document.getElementById('modalValue').innerText = `${r.value_rating} / 5`;
  document.getElementById('modalHospitality').innerText = `${r.hospitality_rating} / 5`;

  // Review Text
  document.getElementById('modalReviewTitle').innerText = r.review_title ? `"${r.review_title}"` : 'Untitled Review';
  document.getElementById('modalReviewText').innerText = r.review_text;

  // Admin notes
  document.getElementById('modalAdminNotes').innerText = r.admin_notes || 'No administrative notes recorded.';

  // Delete form hidden ID
  document.getElementById('modalDeleteReviewId').value = r.id;
  document.getElementById('responseFormReviewId').value = r.id;

  // Responses
  const respList = document.getElementById('modalResponsesList');
  respList.innerHTML = '';
  if (r.responses && r.responses.length > 0) {
    r.responses.forEach(resp => {
      const card = document.createElement('div');
      card.className = "p-3.5 bg-blue-50/60 rounded-xl border border-blue-100 text-xs space-y-1";
      card.innerHTML = `
        <div class="flex items-center justify-between">
          <span class="font-bold text-slate-900">${resp.responder_name} <span class="text-[10px] text-blue-700 bg-blue-100 px-1.5 py-0.2 rounded capitalize">${resp.responder_type}</span></span>
          <span class="text-[10px] text-slate-400">${resp.created_at}</span>
        </div>
        <p class="text-slate-700">${resp.response_text}</p>
        <div class="pt-1 flex justify-end">
          <form method="POST" action="<?= url('admin/reviews/delete-response') ?>" onsubmit="return confirm('Remove this response?')">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="response_id" value="${resp.id}">
            <button type="submit" class="text-[10px] text-rose-600 hover:underline">Delete Response</button>
          </form>
        </div>
      `;
      respList.appendChild(card);
    });
  } else {
    respList.innerHTML = `<p class="text-slate-400 italic text-[11px]">No official response posted yet.</p>`;
  }

  // Audit Logs
  const auditList = document.getElementById('modalAuditLogs');
  auditList.innerHTML = '';
  if (r.audit_logs && r.audit_logs.length > 0) {
    r.audit_logs.forEach(log => {
      const item = document.createElement('div');
      item.className = "p-2 bg-slate-50 rounded-xl border border-slate-100 text-[11px] flex items-center justify-between";
      item.innerHTML = `
        <div>
          <span class="font-bold text-slate-800">${log.performed_by_name || 'System'}:</span>
          <span class="text-slate-600">${log.change_summary || log.action}</span>
        </div>
        <span class="text-[10px] text-slate-400 shrink-0 ml-2">${log.created_at}</span>
      `;
      auditList.appendChild(item);
    });
  } else {
    auditList.innerHTML = `<p class="text-slate-400 italic text-[11px]">No audit events logged.</p>`;
  }

  // Action Buttons
  const btnContainer = document.getElementById('modalActionButtons');
  btnContainer.innerHTML = '';

  if (r.status !== 'approved') {
    btnContainer.innerHTML += `
      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="review_id" value="${r.id}">
        <input type="hidden" name="status" value="approved">
        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">check_circle</span>
          <span>Approve &amp; Publish</span>
        </button>
      </form>
    `;
  }

  if (r.status !== 'rejected') {
    btnContainer.innerHTML += `
      <button type="button" onclick="openRejectModal(${r.id}, '${escapeQuote(r.guest_name)}')" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold transition-all flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px]">cancel</span>
        <span>Reject</span>
      </button>
    `;
  }

  if (r.status === 'approved') {
    btnContainer.innerHTML += `
      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="review_id" value="${r.id}">
        <input type="hidden" name="status" value="hidden">
        <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-bold transition-all flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">visibility_off</span>
          <span>Hide</span>
        </button>
      </form>
    `;
  } else if (r.status === 'hidden') {
    btnContainer.innerHTML += `
      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="review_id" value="${r.id}">
        <input type="hidden" name="status" value="approved">
        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">publish</span>
          <span>Restore to Live</span>
        </button>
      </form>
    `;
  }

  if (r.status !== 'flagged') {
    btnContainer.innerHTML += `
      <form method="POST" action="<?= url('admin/reviews/update-status') ?>" class="inline">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="review_id" value="${r.id}">
        <input type="hidden" name="status" value="flagged">
        <button type="submit" class="px-3 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold transition-all" title="Flag for Investigation">
          <span class="material-symbols-outlined text-[16px]">flag</span>
        </button>
      </form>
    `;
  }
}

function showAddResponseForm() {
  document.getElementById('modalAddResponseContainer').classList.remove('hidden');
}

function hideAddResponseForm() {
  const container = document.getElementById('modalAddResponseContainer');
  if (container) container.classList.add('hidden');
}

// Reject Modal Handlers
function openRejectModal(reviewId, guestName) {
  document.getElementById('rejectReviewId').value = reviewId;
  document.getElementById('rejectReviewIdText').innerText = String(reviewId).padStart(5, '0');
  document.getElementById('rejectGuestNameText').innerText = guestName;
  document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
  document.getElementById('rejectModal').classList.add('hidden');
}

function handleRejectReasonChange(select) {
  const custom = document.getElementById('rejectionReasonInput');
  if (select.value === 'custom') {
    custom.value = '';
    custom.focus();
  } else {
    custom.value = select.value;
  }
}

// Edit Content Modal Handlers
function openEditModalFromDetail() {
  if (!activeLoadedReview) return;
  document.getElementById('editReviewId').value = activeLoadedReview.id;
  document.getElementById('editReviewTitle').value = activeLoadedReview.review_title || '';
  document.getElementById('editReviewText').value = activeLoadedReview.review_text || '';
  document.getElementById('editContentModal').classList.remove('hidden');
}

function closeEditModal() {
  document.getElementById('editContentModal').classList.add('hidden');
}

// Response Modal Handlers
function openResponseModal(reviewId, guestName, propertyTitle) {
  document.getElementById('responseReviewId').value = reviewId;
  document.getElementById('responseGuestName').innerText = `${guestName} (${propertyTitle})`;
  document.getElementById('responseModal').classList.remove('hidden');
}

function closeResponseModal() {
  document.getElementById('responseModal').classList.add('hidden');
}

function escapeQuote(str) {
  if (!str) return '';
  return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
</script>

<?php
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php';
?>
