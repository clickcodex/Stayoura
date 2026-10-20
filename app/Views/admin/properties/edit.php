<?php
$pageTitle   = "Edit Property #" . $property['id'] . " • Stayora Executive Console";
$currentPage = "properties";
$headerTitle = "Edit Property #" . $property['id'];

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../partials/prose_styles.php';
require_once __DIR__ . '/../partials/icon_picker_modal.php';

$allAmenitiesGrouped = $allAmenitiesGrouped ?? [];
$rulePresets         = $rulePresets ?? [];
$owners              = $owners ?? [];
$property            = $property ?? [];

// Helper for image URLs
function resolveImgSrc(?string $url): string {
    if (empty($url)) return 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=400&q=80';
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) return $url;
    return asset($url);
}

$currentRentalType = $property['rental_type'] ?? (!empty($property['allow_room_booking']) ? 'both' : 'complete');
$selectedAmenityIds = $property['amenity_ids'] ?? [];
$existingRooms = $property['room_types'] ?? [];
$existingRules = $property['rules'] ?? [];
?>

<!-- Quill Rich Text Editor Styles -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
  .ql-toolbar.ql-snow {
    border: 1px solid #e2e8f0 !important;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    background-color: #f8fafc;
    padding: 10px 14px !important;
  }
  .ql-container.ql-snow {
    border: 1px solid #e2e8f0 !important;
    border-top: none !important;
    border-bottom-left-radius: 1rem;
    border-bottom-right-radius: 1rem;
    background-color: #ffffff;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    font-size: 14px !important;
    min-height: 220px;
  }
  .ql-editor {
    min-height: 220px;
    line-height: 1.7;
    color: #1e293b;
    padding: 16px 20px !important;
  }
  .ql-editor.ql-blank::before {
    color: #94a3b8;
    font-style: normal;
    left: 20px;
  }
  /* Amenity chip toggle styles */
  .amenity-chip {
    transition: all 0.15s ease-in-out;
  }
  .amenity-chip.selected {
    background-color: #eff6ff !important;
    border-color: #3b82f6 !important;
    color: #1d4ed8 !important;
    box-shadow: 0 1px 3px rgba(59, 130, 246, 0.15);
  }
  .amenity-chip.selected .chip-check {
    opacity: 1 !important;
    transform: scale(1);
  }
  .amenity-chip .chip-check {
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.15s ease-in-out;
  }
  /* Booking mode card styles */
  .mode-card {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .mode-card.active {
    border-color: #2563eb !important;
    background: linear-gradient(to bottom, #eff6ff, #ffffff) !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
  }
</style>

<!-- ══════════════════════════════════════════════
     TOP BREADCRUMB & HEADER
     ══════════════════════════════════════════════ -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
  <div>
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
      <a href="<?= url('admin/properties') ?>" class="hover:text-brand-700 transition-colors">Manage Properties</a>
      <span>/</span>
      <span class="text-slate-700 font-bold">Edit Listing</span>
    </div>
    <h2 class="font-headline font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
      Modify: <?= htmlspecialchars($property['title']) ?>
    </h2>
    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
      Update property specifications, booking modes, rich descriptions, room inventory, amenities, and policies.
    </p>
  </div>

  <div class="flex items-center gap-3">
    <a href="<?= url('admin/properties/view?id=' . encrypt_id($property['id'])) ?>" class="px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
      <span class="material-symbols-outlined text-sm">visibility</span>
      <span>View Live</span>
    </a>
    <a href="<?= url('admin/properties') ?>" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
      <span class="material-symbols-outlined text-sm">arrow_back</span>
      <span>Back</span>
    </a>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     PROPERTY EDIT FORM
     ══════════════════════════════════════════════ -->
<form 
  action="<?= url('admin/properties/update') ?>" 
  method="POST" 
  enctype="multipart/form-data" 
  id="edit-property-form"
  onsubmit="handlePropertySubmit(event)"
  class="space-y-6">

  <input type="hidden" name="id" value="<?= encrypt_id($property['id']) ?>">
  <input type="hidden" id="current-farmhouse-id" value="<?= $property['id'] ?>">

  <!-- Section 1: Basic Identification -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
      <span class="w-8 h-8 rounded-xl bg-blue-50 text-brand-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-lg">badge</span>
      </span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Essential Identification</h3>
        <p class="text-xs text-slate-400">Public listing title and classification</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
      
      <!-- Title -->
      <div class="md:col-span-6">
        <label for="prop-title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Property Title <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="title" 
          id="prop-title" 
          required 
          maxlength="150"
          value="<?= htmlspecialchars($property['title'] ?? '') ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
        <p class="text-[11px] text-slate-400 mt-1">Make it compelling and memorable for prospective guests.</p>
      </div>

      <!-- Property Type -->
      <div class="md:col-span-3">
        <label for="prop-type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Property Type <span class="text-rose-500">*</span>
        </label>
        <select 
          name="property_type_id" 
          id="prop-type" 
          required 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="">Select Property Type...</option>
          <?php foreach ($propertyTypes as $pt): ?>
            <option value="<?= $pt['id'] ?>" <?= ((string)($property['property_type_id'] ?? '')) === ((string)$pt['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($pt['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="text-[11px] text-slate-400 mt-1">Classification type.</p>
      </div>

      <!-- Property Owner -->
      <div class="md:col-span-3">
        <div class="flex items-center justify-between mb-1.5">
          <label for="prop-owner" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            Property Owner
          </label>
          <?php if (!empty($property['owner_id'])): ?>
            <a href="<?= url('admin/owners/view/' . $property['owner_id']) ?>" target="_blank" class="text-[10px] text-brand-700 font-bold hover:underline flex items-center gap-0.5" title="View Owner Dossier">
              <span class="material-symbols-outlined text-[12px]">open_in_new</span> Dossier
            </a>
          <?php else: ?>
            <a href="<?= url('admin/owners/create') ?>" target="_blank" class="text-[10px] text-brand-700 font-bold hover:underline flex items-center gap-0.5" title="Register New Owner">
              <span class="material-symbols-outlined text-[12px]">add_circle</span> New
            </a>
          <?php endif; ?>
        </div>
        <select 
          name="owner_id" 
          id="prop-owner" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="">In-House / Stayora Managed</option>
          <?php if (!empty($owners)): ?>
            <?php foreach ($owners as $ow): ?>
              <option value="<?= $ow['id'] ?>" <?= ((string)($property['owner_id'] ?? '')) === ((string)$ow['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ow['name']) ?><?= !empty($ow['business_name']) ? ' (' . htmlspecialchars($ow['business_name']) . ')' : '' ?><?= !empty($ow['city']) ? ' • ' . htmlspecialchars($ow['city']) : '' ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <p class="text-[11px] text-slate-400 mt-1">Estate host / owner record.</p>
      </div>

    </div>
  </div>

  <!-- Section 2: Location & Mapping -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
      <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-lg">location_on</span>
      </span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Geospatial &amp; Address</h3>
        <p class="text-xs text-slate-400">Destination city, street address, and navigation directions</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      
      <!-- City / Region -->
      <div>
        <label for="prop-location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Destination City / Region <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="location" 
          id="prop-location" 
          required 
          list="known-locations"
          value="<?= htmlspecialchars($property['location'] ?? '') ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
        <datalist id="known-locations">
          <?php foreach ($locations as $loc): ?>
            <option value="<?= htmlspecialchars($loc) ?>">
          <?php endforeach; ?>
        </datalist>
      </div>

      <!-- Full Street Address -->
      <div class="md:col-span-2">
        <label for="prop-address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Physical Street Address <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="address" 
          id="prop-address" 
          required 
          value="<?= htmlspecialchars($property['address'] ?? '') ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Google Maps Link -->
      <div class="md:col-span-3">
        <label for="prop-map" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Google Maps Navigation URL <span class="text-slate-400 font-normal">(Optional)</span>
        </label>
        <input 
          type="url" 
          name="google_map_link" 
          id="prop-map" 
          value="<?= htmlspecialchars($property['google_map_link'] ?? '') ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════
       Section 3: Booking Modes Architecture (Dual Booking Mode)
       ══════════════════════════════════════════════════════════════ -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">meeting_room</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Flexible Booking Modes</h3>
          <p class="text-xs text-slate-400">Configure how guests can reserve this property (Complete, Room-Wise, or Both)</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-semibold text-slate-400">Selected Architecture:</span>
        <span id="booking-mode-indicator" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
          <?= match($currentRentalType) {
            'room_wise' => 'Room-Wise Booking Only',
            'both'      => 'Dual Mode (Both Enabled)',
            default     => 'Complete Property Exclusive'
          } ?>
        </span>
      </div>
    </div>

    <!-- Mode Selector Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      
      <!-- Option A: Complete Property Booking -->
      <label class="mode-card relative block p-4 sm:p-5 rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer <?= $currentRentalType === 'complete' ? 'active' : '' ?>" id="card-mode-complete">
        <input 
          type="radio" 
          name="rental_type" 
          value="complete" 
          class="sr-only" 
          <?= $currentRentalType === 'complete' ? 'checked' : '' ?>
          onchange="switchBookingMode('complete')"
        />
        <div class="flex items-start justify-between">
          <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-brand-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">home</span>
          </div>
          <span class="mode-check-badge w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center text-white bg-transparent">
            <span class="material-symbols-outlined text-xs font-black">check</span>
          </span>
        </div>
        <div class="mt-3">
          <h4 class="font-headline font-bold text-sm text-slate-900">Complete Property Booking</h4>
          <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Guests reserve the entire estate exclusively as a single unit. Room-wise booking is disabled.
          </p>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center gap-1.5 text-[11px] font-bold text-brand-700">
          <span class="material-symbols-outlined text-sm">lock</span>
          <span>Full Estate Exclusive</span>
        </div>
      </label>

      <!-- Option B: Room-Wise Booking -->
      <label class="mode-card relative block p-4 sm:p-5 rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer <?= $currentRentalType === 'room_wise' ? 'active' : '' ?>" id="card-mode-room-wise">
        <input 
          type="radio" 
          name="rental_type" 
          value="room_wise" 
          class="sr-only" 
          <?= $currentRentalType === 'room_wise' ? 'checked' : '' ?>
          onchange="switchBookingMode('room_wise')"
        />
        <div class="flex items-start justify-between">
          <div class="w-10 h-10 rounded-xl bg-purple-100/70 text-purple-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">hotel</span>
          </div>
          <span class="mode-check-badge w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center text-white bg-transparent">
            <span class="material-symbols-outlined text-xs font-black">check</span>
          </span>
        </div>
        <div class="mt-3">
          <h4 class="font-headline font-bold text-sm text-slate-900">Room-Wise Booking Only</h4>
          <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Guests book individual room types (Deluxe, Suite, Standard). Whole-property booking is disabled.
          </p>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center gap-1.5 text-[11px] font-bold text-purple-700">
          <span class="material-symbols-outlined text-sm">inventory_2</span>
          <span>Per-Room Inventory</span>
        </div>
      </label>

      <!-- Option C: Both (Dual Mode Architecture) -->
      <label class="mode-card relative block p-4 sm:p-5 rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer <?= $currentRentalType === 'both' ? 'active' : '' ?>" id="card-mode-both">
        <input 
          type="radio" 
          name="rental_type" 
          value="both" 
          class="sr-only" 
          <?= $currentRentalType === 'both' ? 'checked' : '' ?>
          onchange="switchBookingMode('both')"
        />
        <div class="flex items-start justify-between">
          <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-emerald-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">all_inclusive</span>
          </div>
          <span class="mode-check-badge w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center text-white bg-transparent">
            <span class="material-symbols-outlined text-xs font-black">check</span>
          </span>
        </div>
        <div class="mt-3">
          <h4 class="font-headline font-bold text-sm text-slate-900">Both Booking Modes</h4>
          <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Allows booking the entire estate OR individual rooms. Automated conflict engine prevents overlapping reservations.
          </p>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center gap-1.5 text-[11px] font-bold text-emerald-700">
          <span class="material-symbols-outlined text-sm">sync_alt</span>
          <span>Dual Architecture Matrix</span>
        </div>
      </label>

    </div>

    <!-- Complete Property Pricing Container -->
    <div id="complete-pricing-section" class="pt-2 border-t border-slate-100 space-y-4 <?= $currentRentalType === 'room_wise' ? 'hidden' : '' ?>">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-slate-500 text-lg">payments</span>
          <span class="font-headline font-bold text-xs uppercase tracking-wider text-slate-700">Complete Farmhouse Booking Price</span>
        </div>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-brand-700 border border-blue-200">
          Full Estate Exclusive
        </span>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Weekday Price -->
        <div>
          <label for="prop-price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Complete Rate (Weekday ₹) <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
            <input 
              type="number" 
              name="price" 
              id="prop-price" 
              min="0" 
              step="100"
              value="<?= htmlspecialchars((string)($property['price'] ?? '0')) ?>"
              oninput="updateRatePreview()"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-8 pr-4 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
            />
          </div>
          <p class="text-[11px] text-slate-400 mt-1">Nightly rate when a guest reserves the entire property.</p>
        </div>

        <!-- Weekend Price -->
        <div>
          <label for="prop-weekend-price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Weekend Rate (₹)
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
            <input 
              type="number" 
              name="weekend_price" 
              id="prop-weekend-price" 
              min="0" 
              step="100"
              value="<?= htmlspecialchars((string)($property['weekend_price'] ?? '')) ?>"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-8 pr-4 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
            />
          </div>
          <p class="text-[11px] text-slate-400 mt-1">Friday &amp; Saturday peak tariffs</p>
        </div>

        <!-- Security Deposit -->
        <div>
          <label for="prop-deposit" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Security Deposit (₹)
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
            <input 
              type="number" 
              name="security_deposit" 
              id="prop-deposit" 
              min="0" 
              step="500"
              value="<?= htmlspecialchars((string)($property['security_deposit'] ?? '0')) ?>"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-8 pr-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
            />
          </div>
          <p class="text-[11px] text-slate-400 mt-1">100% refundable at check-out</p>
        </div>

        <!-- Cleaning Fee -->
        <div>
          <label for="prop-cleaning" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Cleaning Fee (₹)
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
            <input 
              type="number" 
              name="cleaning_fee" 
              id="prop-cleaning" 
              min="0" 
              step="100"
              value="<?= htmlspecialchars((string)($property['cleaning_fee'] ?? '0')) ?>"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-8 pr-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
            />
          </div>
          <p class="text-[11px] text-slate-400 mt-1">Post-stay sanitized turnover fee</p>
        </div>

        <!-- Weekly Discount % -->
        <div>
          <label for="prop-weekly-disc" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            7+ Nights Discount (%)
          </label>
          <input 
            type="number" 
            name="weekly_discount_percent" 
            id="prop-weekly-disc" 
            min="0" 
            max="50"
            value="<?= htmlspecialchars((string)($property['weekly_discount_percent'] ?? '10')) ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Monthly Discount % -->
        <div>
          <label for="prop-monthly-disc" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            30+ Nights Discount (%)
          </label>
          <input 
            type="number" 
            name="monthly_discount_percent" 
            id="prop-monthly-disc" 
            min="0" 
            max="70"
            value="<?= htmlspecialchars((string)($property['monthly_discount_percent'] ?? '20')) ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Rate Preview Badge -->
        <div class="md:col-span-2 flex items-center justify-end pt-5">
          <div class="px-4 py-2.5 rounded-2xl bg-slate-100 border border-slate-200 flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase">Full Farmhouse Rate:</span>
            <span id="rate-preview-text" class="text-sm font-black text-brand-700">₹<?= number_format((float)($property['price'] ?? 0)) ?> / night</span>
          </div>
        </div>

      </div>
    </div>

    <!-- Room Types & Inventory Configuration (Visible when room_wise or both) -->
    <div id="room-inventory-section" class="pt-4 border-t border-slate-100 space-y-4 <?= $currentRentalType === 'complete' ? 'hidden' : '' ?>">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-600 text-lg">hotel</span>
            <h4 class="font-headline font-bold text-sm text-slate-900">Room Types, Inventory &amp; Room-Wise Pricing</h4>
          </div>
          <p class="text-xs text-slate-400 mt-0.5">
            Manage multiple room tiers (e.g. Standard Room, Deluxe Room, Luxury Suite), set available inventory and room tariffs.
          </p>
        </div>
        
        <button 
          type="button" 
          onclick="openAddRoomModal()" 
          class="px-3.5 py-2 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-bold transition-all flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
          <span class="material-symbols-outlined text-base">add</span>
          <span>Add Room Type</span>
        </button>
      </div>

      <!-- Room Types Dynamic List -->
      <div id="room-types-table-wrap" class="overflow-x-auto rounded-2xl border border-slate-200">
        <table class="w-full text-left text-xs text-slate-600" id="room-types-table">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Room Type</th>
              <th class="py-3 px-3">Units</th>
              <th class="py-3 px-3">Weekday Rate</th>
              <th class="py-3 px-3">Weekend Rate</th>
              <th class="py-3 px-3">Capacity</th>
              <th class="py-3 px-3">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="room-types-tbody" class="divide-y divide-slate-100">
            <!-- Dynamically populated via JavaScript -->
          </tbody>
        </table>
        <div id="no-rooms-message" class="p-6 text-center text-xs text-slate-400 hidden">
          No room types added yet. Click <strong>"Add Room Type"</strong> above to configure inventory.
        </div>
      </div>
      <!-- Hidden serialized room types payload -->
      <input type="hidden" name="room_types_json" id="room-types-json" value="[]" />
    </div>

  </div>

  <!-- Section 4: Accommodation Capacities -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
      <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-lg">hotel</span>
      </span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Accommodation Capacities</h3>
        <p class="text-xs text-slate-400">Bedrooms, guest count limitations, and sleeping capacity</p>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <!-- Bedrooms -->
      <div>
        <label for="prop-bedrooms" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Bedrooms (BHK Count) <span class="text-rose-500">*</span>
        </label>
        <input 
          type="number" 
          name="bedrooms" 
          id="prop-bedrooms" 
          required 
          min="1" 
          max="50"
          value="<?= htmlspecialchars((string)($property['bedrooms'] ?? '1')) ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Bedroom Capacity -->
      <div>
        <label for="prop-bedroom-cap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Guests per Bedroom <span class="text-rose-500">*</span>
        </label>
        <input 
          type="number" 
          name="bedroom_capacity" 
          id="prop-bedroom-cap" 
          required
          min="1" 
          max="10"
          value="<?= htmlspecialchars((string)($property['bedroom_capacity'] ?? '2')) ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Day Guests Capacity -->
      <div>
        <label for="prop-day-cap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Max Day Gathering (Event) <span class="text-rose-500">*</span>
        </label>
        <input 
          type="number" 
          name="day_capacity" 
          id="prop-day-cap" 
          required 
          min="1" 
          max="500"
          value="<?= htmlspecialchars((string)($property['day_capacity'] ?? '10')) ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <!-- Night Sleeping Capacity -->
      <div>
        <label for="prop-night-cap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Max Overnight Guests <span class="text-rose-500">*</span>
        </label>
        <input 
          type="number" 
          name="night_capacity" 
          id="prop-night-cap" 
          required 
          min="1" 
          max="100"
          value="<?= htmlspecialchars((string)($property['night_capacity'] ?? '6')) ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════
       Section 5: Description with WYSIWYG Rich Text Editor
       ══════════════════════════════════════════════════════════════ -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">notes</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Detailed Description</h3>
          <p class="text-xs text-slate-400">Describe the atmosphere, panoramic views, facilities, and unique estate highlights</p>
        </div>
      </div>

      <!-- Editor vs Live Preview Switcher -->
      <div class="flex items-center rounded-xl bg-slate-100 p-1 border border-slate-200">
        <button 
          type="button" 
          id="btn-tab-editor" 
          onclick="toggleDescriptionTab('editor')" 
          class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-xs">
          WYSIWYG Editor
        </button>
        <button 
          type="button" 
          id="btn-tab-preview" 
          onclick="toggleDescriptionTab('preview')" 
          class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-500 hover:text-slate-800">
          Guest Preview
        </button>
      </div>
    </div>

    <!-- Quill Editor Container -->
    <div id="quill-editor-wrapper">
      <div id="quill-editor"></div>
      <!-- Hidden input submitted with form -->
      <input type="hidden" name="description" id="prop-description-hidden" value="<?= htmlspecialchars($property['description'] ?? '') ?>">
    </div>

    <!-- Live Preview Container (Hidden by default) -->
    <div id="quill-preview-wrapper" class="hidden p-6 rounded-2xl bg-slate-50 border border-slate-200">
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Public Guest View Rendering:</div>
      <div id="quill-preview-content" class="stayora-prose prose max-w-none text-slate-700 text-sm leading-relaxed">
        <!-- Rendered live via JS -->
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════
       Section 6: Equipped Amenities Management (Matching Screenshot 2)
       ══════════════════════════════════════════════════════════════ -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">checklist</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Equipped Amenities</h3>
          <p class="text-xs text-slate-400">Check all amenities available at this farmhouse estate</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a 
          href="<?= url('admin/amenities-rules?tab=amenities') ?>" 
          target="_blank"
          class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-brand-300 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition-all inline-flex items-center gap-1.5"
          title="Open Master Amenities & Rules Hub in new tab">
          <span class="material-symbols-outlined text-sm text-brand-600">open_in_new</span>
          <span>Manage Master Catalog</span>
        </a>
        <button 
          type="button" 
          onclick="openAddAmenityModal()" 
          class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
          <span class="material-symbols-outlined text-sm">add</span>
          <span>Add Custom Amenity</span>
        </button>
      </div>
    </div>

    <!-- Categorized Amenities Lists -->
    <div class="space-y-6" id="amenities-container">
      <?php foreach ($allAmenitiesGrouped as $groupTitle => $items): ?>
        <div class="amenity-group space-y-3" data-group="<?= htmlspecialchars($groupTitle) ?>">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-brand-600"></span>
              <h4 class="font-headline font-bold text-xs uppercase tracking-wider text-slate-700">
                <?= htmlspecialchars($groupTitle) ?> <span class="text-slate-400 font-semibold">(<?= count($items) ?>)</span>
              </h4>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
              <button 
                type="button" 
                onclick="selectAllInGroup('<?= htmlspecialchars(addslashes($groupTitle)) ?>')" 
                class="text-brand-700 hover:underline cursor-pointer">
                Select All
              </button>
              <span class="text-slate-300">·</span>
              <button 
                type="button" 
                onclick="clearAllInGroup('<?= htmlspecialchars(addslashes($groupTitle)) ?>')" 
                class="text-slate-400 hover:text-slate-600 cursor-pointer">
                Clear
              </button>
            </div>
          </div>

          <div class="flex flex-wrap gap-2.5">
            <?php foreach ($items as $am): 
              $isPreSelected = in_array((int)$am['id'], $selectedAmenityIds, true);
            ?>
              <div 
                class="amenity-chip group inline-flex items-center gap-1.5 py-1.5 pl-3 pr-1.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 select-none <?= $isPreSelected ? 'selected' : '' ?>"
                id="amenity-chip-<?= $am['id'] ?>"
                data-amenity-id="<?= $am['id'] ?>"
                data-group="<?= htmlspecialchars($groupTitle) ?>">
                <label class="inline-flex items-center gap-2 cursor-pointer py-1">
                  <input 
                    type="checkbox" 
                    name="amenities[]" 
                    value="<?= $am['id'] ?>" 
                    class="sr-only amenity-checkbox" 
                    <?= $isPreSelected ? 'checked' : '' ?>
                    onchange="toggleAmenityChip(this)"
                  />
                  <span class="amenity-icon material-symbols-outlined text-base text-slate-500">
                    <?= htmlspecialchars($am['icon_class'] ?: 'check_circle') ?>
                  </span>
                  <span class="amenity-name-text"><?= htmlspecialchars($am['name']) ?></span>
                  <span class="chip-check material-symbols-outlined text-sm text-brand-700 font-black">
                    check
                  </span>
                </label>
                <!-- Quick Edit trigger button -->
                <button 
                  type="button" 
                  onclick="openEditAmenityModal(<?= $am['id'] ?>)" 
                  class="opacity-60 md:opacity-0 md:group-hover:opacity-100 hover:opacity-100 p-1 rounded-lg hover:bg-slate-200/70 text-slate-400 hover:text-brand-700 transition-all cursor-pointer" 
                  title="Edit Amenity (Name, Icon, Category)">
                  <span class="material-symbols-outlined text-[15px] block">edit</span>
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════
       Section 7: Property & Stay Rules (Matching Screenshot 3)
       ══════════════════════════════════════════════════════════════ -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">gavel</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Property &amp; Stay Rules</h3>
          <p class="text-xs text-slate-400">Specify policies for guests (e.g. pets, smoking, loud music, parties)</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a 
          href="<?= url('admin/amenities-rules?tab=rules') ?>" 
          target="_blank"
          class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-purple-300 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition-all inline-flex items-center gap-1.5"
          title="Open Master Amenities & Rules Hub in new tab">
          <span class="material-symbols-outlined text-sm text-purple-600">open_in_new</span>
          <span>Manage Master Rules</span>
        </a>
        <button 
          type="button" 
          onclick="openAddRuleModal()" 
          class="px-3.5 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-bold transition-all flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
          <span class="material-symbols-outlined text-base">add</span>
          <span>Add Custom Policy / Rule</span>
        </button>
      </div>
    </div>

    <!-- Rules Interactive Cards Grid (Allowed / Not Allowed Toggle) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5" id="rules-cards-grid">
      <?php 
      // If property has configured rules, use them; otherwise use standard seed
      $rulesToDisplay = !empty($existingRules) ? $existingRules : [
        ['rule_name' => 'Alcohol', 'is_allowed' => 1, 'icon_class' => 'wine_bar'],
        ['rule_name' => 'Bachelor Group', 'is_allowed' => 1, 'icon_class' => 'groups'],
        ['rule_name' => 'DJ / Loud Music', 'is_allowed' => 1, 'icon_class' => 'music_note'],
        ['rule_name' => 'Networking', 'is_allowed' => 1, 'icon_class' => 'wifi'],
        ['rule_name' => 'Non Veg', 'is_allowed' => 1, 'icon_class' => 'restaurant'],
        ['rule_name' => 'Outside Food', 'is_allowed' => 1, 'icon_class' => 'lunch_dining'],
        ['rule_name' => 'Pets', 'is_allowed' => 1, 'icon_class' => 'pets'],
        ['rule_name' => 'Smoking', 'is_allowed' => 1, 'icon_class' => 'smoking_rooms'],
      ];
      ?>
      <?php foreach ($rulesToDisplay as $rIdx => $rItem): 
        $rName = $rItem['rule_name'] ?? $rItem['name'] ?? '';
        $isAllowed = !empty($rItem['is_allowed']);
        $rIcon = $rItem['icon_class'] ?? 'verified';
        $rDesc = $rItem['description'] ?? '';
      ?>
        <div class="rule-card p-3.5 rounded-2xl border border-slate-200 bg-white hover:border-slate-300 flex items-center justify-between gap-3 shadow-2xs group" data-rule-index="<?= $rIdx ?>">
          <div class="flex items-center gap-3 min-w-0">
            <span class="rule-icon-box w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-lg"><?= htmlspecialchars($rIcon) ?></span>
            </span>
            <div class="min-w-0">
              <span class="font-headline font-bold text-xs sm:text-sm text-slate-800 rule-name-label block truncate"><?= htmlspecialchars($rName) ?></span>
              <span class="rule-desc-label <?= empty($rDesc) ? 'hidden' : '' ?> block text-[11px] text-slate-400 truncate max-w-[180px] sm:max-w-xs"><?= htmlspecialchars($rDesc) ?></span>
              <input type="hidden" name="rules[<?= $rIdx ?>][name]" class="rule-name-input" value="<?= htmlspecialchars($rName) ?>" />
              <input type="hidden" name="rules[<?= $rIdx ?>][icon_class]" class="rule-icon-input" value="<?= htmlspecialchars($rIcon) ?>" />
              <input type="hidden" name="rules[<?= $rIdx ?>][is_allowed]" class="rule-allowed-input" value="<?= $isAllowed ? '1' : '0' ?>" />
              <input type="hidden" name="rules[<?= $rIdx ?>][description]" class="rule-desc-input" value="<?= htmlspecialchars($rDesc) ?>" />
            </div>
          </div>

          <div class="flex items-center gap-1.5 shrink-0">
            <!-- Allowed / Not Allowed Toggle Pill -->
            <button 
              type="button" 
              onclick="toggleRuleStatus(this)" 
              class="rule-toggle-btn px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer <?= $isAllowed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' ?>">
              <span class="material-symbols-outlined text-sm"><?= $isAllowed ? 'check' : 'close' ?></span>
              <span class="status-text"><?= $isAllowed ? 'Allowed' : 'Not Allowed' ?></span>
            </button>

            <!-- Edit Rule Button -->
            <button 
              type="button" 
              onclick="openEditRuleModal(this)" 
              class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-400 hover:text-brand-700 transition-colors cursor-pointer"
              title="Edit Rule & Icon">
              <span class="material-symbols-outlined text-base">edit</span>
            </button>

            <!-- Delete Rule Button -->
            <button 
              type="button" 
              onclick="removeRuleCard(this)" 
              class="p-1.5 rounded-xl border border-slate-200 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
              title="Remove Rule">
              <span class="material-symbols-outlined text-base">delete</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Section 8: Operations, Verification & Governance -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
      <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-lg">verified_user</span>
      </span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Governance &amp; Certification</h3>
        <p class="text-xs text-slate-400">Status, verification audit notes, and booking workflow</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Status -->
      <div>
        <label for="prop-status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Publishing Status <span class="text-rose-500">*</span>
        </label>
        <select 
          name="status" 
          id="prop-status" 
          required 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="active" <?= ($property['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Published Live)</option>
          <option value="pending" <?= ($property['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Review (Hidden)</option>
          <option value="rejected" <?= ($property['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected (Suspended)</option>
        </select>
      </div>

      <!-- Booking Approval Workflow -->
      <div>
        <label for="prop-mode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Approval Workflow
        </label>
        <select 
          name="booking_mode" 
          id="prop-mode" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="request" <?= ($property['booking_mode'] ?? 'request') === 'request' ? 'selected' : '' ?>>Host Approval Required</option>
          <option value="instant" <?= ($property['booking_mode'] ?? '') === 'instant' ? 'selected' : '' ?>>Instant Booking Enabled</option>
        </select>
      </div>

      <!-- Verified Toggle -->
      <div class="flex items-center gap-3 pt-6">
        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input 
            type="checkbox" 
            name="is_verified" 
            value="1" 
            <?= !empty($property['is_verified']) ? 'checked' : '' ?>
            class="w-4 h-4 rounded text-brand-700 focus:ring-brand-500"
          />
          <span class="text-xs font-bold text-slate-800">Stayora Certified Verified Listing</span>
        </label>
      </div>

      <!-- Verification Notes -->
      <div class="md:col-span-3">
        <label for="prop-ver-notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Audit &amp; Inspection Certification Notes
        </label>
        <input 
          type="text" 
          name="verification_notes" 
          id="prop-ver-notes" 
          value="<?= htmlspecialchars($property['verification_notes'] ?? 'Physically Inspected & Host Verified by Stayora') ?>"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>
    </div>
  </div>

  <!-- Section 9: Host Contact Information -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
      <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-lg">call</span>
      </span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Host Contact &amp; Concierge Inquiries</h3>
        <p class="text-xs text-slate-400">Direct escalation phone and WhatsApp communication channels</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="prop-phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Host Contact Phone
        </label>
        <input 
          type="text" 
          name="contact_phone" 
          id="prop-phone" 
          value="<?= htmlspecialchars($property['contact_phone'] ?? '') ?>"
          placeholder="+91 9820123456" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <div>
        <label for="prop-whatsapp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          WhatsApp Inquiries Number
        </label>
        <input 
          type="text" 
          name="whatsapp_number" 
          id="prop-whatsapp" 
          value="<?= htmlspecialchars($property['whatsapp_number'] ?? '') ?>"
          placeholder="+91 9820123456" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>
    </div>
  </div>

  <!-- Section 10: Existing Gallery & Additional Uploads -->
  <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-6">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
      <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-lg">photo_library</span>
      </span>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Property Photography Gallery</h3>
        <p class="text-xs text-slate-400">Manage existing photos or upload additional high-resolution images</p>
      </div>
    </div>

    <!-- Existing Photos Grid -->
    <?php if (!empty($property['images'])): ?>
      <div>
        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">
          Current Gallery (<?= count($property['images']) ?> Photos)
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
          <?php foreach ($property['images'] as $img): ?>
            <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-4/3 shadow-2xs">
              <img 
                src="<?= resolveImgSrc($img['image_url']) ?>" 
                alt="Property Photo"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              
              <?php if (!empty($img['is_featured'])): ?>
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[9px] font-black bg-brand-700 text-white shadow-xs">
                  Cover
                </span>
              <?php endif; ?>

              <!-- Hover Action Toolbar -->
              <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2">
                <?php if (empty($img['is_featured'])): ?>
                  <a 
                    href="<?= url('admin/properties/set-featured?image_id=' . encrypt_id($img['id']) . '&farmhouse_id=' . encrypt_id($property['id'])) ?>" 
                    class="p-1.5 rounded-lg bg-white/90 hover:bg-white text-slate-700 hover:text-brand-700 transition-colors shadow-xs"
                    title="Set as Cover Photo">
                    <span class="material-symbols-outlined text-sm">star</span>
                  </a>
                <?php endif; ?>

                <a 
                  href="<?= url('admin/properties/delete-image?image_id=' . encrypt_id($img['id']) . '&farmhouse_id=' . encrypt_id($property['id'])) ?>" 
                  onclick="return confirm('Remove this image from property?')"
                  class="p-1.5 rounded-lg bg-white/90 hover:bg-white text-slate-700 hover:text-rose-600 transition-colors shadow-xs"
                  title="Delete Photo">
                  <span class="material-symbols-outlined text-sm">delete</span>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Drag & Drop Upload Container -->
    <div class="space-y-4">
      <div class="border-2 border-dashed border-slate-300 hover:border-brand-500 bg-slate-50/70 rounded-2xl p-6 text-center transition-all cursor-pointer relative" onclick="document.getElementById('file-upload-input').click()">
        <input 
          type="file" 
          name="images[]" 
          id="file-upload-input" 
          multiple 
          accept="image/jpeg,image/png,image/webp" 
          class="hidden" 
          onchange="previewUploadedFiles(this)"
        />
        <div class="space-y-2 pointer-events-none">
          <div class="w-12 h-12 rounded-2xl bg-white text-brand-700 mx-auto flex items-center justify-center shadow-xs">
            <span class="material-symbols-outlined text-2xl">add_photo_alternate</span>
          </div>
          <div class="font-headline font-bold text-sm text-slate-800">
            Click or drag &amp; drop additional photos here
          </div>
          <p class="text-xs text-slate-400">
            JPG, PNG, or WebP up to 5MB per file.
          </p>
        </div>
      </div>

      <!-- Live Thumbnail Previews Container -->
      <div id="upload-preview-container" class="hidden grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 pt-2"></div>

      <!-- Fallback External Image URLs -->
      <div class="pt-3 border-t border-slate-100">
        <label for="prop-manual-urls" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          Or Append External Image URLs <span class="text-slate-400 font-normal">(One URL per line)</span>
        </label>
        <textarea 
          name="manual_image_urls" 
          id="prop-manual-urls" 
          rows="2" 
          placeholder="https://images.unsplash.com/...&#10;https://images.unsplash.com/..." 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono focus:bg-white focus:border-brand-500 focus:outline-none transition-all"></textarea>
      </div>
    </div>
  </div>

  <!-- Form Actions -->
  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
    <a href="<?= url('admin/properties') ?>" class="px-5 py-3 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors">
      Cancel
    </a>

    <button 
      type="submit" 
      id="submit-property-btn"
      class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white font-headline font-bold text-xs shadow-lg shadow-brand-700/20 flex items-center gap-2 transition-all cursor-pointer">
      <span class="material-symbols-outlined text-base">save</span>
      <span id="btn-text">Update Property Specifications</span>
    </button>
  </div>

</form>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: ADD / EDIT ROOM TYPE
     ══════════════════════════════════════════════════════════════ -->
<div id="room-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">hotel</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900" id="room-modal-title">Edit Room Type</h3>
      </div>
      <button type="button" onclick="closeRoomModal()" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <input type="hidden" id="modal-room-id" value="0" />
    <input type="hidden" id="modal-room-index" value="-1" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Room Type Name <span class="text-rose-500">*</span></label>
        <input type="text" id="modal-room-name" placeholder="e.g. Deluxe Pool View Suite" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Available Units <span class="text-rose-500">*</span></label>
        <input type="number" id="modal-room-count" min="1" value="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Max Guests / Unit <span class="text-rose-500">*</span></label>
        <input type="number" id="modal-room-capacity" min="1" value="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Weekday Rate (₹) <span class="text-rose-500">*</span></label>
        <input type="number" id="modal-room-price" min="0" step="50" placeholder="2500" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Weekend Rate (₹)</label>
        <input type="number" id="modal-room-weekend-price" min="0" step="50" placeholder="3200" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Features / Description</label>
        <input type="text" id="modal-room-desc" placeholder="e.g. King Bed, En-suite Jacuzzi, Garden View" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status</label>
        <select id="modal-room-status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="active">Active (Available for booking)</option>
          <option value="inactive">Inactive (Disabled)</option>
        </select>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
      <button type="button" onclick="closeRoomModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100">Cancel</button>
      <button type="button" onclick="saveRoomFromModal()" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold">Save Room Type</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: SAFEGUARD WARNING DIALOG (CANNOT DELETE ROOM)
     ══════════════════════════════════════════════════════════════ -->
<div id="safeguard-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 text-center space-y-4">
    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center shadow-xs">
      <span class="material-symbols-outlined text-3xl">shield_lock</span>
    </div>
    
    <div>
      <h3 class="font-headline font-bold text-lg text-slate-900">Protected Inventory Safeguard</h3>
      <p class="text-xs text-slate-500 mt-2 leading-relaxed" id="safeguard-modal-text">
        This room type has active or historical booking records. To maintain financial and calendar integrity, it cannot be permanently deleted.
      </p>
    </div>

    <div class="pt-2">
      <button type="button" onclick="closeSafeguardModal()" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
        Understood, Close
      </button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: ADD CUSTOM AMENITY
     ══════════════════════════════════════════════════════════════ -->
<div id="amenity-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">add_circle</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900">Add Custom Amenity</h3>
      </div>
      <button type="button" onclick="closeAmenityModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <div class="space-y-3 text-left">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amenity Name <span class="text-rose-500">*</span></label>
        <input type="text" id="modal-amenity-name" placeholder="e.g. Steam Sauna, EV Charger" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category</label>
        <select id="modal-amenity-category" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="general">General &amp; Living</option>
          <option value="bedroom">Bedrooms &amp; Bath</option>
          <option value="kitchen">Kitchen &amp; Dining</option>
          <option value="leisure">Outdoor, Pool &amp; Leisure</option>
          <option value="wellness">Entertainment &amp; Wellness</option>
          <option value="technology">Technology &amp; Concierge</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Visual Icon</label>
        <div class="flex items-center gap-2">
          <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
            <span id="modal-amenity-icon-preview" class="material-symbols-outlined text-2xl">check_circle</span>
          </div>
          <input type="text" id="modal-amenity-icon" value="check_circle" readonly class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-mono text-slate-700" />
          <button type="button" onclick="StayoraIconPicker.open({ targetInputId: 'modal-amenity-icon', previewElId: 'modal-amenity-icon-preview', currentIcon: document.getElementById('modal-amenity-icon').value })" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">palette</span>
            <span>Browse</span>
          </button>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
      <button type="button" onclick="closeAmenityModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
      <button type="button" onclick="saveCustomAmenity()" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold cursor-pointer">Add Amenity</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: EDIT AMENITY
     ══════════════════════════════════════════════════════════════ -->
<div id="edit-amenity-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">edit</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900">Edit Amenity</h3>
      </div>
      <button type="button" onclick="closeEditAmenityModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <input type="hidden" id="edit-amenity-id" value="0" />

    <!-- Shared catalog usage alert -->
    <div id="edit-amenity-usage-banner" class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
      <span class="material-symbols-outlined text-base text-amber-700 shrink-0 mt-0.5">info</span>
      <div id="edit-amenity-usage-text">
        Loading amenity usage across properties...
      </div>
    </div>

    <div class="space-y-3 text-left">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amenity Name <span class="text-rose-500">*</span></label>
        <input type="text" id="edit-amenity-name" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category</label>
        <select id="edit-amenity-category" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="general">General &amp; Living</option>
          <option value="bedroom">Bedrooms &amp; Bath</option>
          <option value="kitchen">Kitchen &amp; Dining</option>
          <option value="leisure">Outdoor, Pool &amp; Leisure</option>
          <option value="wellness">Entertainment &amp; Wellness</option>
          <option value="technology">Technology &amp; Concierge</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Visual Icon</label>
        <div class="flex items-center gap-2">
          <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
            <span id="edit-amenity-icon-preview" class="material-symbols-outlined text-2xl">check_circle</span>
          </div>
          <input type="text" id="edit-amenity-icon" value="check_circle" readonly class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-mono text-slate-700" />
          <button type="button" onclick="StayoraIconPicker.open({ targetInputId: 'edit-amenity-icon', previewElId: 'edit-amenity-icon-preview', currentIcon: document.getElementById('edit-amenity-icon').value })" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">palette</span>
            <span>Browse</span>
          </button>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
      <button type="button" onclick="promptDeleteAmenity()" class="px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 text-xs font-bold flex items-center gap-1 cursor-pointer">
        <span class="material-symbols-outlined text-base">delete</span>
        <span>Delete Amenity</span>
      </button>

      <div class="flex items-center gap-2">
        <button type="button" onclick="closeEditAmenityModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
        <button type="button" id="save-amenity-btn" onclick="saveEditedAmenity()" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold cursor-pointer">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: DELETE AMENITY CONFIRMATION
     ══════════════════════════════════════════════════════════════ -->
<div id="delete-amenity-confirm-modal" class="fixed inset-0 z-[60] hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-xl">warning</span>
      </div>
      <div>
        <h3 class="font-headline font-bold text-base text-slate-900">Delete Amenity?</h3>
        <p class="text-xs text-slate-400">This action cannot be undone</p>
      </div>
    </div>

    <p id="delete-amenity-confirm-msg" class="text-xs sm:text-sm text-slate-600 leading-relaxed">
      Are you sure you want to delete this amenity from the master catalog?
    </p>

    <div class="flex items-center justify-end gap-2 pt-2">
      <button type="button" onclick="closeDeleteAmenityModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
      <button type="button" id="confirm-delete-amenity-btn" onclick="executeDeleteAmenity()" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold cursor-pointer">Delete Permanently</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: ADD CUSTOM POLICY / RULE
     ══════════════════════════════════════════════════════════════ -->
<div id="rule-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">gavel</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900">Add Custom Policy / Rule</h3>
      </div>
      <button type="button" onclick="closeRuleModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <div class="space-y-3 text-left">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Policy / Rule Name <span class="text-rose-500">*</span></label>
        <input type="text" id="modal-rule-name" placeholder="e.g. Commercial Photography, Pool Hours" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status</label>
        <select id="modal-rule-status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="1">Allowed (Permitted)</option>
          <option value="0">Prohibited (Not Allowed)</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Visual Icon</label>
        <div class="flex items-center gap-2">
          <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center shrink-0">
            <span id="modal-rule-icon-preview" class="material-symbols-outlined text-2xl">verified</span>
          </div>
          <input type="text" id="modal-rule-icon" value="verified" readonly class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-mono text-slate-700" />
          <button type="button" onclick="StayoraIconPicker.open({ targetInputId: 'modal-rule-icon', previewElId: 'modal-rule-icon-preview', currentIcon: document.getElementById('modal-rule-icon').value })" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">palette</span>
            <span>Browse</span>
          </button>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Optional Details / Notes</label>
        <input type="text" id="modal-rule-desc" placeholder="e.g. Permitted strictly in designated outdoor zones" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
      <button type="button" onclick="closeRuleModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
      <button type="button" onclick="addCustomRuleCard()" class="px-6 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-600 text-white text-xs font-bold cursor-pointer">Add Rule</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: EDIT POLICY / RULE
     ══════════════════════════════════════════════════════════════ -->
<div id="edit-rule-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">edit</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900">Edit Policy / Rule</h3>
      </div>
      <button type="button" onclick="closeEditRuleModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <div class="space-y-3 text-left">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Policy / Rule Name <span class="text-rose-500">*</span></label>
        <input type="text" id="edit-rule-name" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status</label>
        <select id="edit-rule-status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-bold focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="1">Allowed (Permitted)</option>
          <option value="0">Prohibited (Not Allowed)</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Visual Icon</label>
        <div class="flex items-center gap-2">
          <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center shrink-0">
            <span id="edit-rule-icon-preview" class="material-symbols-outlined text-2xl">verified</span>
          </div>
          <input type="text" id="edit-rule-icon" value="verified" readonly class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-mono text-slate-700" />
          <button type="button" onclick="StayoraIconPicker.open({ targetInputId: 'edit-rule-icon', previewElId: 'edit-rule-icon-preview', currentIcon: document.getElementById('edit-rule-icon').value })" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">palette</span>
            <span>Browse</span>
          </button>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Details / Notes</label>
        <input type="text" id="edit-rule-desc" placeholder="e.g. Strictly prohibited indoors" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
      <button type="button" onclick="closeEditRuleModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
      <button type="button" onclick="saveEditedRule()" class="px-6 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-600 text-white text-xs font-bold cursor-pointer">Save Changes</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & WYSIWYG INITIALIZATION
     ══════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
  // ── 1. Initialize Quill WYSIWYG Rich Text Editor ──
  const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: 'Describe the atmosphere, panoramic views, facilities, and unique estate highlights...',
    modules: {
      toolbar: [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['blockquote', 'link'],
        ['clean']
      ]
    }
  });

  // Safely paste initial HTML into Quill delta engine to preserve list types and formatting
  const initialDescription = document.getElementById('prop-description-hidden').value;
  if (initialDescription) {
    quill.clipboard.dangerouslyPasteHTML(initialDescription);
  }

  // Keep hidden input synchronized
  quill.on('text-change', function() {
    const html = quill.root.innerHTML;
    document.getElementById('prop-description-hidden').value = (html === '<p><br></p>') ? '' : html;
  });

  function toggleDescriptionTab(tab) {
    const editorWrap = document.getElementById('quill-editor-wrapper');
    const previewWrap = document.getElementById('quill-preview-wrapper');
    const btnEditor = document.getElementById('btn-tab-editor');
    const btnPreview = document.getElementById('btn-tab-preview');
    const previewContent = document.getElementById('quill-preview-content');

    if (tab === 'preview') {
      editorWrap.classList.add('hidden');
      previewWrap.classList.remove('hidden');
      btnPreview.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-xs';
      btnEditor.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-500 hover:text-slate-800';
      previewContent.innerHTML = quill.root.innerHTML || '<p class="text-slate-400 italic">No description provided.</p>';
    } else {
      previewWrap.classList.add('hidden');
      editorWrap.classList.remove('hidden');
      btnEditor.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-xs';
      btnPreview.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-500 hover:text-slate-800';
    }
  }

  // ── 2. Flexible Booking Modes Controller ──
  let currentRentalType = '<?= $currentRentalType ?>';

  function switchBookingMode(mode) {
    currentRentalType = mode;

    document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('active'));
    const targetCard = document.getElementById(`card-mode-${mode.replace('_', '-')}`);
    if (targetCard) targetCard.classList.add('active');

    const ind = document.getElementById('booking-mode-indicator');
    const compPricing = document.getElementById('complete-pricing-section');
    const roomSection = document.getElementById('room-inventory-section');

    if (mode === 'complete') {
      ind.textContent = 'Complete Property Exclusive';
      ind.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-brand-700 border border-blue-200';
      compPricing.classList.remove('hidden');
      roomSection.classList.add('hidden');
    } else if (mode === 'room_wise') {
      ind.textContent = 'Room-Wise Booking Only';
      ind.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200';
      compPricing.classList.add('hidden');
      roomSection.classList.remove('hidden');
    } else {
      ind.textContent = 'Dual Mode (Both Enabled)';
      ind.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
      compPricing.classList.remove('hidden');
      roomSection.classList.remove('hidden');
    }
  }

  function updateRatePreview() {
    const val = document.getElementById('prop-price').value;
    const formatted = Number(val || 0).toLocaleString();
    document.getElementById('rate-preview-text').textContent = `₹${formatted} / night`;
  }

  // ── 3. Room Types In-Memory / AJAX Management ──
  let roomTypesData = <?= json_encode($existingRooms) ?>;
  const farmhouseId = <?= (int)$property['id'] ?>;

  function renderRoomTypesTable() {
    const tbody = document.getElementById('room-types-tbody');
    const noMsg = document.getElementById('no-rooms-message');
    tbody.innerHTML = '';

    if (!roomTypesData || roomTypesData.length === 0) {
      noMsg.classList.remove('hidden');
    } else {
      noMsg.classList.add('hidden');
      roomTypesData.forEach((rt, idx) => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition-colors';
        const hasBookings = !!rt.has_bookings;
        tr.innerHTML = `
          <td class="py-3 px-4">
            <div class="font-bold text-slate-800">${escapeHtml(rt.room_type_name)}</div>
            <div class="text-[11px] text-slate-400 truncate max-w-xs">${escapeHtml(rt.description || 'Standard luxury room')}</div>
          </td>
          <td class="py-3 px-3 font-bold text-slate-700">${rt.total_rooms}</td>
          <td class="py-3 px-3 font-bold text-slate-900">₹${Number(rt.price_per_room || 0).toLocaleString()}</td>
          <td class="py-3 px-3 font-bold text-slate-900">₹${Number(rt.weekend_price || (rt.price_per_room * 1.25)).toLocaleString()}</td>
          <td class="py-3 px-3 text-slate-600">${rt.capacity_per_room} Guests</td>
          <td class="py-3 px-3">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${rt.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'}">
              ${rt.status === 'active' ? 'Active' : 'Inactive'}
            </span>
          </td>
          <td class="py-3 px-4 text-right">
            <div class="flex items-center justify-end gap-1">
              <button type="button" onclick="openEditRoomModal(${idx})" class="p-1 rounded-lg text-slate-400 hover:text-brand-700 hover:bg-slate-100" title="Edit Room">
                <span class="material-symbols-outlined text-base">edit</span>
              </button>
              <button type="button" onclick="handleDeleteRoom(${idx})" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="${hasBookings ? 'Protected from Deletion' : 'Delete Room'}">
                <span class="material-symbols-outlined text-base">${hasBookings ? 'lock' : 'delete'}</span>
              </button>
            </div>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    document.getElementById('room-types-json').value = JSON.stringify(roomTypesData);
  }

  function openAddRoomModal() {
    document.getElementById('modal-room-id').value = '0';
    document.getElementById('modal-room-index').value = '-1';
    document.getElementById('room-modal-title').textContent = 'Add Room Type';
    document.getElementById('modal-room-name').value = '';
    document.getElementById('modal-room-count').value = '1';
    document.getElementById('modal-room-capacity').value = '2';
    document.getElementById('modal-room-price').value = '2500';
    document.getElementById('modal-room-weekend-price').value = '3200';
    document.getElementById('modal-room-desc').value = '';
    document.getElementById('modal-room-status').value = 'active';
    document.getElementById('room-modal').classList.remove('hidden');
  }

  function openEditRoomModal(idx) {
    const rt = roomTypesData[idx];
    if (!rt) return;
    document.getElementById('modal-room-id').value = rt.id || '0';
    document.getElementById('modal-room-index').value = idx;
    document.getElementById('room-modal-title').textContent = 'Edit Room Type';
    document.getElementById('modal-room-name').value = rt.room_type_name;
    document.getElementById('modal-room-count').value = rt.total_rooms;
    document.getElementById('modal-room-capacity').value = rt.capacity_per_room;
    document.getElementById('modal-room-price').value = rt.price_per_room;
    document.getElementById('modal-room-weekend-price').value = rt.weekend_price || '';
    document.getElementById('modal-room-desc').value = rt.description || '';
    document.getElementById('modal-room-status').value = rt.status || 'active';
    document.getElementById('room-modal').classList.remove('hidden');
  }

  function closeRoomModal() {
    document.getElementById('room-modal').classList.add('hidden');
  }

  function saveRoomFromModal() {
    const name = document.getElementById('modal-room-name').value.trim();
    if (!name) {
      if (window.adminToast) adminToast.error('Please enter a room type name');
      return;
    }
    const idx = parseInt(document.getElementById('modal-room-index').value, 10);
    const roomId = parseInt(document.getElementById('modal-room-id').value, 10);

    const formData = new FormData();
    formData.append('farmhouse_id', farmhouseId);
    if (roomId > 0) formData.append('room_type_id', roomId);
    formData.append('room_type_name', name);
    formData.append('total_rooms', document.getElementById('modal-room-count').value);
    formData.append('capacity_per_room', document.getElementById('modal-room-capacity').value);
    formData.append('price_per_room', document.getElementById('modal-room-price').value);
    formData.append('weekend_price', document.getElementById('modal-room-weekend-price').value);
    formData.append('description', document.getElementById('modal-room-desc').value.trim());
    formData.append('status', document.getElementById('modal-room-status').value);

    // Save directly via AJAX for persistent live sync
    fetch('<?= url('admin/properties/save-room') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (res.success && res.rooms) {
        roomTypesData = res.rooms;
        renderRoomTypesTable();
        closeRoomModal();
        if (window.adminToast) adminToast.success('Room type saved successfully');
      } else {
        if (window.adminToast) adminToast.error(res.message || 'Failed to save room type');
      }
    })
    .catch(err => {
      // Fallback in-memory
      const room = {
        id: roomId > 0 ? roomId : null,
        room_type_name: name,
        total_rooms: parseInt(document.getElementById('modal-room-count').value, 10) || 1,
        capacity_per_room: parseInt(document.getElementById('modal-room-capacity').value, 10) || 2,
        price_per_room: parseFloat(document.getElementById('modal-room-price').value) || 0,
        weekend_price: parseFloat(document.getElementById('modal-room-weekend-price').value) || null,
        description: document.getElementById('modal-room-desc').value.trim(),
        status: document.getElementById('modal-room-status').value
      };
      if (idx >= 0) roomTypesData[idx] = room; else roomTypesData.push(room);
      renderRoomTypesTable();
      closeRoomModal();
    });
  }

  function handleDeleteRoom(idx) {
    const rt = roomTypesData[idx];
    if (!rt) return;

    // Safeguard check
    if (rt.has_bookings) {
      document.getElementById('safeguard-modal-text').innerHTML = `
        <strong>"${escapeHtml(rt.room_type_name)}"</strong> has historical or active bookings in the system.<br><br>
        To preserve customer records and accounting integrity, this room type cannot be permanently deleted.<br><br>
        <em>Recommendation:</em> Set its status to <strong>"Inactive"</strong> to disable new reservations.
      `;
      document.getElementById('safeguard-modal').classList.remove('hidden');
      return;
    }

    if (!confirm(`Are you sure you want to remove the room type "${rt.room_type_name}"?`)) {
      return;
    }

    if (rt.id && rt.id > 0) {
      const fd = new FormData();
      fd.append('farmhouse_id', farmhouseId);
      fd.append('room_type_id', rt.id);

      fetch('<?= url('admin/properties/delete-room') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(res => {
        if (res.success && res.rooms) {
          roomTypesData = res.rooms;
          renderRoomTypesTable();
          if (window.adminToast) adminToast.success(res.message || 'Room type removed.');
        } else {
          document.getElementById('safeguard-modal-text').textContent = res.message || 'Cannot delete room.';
          document.getElementById('safeguard-modal').classList.remove('hidden');
        }
      })
      .catch(err => {
        if (window.adminToast) adminToast.error('Network error deleting room');
      });
    } else {
      roomTypesData.splice(idx, 1);
      renderRoomTypesTable();
    }
  }

  function closeSafeguardModal() {
    document.getElementById('safeguard-modal').classList.add('hidden');
  }

  // ── 4. Equipped Amenities Selection & Select All / Clear ──
  function toggleAmenityChip(checkbox) {
    const label = checkbox.closest('.amenity-chip');
    if (checkbox.checked) {
      label.classList.add('selected');
    } else {
      label.classList.remove('selected');
    }
  }

  function selectAllInGroup(groupTitle) {
    const group = document.querySelector(`.amenity-group[data-group="${groupTitle}"]`);
    if (!group) return;
    group.querySelectorAll('.amenity-checkbox').forEach(cb => {
      cb.checked = true;
      cb.closest('.amenity-chip').classList.add('selected');
    });
  }

  function clearAllInGroup(groupTitle) {
    const group = document.querySelector(`.amenity-group[data-group="${groupTitle}"]`);
    if (!group) return;
    group.querySelectorAll('.amenity-checkbox').forEach(cb => {
      cb.checked = false;
      cb.closest('.amenity-chip').classList.remove('selected');
    });
  }

  // ── 4. Equipped Amenities Selection & Management ──
  function openAddAmenityModal() {
    document.getElementById('modal-amenity-name').value = '';
    document.getElementById('modal-amenity-category').value = 'general';
    document.getElementById('modal-amenity-icon').value = 'check_circle';
    document.getElementById('modal-amenity-icon-preview').textContent = 'check_circle';
    document.getElementById('amenity-modal').classList.remove('hidden');
  }

  function closeAmenityModal() {
    document.getElementById('amenity-modal').classList.add('hidden');
  }

  function saveCustomAmenity() {
    const name = document.getElementById('modal-amenity-name').value.trim();
    const category = document.getElementById('modal-amenity-category').value;
    const icon = document.getElementById('modal-amenity-icon').value.trim() || 'check_circle';

    if (!name) {
      if (window.adminToast) adminToast.error('Please enter amenity name');
      return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('category', category);
    formData.append('icon_class', icon);

    fetch('<?= url('admin/properties/add-amenity') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (res.success && res.amenity) {
        const am = res.amenity;
        const newChip = document.createElement('div');
        newChip.className = 'amenity-chip group inline-flex items-center gap-1.5 py-1.5 pl-3 pr-1.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 select-none selected';
        newChip.id = `amenity-chip-${am.id}`;
        newChip.dataset.amenityId = am.id;
        newChip.innerHTML = `
          <label class="inline-flex items-center gap-2 cursor-pointer py-1">
            <input 
              type="checkbox" 
              name="amenities[]" 
              value="${am.id}" 
              class="sr-only amenity-checkbox" 
              checked 
              onchange="toggleAmenityChip(this)" 
            />
            <span class="amenity-icon material-symbols-outlined text-base text-slate-500">${escapeHtml(am.icon_class)}</span>
            <span class="amenity-name-text">${escapeHtml(am.name)}</span>
            <span class="chip-check material-symbols-outlined text-sm text-brand-700 font-black">check</span>
          </label>
          <button 
            type="button" 
            onclick="openEditAmenityModal(${am.id})" 
            class="opacity-60 md:opacity-0 md:group-hover:opacity-100 hover:opacity-100 p-1 rounded-lg hover:bg-slate-200/70 text-slate-400 hover:text-brand-700 transition-all cursor-pointer" 
            title="Edit Amenity (Name, Icon, Category)">
            <span class="material-symbols-outlined text-[15px] block">edit</span>
          </button>
        `;
        const firstGroupFlex = document.querySelector('.amenity-group .flex-wrap');
        if (firstGroupFlex) {
          firstGroupFlex.prepend(newChip);
        }
        closeAmenityModal();
        if (window.adminToast) adminToast.success(`Amenity "${am.name}" added and selected.`);
      } else {
        if (window.adminToast) adminToast.error(res.message || 'Failed to add amenity');
      }
    })
    .catch(err => {
      if (window.adminToast) adminToast.error('Network error adding amenity');
    });
  }

  // Edit & Delete Amenity Handlers
  let currentEditingAmenityData = null;

  function openEditAmenityModal(id) {
    const modal = document.getElementById('edit-amenity-modal');
    document.getElementById('edit-amenity-id').value = id;
    document.getElementById('edit-amenity-name').value = 'Loading...';
    document.getElementById('edit-amenity-usage-text').innerHTML = 'Inspecting catalog usage across properties...';
    modal.classList.remove('hidden');

    fetch(`<?= url('admin/properties/get-amenity?id=') ?>${id}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (res.success && res.amenity) {
        currentEditingAmenityData = res;
        const am = res.amenity;
        document.getElementById('edit-amenity-name').value = am.name;
        document.getElementById('edit-amenity-category').value = am.category || 'general';
        document.getElementById('edit-amenity-icon').value = am.icon_class || 'check_circle';
        document.getElementById('edit-amenity-icon-preview').textContent = am.icon_class || 'check_circle';

        const count = res.usage_count || 0;
        const banner = document.getElementById('edit-amenity-usage-text');
        if (count > 0) {
          banner.innerHTML = `This amenity is linked to <strong>${count} property listing(s)</strong> in your catalog. Changes to its name or icon will apply across all linked listings.`;
        } else {
          banner.innerHTML = `This amenity is currently not linked to any other property listings. Safe to modify or delete.`;
        }
      } else {
        if (window.adminToast) adminToast.error(res.message || 'Failed to load amenity details');
        closeEditAmenityModal();
      }
    })
    .catch(err => {
      if (window.adminToast) adminToast.error('Network error loading amenity details');
      closeEditAmenityModal();
    });
  }

  function closeEditAmenityModal() {
    document.getElementById('edit-amenity-modal').classList.add('hidden');
    currentEditingAmenityData = null;
  }

  function saveEditedAmenity() {
    const id = document.getElementById('edit-amenity-id').value;
    const name = document.getElementById('edit-amenity-name').value.trim();
    const category = document.getElementById('edit-amenity-category').value;
    const icon = document.getElementById('edit-amenity-icon').value.trim() || 'check_circle';

    if (!name) {
      if (window.adminToast) adminToast.error('Please enter an amenity name');
      return;
    }

    const btn = document.getElementById('save-amenity-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const fd = new FormData();
    fd.append('id', id);
    fd.append('name', name);
    fd.append('category', category);
    fd.append('icon_class', icon);

    fetch('<?= url('admin/properties/update-amenity') ?>', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.textContent = 'Save Changes';
      if (res.success && res.amenity) {
        // Update chip in DOM
        const chip = document.getElementById(`amenity-chip-${id}`);
        if (chip) {
          const iconEl = chip.querySelector('.amenity-icon');
          if (iconEl) iconEl.textContent = icon;
          const textEl = chip.querySelector('.amenity-name-text');
          if (textEl) textEl.textContent = name;
        }
        closeEditAmenityModal();
        if (window.adminToast) adminToast.success(`Amenity "${name}" updated successfully.`);
      } else {
        if (window.adminToast) adminToast.error(res.message || 'Failed to update amenity');
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.textContent = 'Save Changes';
      if (window.adminToast) adminToast.error('Network error updating amenity');
    });
  }

  function promptDeleteAmenity() {
    if (!currentEditingAmenityData || !currentEditingAmenityData.amenity) return;
    const am = currentEditingAmenityData.amenity;
    const count = currentEditingAmenityData.usage_count || 0;

    let msg = `Are you sure you want to permanently delete <strong>"${escapeHtml(am.name)}"</strong> from the catalog?`;
    if (count > 0) {
      msg += `<br><br><span class="text-rose-600 font-bold">Caution:</span> It is currently active on <strong>${count} property listing(s)</strong> and will be unlinked from them.`;
    }
    document.getElementById('delete-amenity-confirm-msg').innerHTML = msg;
    document.getElementById('delete-amenity-confirm-modal').classList.remove('hidden');
  }

  function closeDeleteAmenityModal() {
    document.getElementById('delete-amenity-confirm-modal').classList.add('hidden');
  }

  function executeDeleteAmenity() {
    if (!currentEditingAmenityData || !currentEditingAmenityData.amenity) return;
    const id = currentEditingAmenityData.amenity.id;
    const name = currentEditingAmenityData.amenity.name;

    const btn = document.getElementById('confirm-delete-amenity-btn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    const fd = new FormData();
    fd.append('id', id);

    fetch('<?= url('admin/properties/delete-amenity') ?>', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.textContent = 'Delete Permanently';
      if (res.success) {
        const chip = document.getElementById(`amenity-chip-${id}`);
        if (chip) chip.remove();
        closeDeleteAmenityModal();
        closeEditAmenityModal();
        if (window.adminToast) adminToast.success(`Amenity "${name}" deleted.`);
      } else {
        if (window.adminToast) adminToast.error(res.message || 'Failed to delete amenity');
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.textContent = 'Delete Permanently';
      if (window.adminToast) adminToast.error('Network error deleting amenity');
    });
  }

  // ── 5. Property Rules Management ──
  function toggleRuleStatus(btn) {
    const card = btn.closest('.rule-card');
    const input = card.querySelector('.rule-allowed-input');
    const statusText = btn.querySelector('.status-text');
    const icon = btn.querySelector('.material-symbols-outlined');

    if (input.value === '1') {
      input.value = '0';
      btn.className = 'rule-toggle-btn px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100';
      statusText.textContent = 'Not Allowed';
      icon.textContent = 'close';
    } else {
      input.value = '1';
      btn.className = 'rule-toggle-btn px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100';
      statusText.textContent = 'Allowed';
      icon.textContent = 'check';
    }
  }

  function openAddRuleModal() {
    document.getElementById('modal-rule-name').value = '';
    document.getElementById('modal-rule-status').value = '1';
    document.getElementById('modal-rule-icon').value = 'verified';
    document.getElementById('modal-rule-icon-preview').textContent = 'verified';
    document.getElementById('modal-rule-desc').value = '';
    document.getElementById('rule-modal').classList.remove('hidden');
  }

  function closeRuleModal() {
    document.getElementById('rule-modal').classList.add('hidden');
  }

  let nextCustomRuleIdx = 200;
  function addCustomRuleCard() {
    const name = document.getElementById('modal-rule-name').value.trim();
    const allowed = document.getElementById('modal-rule-status').value;
    const icon = document.getElementById('modal-rule-icon').value.trim() || 'verified';
    const desc = document.getElementById('modal-rule-desc').value.trim();

    if (!name) {
      if (window.adminToast) adminToast.error('Please enter a rule or policy name');
      return;
    }

    const grid = document.getElementById('rules-cards-grid');
    const idx = nextCustomRuleIdx++;
    const isAllow = allowed === '1';

    const card = document.createElement('div');
    card.className = 'rule-card p-3.5 rounded-2xl border border-slate-200 bg-white hover:border-slate-300 flex items-center justify-between gap-3 shadow-2xs group';
    card.dataset.ruleIndex = idx;
    card.innerHTML = `
      <div class="flex items-center gap-3 min-w-0">
        <span class="rule-icon-box w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-lg">${escapeHtml(icon)}</span>
        </span>
        <div class="min-w-0">
          <span class="font-headline font-bold text-xs sm:text-sm text-slate-800 rule-name-label block truncate">${escapeHtml(name)}</span>
          ${desc ? `<span class="rule-desc-label block text-[11px] text-slate-400 truncate max-w-[180px] sm:max-w-xs">${escapeHtml(desc)}</span>` : `<span class="rule-desc-label hidden block text-[11px] text-slate-400 truncate max-w-[180px] sm:max-w-xs"></span>`}
          <input type="hidden" name="rules[${idx}][name]" class="rule-name-input" value="${escapeHtml(name)}" />
          <input type="hidden" name="rules[${idx}][icon_class]" class="rule-icon-input" value="${escapeHtml(icon)}" />
          <input type="hidden" name="rules[${idx}][is_allowed]" class="rule-allowed-input" value="${allowed}" />
          <input type="hidden" name="rules[${idx}][description]" class="rule-desc-input" value="${escapeHtml(desc)}" />
        </div>
      </div>
      <div class="flex items-center gap-1.5 shrink-0">
        <button 
          type="button" 
          onclick="toggleRuleStatus(this)" 
          class="rule-toggle-btn px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer ${isAllow ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'}">
          <span class="material-symbols-outlined text-sm">${isAllow ? 'check' : 'close'}</span>
          <span class="status-text">${isAllow ? 'Allowed' : 'Not Allowed'}</span>
        </button>
        <button 
          type="button" 
          onclick="openEditRuleModal(this)" 
          class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-400 hover:text-brand-700 transition-colors cursor-pointer"
          title="Edit Rule & Icon">
          <span class="material-symbols-outlined text-base">edit</span>
        </button>
        <button 
          type="button" 
          onclick="removeRuleCard(this)" 
          class="p-1.5 rounded-xl border border-slate-200 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
          title="Remove Rule">
          <span class="material-symbols-outlined text-base">delete</span>
        </button>
      </div>
    `;
    grid.appendChild(card);
    closeRuleModal();
    if (window.adminToast) adminToast.success(`Policy "${name}" added to list.`);
  }

  // Edit & Delete Rule Card Handlers
  let currentEditingRuleCard = null;

  function openEditRuleModal(btn) {
    const card = btn.closest('.rule-card');
    currentEditingRuleCard = card;

    const name = card.querySelector('.rule-name-input').value;
    const icon = card.querySelector('.rule-icon-input').value;
    const allowed = card.querySelector('.rule-allowed-input').value;
    const desc = card.querySelector('.rule-desc-input')?.value || '';

    document.getElementById('edit-rule-name').value = name;
    document.getElementById('edit-rule-status').value = allowed;
    document.getElementById('edit-rule-icon').value = icon;
    document.getElementById('edit-rule-icon-preview').textContent = icon;
    document.getElementById('edit-rule-desc').value = desc;

    document.getElementById('edit-rule-modal').classList.remove('hidden');
  }

  function closeEditRuleModal() {
    document.getElementById('edit-rule-modal').classList.add('hidden');
    currentEditingRuleCard = null;
  }

  function saveEditedRule() {
    if (!currentEditingRuleCard) return;

    const name = document.getElementById('edit-rule-name').value.trim();
    const allowed = document.getElementById('edit-rule-status').value;
    const icon = document.getElementById('edit-rule-icon').value.trim() || 'verified';
    const desc = document.getElementById('edit-rule-desc').value.trim();

    if (!name) {
      if (window.adminToast) adminToast.error('Please enter a policy name');
      return;
    }

    currentEditingRuleCard.querySelector('.rule-name-label').textContent = name;
    currentEditingRuleCard.querySelector('.rule-name-input').value = name;
    currentEditingRuleCard.querySelector('.rule-icon-input').value = icon;
    currentEditingRuleCard.querySelector('.rule-icon-box .material-symbols-outlined').textContent = icon;
    currentEditingRuleCard.querySelector('.rule-allowed-input').value = allowed;

    const descInput = currentEditingRuleCard.querySelector('.rule-desc-input');
    if (descInput) descInput.value = desc;
    const descLabel = currentEditingRuleCard.querySelector('.rule-desc-label');
    if (descLabel) {
      descLabel.textContent = desc;
      if (desc) {
        descLabel.classList.remove('hidden');
      } else {
        descLabel.classList.add('hidden');
      }
    }

    const toggleBtn = currentEditingRuleCard.querySelector('.rule-toggle-btn');
    const isAllow = allowed === '1';
    toggleBtn.className = `rule-toggle-btn px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer ${isAllow ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'}`;
    toggleBtn.querySelector('.material-symbols-outlined').textContent = isAllow ? 'check' : 'close';
    toggleBtn.querySelector('.status-text').textContent = isAllow ? 'Allowed' : 'Not Allowed';

    closeEditRuleModal();
    if (window.adminToast) adminToast.success(`Policy "${name}" updated.`);
  }

  function removeRuleCard(btn) {
    const card = btn.closest('.rule-card');
    const name = card.querySelector('.rule-name-label')?.textContent || 'this policy';
    if (confirm(`Remove policy "${name}" from this property listing?`)) {
      card.remove();
      if (window.adminToast) adminToast.info(`Policy "${name}" removed. Remember to save property updates.`);
    }
  }

  // ── 6. Additional Image Upload Previews ──
  function previewUploadedFiles(input) {
    const container = document.getElementById('upload-preview-container');
    container.innerHTML = '';
    
    if (!input.files || input.files.length === 0) {
      container.classList.add('hidden');
      return;
    }

    container.classList.remove('hidden');

    Array.from(input.files).forEach((file, idx) => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        const div = document.createElement('div');
        div.className = 'w-full h-24 rounded-xl bg-slate-100 overflow-hidden relative border border-slate-200 shadow-xs';
        div.innerHTML = `
          <img src="${e.target.result}" class="w-full h-full object-cover">
          <span class="absolute top-1 left-1 bg-black/60 text-white text-[9px] px-1 rounded font-bold">New #${idx + 1}</span>
        `;
        container.appendChild(div);
      };
      reader.readAsDataURL(file);
    });

    if (window.adminToast) {
      adminToast.info(`${input.files.length} photo(s) selected for upload.`, 'Upload Preview');
    }
  }

  // ── 7. Submit Handler & Validation ──
  function handlePropertySubmit(e) {
    // Sync Quill HTML to hidden input
    const html = quill.root.innerHTML;
    document.getElementById('prop-description-hidden').value = (html === '<p><br></p>') ? '' : html;

    const title = document.getElementById('prop-title').value.trim();
    const type  = document.getElementById('prop-type').value;
    const loc   = document.getElementById('prop-location').value.trim();
    const addr  = document.getElementById('prop-address').value.trim();
    const price = document.getElementById('prop-price').value;

    if (!title || title.length < 3) {
      e.preventDefault();
      if (window.adminToast) adminToast.error('Please enter a valid title with at least 3 characters.', 'Validation');
      document.getElementById('prop-title').focus();
      return false;
    }

    if (!type) {
      e.preventDefault();
      if (window.adminToast) adminToast.error('Please select a property type classification.', 'Validation');
      document.getElementById('prop-type').focus();
      return false;
    }

    if (!loc) {
      e.preventDefault();
      if (window.adminToast) adminToast.error('Please specify a destination city or region.', 'Validation');
      document.getElementById('prop-location').focus();
      return false;
    }

    if (!addr) {
      e.preventDefault();
      if (window.adminToast) adminToast.error('Please specify the physical address.', 'Validation');
      document.getElementById('prop-address').focus();
      return false;
    }

    // Validate pricing depending on booking mode
    if ((currentRentalType === 'complete' || currentRentalType === 'both') && (!price || Number(price) <= 0)) {
      e.preventDefault();
      if (window.adminToast) adminToast.error('Complete property rate must be greater than zero.', 'Validation');
      document.getElementById('prop-price').focus();
      return false;
    }

    if (currentRentalType === 'room_wise' && roomTypesData.length === 0) {
      e.preventDefault();
      if (window.adminToast) adminToast.error('Please configure at least one room type for Room-Wise Booking mode.', 'Validation');
      return false;
    }

    const btn = document.getElementById('submit-property-btn');
    const text = document.getElementById('btn-text');
    if (btn && text) {
      btn.disabled = true;
      btn.classList.add('opacity-80', 'cursor-not-allowed');
      text.textContent = 'Updating Property...';
    }

    if (window.adminToast) {
      adminToast.info('Saving updated specifications and media assets...', 'Saving Changes', 3000);
    }
    return true;
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
  }

  // Initial render
  document.addEventListener('DOMContentLoaded', function() {
    switchBookingMode(currentRentalType);
    renderRoomTypesTable();
  });
</script>

<!-- ── Shared Admin Layout Footer ── -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
