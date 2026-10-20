<?php
$pageTitle   = $pageTitle ?? "Booking Availability Calendar • Stayora Executive Console";
$currentPage = "bookings";
$headerTitle = "Occupancy & Availability Calendar";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

$farmhouses  = $farmhouses ?? [];
$selectedId  = $selectedId ?? 0;
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
        <span class="text-brand-700 font-bold">Occupancy Calendar</span>
      </nav>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-700/20">
          <span class="material-symbols-outlined text-xl">calendar_view_month</span>
        </div>
        <div>
          <h1 class="font-headline font-bold text-xl sm:text-2xl text-slate-900 tracking-tight leading-none">
            Occupancy &amp; Inventory Matrix
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Visual calendar of booked stays, in-house guests, turnovers, and room allocations.
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
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-brand-700 text-white shadow-xs">
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
       CALENDAR CONTROLS & PROPERTY FILTER
       ══════════════════════════════════════════════ -->
  <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
    
    <!-- Month Navigation -->
    <div class="flex items-center gap-3">
      <div class="inline-flex items-center border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
        <button type="button" onclick="navigateMonth(-1)" class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" title="Previous Month">
          <span class="material-symbols-outlined text-lg">chevron_left</span>
        </button>
        <button type="button" onclick="goToToday()" class="px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors">
          Today
        </button>
        <button type="button" onclick="navigateMonth(1)" class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" title="Next Month">
          <span class="material-symbols-outlined text-lg">chevron_right</span>
        </button>
      </div>

      <h2 id="calendarMonthTitle" class="text-base sm:text-lg font-black font-headline text-slate-900 tracking-tight">
        Loading...
      </h2>
    </div>

    <!-- Property Selector Dropdown & Legend -->
    <div class="flex flex-wrap items-center gap-3">
      <div class="min-w-[220px]">
        <select id="calendarPropertyFilter" onchange="loadCalendarEvents()" 
                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
          <option value="">All Estates &amp; Farmhouses</option>
          <?php foreach ($farmhouses as $fh): ?>
            <option value="<?= $fh['id'] ?>" <?= $selectedId === (int)$fh['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($fh['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Color Legend -->
      <div class="hidden xl:flex items-center gap-3 text-[11px] font-bold text-slate-500 border-l border-slate-200 pl-3">
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Confirmed</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> In-House</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Pending</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Checked Out</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Blocked</span>
      </div>
    </div>

  </div>

  <!-- ══════════════════════════════════════════════
       VISUAL MONTH MATRIX
       ══════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    
    <!-- Weekday Headers -->
    <div class="grid grid-cols-7 border-b border-slate-200 text-center bg-slate-50/75 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 py-2.5">
      <div class="text-rose-500">Sun</div>
      <div>Mon</div>
      <div>Tue</div>
      <div>Wed</div>
      <div>Thu</div>
      <div>Fri</div>
      <div class="text-indigo-600">Sat</div>
    </div>

    <!-- Day Cells Grid -->
    <div id="calendarGrid" class="grid grid-cols-7 divide-x divide-y divide-slate-100 min-h-[580px]">
      <!-- Dynamically populated via JS -->
    </div>

  </div>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     EVENT INSPECTION DRAWER / MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div id="bookingDrawer" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeBookingDrawer()"></div>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden p-6 space-y-4">
      
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div>
          <span class="font-mono font-bold text-brand-700 text-sm" id="drawerRefCode">#STAY-BK-00000</span>
          <h3 class="font-headline font-bold text-base text-slate-900" id="drawerPropertyTitle">Property Title</h3>
        </div>
        <button type="button" onclick="closeBookingDrawer()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Guest and Dates -->
      <div class="space-y-2 text-xs">
        <div class="flex justify-between">
          <span class="text-slate-400">Guest Name:</span>
          <strong class="text-slate-800" id="drawerGuestName">Guest Name</strong>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">Phone:</span>
          <span class="font-bold text-slate-700" id="drawerGuestPhone">—</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">Dates:</span>
          <strong class="text-slate-800" id="drawerStayDates">Check-In → Check-Out</strong>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">Assigned Unit:</span>
          <span class="font-bold text-slate-700" id="drawerRoomUnit">Room 1</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">Total Tariff:</span>
          <strong class="font-mono text-slate-900" id="drawerTotalAmount">₹0</strong>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">Balance Due:</span>
          <strong class="font-mono text-rose-600" id="drawerBalanceDue">₹0</strong>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-slate-100">
          <span class="text-slate-400">Status:</span>
          <span id="drawerStatusBadge" class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">Confirmed</span>
        </div>
      </div>

      <!-- Actions Footer -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
        <button type="button" onclick="closeBookingDrawer()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
          Close
        </button>
        <a id="drawerDetailsLink" href="#" class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-md shadow-brand-700/20 transition-all flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm">visibility</span>
          <span>Open Full Dossier</span>
        </a>
      </div>

    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     CALENDAR JAVASCRIPT ENGINE
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
  let currentYear = new Date().getFullYear();
  let currentMonth = new Date().getMonth(); // 0-indexed
  let eventsData = { bookings: [], blocked: [] };

  const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
    loadCalendarEvents();
  });

  function navigateMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    } else if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    }
    renderCalendar();
    loadCalendarEvents();
  }

  function goToToday() {
    const today = new Date();
    currentYear = today.getFullYear();
    currentMonth = today.getMonth();
    renderCalendar();
    loadCalendarEvents();
  }

  function renderCalendar() {
    document.getElementById('calendarMonthTitle').innerText = monthNames[currentMonth] + ' ' + currentYear;

    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay(); // 0 = Sun
    const totalDaysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

    const today = new Date();
    const isThisMonth = (today.getFullYear() === currentYear && today.getMonth() === currentMonth);
    const todayDate = today.getDate();

    // 1. Previous month trailing days
    for (let i = firstDayIndex - 1; i >= 0; i--) {
      const dayNum = prevMonthDays - i;
      grid.appendChild(createDayCell(dayNum, true, false));
    }

    // 2. Current month days
    for (let day = 1; day <= totalDaysInMonth; day++) {
      const isToday = isThisMonth && (day === todayDate);
      grid.appendChild(createDayCell(day, false, isToday));
    }

    // 3. Next month leading days to complete row grid (up to 35 or 42 cells)
    const currentTotalCells = firstDayIndex + totalDaysInMonth;
    const remainingCells = (currentTotalCells <= 35 ? 35 : 42) - currentTotalCells;
    for (let day = 1; day <= remainingCells; day++) {
      grid.appendChild(createDayCell(day, true, false));
    }
  }

  function createDayCell(dayNum, isOtherMonth, isToday) {
    const cell = document.createElement('div');
    cell.className = 'min-h-[110px] p-1.5 flex flex-col transition-colors relative ' + 
                     (isOtherMonth ? 'bg-slate-50/40 text-slate-300' : 'bg-white hover:bg-slate-50/60');
    
    // Construct date string YYYY-MM-DD for matching
    let cellMonth = currentMonth + 1;
    let cellYear = currentYear;
    if (isOtherMonth) {
      // rough representation for other months
      cell.innerHTML = `<span class="text-xs font-semibold text-slate-300 ml-1">${dayNum}</span>`;
      return cell;
    }

    const monthStr = String(cellMonth).padStart(2, '0');
    const dayStr = String(dayNum).padStart(2, '0');
    const dateKey = `${cellYear}-${monthStr}-${dayStr}`;
    cell.dataset.date = dateKey;

    const topBar = document.createElement('div');
    topBar.className = 'flex items-center justify-between mb-1';
    
    const dayBadge = document.createElement('span');
    dayBadge.className = isToday 
      ? 'w-6 h-6 rounded-full bg-brand-700 text-white font-extrabold text-xs flex items-center justify-center shadow-xs' 
      : 'text-xs font-bold text-slate-600 ml-1';
    dayBadge.innerText = dayNum;
    topBar.appendChild(dayBadge);

    cell.appendChild(topBar);

    // Events container
    const eventsContainer = document.createElement('div');
    eventsContainer.className = 'space-y-1 overflow-y-auto max-h-[85px] no-scrollbar flex-1';
    eventsContainer.id = 'events-' + dateKey;
    cell.appendChild(eventsContainer);

    return cell;
  }

  function loadCalendarEvents() {
    const farmId = document.getElementById('calendarPropertyFilter').value;
    const startStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-01`;
    const lastDay = new Date(currentYear, currentMonth + 1, 0).getDate();
    const endStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;

    let url = '<?= url('admin/bookings/calendar-data') ?>?start=' + startStr + '&end=' + endStr;
    if (farmId) url += '&farmhouse_id=' + farmId;

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        eventsData = data;
        populateEventsIntoCalendar(data);
      })
      .catch(() => {
        console.error('Failed to load calendar events');
      });
  }

  function populateEventsIntoCalendar(data) {
    // Clear any previous events
    document.querySelectorAll('[id^="events-"]').forEach(el => el.innerHTML = '');

    if (!data.bookings || data.bookings.length === 0) return;

    data.bookings.forEach(b => {
      // Find cells between check_in and check_out
      const ci = new Date(b.check_in);
      const co = new Date(b.check_out);

      // Status color
      let bgClass = 'bg-blue-50 text-blue-700 border-blue-200';
      if (b.status === 'checked_in') bgClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
      else if (b.status === 'pending') bgClass = 'bg-amber-50 text-amber-800 border-amber-200';
      else if (b.status === 'checked_out' || b.status === 'completed') bgClass = 'bg-slate-100 text-slate-600 border-slate-200';

      const ref = b.booking_reference || ('#' + b.id);

      // Iterate dates of stay
      for (let d = new Date(ci); d < co; d.setDate(d.getDate() + 1)) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const key = `${y}-${m}-${day}`;
        const container = document.getElementById('events-' + key);

        if (container) {
          const pill = document.createElement('div');
          pill.className = `px-1.5 py-0.5 rounded-md text-[10px] font-bold border truncate cursor-pointer transition-transform hover:scale-[1.02] ${bgClass}`;
          pill.innerText = `${ref}: ${b.customer_name || 'Guest'}`;
          pill.title = `${ref} - ${b.farmhouse_title} (${b.check_in} to ${b.check_out})`;
          pill.onclick = (e) => {
            e.stopPropagation();
            openBookingDrawer(b);
          };
          container.appendChild(pill);
        }
      }
    });
  }

  function openBookingDrawer(b) {
    const ref = b.booking_reference || ('STAY-BK-' + String(b.id).padStart(5, '0'));
    document.getElementById('drawerRefCode').innerText = '#' + ref;
    document.getElementById('drawerPropertyTitle').innerText = b.farmhouse_title || 'Estate';
    document.getElementById('drawerGuestName').innerText = b.customer_name || 'Guest';
    document.getElementById('drawerGuestPhone').innerText = b.customer_phone || '—';
    document.getElementById('drawerStayDates').innerText = b.check_in + ' → ' + b.check_out;
    document.getElementById('drawerRoomUnit').innerText = b.assigned_room_number || (b.booking_type === 'complete' ? 'Entire Farmhouse' : (b.room_type_name || 'Standard Room'));
    document.getElementById('drawerTotalAmount').innerText = '₹' + Number(b.price || 0).toLocaleString('en-IN');
    document.getElementById('drawerBalanceDue').innerText = '₹' + Number(b.balance_amount || 0).toLocaleString('en-IN');

    const badge = document.getElementById('drawerStatusBadge');
    badge.innerText = b.status.toUpperCase();
    document.getElementById('drawerDetailsLink').href = '<?= url('admin/bookings/view/') ?>' + b.id;

    document.getElementById('bookingDrawer').classList.remove('hidden');
  }

  function closeBookingDrawer() {
    document.getElementById('bookingDrawer').classList.add('hidden');
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeBookingDrawer();
  });
</script>

<?php 
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php'; 
?>
