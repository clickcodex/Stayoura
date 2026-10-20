<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = $currentPage ?? 'dashboard';
$adminName = htmlspecialchars($admin['name'] ?? $_SESSION['user_name'] ?? 'Administrator');
$adminEmail = htmlspecialchars($admin['email'] ?? $_SESSION['user_email'] ?? 'admin@stayora.com');
$adminAvatar = !empty($admin['profile_image']) ? asset('assets/images/uploads/avatars/' . htmlspecialchars($admin['profile_image'])) : '';
?>

<!-- Mobile Sidebar Backdrop Overlay -->
<div id="sidebar-backdrop" onclick="toggleAdminSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

<!-- ══════════════════════════════════════════════
     ADMIN SIDEBAR NAVIGATION
     ══════════════════════════════════════════════ -->
<aside id="admin-sidebar" class="fixed lg:sticky top-0 left-0 h-screen w-72 bg-slate-900 text-slate-300 z-50 flex flex-col border-r border-slate-800 shadow-2xl lg:shadow-none -translate-x-full lg:translate-x-0 shrink-0">
  
  <!-- Brand Header -->
  <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/40">
    <a href="<?= url('admin/dashboard') ?>" class="flex items-center gap-3 text-decoration-none group">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-400 p-0.5 shadow-md shadow-brand-700/20 flex items-center justify-center shrink-0">
        <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center p-1.5 overflow-hidden">
          <img src="<?= asset('assets/images/logo.webp') ?>" alt="Stayora" class="w-full h-full object-contain" onerror="this.src='https://ui-avatars.com/api/?name=Stayora&background=1D4ED8&color=fff&bold=true';" />
        </div>
      </div>
      <div>
        <span class="font-headline font-black text-xl text-white tracking-tight leading-none block">
          Stay<span class="text-brand-500">ora</span>
        </span>
        <span class="text-[9.5px] font-bold uppercase tracking-widest text-slate-400 mt-1 block">
          Executive Console
        </span>
      </div>
    </a>

    <!-- Mobile Close Button -->
    <button type="button" onclick="toggleAdminSidebar()" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Close sidebar">
      <span class="material-symbols-outlined text-xl">close</span>
    </button>
  </div>

  <!-- Navigation Links Area -->
  <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
    
    <!-- Primary Management Section -->
    <div>
      <div class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
        Platform Operations
      </div>
      <nav class="space-y-1">
        
        <!-- Dashboard -->
        <a href="<?= url('admin/dashboard') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'dashboard' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">dashboard</span>
            <span>Dashboard</span>
          </div>
          <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-md <?= $currentPage === 'dashboard' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' ?>">
            v2.0
          </span>
        </a>

        <!-- Manage Properties -->
        <a href="<?= url('admin/properties') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'properties' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'properties' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">villa</span>
            <span>Manage Properties</span>
          </div>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= $currentPage === 'properties' ? 'bg-white/20 text-white' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' ?>">
            Active
          </span>
        </a>

        <?php
        $activeOwnersCount = 0;
        try {
            $dbOwnersNav = \App\Config\Database::connect();
            $oRes = $dbOwnersNav->query("SELECT COUNT(*) as c FROM owners WHERE status = 'active'");
            if ($oRes && $oRow = $oRes->fetch_assoc()) {
                $activeOwnersCount = (int)$oRow['c'];
            }
        } catch (\Throwable $e) {}
        ?>

        <!-- Property Owners (Full Lifecycle Management) -->
        <a href="<?= url('admin/owners') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'owners' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'owners' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">real_estate_agent</span>
            <span>Property Owners</span>
          </div>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= $currentPage === 'owners' ? 'bg-white/20 text-white' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' ?>">
            <?= $activeOwnersCount > 0 ? $activeOwnersCount . ' Active' : 'Active' ?>
          </span>
        </a>

        <!-- Amenities & Rules Management -->
        <a href="<?= url('admin/amenities-rules') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'amenities_rules' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'amenities_rules' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">tune</span>
            <span>Amenities &amp; Rules</span>
          </div>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= $currentPage === 'amenities_rules' ? 'bg-white/20 text-white' : 'bg-brand-500/10 text-brand-400 border border-brand-500/20' ?>">
            Catalog
          </span>
        </a>

        <?php
        $pendingBookingsBadge = 0;
        $totalGuestsBadge = 0;
        $pendingReviewsBadge = 0;
        try {
            $dbNav = \App\Config\Database::connect();
            $bStmt = $dbNav->query("SELECT COUNT(*) as c FROM booking_requests WHERE status = 'pending'");
            if ($bStmt && $bRow = $bStmt->fetch_assoc()) {
                $pendingBookingsBadge = (int)$bRow['c'];
            }
            $gStmt = $dbNav->query("SELECT COUNT(*) as c FROM users WHERE status = 'active'");
            if ($gStmt && $gRow = $gStmt->fetch_assoc()) {
                $totalGuestsBadge = (int)$gRow['c'];
            }
            $rStmt = $dbNav->query("SELECT COUNT(*) as c FROM reviews WHERE status = 'pending'");
            if ($rStmt && $rRow = $rStmt->fetch_assoc()) {
                $pendingReviewsBadge = (int)$rRow['c'];
            }
        } catch (\Throwable $e) {}
        ?>

        <!-- Bookings & Stays (Active Core Management) -->
        <a href="<?= url('admin/bookings') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= in_array($currentPage, ['bookings', 'booking_calendar', 'booking_reports', 'booking_show', 'booking_create']) ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= in_array($currentPage, ['bookings', 'booking_calendar', 'booking_reports', 'booking_show', 'booking_create']) ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">calendar_month</span>
            <span>Bookings &amp; Stays</span>
          </div>
          <?php if ($pendingBookingsBadge > 0): ?>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-slate-950 animate-pulse">
              <?= $pendingBookingsBadge ?> new
            </span>
          <?php else: ?>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= in_array($currentPage, ['bookings', 'booking_calendar', 'booking_reports', 'booking_show', 'booking_create']) ? 'bg-white/20 text-white' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' ?>">
              Active
            </span>
          <?php endif; ?>
        </a>

        <!-- Guests & Members (Executive Guest Management) -->
        <a href="<?= url('admin/guests') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'guests' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'guests' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">group</span>
            <span>Guests &amp; Members</span>
          </div>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= $currentPage === 'guests' ? 'bg-white/20 text-white' : 'bg-brand-500/10 text-brand-400 border border-brand-500/20' ?>">
            <?= $totalGuestsBadge > 0 ? $totalGuestsBadge . ' Guests' : 'Active' ?>
          </span>
        </a>

        <!-- Guest Reviews & Moderation (Active Executive Moderation) -->
        <a href="<?= url('admin/reviews') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'reviews' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'reviews' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">hotel_class</span>
            <span>Guest Reviews</span>
          </div>
          <?php if ($pendingReviewsBadge > 0): ?>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-slate-950 animate-pulse">
              <?= $pendingReviewsBadge ?> new
            </span>
          <?php else: ?>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= $currentPage === 'reviews' ? 'bg-white/20 text-white' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' ?>">
              Live
            </span>
          <?php endif; ?>
        </a>

        <!-- Platform Settings (Active Platform Configuration Hub) -->
        <a href="<?= url('admin/settings') ?>" 
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $currentPage === 'settings' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white shadow-lg shadow-brand-700/30 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[19px] <?= $currentPage === 'settings' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">settings</span>
            <span>Platform Settings</span>
          </div>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold <?= $currentPage === 'settings' ? 'bg-white/20 text-white' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' ?>">
            Live
          </span>
        </a>

      </nav>
    </div>

    <!-- Future Administration Modules -->
    <div>
      <div class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
        Future Modules
      </div>
      <nav class="space-y-1">

        <!-- Payment Gateway Engine & Payout Reconciliation -->
        <a href="javascript:void(0)" onclick="handleUpcomingModule('Automated Payment Gateways & Payout Reconciliation')" 
           class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all group cursor-pointer">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[18px] text-slate-500 group-hover:text-slate-300 transition-colors">account_balance_wallet</span>
            <span>Automated Payouts</span>
          </div>
          <span class="text-[9.5px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-500 font-medium">Phase 3</span>
        </a>

      </nav>
    </div>

    <!-- Quick External Portal Link -->
    <div class="pt-2 border-t border-slate-800/60">
      <a href="<?= url('farmhouses') ?>" target="_blank" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all group">
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-[18px] text-slate-400 group-hover:text-sky-400 transition-colors">travel_explore</span>
          <span>Public Directory</span>
        </div>
        <span class="material-symbols-outlined text-xs text-slate-400">open_in_new</span>
      </a>
    </div>

  </div>

  <!-- Admin User Profile Chip at Bottom -->
  <div class="p-4 border-t border-slate-800 bg-slate-950/60">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-10 h-10 rounded-full bg-brand-700 text-white font-bold text-sm flex items-center justify-center shadow overflow-hidden shrink-0">
        <?php if (!empty($adminAvatar)): ?>
          <img src="<?= $adminAvatar ?>" alt="Avatar" class="w-full h-full object-cover" onerror="this.style.display='none';">
        <?php else: ?>
          <span><?= strtoupper(substr($adminName, 0, 1)) ?></span>
        <?php endif; ?>
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-xs font-bold text-white truncate"><?= $adminName ?></div>
        <div class="text-[10px] text-slate-400 truncate"><?= $adminEmail ?></div>
        <div class="flex items-center gap-1.5 mt-0.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
          <span class="text-[9.5px] font-bold text-emerald-400 uppercase tracking-wider">Super Administrator</span>
        </div>
      </div>
    </div>

    <a href="<?= url('admin/logout') ?>" onclick="handleAdminSignOut(event, this.href)" class="w-full py-2 px-3 rounded-xl bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/20 hover:border-red-500 text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer">
      <span class="material-symbols-outlined text-base">logout</span>
      <span>Secure Sign Out</span>
    </a>
  </div>

</aside>
