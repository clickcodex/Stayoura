<?php
$pageTitle   = $pageTitle ?? "Booking #{$booking['booking_reference']} • Stayora Executive Console";
$currentPage = "bookings";
$headerTitle = "Stay Dossier & Operations";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

$st = $booking['status'] ?? 'pending';
$refCode = $booking['booking_reference'] ?? ('STAY-BK-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT));
$nights = max(1, (int)round((strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400));
$totalPrice = (float)($booking['price'] ?? 0);
$paid = (float)($booking['amount_paid'] ?? 0);
$balance = (float)($booking['balance_amount'] ?? max(0, $totalPrice - $paid));
$isFullyPaid = ($balance <= 0 && $totalPrice > 0);

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
$style = $statusStyles[$st] ?? $statusStyles['pending'];

$addons = !empty($booking['addons_selected']) 
    ? (is_string($booking['addons_selected']) ? json_decode($booking['addons_selected'], true) : $booking['addons_selected']) 
    : [];

$payments     = $booking['payments'] ?? [];
$extraCharges = $booking['extra_charges'] ?? [];
$auditLogs    = $booking['audit_logs'] ?? [];
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
        <span class="text-brand-700 font-bold">#<?= htmlspecialchars($refCode) ?></span>
      </nav>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-700/20">
          <span class="material-symbols-outlined text-xl">luggage</span>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-headline font-bold text-xl sm:text-2xl text-slate-900 tracking-tight leading-none">
              Reservation #<?= htmlspecialchars($refCode) ?>
            </h1>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold border <?= $style['bg'] ?>">
              <span class="w-1.5 h-1.5 rounded-full <?= $style['dot'] ?>"></span>
              <span><?= $style['label'] ?></span>
            </span>
          </div>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Booked on <?= date('d M Y, h:i A', strtotime($booking['created_at'])) ?>
            <span class="mx-1">•</span>
            Property: <strong class="text-slate-700"><?= htmlspecialchars($booking['farmhouse_title'] ?? '') ?></strong>
          </p>
        </div>
      </div>
    </div>

    <!-- Quick Action Toolbelt -->
    <div class="flex flex-wrap items-center gap-2">
      
      <!-- Back to list -->
      <a href="<?= url('admin/bookings') ?>" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all flex items-center gap-1">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        <span>All Bookings</span>
      </a>

      <!-- Status-driven Operational Buttons -->
      <?php if (in_array($st, ['confirmed', 'approved'])): ?>
        <button type="button" onclick="openCheckInModal(<?= htmlspecialchars(json_encode($booking)) ?>)" 
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">hotel</span>
          <span>Check-In Guest</span>
        </button>
      <?php elseif ($st === 'checked_in'): ?>
        <button type="button" onclick="openCheckOutModal(<?= htmlspecialchars(json_encode($booking)) ?>)" 
                class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">logout</span>
          <span>Check-Out Guest</span>
        </button>
        <button type="button" onclick="openExtendStayModal()" 
                class="px-3 py-2 rounded-xl bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">more_time</span>
          <span>Extend Stay</span>
        </button>
      <?php endif; ?>

      <!-- Extra Charge Button -->
      <?php if (in_array($st, ['confirmed', 'approved', 'checked_in'])): ?>
        <button type="button" onclick="openAddChargeModal()" 
                class="px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">add_circle</span>
          <span>+ Add Charge</span>
        </button>
      <?php endif; ?>

      <!-- Record Payment Button -->
      <button type="button" onclick="openPaymentModal(<?= htmlspecialchars(json_encode($booking)) ?>)" 
              class="px-3.5 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md shadow-brand-700/20 transition-all flex items-center gap-1.5 cursor-pointer">
        <span class="material-symbols-outlined text-base">payments</span>
        <span>Record Payment</span>
      </button>

      <!-- Status change modal -->
      <button type="button" onclick="openStatusModal(<?= $booking['id'] ?>, '<?= $st ?>', '<?= htmlspecialchars($refCode) ?>')" 
              class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer" title="Change Status">
        <span class="material-symbols-outlined text-base">tune</span>
        <span>Status</span>
      </button>

      <!-- Printable Voucher / Invoice -->
      <a href="<?= url('admin/bookings/invoice/' . $booking['id']) ?>" target="_blank" 
         class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all flex items-center gap-1.5" title="Print Tax Invoice & Voucher">
        <span class="material-symbols-outlined text-base">print</span>
        <span>Invoice</span>
      </a>

    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       VISUAL STAY LIFECYCLE STEPPER
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
    <div class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-4">
      Stay Lifecycle &amp; Operational Milestone Progress
    </div>

    <?php if (in_array($st, ['cancelled', 'rejected', 'no_show'])): ?>
      <!-- Cancelled Warning Banner -->
      <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3">
        <span class="material-symbols-outlined text-2xl text-rose-600 shrink-0 mt-0.5">warning</span>
        <div>
          <h4 class="font-bold text-sm">Stay <?= ucfirst($st) ?></h4>
          <p class="text-xs text-rose-700 mt-0.5">
            <?= htmlspecialchars($booking['cancellation_reason'] ?: ($booking['admin_notes'] ?: 'No specific cancellation remarks provided.')) ?>
          </p>
          <?php if (!empty($booking['cancelled_at'])): ?>
            <span class="text-[11px] text-rose-500 mt-1 block">
              Cancelled on <?= date('d M Y, h:i A', strtotime($booking['cancelled_at'])) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <!-- 4-Stage Stepper -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 relative">
        
        <!-- Step 1: Requested -->
        <div class="flex items-center gap-3 p-3 rounded-2xl <?= in_array($st, ['pending', 'approved', 'confirmed', 'checked_in', 'checked_out', 'completed']) ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' ?>">
          <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
            ✓
          </div>
          <div>
            <div class="text-xs font-bold text-slate-900">1. Booking Placed</div>
            <div class="text-[11px] text-slate-500"><?= date('d M, h:i A', strtotime($booking['created_at'])) ?></div>
          </div>
        </div>

        <!-- Step 2: Confirmed -->
        <?php $step2Done = in_array($st, ['approved', 'confirmed', 'checked_in', 'checked_out', 'completed']); ?>
        <div class="flex items-center gap-3 p-3 rounded-2xl <?= $step2Done ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200 animate-pulse' ?>">
          <div class="w-8 h-8 rounded-full <?= $step2Done ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' ?> flex items-center justify-center font-bold text-sm shrink-0">
            <?= $step2Done ? '✓' : '2' ?>
          </div>
          <div>
            <div class="text-xs font-bold text-slate-900">2. Confirmed</div>
            <div class="text-[11px] text-slate-500">
              <?= $step2Done ? 'Inventory Locked' : 'Awaiting Approval' ?>
            </div>
          </div>
        </div>

        <!-- Step 3: Checked In -->
        <?php $step3Done = in_array($st, ['checked_in', 'checked_out', 'completed']); ?>
        <div class="flex items-center gap-3 p-3 rounded-2xl <?= $step3Done ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' ?>">
          <div class="w-8 h-8 rounded-full <?= $step3Done ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' ?> flex items-center justify-center font-bold text-sm shrink-0">
            <?= $step3Done ? '✓' : '3' ?>
          </div>
          <div>
            <div class="text-xs font-bold text-slate-900">3. Checked In</div>
            <div class="text-[11px] text-slate-500">
              <?php if (!empty($booking['actual_check_in_at'])): ?>
                <?= date('d M, h:i A', strtotime($booking['actual_check_in_at'])) ?>
              <?php else: ?>
                Sched: <?= date('d M', strtotime($booking['check_in'])) ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Step 4: Checked Out -->
        <?php $step4Done = in_array($st, ['checked_out', 'completed']); ?>
        <div class="flex items-center gap-3 p-3 rounded-2xl <?= $step4Done ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' ?>">
          <div class="w-8 h-8 rounded-full <?= $step4Done ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' ?> flex items-center justify-center font-bold text-sm shrink-0">
            <?= $step4Done ? '✓' : '4' ?>
          </div>
          <div>
            <div class="text-xs font-bold text-slate-900">4. Checked Out</div>
            <div class="text-[11px] text-slate-500">
              <?php if (!empty($booking['actual_check_out_at'])): ?>
                <?= date('d M, h:i A', strtotime($booking['actual_check_out_at'])) ?>
              <?php else: ?>
                Sched: <?= date('d M', strtotime($booking['check_out'])) ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    <?php endif; ?>
  </div>

  <!-- ══════════════════════════════════════════════
       MAIN 2-COLUMN DOSSIER GRID
       ══════════════════════════════════════════════ -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ── LEFT 2 COLUMNS: GUEST, PROPERTY, ALLOCATION, SPECIAL REQUESTS ── -->
    <div class="lg:col-span-2 space-y-6">

      <!-- 1. Guest Information & KYC Dossier -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-700">badge</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Guest Dossier &amp; Identification</h3>
          </div>
          <a href="<?= url('admin/guests/view/' . (int)($booking['user_id'] ?? 0)) ?>" 
             class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 hover:bg-brand-100 transition-colors">
            <span class="material-symbols-outlined text-[14px]">account_circle</span>
            <span>View Guest Dossier &rarr;</span>
          </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Primary Contact -->
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-lg font-headline">
                <?= strtoupper(substr($booking['user_name'] ?: ($booking['name'] ?? 'G'), 0, 1)) ?>
              </div>
              <div>
                <h4 class="font-bold text-slate-900 text-sm leading-tight">
                  <?= htmlspecialchars($booking['user_name'] ?: ($booking['name'] ?? 'Guest')) ?>
                </h4>
                <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($booking['user_email'] ?: ($booking['email'] ?? '')) ?></p>
                <p class="text-xs font-bold text-slate-700 mt-0.5">📞 <?= htmlspecialchars($booking['user_phone'] ?: ($booking['mobile'] ?? '')) ?></p>
              </div>
            </div>

            <div class="pt-2 text-xs text-slate-600 space-y-1">
              <div>Total Occupants: <strong class="text-slate-900"><?= (int)($booking['guests'] ?? 1) ?> Guests</strong> (<?= (int)($booking['adults'] ?? 1) ?> Adults, <?= (int)($booking['children'] ?? 0) ?> Children)</div>
            </div>
          </div>

          <!-- KYC Identification Status -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">KYC Verification</span>
              <?php if (!empty($booking['guest_id_number'])): ?>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                  <span class="material-symbols-outlined text-xs">verified</span> Verified
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                  <span class="material-symbols-outlined text-xs">pending</span> Pending at Check-In
                </span>
              <?php endif; ?>
            </div>

            <div class="text-xs">
              <span class="text-slate-400 block">ID Type:</span>
              <strong class="text-slate-800"><?= htmlspecialchars($booking['guest_id_type'] ?: 'Not recorded yet') ?></strong>
            </div>

            <div class="text-xs">
              <span class="text-slate-400 block">ID Number:</span>
              <strong class="font-mono text-slate-800"><?= htmlspecialchars($booking['guest_id_number'] ?: '—') ?></strong>
            </div>

            <?php if (!empty($booking['guest_id_document'])): ?>
              <div class="pt-1">
                <a href="<?= asset(htmlspecialchars($booking['guest_id_document'])) ?>" target="_blank" 
                   class="inline-flex items-center gap-1 text-xs font-bold text-brand-700 hover:text-brand-900 hover:underline">
                  <span class="material-symbols-outlined text-sm">attachment</span>
                  <span>View ID Document File</span>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 2. Property & Room Allocation -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-700">villa</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Property &amp; Stay Allocation</h3>
          </div>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700">
            <?= ($booking['booking_type'] ?? '') === 'complete' ? 'Complete Estate' : 'Room-Wise Stay' ?>
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          
          <!-- Property Overview -->
          <div class="space-y-2">
            <h4 class="font-bold text-slate-900 text-sm">
              <?= htmlspecialchars($booking['farmhouse_title'] ?? '') ?>
            </h4>
            <p class="text-xs text-slate-500">
              <?= htmlspecialchars($booking['farmhouse_address'] ?: ($booking['farmhouse_location'] ?? '')) ?>
            </p>
            <div class="text-xs text-slate-600">
              Host / Desk Phone: <strong class="text-slate-800"><?= htmlspecialchars($booking['farmhouse_contact'] ?: 'N/A') ?></strong>
            </div>
            
            <div class="pt-2">
              <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block">Assigned Unit / Room</span>
              <div class="inline-flex items-center gap-2 mt-1 px-3 py-1.5 bg-slate-100 rounded-xl">
                <span class="material-symbols-outlined text-base text-slate-600">meeting_room</span>
                <span class="font-bold text-xs text-slate-900">
                  <?= !empty($booking['assigned_room_number']) ? 'Room / Unit: ' . htmlspecialchars($booking['assigned_room_number']) : (htmlspecialchars($booking['resolved_room_name'] ?: 'Entire Farmhouse')) ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Schedule & Duration -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-3">
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Check-In Date</span>
                <span class="font-bold text-slate-900 text-sm"><?= date('d M Y', strtotime($booking['check_in'])) ?></span>
                <span class="text-[11px] text-slate-400 block">12:00 PM standard</span>
              </div>
              <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Check-Out Date</span>
                <span class="font-bold text-slate-900 text-sm"><?= date('d M Y', strtotime($booking['check_out'])) ?></span>
                <span class="text-[11px] text-slate-400 block">11:00 AM standard</span>
              </div>
            </div>

            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
              <span class="text-slate-500 font-semibold">Total Duration:</span>
              <span class="font-extrabold text-slate-900"><?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?></span>
            </div>

            <?php if (!empty($booking['actual_check_in_at']) || !empty($booking['actual_check_out_at'])): ?>
              <div class="pt-2 border-t border-slate-200 text-[11px] text-slate-500 space-y-1">
                <?php if (!empty($booking['actual_check_in_at'])): ?>
                  <div>
                    ✓ Checked in: <strong><?= date('d M Y, h:i A', strtotime($booking['actual_check_in_at'])) ?></strong>
                    <?php if (!empty($booking['checked_in_by_name'])): ?> by <?= htmlspecialchars($booking['checked_in_by_name']) ?><?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($booking['actual_check_out_at'])): ?>
                  <div>
                    ✓ Checked out: <strong><?= date('d M Y, h:i A', strtotime($booking['actual_check_out_at'])) ?></strong>
                    <?php if (!empty($booking['checked_out_by_name'])): ?> by <?= htmlspecialchars($booking['checked_out_by_name']) ?><?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- 3. Add-Ons & Food Services -->
      <?php if (!empty($addons) || !empty($booking['notes'])): ?>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
          <div class="flex items-center gap-2 pb-4 border-b border-slate-100 mb-4">
            <span class="material-symbols-outlined text-brand-700">room_service</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Add-ons &amp; Guest Requests</h3>
          </div>

          <?php if (!empty($addons)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
              <?php foreach ($addons as $ad): ?>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-600 text-base">check_circle</span>
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($ad['name'] ?? 'Addon Service') ?></span>
                  </div>
                  <span class="font-bold text-slate-900">₹<?= number_format((float)($ad['price'] ?? 0)) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($booking['notes'])): ?>
            <div class="p-3 bg-amber-50/60 rounded-2xl border border-amber-200/60 text-xs text-amber-900">
              <span class="font-bold block mb-0.5">Guest Remarks / Preferences:</span>
              <p><?= nl2br(htmlspecialchars($booking['notes'])) ?></p>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- 4. Operational Audit Trail -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-700">history</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Chronological Stay Audit Trail</h3>
          </div>
          <span class="text-xs text-slate-400 font-semibold"><?= count($auditLogs) ?> logged events</span>
        </div>

        <?php if (empty($auditLogs)): ?>
          <p class="text-xs text-slate-400 italic">No administrative audit events recorded yet.</p>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($auditLogs as $log): ?>
              <div class="flex items-start gap-3 text-xs">
                <div class="w-2 h-2 rounded-full bg-brand-500 mt-1.5 shrink-0"></div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 uppercase tracking-wide text-[11px]"><?= htmlspecialchars($log['action']) ?></span>
                    <span class="text-[10.5px] text-slate-400"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></span>
                  </div>
                  <p class="text-slate-600 mt-0.5"><?= htmlspecialchars($log['notes'] ?? $log['description'] ?? '') ?></p>
                  <span class="text-[10px] text-slate-400 mt-0.5 block">By <?= htmlspecialchars($log['actor_name'] ?? 'System') ?> (<?= htmlspecialchars($log['actor_type'] ?? $log['actor_role'] ?? 'admin') ?>)</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 5. Stay Feedback & Guest Review -->
      <?php
      $bookingReview = null;
      try {
          $dbRevCheck = \App\Config\Database::connect();
          $revStmt = $dbRevCheck->prepare("SELECT * FROM reviews WHERE booking_id = ? LIMIT 1");
          if ($revStmt) {
              $revStmt->bind_param("i", $bookingId);
              $revStmt->execute();
              $bookingReview = $revStmt->get_result()->fetch_assoc();
              $revStmt->close();
          }
      } catch (\Throwable $e) {}
      ?>
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500">hotel_class</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Stay Review &amp; Feedback</h3>
          </div>
          <?php if ($bookingReview): ?>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border <?= match($bookingReview['status']) {
              'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
              'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
              'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
              'hidden'   => 'bg-slate-100 text-slate-600 border-slate-200',
              default    => 'bg-purple-50 text-purple-700 border-purple-200'
            } ?>">
              <?= htmlspecialchars($bookingReview['status']) ?>
            </span>
          <?php else: ?>
            <span class="text-xs text-slate-400">No Review</span>
          <?php endif; ?>
        </div>

        <?php if ($bookingReview): ?>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1 text-amber-500 font-bold text-sm">
                <span class="material-symbols-outlined text-base">star</span>
                <span><?= number_format((float)$bookingReview['overall_rating'], 1) ?> / 5.0</span>
              </div>
              <span class="text-[11px] text-teal-700 font-bold bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">verified</span> Verified Stay
              </span>
            </div>

            <?php if (!empty($bookingReview['review_title'])): ?>
              <div class="font-bold text-xs text-slate-800">"<?= htmlspecialchars($bookingReview['review_title']) ?>"</div>
            <?php endif; ?>
            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-2xl border border-slate-100">
              <?= htmlspecialchars($bookingReview['review_text']) ?>
            </p>

            <div class="flex items-center justify-between text-[10.5px] text-slate-400 pt-1">
              <span>Submitted on <?= date('d M Y', strtotime($bookingReview['created_at'])) ?></span>
              <a href="<?= url('admin/reviews?search=' . urlencode($booking['booking_reference'] ?? '')) ?>" class="text-brand-700 font-bold hover:underline">
                Manage in Review Console &rarr;
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="p-6 text-center text-slate-400 text-xs italic bg-slate-50 rounded-2xl border border-slate-100">
            No guest review has been submitted for this booking yet.
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- ── RIGHT 1 COLUMN: FINANCIAL FOLIO, EXTRA CHARGES, PAYMENTS ── -->
    <div class="space-y-6">

      <!-- 1. Executive Financial Folio Summary -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-700">receipt_long</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Folio Balance</h3>
          </div>
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold <?= $isFullyPaid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
            <span class="material-symbols-outlined text-xs"><?= $isFullyPaid ? 'check' : 'pending' ?></span>
            <span><?= $isFullyPaid ? 'Settled' : 'Balance Due' ?></span>
          </span>
        </div>

        <!-- Tariff Breakdown Table -->
        <div class="space-y-2 text-xs">
          <div class="flex items-center justify-between text-slate-600">
            <span>Base Tariff (<?= $nights ?> Nights):</span>
            <span class="font-bold text-slate-900">
              ₹<?= number_format((float)$booking['price'] - (float)$booking['addon_charges'] - (float)$booking['cleaning_fee'] - (float)$booking['security_deposit'] + (float)$booking['discount_amount']) ?>
            </span>
          </div>

          <?php if ((float)$booking['discount_amount'] > 0): ?>
            <div class="flex items-center justify-between text-emerald-600">
              <span>Coupon Discount (<?= htmlspecialchars($booking['coupon_code'] ?? '') ?>):</span>
              <span class="font-bold">- ₹<?= number_format((float)$booking['discount_amount']) ?></span>
            </div>
          <?php endif; ?>

          <?php if ((float)$booking['addon_charges'] > 0): ?>
            <div class="flex items-center justify-between text-slate-600">
              <span>Addon Services:</span>
              <span class="font-bold text-slate-900">+ ₹<?= number_format((float)$booking['addon_charges']) ?></span>
            </div>
          <?php endif; ?>

          <?php if ((float)$booking['cleaning_fee'] > 0): ?>
            <div class="flex items-center justify-between text-slate-600">
              <span>Cleaning &amp; Sanitization:</span>
              <span class="font-bold text-slate-900">+ ₹<?= number_format((float)$booking['cleaning_fee']) ?></span>
            </div>
          <?php endif; ?>

          <?php if ((float)$booking['security_deposit'] > 0): ?>
            <div class="flex items-center justify-between text-slate-600">
              <span>Security Deposit (Refundable):</span>
              <span class="font-bold text-slate-900">+ ₹<?= number_format((float)$booking['security_deposit']) ?></span>
            </div>
          <?php endif; ?>

          <!-- Extra incidental charges -->
          <?php if (!empty($extraCharges)): ?>
            <?php 
            $totalExtras = array_sum(array_column($extraCharges, 'amount')); 
            ?>
            <div class="flex items-center justify-between text-purple-700 font-semibold pt-1 border-t border-slate-100">
              <span>Extra Incidentals (<?= count($extraCharges) ?>):</span>
              <span class="font-bold">+ ₹<?= number_format($totalExtras) ?></span>
            </div>
          <?php endif; ?>

          <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-900">Total Payable:</span>
            <span class="font-mono font-black text-base text-slate-900">₹<?= number_format($totalPrice) ?></span>
          </div>

          <div class="flex items-center justify-between text-emerald-600 pt-1">
            <span class="text-xs font-bold">Total Settled / Paid:</span>
            <span class="font-mono font-black text-sm">₹<?= number_format($paid) ?></span>
          </div>

          <div class="p-3 rounded-2xl <?= $balance > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-emerald-50 border border-emerald-200' ?> flex items-center justify-between mt-2">
            <div>
              <span class="text-[10px] font-extrabold uppercase tracking-wider <?= $balance > 0 ? 'text-amber-700' : 'text-emerald-700' ?> block">
                Outstanding Balance
              </span>
              <span class="font-mono font-black text-lg <?= $balance > 0 ? 'text-amber-900' : 'text-emerald-900' ?>">
                ₹<?= number_format(max(0, $balance)) ?>
              </span>
            </div>
            <button type="button" onclick="openPaymentModal(<?= htmlspecialchars(json_encode($booking)) ?>)" 
                    class="px-3 py-1.5 rounded-xl bg-brand-700 text-white font-bold text-xs shadow-xs hover:bg-brand-800 transition-colors">
              Settle Now
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Extra Incidental Charges -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-600">room_service</span>
            <h3 class="font-headline font-bold text-sm text-slate-900">Incidentals &amp; Extra Charges</h3>
          </div>
          <button type="button" onclick="openAddChargeModal()" class="text-xs font-bold text-brand-700 hover:text-brand-900 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Add</span>
          </button>
        </div>

        <?php if (empty($extraCharges)): ?>
          <p class="text-xs text-slate-400 italic">No extra charges posted to this stay.</p>
        <?php else: ?>
          <div class="divide-y divide-slate-100 text-xs">
            <?php foreach ($extraCharges as $ec): ?>
              <div class="py-2.5 flex items-start justify-between gap-2">
                <div>
                  <span class="font-bold text-slate-800 block"><?= htmlspecialchars($ec['title']) ?></span>
                  <span class="text-[10.5px] text-slate-400"><?= date('d M, h:i A', strtotime($ec['created_at'])) ?> by <?= htmlspecialchars($ec['created_by_name'] ?? 'Staff') ?></span>
                  <?php if (!empty($ec['notes'])): ?>
                    <p class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($ec['notes']) ?></p>
                  <?php endif; ?>
                </div>
                <span class="font-mono font-bold text-slate-900 shrink-0">+ ₹<?= number_format((float)$ec['amount']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 3. Payments Ledger & Installments -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-600">payments</span>
            <h3 class="font-headline font-bold text-sm text-slate-900">Payment Transactions</h3>
          </div>
          <button type="button" onclick="openPaymentModal(<?= htmlspecialchars(json_encode($booking)) ?>)" class="text-xs font-bold text-brand-700 hover:text-brand-900 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Post</span>
          </button>
        </div>

        <?php if (empty($payments)): ?>
          <p class="text-xs text-slate-400 italic">No payments logged in the transactions table yet.</p>
        <?php else: ?>
          <div class="divide-y divide-slate-100 text-xs">
            <?php foreach ($payments as $pm): ?>
              <div class="py-2.5 space-y-1">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <span class="font-bold text-slate-800 uppercase text-[11px]"><?= htmlspecialchars($pm['payment_method']) ?></span>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-slate-100 text-slate-600"><?= htmlspecialchars($pm['payment_type'] ?? 'installment') ?></span>
                  </div>
                  <span class="font-mono font-bold text-emerald-600 text-sm">₹<?= number_format((float)$pm['amount']) ?></span>
                </div>
                
                <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                  <span><?= date('d M Y, h:i A', strtotime($pm['created_at'])) ?></span>
                  <?php if (!empty($pm['utr_number'])): ?>
                    <span class="font-mono font-semibold">Ref: <?= htmlspecialchars($pm['utr_number']) ?></span>
                  <?php endif; ?>
                </div>

                <?php if (!empty($pm['payment_proof'])): ?>
                  <div class="pt-0.5">
                    <a href="<?= asset(htmlspecialchars($pm['payment_proof'])) ?>" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:underline">
                      <span class="material-symbols-outlined text-xs">receipt</span>
                      <span>View Receipt Proof</span>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     SHOW PAGE MODALS
     ═══════════════════════════════════════════════════════════════════════════ -->

<!-- 1. CHECK-IN MODAL (reused) -->
<div id="checkInModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeCheckInModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl p-6 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h3 class="font-headline font-bold text-lg text-slate-900">Complete Guest Check-In</h3>
        <button type="button" onclick="closeCheckInModal()" class="text-slate-400 hover:text-slate-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="checkInForm" onsubmit="handleCheckInSubmit(event)" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Room / Unit Assignment <span class="text-rose-500">*</span></label>
          <input type="text" name="assigned_room_number" required 
                 value="<?= htmlspecialchars($booking['assigned_room_number'] ?: ($booking['booking_type'] === 'complete' ? 'Entire Farmhouse Estate' : ($booking['resolved_room_name'] ?? 'Room 1'))) ?>"
                 class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">ID Type</label>
            <select name="guest_id_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
              <option value="Aadhaar Card" <?= ($booking['guest_id_type'] ?? '') === 'Aadhaar Card' ? 'selected' : '' ?>>Aadhaar Card</option>
              <option value="Driving License" <?= ($booking['guest_id_type'] ?? '') === 'Driving License' ? 'selected' : '' ?>>Driving License</option>
              <option value="Passport" <?= ($booking['guest_id_type'] ?? '') === 'Passport' ? 'selected' : '' ?>>Passport</option>
              <option value="Voter ID" <?= ($booking['guest_id_type'] ?? '') === 'Voter ID' ? 'selected' : '' ?>>Voter ID</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">ID Number</label>
            <input type="text" name="guest_id_number" value="<?= htmlspecialchars($booking['guest_id_number'] ?? '') ?>" placeholder="ID Number" 
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Upload ID Scan / Photo (Optional)</label>
          <input type="file" name="guest_id_document" accept="image/*,.pdf" 
                 class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-emerald-700">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Check-In Notes</label>
          <textarea name="notes" rows="2" placeholder="Keys issued, luggage assistance..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
          <button type="button" onclick="closeCheckInModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600">Cancel</button>
          <button type="submit" id="btnSubmitCheckIn" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">Complete Check-In</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. CHECK-OUT MODAL -->
<div id="checkOutModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeCheckOutModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl p-6 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h3 class="font-headline font-bold text-lg text-slate-900">Final Guest Check-Out &amp; Folio Settlement</h3>
        <button type="button" onclick="closeCheckOutModal()" class="text-slate-400 hover:text-slate-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="checkOutForm" onsubmit="handleCheckOutSubmit(event)" class="space-y-4">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-1">
          <div class="flex justify-between"><span>Total Tariff:</span><strong class="font-mono">₹<?= number_format($totalPrice) ?></strong></div>
          <div class="flex justify-between"><span>Settled to Date:</span><strong class="font-mono text-emerald-600">₹<?= number_format($paid) ?></strong></div>
          <div class="flex justify-between pt-1 border-t border-slate-200"><span>Remaining Due:</span><strong class="font-mono <?= $balance > 0 ? 'text-rose-600 font-black' : 'text-emerald-600' ?>">₹<?= number_format($balance) ?></strong></div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Security Deposit Status</label>
          <select name="deposit_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            <option value="refunded">100% Refunded to Guest</option>
            <option value="retained">Retained / Deducted for Incidental Damages</option>
            <option value="not_applicable">Not Applicable</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Check-Out Notes</label>
          <textarea name="notes" rows="2" placeholder="Keys returned, room inspected..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
          <button type="button" onclick="closeCheckOutModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600">Cancel</button>
          <button type="submit" id="btnSubmitCheckOut" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md">Complete Check-Out</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 3. EXTEND STAY MODAL -->
<div id="extendStayModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeExtendStayModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-indigo-600">more_time</span>
          <h3 class="font-headline font-bold text-base text-slate-900">Extend In-House Stay</h3>
        </div>
        <button type="button" onclick="closeExtendStayModal()" class="text-slate-400 hover:text-slate-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="extendStayForm" onsubmit="handleExtendStaySubmit(event)" class="space-y-4">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div class="text-xs text-slate-600">
          Current Check-Out: <strong class="text-slate-900"><?= date('d M Y', strtotime($booking['check_out'])) ?></strong>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">New Check-Out Date <span class="text-rose-500">*</span></label>
          <input type="date" name="new_check_out" required 
                 min="<?= date('Y-m-d', strtotime($booking['check_out'] . ' +1 day')) ?>" 
                 value="<?= date('Y-m-d', strtotime($booking['check_out'] . ' +1 day')) ?>"
                 class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500">
          <p class="text-[11px] text-slate-400 mt-1">Tariff will be dynamically recalculated based on season, weekday/weekend rates, and room config.</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Notes</label>
          <input type="text" name="notes" placeholder="Guest requested +1 day extension..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
          <button type="button" onclick="closeExtendStayModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600">Cancel</button>
          <button type="submit" id="btnSubmitExtendStay" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md">Confirm Extension</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 4. ADD EXTRA INCIDENTAL CHARGE MODAL -->
<div id="addChargeModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeAddChargeModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-purple-600">room_service</span>
          <h3 class="font-headline font-bold text-base text-slate-900">Post Extra Incidental Charge</h3>
        </div>
        <button type="button" onclick="closeAddChargeModal()" class="text-slate-400 hover:text-slate-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="addChargeForm" onsubmit="handleAddChargeSubmit(event)" class="space-y-4">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Item / Service Title <span class="text-rose-500">*</span></label>
          <input type="text" name="title" required placeholder="e.g., BBQ Dinner, Minibar, Extra Bed, Pool Cabana" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Charge Amount (₹) <span class="text-rose-500">*</span></label>
          <input type="number" step="0.01" min="1" name="amount" required placeholder="500.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Incidental Notes</label>
          <textarea name="notes" rows="2" placeholder="Guest requested evening campfire setup..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
          <button type="button" onclick="closeAddChargeModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600">Cancel</button>
          <button type="submit" id="btnSubmitAddCharge" class="px-5 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs shadow-md">Post Charge</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 5. RECORD PAYMENT MODAL -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closePaymentModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl p-6 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h3 class="font-headline font-bold text-lg text-slate-900">Record Payment Installment</h3>
        <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="paymentForm" onsubmit="handlePaymentSubmit(event)" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" name="amount" required min="1" value="<?= max(0, $balance) ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Method</label>
            <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
              <option value="cash">Cash at Property</option>
              <option value="upi">UPI / QR Code</option>
              <option value="card">Credit / Debit Card</option>
              <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Type</label>
            <select name="payment_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
              <option value="balance">Balance Settlement</option>
              <option value="advance">Advance Booking</option>
              <option value="security_deposit">Security Deposit</option>
              <option value="incidental">Extra / Incidental</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">UTR / Ref Number</label>
            <input type="text" name="utr_number" placeholder="Optional" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Proof File (Optional)</label>
          <input type="file" name="payment_proof" accept="image/*,.pdf" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-brand-50 file:text-brand-700">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Internal Note</label>
          <input type="text" name="notes" placeholder="Receipt issued..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
          <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600">Cancel</button>
          <button type="submit" id="btnSubmitPayment" class="px-5 py-2.5 rounded-xl bg-brand-700 text-white font-bold text-xs shadow-md">Post Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 6. STATUS CHANGE MODAL -->
<div id="statusModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeStatusModal()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h3 class="font-headline font-bold text-base text-slate-900">Update Stay Status</h3>
        <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="statusForm" onsubmit="handleStatusSubmit(event)" class="space-y-4">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
          <select name="status" id="statusSelectInput" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
            <option value="rejected">Rejected</option>
            <option value="no_show">Mark as No-Show</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Notes <span class="text-rose-500">*</span></label>
          <textarea name="notes" rows="3" required placeholder="Reason for status change..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
          <button type="button" onclick="closeStatusModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600">Cancel</button>
          <button type="submit" id="btnSubmitStatus" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md">Save Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT LOGIC
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
  function openCheckInModal() { document.getElementById('checkInModal').classList.remove('hidden'); }
  function closeCheckInModal() { document.getElementById('checkInModal').classList.add('hidden'); }

  function openCheckOutModal() { document.getElementById('checkOutModal').classList.remove('hidden'); }
  function closeCheckOutModal() { document.getElementById('checkOutModal').classList.add('hidden'); }

  function openExtendStayModal() { document.getElementById('extendStayModal').classList.remove('hidden'); }
  function closeExtendStayModal() { document.getElementById('extendStayModal').classList.add('hidden'); }

  function openAddChargeModal() { document.getElementById('addChargeModal').classList.remove('hidden'); }
  function closeAddChargeModal() { document.getElementById('addChargeModal').classList.add('hidden'); }

  function openPaymentModal() { document.getElementById('paymentModal').classList.remove('hidden'); }
  function closePaymentModal() { document.getElementById('paymentModal').classList.add('hidden'); }

  function openStatusModal(id, currentStatus, ref) { 
    document.getElementById('statusSelectInput').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden'); 
  }
  function closeStatusModal() { document.getElementById('statusModal').classList.add('hidden'); }

  // Generic submit helper
  function submitAjaxForm(formId, url, btnId, successMsg) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    const btn = document.getElementById(btnId);
    btn.disabled = true;

    fetch(url, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
      btn.disabled = false;
      if (d.success) {
        showToast(d.message || successMsg, 'success');
        setTimeout(() => window.location.reload(), 400);
      } else {
        showToast(d.message || 'Action failed', 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      showToast('A network error occurred.', 'error');
    });
  }

  function handleCheckInSubmit(e) {
    e.preventDefault();
    submitAjaxForm('checkInForm', '<?= url('admin/bookings/check-in') ?>', 'btnSubmitCheckIn', 'Guest checked in successfully!');
  }

  function handleCheckOutSubmit(e) {
    e.preventDefault();
    submitAjaxForm('checkOutForm', '<?= url('admin/bookings/check-out') ?>', 'btnSubmitCheckOut', 'Guest checked out successfully!');
  }

  function handleExtendStaySubmit(e) {
    e.preventDefault();
    submitAjaxForm('extendStayForm', '<?= url('admin/bookings/extend-stay') ?>', 'btnSubmitExtendStay', 'Stay extended successfully!');
  }

  function handleAddChargeSubmit(e) {
    e.preventDefault();
    submitAjaxForm('addChargeForm', '<?= url('admin/bookings/add-charge') ?>', 'btnSubmitAddCharge', 'Extra charge added to stay!');
  }

  function handlePaymentSubmit(e) {
    e.preventDefault();
    submitAjaxForm('paymentForm', '<?= url('admin/bookings/payment') ?>', 'btnSubmitPayment', 'Payment installment logged successfully!');
  }

  function handleStatusSubmit(e) {
    e.preventDefault();
    submitAjaxForm('statusForm', '<?= url('admin/bookings/status') ?>', 'btnSubmitStatus', 'Stay status updated successfully!');
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeCheckInModal();
      closeCheckOutModal();
      closeExtendStayModal();
      closeAddChargeModal();
      closePaymentModal();
      closeStatusModal();
    }
  });
</script>

<?php 
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php'; 
?>
