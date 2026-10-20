<?php
$pageTitle   = $pageTitle ?? "Create New Reservation • Stayora Executive Console";
$currentPage = "bookings";
$headerTitle = "New Direct Reservation";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

$farmhouses = $farmhouses ?? [];
?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER
     ═══════════════════════════════════════════════════════════════════════════ -->
<main class="flex-1 overflow-y-auto space-y-6 mt-0 bg-slate-50/50 pb-16">

  <!-- Breadcrumb & Top Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1.5" aria-label="Breadcrumb">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-slate-600 transition-colors">Admin</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="<?= url('admin/bookings') ?>" class="hover:text-slate-600 transition-colors">Bookings</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-brand-700 font-bold">New Reservation</span>
      </nav>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-700/20">
          <span class="material-symbols-outlined text-xl">add_business</span>
        </div>
        <div>
          <h1 class="font-headline font-bold text-xl sm:text-2xl text-slate-900 tracking-tight leading-none">
            Direct / Walk-In Reservation
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Manual booking entry with real-time double-booking conflict protection, folio setup, and immediate check-in option.
          </p>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div>
      <a href="<?= url('admin/bookings') ?>" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        <span>Back to Bookings</span>
      </a>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       MANUAL BOOKING CREATION FORM
       ══════════════════════════════════════════════ -->
  <form id="createBookingForm" method="POST" action="<?= url('admin/bookings/store') ?>" class="space-y-6">
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ── LEFT 2 COLUMNS: PROPERTY, STAY DATES, GUEST INFO ── -->
      <div class="lg:col-span-2 space-y-6">

        <!-- 1. Property & Mode Selection -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="material-symbols-outlined text-brand-700">villa</span>
            <h3 class="font-headline font-bold text-base text-slate-900">1. Property &amp; Inventory Selection</h3>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Farmhouse Selector -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Select Estate / Farmhouse <span class="text-rose-500">*</span>
              </label>
              <select name="farmhouse_id" id="selectFarmhouse" required onchange="handlePropertyChange()"
                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                <option value="">-- Choose Farmhouse Property --</option>
                <?php foreach ($farmhouses as $fh): ?>
                  <option value="<?= $fh['id'] ?>" 
                          data-rental="<?= htmlspecialchars($fh['rental_type']) ?>" 
                          data-base-price="<?= (float)$fh['price'] ?>" 
                          data-weekend-price="<?= (float)$fh['weekend_price'] ?>"
                          data-max-guests="<?= (int)$fh['max_guests'] ?>">
                    <?= htmlspecialchars($fh['title']) ?> (₹<?= number_format((float)$fh['price']) ?>/night • <?= ucfirst($fh['rental_type']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Booking Mode Toggle -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Booking Mode <span class="text-rose-500">*</span></label>
              <select name="booking_type" id="selectBookingType" required onchange="handleModeChange()"
                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                <option value="complete">Entire Property / Complete Farmhouse</option>
                <option value="per_room">Individual Room-Wise Stay</option>
              </select>
            </div>

            <!-- Room Type Selection (only shown for per_room) -->
            <div id="roomTypeContainer" class="hidden">
              <label class="block text-xs font-bold text-slate-700 mb-1">Room Category</label>
              <select name="room_type_id" id="selectRoomType" onchange="recalculatePrice()"
                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                <option value="">Select Room Type...</option>
              </select>
            </div>

            <!-- Rooms Count -->
            <div id="roomsCountContainer" class="hidden">
              <label class="block text-xs font-bold text-slate-700 mb-1">Number of Rooms</label>
              <input type="number" name="rooms" id="inputRoomsCount" min="1" max="20" value="1" onchange="recalculatePrice()"
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Assigned Room Number -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Room / Unit # (Optional)</label>
              <input type="text" name="assigned_room_number" placeholder="e.g., Suite 101, Villa Main" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

          </div>
        </div>

        <!-- 2. Stay Dates & Schedule -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="material-symbols-outlined text-brand-700">calendar_month</span>
            <h3 class="font-headline font-bold text-base text-slate-900">2. Stay Dates &amp; Occupancy</h3>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Check-in Date -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Check-In Date <span class="text-rose-500">*</span></label>
              <input type="date" name="check_in" id="inputCheckIn" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" onchange="handleDatesChange()"
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Check-out Date -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Check-Out Date <span class="text-rose-500">*</span></label>
              <input type="date" name="check_out" id="inputCheckOut" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" onchange="handleDatesChange()"
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Check-in / Check-out Times -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Check-In Time</label>
              <input type="time" name="check_in_time" value="12:00" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Check-Out Time</label>
              <input type="time" name="check_out_time" value="11:00" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Occupant Counts -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 mb-1">Total Guests <span class="text-rose-500">*</span></label>
              <input type="number" name="guests" id="inputGuests" min="1" value="2" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

          </div>

          <!-- Conflict Status Indicator -->
          <div id="availabilityNotice" class="hidden p-3 rounded-2xl text-xs font-bold flex items-center gap-2"></div>
        </div>

        <!-- 3. Guest Profile & Identification -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-brand-700">person</span>
              <h3 class="font-headline font-bold text-base text-slate-900">3. Primary Guest Information</h3>
            </div>
            <a href="<?= url('admin/guests/create') ?>" target="_blank" class="text-xs font-bold text-brand-700 hover:underline flex items-center gap-1">
              <span class="material-symbols-outlined text-[15px]">person_add</span>
              <span>+ Register New Profile</span>
            </a>
          </div>

          <!-- Guest Autocomplete Search -->
          <div class="relative">
            <label class="block text-xs font-bold text-slate-700 mb-1">Search Existing Guest / Member</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
              <input type="text" id="guestSearchInput" onkeyup="handleGuestSearch(this.value)" placeholder="Type name, phone, email, or guest ID (e.g., Aarav, 9876543210)..." autocomplete="off"
                     class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
              <button type="button" id="clearGuestBtn" onclick="clearSelectedGuest()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[16px]">close</span>
              </button>
            </div>

            <!-- Autocomplete Results Dropdown -->
            <div id="guestSearchResults" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100"></div>

            <!-- Selected Guest Pill -->
            <div id="selectedGuestBanner" class="hidden mt-2.5 p-3 rounded-2xl bg-brand-50/50 border border-brand-200 flex items-center justify-between">
              <div class="flex items-center gap-2 text-xs">
                <span class="material-symbols-outlined text-brand-700 text-lg">verified</span>
                <div>
                  <span id="selectedGuestNameText" class="font-bold text-slate-900"></span>
                  <span id="selectedGuestMetaText" class="text-[11px] text-slate-500 ml-1"></span>
                </div>
              </div>
              <button type="button" onclick="clearSelectedGuest()" class="text-xs font-bold text-slate-400 hover:text-rose-600">Deselect</button>
            </div>
          </div>

          <input type="hidden" name="user_id" id="inputUserId" value="0">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Full Name -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 mb-1">Guest Full Name <span class="text-rose-500">*</span></label>
              <input type="text" name="customer_name" id="inputCustomerName" required placeholder="e.g., Rajesh Sharma" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Mobile Phone -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone <span class="text-rose-500">*</span></label>
              <input type="tel" name="customer_phone" id="inputCustomerPhone" required placeholder="10-digit mobile number" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
              <input type="email" name="customer_email" id="inputCustomerEmail" placeholder="guest@example.com (optional)" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Internal Staff Notes -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 mb-1">Administrative / Front-Desk Notes</label>
              <textarea name="admin_notes" rows="2" placeholder="Direct booking taken via phone call; VIP guest..." 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"></textarea>
            </div>

          </div>
        </div>

      </div>

      <!-- ── RIGHT 1 COLUMN: FINANCIAL CALCULATOR & INITIAL PAYMENT ── -->
      <div class="space-y-6">

        <!-- Financial Breakdown & Tariff Setting -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="material-symbols-outlined text-brand-700">payments</span>
            <h3 class="font-headline font-bold text-base text-slate-900">Tariff &amp; Billing</h3>
          </div>

          <div class="space-y-3">
            
            <!-- Calculated Duration Badge -->
            <div class="p-3 bg-slate-50 rounded-2xl flex items-center justify-between text-xs font-semibold text-slate-700">
              <span>Duration:</span>
              <span id="calculatedNights" class="font-bold text-slate-900">1 Night</span>
            </div>

            <!-- Total Price Input -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Total Stay Tariff (₹) <span class="text-rose-500">*</span></label>
              <input type="number" step="0.01" name="price" id="inputTotalPrice" required min="1" 
                     placeholder="0.00" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-base font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
              <span class="text-[11px] text-slate-400 mt-1 block">Auto-estimated from property rates; editable for negotiated corporate/walk-in pricing.</span>
            </div>

            <!-- Initial Payment Amount -->
            <div class="pt-3 border-t border-slate-100">
              <label class="block text-xs font-bold text-slate-700 mb-1">Initial Payment Received (₹)</label>
              <input type="number" step="0.01" name="amount_paid" id="inputAmountPaid" min="0" value="0" 
                     class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-emerald-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method</label>
              <select name="payment_method" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                <option value="cash">Cash at Front Desk</option>
                <option value="upi">UPI / Instant QR</option>
                <option value="card">Credit / Debit Card</option>
                <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                <option value="pay_at_property">Pay at Property (Pending)</option>
              </select>
            </div>

            <!-- Initial Booking Status -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Initial Stay Status</label>
              <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                <option value="confirmed" selected>Confirmed (Locked Inventory)</option>
                <option value="checked_in">Checked In Immediately (Guest Arrived)</option>
                <option value="pending">Pending Admin Approval</option>
              </select>
            </div>

          </div>

          <!-- Submit Button -->
          <div class="pt-4 border-t border-slate-100">
            <button type="submit" id="btnSubmitCreate" 
                    class="w-full py-3 px-4 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-brand-700/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
              <span class="material-symbols-outlined text-base">task_alt</span>
              <span>Confirm &amp; Create Reservation</span>
            </button>
          </div>

        </div>

      </div>

    </div>

  </form>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     DYNAMIC FORM SCRIPT
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
  function handlePropertyChange() {
    const sel = document.getElementById('selectFarmhouse');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const rentalType = opt.dataset.rental || 'complete';
    const typeSel = document.getElementById('selectBookingType');

    if (rentalType === 'complete') {
      typeSel.value = 'complete';
      typeSel.innerHTML = '<option value="complete">Entire Property / Complete Farmhouse</option>';
    } else if (rentalType === 'per_room') {
      typeSel.value = 'per_room';
      typeSel.innerHTML = '<option value="per_room">Individual Room-Wise Stay</option>';
    } else {
      typeSel.innerHTML = '<option value="complete">Entire Property / Complete Farmhouse</option><option value="per_room">Individual Room-Wise Stay</option>';
    }

    handleModeChange();
    loadRoomsForProperty(opt.value);
    recalculatePrice();
  }

  function handleModeChange() {
    const mode = document.getElementById('selectBookingType').value;
    const roomCont = document.getElementById('roomTypeContainer');
    const countCont = document.getElementById('roomsCountContainer');

    if (mode === 'per_room') {
      roomCont.classList.remove('hidden');
      countCont.classList.remove('hidden');
    } else {
      roomCont.classList.add('hidden');
      countCont.classList.add('hidden');
    }
    recalculatePrice();
  }

  function loadRoomsForProperty(farmhouseId) {
    const roomSel = document.getElementById('selectRoomType');
    roomSel.innerHTML = '<option value="">Loading rooms...</option>';

    fetch('<?= url('admin/bookings/rooms?farmhouse_id=') ?>' + farmhouseId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
      roomSel.innerHTML = '<option value="">-- Choose Room Type --</option>';
      if (d.success && d.rooms) {
        d.rooms.forEach(rm => {
          const opt = document.createElement('option');
          opt.value = rm.id;
          opt.dataset.price = rm.price_per_room;
          opt.innerText = rm.room_type_name + ' (₹' + Number(rm.price_per_room).toLocaleString('en-IN') + '/night)';
          roomSel.appendChild(opt);
        });
      }
    })
    .catch(() => {
      roomSel.innerHTML = '<option value="">Standard Room</option>';
    });
  }

  function handleDatesChange() {
    const ci = document.getElementById('inputCheckIn').value;
    const co = document.getElementById('inputCheckOut').value;
    if (!ci || !co) return;

    const d1 = new Date(ci);
    const d2 = new Date(co);
    let diffDays = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
    if (diffDays <= 0) {
      diffDays = 1;
      const nextDay = new Date(d1);
      nextDay.setDate(nextDay.getDate() + 1);
      document.getElementById('inputCheckOut').value = nextDay.toISOString().split('T')[0];
    }

    document.getElementById('calculatedNights').innerText = diffDays + ' Night' + (diffDays > 1 ? 's' : '');
    recalculatePrice();
  }

  function recalculatePrice() {
    const fhSel = document.getElementById('selectFarmhouse');
    const opt = fhSel.options[fhSel.selectedIndex];
    if (!opt || !opt.value) return;

    const ci = document.getElementById('inputCheckIn').value;
    const co = document.getElementById('inputCheckOut').value;
    if (!ci || !co) return;

    const d1 = new Date(ci);
    const d2 = new Date(co);
    const nights = Math.max(1, Math.round((d2 - d1) / (1000 * 60 * 60 * 24)));

    const mode = document.getElementById('selectBookingType').value;
    let baseRate = 0;

    if (mode === 'per_room') {
      const rmSel = document.getElementById('selectRoomType');
      const rmOpt = rmSel.options[rmSel.selectedIndex];
      const rmPrice = rmOpt ? parseFloat(rmOpt.dataset.price || 0) : 0;
      const roomsCount = parseInt(document.getElementById('inputRoomsCount').value || 1);
      baseRate = (rmPrice > 0 ? rmPrice : parseFloat(opt.dataset.basePrice || 0)) * roomsCount;
    } else {
      baseRate = parseFloat(opt.dataset.basePrice || 0);
    }

    const totalEstimate = baseRate * nights;
    document.getElementById('inputTotalPrice').value = totalEstimate;
  }

  // ── GUEST AUTOCOMPLETE SEARCH ──
  let searchDebounceTimer = null;
  function handleGuestSearch(query) {
    clearTimeout(searchDebounceTimer);
    const resultsBox = document.getElementById('guestSearchResults');
    const clearBtn = document.getElementById('clearGuestBtn');

    if (!query || query.trim().length < 2) {
      resultsBox.innerHTML = '';
      resultsBox.classList.add('hidden');
      clearBtn.classList.add('hidden');
      return;
    }

    clearBtn.classList.remove('hidden');
    searchDebounceTimer = setTimeout(() => {
      fetch('<?= url('admin/guests/search-ajax') ?>?q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(guests => {
          if (!guests || guests.length === 0) {
            resultsBox.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center italic">No matching registered guests found.</div>';
            resultsBox.classList.remove('hidden');
            return;
          }

          let html = '';
          guests.forEach(g => {
            const tierBadge = g.membership_tier && g.membership_tier !== 'none'
              ? `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-amber-50 text-amber-800 uppercase">${g.membership_tier}</span>`
              : `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-medium bg-slate-100 text-slate-500">Standard</span>`;

            html += `
              <div onclick='selectGuest(${JSON.stringify(g)})' class="p-3 hover:bg-brand-50/50 cursor-pointer transition-colors flex items-center justify-between">
                <div>
                  <div class="font-bold text-xs text-slate-900">${escapeHtml(g.name)}</div>
                  <div class="text-[11px] text-slate-500">${escapeHtml(g.phone || '')} • ${escapeHtml(g.email || 'No email')}</div>
                </div>
                <div class="text-right">
                  ${tierBadge}
                  <div class="text-[10px] font-mono text-slate-400 mt-0.5">${escapeHtml(g.guest_code || '')}</div>
                </div>
              </div>
            `;
          });
          resultsBox.innerHTML = html;
          resultsBox.classList.remove('hidden');
        })
        .catch(() => {
          resultsBox.innerHTML = '<div class="p-3 text-xs text-rose-500 text-center">Search error.</div>';
          resultsBox.classList.remove('hidden');
        });
    }, 250);
  }

  function selectGuest(g) {
    document.getElementById('inputUserId').value = g.id;
    document.getElementById('inputCustomerName').value = g.name || '';
    document.getElementById('inputCustomerPhone').value = g.phone || '';
    document.getElementById('inputCustomerEmail').value = g.email || '';

    // Show banner
    document.getElementById('selectedGuestNameText').textContent = g.name;
    document.getElementById('selectedGuestMetaText').textContent = `(${g.guest_code || 'ID#' + g.id}${g.membership_tier && g.membership_tier !== 'none' ? ' • ' + g.membership_tier.toUpperCase() : ''})`;
    document.getElementById('selectedGuestBanner').classList.remove('hidden');

    // Hide search results
    document.getElementById('guestSearchResults').classList.add('hidden');
    document.getElementById('guestSearchInput').value = '';
    document.getElementById('clearGuestBtn').classList.add('hidden');
  }

  function clearSelectedGuest() {
    document.getElementById('inputUserId').value = '0';
    document.getElementById('inputCustomerName').value = '';
    document.getElementById('inputCustomerPhone').value = '';
    document.getElementById('inputCustomerEmail').value = '';

    document.getElementById('selectedGuestBanner').classList.add('hidden');
    document.getElementById('guestSearchInput').value = '';
    document.getElementById('guestSearchResults').innerHTML = '';
    document.getElementById('guestSearchResults').classList.add('hidden');
    document.getElementById('clearGuestBtn').classList.add('hidden');
  }

  function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
</script>

<?php 
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php'; 
?>
