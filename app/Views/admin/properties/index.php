<?php
$pageTitle   = "Manage Properties • Stayora Executive Console";
$currentPage = "properties";
$headerTitle = "Manage Properties";

// ── Shared Admin Layout Header (HTML Head, Sidebar, Top Navigation) ──
require_once __DIR__ . '/../layouts/header.php';

// Helper for image URLs
function resolvePropertyThumb(?string $url): string {
    if (empty($url)) {
        return 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=400&q=80';
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    return asset($url);
}
?>

<!-- ══════════════════════════════════════════════
     1. TOP HEADER & METRIC SUMMARY CARDS
     ══════════════════════════════════════════════ -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
  <div>
    <div class="flex items-center gap-2">
      <span class="p-1.5 rounded-lg bg-brand-50 text-brand-700">
        <span class="material-symbols-outlined text-2xl">villa</span>
      </span>
      <h2 class="font-headline font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
        Property Portfolio Directory
      </h2>
    </div>
    <p class="text-xs sm:text-sm text-slate-500 mt-1">
      Add, edit, inspect, and manage verified luxury farmstays across India.
    </p>
  </div>

  <div class="flex items-center gap-3">
    <a href="<?= url('admin/properties/create') ?>" 
       class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white font-headline font-bold text-xs shadow-lg shadow-brand-700/20 flex items-center gap-2 transition-all cursor-pointer">
      <span class="material-symbols-outlined text-lg">add_circle</span>
      <span>Add New Property</span>
    </a>
  </div>
</div>

<!-- 4 KPI Overview Badges -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Stays</div>
      <div class="font-headline font-black text-2xl text-slate-900 mt-0.5"><?= number_format($kpis['total']) ?></div>
    </div>
    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
      <span class="material-symbols-outlined text-xl">home_work</span>
    </div>
  </div>

  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Active Listings</div>
      <div class="font-headline font-black text-2xl text-emerald-700 mt-0.5"><?= number_format($kpis['active']) ?></div>
    </div>
    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
      <span class="material-symbols-outlined text-xl">check_circle</span>
    </div>
  </div>

  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Pending Review</div>
      <div class="font-headline font-black text-2xl text-amber-700 mt-0.5"><?= number_format($kpis['pending']) ?></div>
    </div>
    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
      <span class="material-symbols-outlined text-xl">hourglass_empty</span>
    </div>
  </div>

  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-brand-600 uppercase tracking-wider">Stayora Certified</div>
      <div class="font-headline font-black text-2xl text-brand-700 mt-0.5"><?= number_format($kpis['verified']) ?></div>
    </div>
    <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-700 flex items-center justify-center">
      <span class="material-symbols-outlined text-xl">verified</span>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     2. FILTER & SEARCH CONTROL BAR
     ══════════════════════════════════════════════ -->
<div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
  <form action="<?= url('admin/properties') ?>" method="GET" class="space-y-4">
    
    <!-- Top Row: Search Input + Submit + Clear -->
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-lg pointer-events-none">search</span>
        <input 
          type="text" 
          name="search" 
          value="<?= htmlspecialchars($filters['search']) ?>" 
          placeholder="Search by property title, city/location, or street address..." 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
      </div>

      <div class="flex items-center gap-2">
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-base">filter_list</span>
          <span>Apply Filters</span>
        </button>

        <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['property_type_id']) || !empty($filters['location']) || $filters['is_verified'] !== ''): ?>
          <a href="<?= url('admin/properties') ?>" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors" title="Clear all filters">
            ✕ Reset
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bottom Row: Filter Dropdowns -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100 text-xs">
      
      <!-- Status Filter -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
        <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="">All Statuses (<?= $kpis['total'] ?>)</option>
          <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active (<?= $kpis['active'] ?>)</option>
          <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending Review (<?= $kpis['pending'] ?>)</option>
          <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected (<?= $kpis['rejected'] ?>)</option>
        </select>
      </div>

      <!-- Property Type Filter -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Property Type</label>
        <select name="property_type_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="">All Property Types</option>
          <?php foreach ($propertyTypes as $pt): ?>
            <option value="<?= $pt['id'] ?>" <?= (string)$filters['property_type_id'] === (string)$pt['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($pt['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Location Filter -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">City / Region</label>
        <select name="location" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="">All Locations</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?= htmlspecialchars($loc) ?>" <?= $filters['location'] === $loc ? 'selected' : '' ?>>
              <?= htmlspecialchars($loc) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Sort By -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sort Order</label>
        <select name="sort" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium focus:bg-white focus:border-brand-500 focus:outline-none">
          <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest Added</option>
          <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
          <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Rate: Low to High</option>
          <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Rate: High to Low</option>
          <option value="title_asc" <?= $filters['sort'] === 'title_asc' ? 'selected' : '' ?>>Title: A to Z</option>
        </select>
      </div>

    </div>

  </form>
</div>

<!-- ══════════════════════════════════════════════
     3. PROPERTIES RESPONSIVE DATA TABLE
     ══════════════════════════════════════════════ -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
  
  <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between">
    <div>
      <h3 class="font-headline font-bold text-base text-slate-900">
        Properties List
      </h3>
      <p class="text-xs text-slate-500 mt-0.5">
        Showing <?= $startItem ?> to <?= $endItem ?> of <?= number_format($totalRecords) ?> total properties
      </p>
    </div>

    <div class="text-xs font-bold text-slate-400">
      Page <?= $page ?> of <?= $totalPages ?>
    </div>
  </div>

  <!-- Desktop Responsive Data Table (Visible on md and up, hidden on mobile) -->
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-left text-xs min-w-[850px]">
      <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
        <tr>
          <th class="py-3.5 px-4 sm:px-6">Property</th>
          <th class="py-3.5 px-4">Location</th>
          <th class="py-3.5 px-4">Pricing</th>
          <th class="py-3.5 px-4">Capacity</th>
          <th class="py-3.5 px-4">Status &amp; Verification</th>
          <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
        <?php if (!empty($properties)): ?>
          <?php foreach ($properties as $prop): ?>
            <?php $encId = encrypt_id($prop['id']); ?>
            <tr id="prop-row-<?= $prop['id'] ?>" data-enc-id="<?= $encId ?>" class="hover:bg-slate-50/80 transition-colors group">
              
              <!-- Property Details -->
              <td class="py-4 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                  <div class="w-14 h-14 rounded-2xl bg-slate-100 overflow-hidden shrink-0 shadow-xs relative border border-slate-200/80">
                    <img 
                      src="<?= resolvePropertyThumb($prop['featured_image'] ?? null) ?>" 
                      alt="<?= htmlspecialchars($prop['title']) ?>" 
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      onerror="this.src='https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=400&q=80';"
                    />
                    <?php if (!empty($prop['image_count']) && $prop['image_count'] > 1): ?>
                      <span class="absolute bottom-1 right-1 bg-black/70 backdrop-blur-xs text-white text-[9px] px-1 rounded font-bold">
                        <?= $prop['image_count'] ?>📸
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="min-w-0 max-w-xs">
                    <a href="<?= url('admin/properties/view?id=' . $encId) ?>" class="font-headline font-bold text-slate-900 text-sm truncate group-hover:text-brand-700 transition-colors block">
                      <?= htmlspecialchars($prop['title']) ?>
                    </a>
                    <div class="flex items-center gap-1.5 mt-0.5">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                        <?= htmlspecialchars($prop['property_type_name'] ?? $prop['category'] ?? 'Farmhouse') ?>
                      </span>
                      <span class="text-slate-400 text-[10px]">#<?= $prop['id'] ?></span>
                      <?php 
                        $rentType = $prop['rental_type'] ?? (!empty($prop['allow_room_booking']) ? 'both' : 'complete');
                        $badgeInfo = match($rentType) {
                          'room_wise' => ['text' => 'Rooms Only', 'class' => 'bg-purple-50 text-purple-700 border border-purple-200'],
                          'both'      => ['text' => 'Dual Mode', 'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
                          default     => ['text' => 'Complete', 'class' => 'bg-blue-50 text-brand-700 border border-blue-200']
                        };
                      ?>
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold <?= $badgeInfo['class'] ?>">
                        <?= $badgeInfo['text'] ?>
                      </span>
                    </div>

                    <?php if (!empty($prop['owner_id']) && !empty($prop['owner_name'])): ?>
                      <div class="mt-1">
                        <a href="<?= url('admin/owners/view/' . $prop['owner_id']) ?>" 
                           class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-600 hover:text-brand-700 bg-slate-50 hover:bg-brand-50 px-2 py-0.5 rounded-md border border-slate-200 transition-colors" 
                           title="Owner: <?= htmlspecialchars($prop['owner_name']) ?><?= !empty($prop['owner_business_name']) ? ' (' . htmlspecialchars($prop['owner_business_name']) . ')' : '' ?>">
                          <span class="material-symbols-outlined text-[13px] text-amber-600">person</span>
                          <span class="truncate max-w-[130px]"><?= htmlspecialchars($prop['owner_name']) ?></span>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <!-- Location -->
              <td class="py-4 px-4">
                <div class="font-bold text-slate-800 flex items-center gap-1">
                  <span class="material-symbols-outlined text-[14px] text-slate-400">location_on</span>
                  <span><?= htmlspecialchars($prop['location'] ?? 'India') ?></span>
                </div>
                <div class="text-[11px] text-slate-400 truncate max-w-[180px] mt-0.5" title="<?= htmlspecialchars($prop['address'] ?? '') ?>">
                  <?= htmlspecialchars($prop['address'] ?? 'Address on file') ?>
                </div>
              </td>

              <!-- Pricing -->
              <td class="py-4 px-4 whitespace-nowrap">
                <div class="font-headline font-black text-slate-900 text-sm">
                  ₹<?= number_format((float)($prop['price'] ?? 0)) ?>
                  <span class="text-[10px] font-normal text-slate-400">/ night</span>
                </div>
                <?php if (!empty($prop['weekend_price']) && (float)$prop['weekend_price'] != (float)$prop['price']): ?>
                  <div class="text-[10px] text-slate-500 font-semibold mt-0.5">
                    Weekend: ₹<?= number_format((float)$prop['weekend_price']) ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Capacity -->
              <td class="py-4 px-4 whitespace-nowrap">
                <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-700">
                  <span class="flex items-center gap-0.5" title="Bedrooms">
                    <span class="material-symbols-outlined text-sm text-slate-400">bed</span>
                    <?= (int)$prop['bedrooms'] ?> Beds
                  </span>
                  <span>•</span>
                  <span class="flex items-center gap-0.5" title="Guests Capacity">
                    <span class="material-symbols-outlined text-sm text-slate-400">group</span>
                    <?= (int)$prop['day_capacity'] ?> Guests
                  </span>
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                  Sleeps <?= (int)$prop['night_capacity'] ?> adults
                </div>
              </td>

              <!-- Status & Verification -->
              <td class="py-4 px-4 whitespace-nowrap">
                <div class="space-y-1">
                  <?php 
                    $st = strtolower((string)($prop['status'] ?? 'pending'));
                    $badgeStyle = match($st) {
                      'active'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                      'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                      default    => 'bg-amber-50 text-amber-700 border-amber-200'
                    };
                  ?>
                  <div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?> capitalize">
                      <span class="w-1.5 h-1.5 rounded-full <?= $st === 'active' ? 'bg-emerald-500' : ($st === 'rejected' ? 'bg-rose-500' : 'bg-amber-500') ?>"></span>
                      <span><?= $st ?></span>
                    </span>
                  </div>

                  <div>
                    <?php if (!empty($prop['is_verified'])): ?>
                      <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-brand-700">
                        <span class="material-symbols-outlined text-[13px]">verified</span>
                        <span>Verified Stay</span>
                      </span>
                    <?php else: ?>
                      <span class="text-[10px] text-slate-400">Unverified</span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <!-- Actions -->
              <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5">
                  
                  <!-- View Details Modal Trigger -->
                  <button 
                    type="button" 
                    onclick="openPropertyModal('<?= $encId ?>')" 
                    class="p-2 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-700 transition-colors cursor-pointer" 
                    title="Inspect complete details">
                    <span class="material-symbols-outlined text-lg">visibility</span>
                  </button>

                  <!-- Edit Property -->
                  <a 
                    href="<?= url('admin/properties/edit?id=' . $encId) ?>" 
                    class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-colors" 
                    title="Edit property">
                    <span class="material-symbols-outlined text-lg">edit</span>
                  </a>

                  <!-- Delete Dialog Trigger -->
                  <button 
                    type="button" 
                    onclick="confirmDeleteProperty('<?= $encId ?>', '<?= htmlspecialchars(addslashes($prop['title'])) ?>')" 
                    class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" 
                    title="Delete property">
                    <span class="material-symbols-outlined text-lg">delete</span>
                  </button>

                </div>
              </td>

            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="py-16 text-center text-slate-400">
              <div class="max-w-xs mx-auto space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                  <span class="material-symbols-outlined text-2xl">search_off</span>
                </div>
                <div class="font-headline font-bold text-slate-700 text-sm">No Properties Found</div>
                <p class="text-xs text-slate-400 leading-relaxed">
                  No properties matched your search or filter settings. Try modifying or clearing your filters.
                </p>
                <a href="<?= url('admin/properties') ?>" class="inline-block px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
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
       MOBILE RESPONSIVE CARD VIEW (Visible on < md)
       ══════════════════════════════════════════════ -->
  <div class="md:hidden p-3.5 sm:p-4 space-y-3.5 bg-slate-50/50">
    <?php if (!empty($properties)): ?>
      <?php foreach ($properties as $prop): ?>
        <?php 
          $encId = encrypt_id($prop['id']);
          $rentType = $prop['rental_type'] ?? (!empty($prop['allow_room_booking']) ? 'both' : 'complete');
          $badgeInfo = match($rentType) {
            'room_wise' => ['text' => 'Rooms Only', 'class' => 'bg-purple-50 text-purple-700 border border-purple-200'],
            'both'      => ['text' => 'Dual Mode', 'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
            default     => ['text' => 'Complete', 'class' => 'bg-blue-50 text-brand-700 border border-blue-200']
          };
          $st = strtolower((string)($prop['status'] ?? 'pending'));
          $badgeStyle = match($st) {
            'active'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            default    => 'bg-amber-50 text-amber-700 border-amber-200'
          };
        ?>
        <div id="prop-card-<?= $prop['id'] ?>" data-enc-id="<?= $encId ?>" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3.5 hover:border-slate-300 transition-all">
          
          <!-- Top Row: Thumbnail + Badges + Status + Title -->
          <div class="flex items-start gap-3">
            
            <!-- Property Thumbnail -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-100 overflow-hidden shrink-0 shadow-xs relative border border-slate-200/80">
              <img 
                src="<?= resolvePropertyThumb($prop['featured_image'] ?? null) ?>" 
                alt="<?= htmlspecialchars($prop['title']) ?>" 
                class="w-full h-full object-cover"
                onerror="this.src='https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=400&q=80';"
              />
              <?php if (!empty($prop['image_count']) && $prop['image_count'] > 1): ?>
                <span class="absolute bottom-1 right-1 bg-black/70 backdrop-blur-xs text-white text-[9px] px-1 rounded font-bold">
                  <?= $prop['image_count'] ?>📸
                </span>
              <?php endif; ?>
            </div>

            <!-- Header Info -->
            <div class="min-w-0 flex-1">
              
              <!-- Badges Row -->
              <div class="flex items-center justify-between gap-1.5 mb-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                    <?= htmlspecialchars($prop['property_type_name'] ?? $prop['category'] ?? 'Farmhouse') ?>
                  </span>
                  <span class="text-slate-400 text-[10px] font-medium">#<?= $prop['id'] ?></span>
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold <?= $badgeInfo['class'] ?>">
                    <?= $badgeInfo['text'] ?>
                  </span>
                </div>

                <!-- Status Pill -->
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?> capitalize shrink-0">
                  <span class="w-1.5 h-1.5 rounded-full <?= $st === 'active' ? 'bg-emerald-500' : ($st === 'rejected' ? 'bg-rose-500' : 'bg-amber-500') ?>"></span>
                  <span><?= $st ?></span>
                </span>
              </div>

              <!-- Property Title -->
              <a href="<?= url('admin/properties/view?id=' . $encId) ?>" class="font-headline font-bold text-slate-900 text-sm hover:text-brand-700 transition-colors line-clamp-1 block leading-snug">
                <?= htmlspecialchars($prop['title']) ?>
              </a>

              <!-- Location & Verification Subtitle -->
              <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-1 flex-wrap">
                <span class="font-semibold text-slate-700 flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-[13px] text-slate-400">location_on</span>
                  <span><?= htmlspecialchars($prop['location'] ?? 'India') ?></span>
                </span>
                <span class="text-slate-300">•</span>
                <?php if (!empty($prop['is_verified'])): ?>
                  <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-brand-700">
                    <span class="material-symbols-outlined text-[13px]">verified</span>
                    <span>Verified</span>
                  </span>
                <?php else: ?>
                  <span class="text-[10px] text-slate-400 font-medium">Unverified</span>
                <?php endif; ?>
              </div>

              <?php if (!empty($prop['owner_id']) && !empty($prop['owner_name'])): ?>
                <div class="mt-1.5">
                  <a href="<?= url('admin/owners/view/' . $prop['owner_id']) ?>" 
                     class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-600 hover:text-brand-700 bg-slate-50 hover:bg-brand-50 px-2 py-0.5 rounded-md border border-slate-200 transition-colors" 
                     title="Owner: <?= htmlspecialchars($prop['owner_name']) ?>">
                    <span class="material-symbols-outlined text-[12px] text-amber-600">person</span>
                    <span class="truncate max-w-[150px]">Owner: <?= htmlspecialchars($prop['owner_name']) ?></span>
                  </a>
                </div>
              <?php endif; ?>

            </div>
          </div>

          <!-- Address line if available -->
          <?php if (!empty($prop['address'])): ?>
            <div class="text-[11px] text-slate-400 truncate flex items-center gap-1 bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-100" title="<?= htmlspecialchars($prop['address']) ?>">
              <span class="material-symbols-outlined text-xs text-slate-400 shrink-0">home_pin</span>
              <span class="truncate"><?= htmlspecialchars($prop['address']) ?></span>
            </div>
          <?php endif; ?>

          <!-- Capacity & Pricing Grid Card -->
          <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100/80 flex items-center justify-between gap-3">
            <!-- Capacity info -->
            <div class="space-y-0.5">
              <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-700">
                <span class="flex items-center gap-0.5" title="Bedrooms">
                  <span class="material-symbols-outlined text-sm text-slate-400">bed</span>
                  <span><?= (int)$prop['bedrooms'] ?> Beds</span>
                </span>
                <span class="text-slate-300">•</span>
                <span class="flex items-center gap-0.5" title="Guests Capacity">
                  <span class="material-symbols-outlined text-sm text-slate-400">group</span>
                  <span><?= (int)$prop['day_capacity'] ?> Guests</span>
                </span>
              </div>
              <div class="text-[10px] text-slate-400">
                Sleeps <?= (int)$prop['night_capacity'] ?> adults
              </div>
            </div>

            <!-- Pricing info -->
            <div class="text-right shrink-0">
              <div class="font-headline font-black text-slate-900 text-sm">
                ₹<?= number_format((float)($prop['price'] ?? 0)) ?>
                <span class="text-[10px] font-normal text-slate-400">/ night</span>
              </div>
              <?php if (!empty($prop['weekend_price']) && (float)$prop['weekend_price'] != (float)$prop['price']): ?>
                <div class="text-[9.5px] text-slate-500 font-semibold mt-0.5">
                  Wknd: ₹<?= number_format((float)$prop['weekend_price']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Bottom Action Buttons: Mobile Touch-Friendly -->
          <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-100">
            <!-- Inspect Modal Button -->
            <button 
              type="button" 
              onclick="openPropertyModal('<?= $encId ?>')" 
              class="py-2 px-2.5 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 font-bold text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer"
              title="Inspect complete details">
              <span class="material-symbols-outlined text-base">visibility</span>
              <span>View</span>
            </button>

            <!-- Edit Property -->
            <a 
              href="<?= url('admin/properties/edit?id=' . $encId) ?>" 
              class="py-2 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-1 transition-colors"
              title="Edit property">
              <span class="material-symbols-outlined text-base">edit</span>
              <span>Edit</span>
            </a>

            <!-- Delete Trigger -->
            <button 
              type="button" 
              onclick="confirmDeleteProperty('<?= $encId ?>', '<?= htmlspecialchars(addslashes($prop['title'])) ?>')" 
              class="py-2 px-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 font-bold text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer"
              title="Delete property">
              <span class="material-symbols-outlined text-base">delete</span>
              <span>Delete</span>
            </button>
          </div>

        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="py-12 px-4 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
        <div class="max-w-xs mx-auto space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">search_off</span>
          </div>
          <div class="font-headline font-bold text-slate-700 text-sm">No Properties Found</div>
          <p class="text-xs text-slate-400 leading-relaxed">
            No properties matched your search or filter settings. Try modifying or clearing your filters.
          </p>
          <a href="<?= url('admin/properties') ?>" class="inline-block px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
            Reset All Filters
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Pagination Bar -->
  <?php if ($totalPages > 1): ?>
    <div class="p-4 sm:p-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="text-xs text-slate-500 font-medium">
        Showing page <span class="font-bold text-slate-900"><?= $page ?></span> of <span class="font-bold text-slate-900"><?= $totalPages ?></span>
      </div>

      <div class="inline-flex items-center gap-1.5 self-start sm:self-center flex-wrap">
        <?php 
          // Build query string helper
          $queryArgs = $filters;
        ?>

        <!-- Previous Page -->
        <?php if ($page > 1): ?>
          <?php $queryArgs['page'] = $page - 1; ?>
          <a href="<?= url('admin/properties?' . http_build_query($queryArgs)) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
            <span>Prev</span>
          </a>
        <?php else: ?>
          <span class="px-3 py-1.5 rounded-lg border border-slate-100 text-slate-300 text-xs font-bold cursor-not-allowed">
            Prev
          </span>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 1 && $p <= $page + 1)): ?>
            <?php $queryArgs['page'] = $p; ?>
            <a href="<?= url('admin/properties?' . http_build_query($queryArgs)) ?>" 
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
          <a href="<?= url('admin/properties?' . http_build_query($queryArgs)) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1">
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

<!-- ══════════════════════════════════════════════
     4. INTERACTIVE PROPERTY DETAILS VIEW MODAL
     ══════════════════════════════════════════════ -->
<div id="property-view-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden my-8 max-h-[90vh] flex flex-col">
    
    <!-- Modal Header -->
    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">villa</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900 leading-tight" id="modal-title">Property Details</h3>
          <p class="text-[11px] text-slate-400" id="modal-subtitle">Stayora Luxury Accommodation</p>
        </div>
      </div>

      <button type="button" onclick="closePropertyModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-xl">close</span>
      </button>
    </div>

    <!-- Modal Scrollable Body -->
    <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
      
      <!-- Primary Photo Carousel / Gallery -->
      <div class="space-y-2">
        <div class="w-full h-56 sm:h-64 rounded-2xl bg-slate-100 overflow-hidden relative border border-slate-200 shadow-inner">
          <img id="modal-featured-img" src="" alt="Property" class="w-full h-full object-cover">
          <div class="absolute bottom-3 left-3 bg-slate-900/80 backdrop-blur-xs text-white text-[11px] px-2.5 py-1 rounded-lg font-bold" id="modal-type-badge">
            Luxury Pool Villa
          </div>
        </div>
        <div id="modal-gallery-thumbs" class="flex items-center gap-2 overflow-x-auto pb-1"></div>
      </div>

      <!-- Pricing & Capacity Matrix -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase">Weekday Rate</div>
          <div class="font-headline font-black text-sm text-slate-900 mt-0.5" id="modal-price">₹0</div>
        </div>
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase">Weekend Rate</div>
          <div class="font-headline font-black text-sm text-slate-900 mt-0.5" id="modal-weekend-price">₹0</div>
        </div>
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase">Bedrooms</div>
          <div class="font-bold text-sm text-slate-900 mt-0.5" id="modal-bedrooms">0 Beds</div>
        </div>
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase">Guests Limit</div>
          <div class="font-bold text-sm text-slate-900 mt-0.5" id="modal-capacity">0 Guests</div>
        </div>
      </div>

      <!-- Address & Map Link -->
      <div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Location &amp; Address</div>
        <div class="font-semibold text-slate-800" id="modal-location">North Goa</div>
        <div class="text-slate-500 mt-0.5" id="modal-address">Address</div>
        <div class="mt-2" id="modal-map-link-container"></div>
      </div>

      <!-- Description -->
      <?php require_once __DIR__ . '/../partials/prose_styles.php'; ?>
      <div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Description</div>
        <div class="stayora-prose bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs max-h-48 overflow-y-auto" id="modal-description">
          Property overview and features.
        </div>
      </div>

      <!-- Contact & Verification -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-100">
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Host Contacts</div>
          <div class="text-slate-700 font-semibold" id="modal-phone">Phone: N/A</div>
          <div class="text-slate-700 font-semibold mt-0.5" id="modal-whatsapp">WhatsApp: N/A</div>
        </div>
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Stayora Verification Status</div>
          <div id="modal-verification-status"></div>
          <div class="text-[11px] text-slate-500 mt-0.5" id="modal-verification-notes"></div>
        </div>
      </div>

    </div>

    <!-- Modal Footer Actions -->
    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
      <button type="button" onclick="closePropertyModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200 text-xs font-bold transition-colors cursor-pointer">
        Close Window
      </button>

      <a id="modal-edit-btn" href="#" class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-colors flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">edit</span>
        <span>Edit Property</span>
      </a>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════
     5. DELETE CONFIRMATION DIALOG MODAL
     ══════════════════════════════════════════════ -->
<div id="delete-confirm-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 text-center space-y-4">
    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center shadow-xs">
      <span class="material-symbols-outlined text-3xl">delete_forever</span>
    </div>
    
    <div>
      <h3 class="font-headline font-bold text-lg text-slate-900">Delete Property Listing?</h3>
      <p class="text-xs text-slate-500 mt-1 leading-relaxed">
        Are you sure you want to remove <strong id="delete-property-title" class="text-slate-900">this property</strong>? All associated gallery images and records will be deleted from the system.
      </p>
    </div>

    <form action="<?= url('admin/properties/delete') ?>" method="POST" class="pt-2 flex items-center gap-2 justify-center">
      <input type="hidden" name="id" id="delete-property-id" value="">
      
      <button type="button" onclick="closeDeleteModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
        Cancel
      </button>

      <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors shadow-md shadow-rose-600/20 cursor-pointer">
        Yes, Delete
      </button>
    </form>
  </div>
</div>

<!-- Page Specific JavaScript for Property Modals -->
<script>
  // ── Open Property Details Modal via AJAX ──
  function openPropertyModal(id) {
    if (window.adminToast) {
      adminToast.info('Loading property specifications...', 'Retrieving Data', 1500);
    }

    fetch(`<?= url('admin/properties/view') ?>?id=${encodeURIComponent(id)}&format=json`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.data) {
        if (window.adminToast) adminToast.error('Could not load property details.');
        return;
      }
      const p = res.data;

      document.getElementById('modal-title').textContent = p.title || 'Untitled Property';
      document.getElementById('modal-subtitle').textContent = `${p.property_type_name || p.category || 'Farmhouse'} • Ref #${p.id}`;
      document.getElementById('modal-type-badge').textContent = p.property_type_name || p.category || 'Luxury Farmstay';
      document.getElementById('modal-price').textContent = `₹${Number(p.price || 0).toLocaleString()}`;
      document.getElementById('modal-weekend-price').textContent = `₹${Number(p.weekend_price || p.price || 0).toLocaleString()}`;
      document.getElementById('modal-bedrooms').textContent = `${p.bedrooms || 1} Bedrooms`;
      document.getElementById('modal-capacity').textContent = `${p.day_capacity || 10} Guests`;
      document.getElementById('modal-location').textContent = p.location || 'India';
      document.getElementById('modal-address').textContent = p.address || 'Physical address on file';
      document.getElementById('modal-description').innerHTML = p.description || '<span class="text-slate-400 italic">No description provided for this listing.</span>';
      document.getElementById('modal-phone').textContent = `Phone: ${p.contact_phone || 'N/A'}`;
      document.getElementById('modal-whatsapp').textContent = `WhatsApp: ${p.whatsapp_number || 'N/A'}`;
      document.getElementById('modal-edit-btn').href = `<?= url('admin/properties/edit') ?>?id=${p.encrypted_id || encodeURIComponent(id)}`;

      // Verification Badge
      const verContainer = document.getElementById('modal-verification-status');
      if (Number(p.is_verified) === 1) {
        verContainer.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="material-symbols-outlined text-[13px]">verified</span> Verified & Certified
        </span>`;
      } else {
        verContainer.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
          Pending Verification
        </span>`;
      }
      document.getElementById('modal-verification-notes').textContent = p.verification_notes || '';

      // Google Map Link
      const mapContainer = document.getElementById('modal-map-link-container');
      if (p.google_map_link) {
        mapContainer.innerHTML = `<a href="${p.google_map_link}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-700 hover:underline">
          <span class="material-symbols-outlined text-sm">map</span>
          <span>View on Google Maps</span>
        </a>`;
      } else {
        mapContainer.innerHTML = '';
      }

      // Photos Gallery
      const featuredImg = document.getElementById('modal-featured-img');
      const thumbsContainer = document.getElementById('modal-gallery-thumbs');
      thumbsContainer.innerHTML = '';

      const images = p.images || [];
      const defaultImg = 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80';
      
      let primaryUrl = defaultImg;
      if (images.length > 0) {
        primaryUrl = resolveImgUrl(images[0].image_url);
        images.forEach(img => {
          const thumbUrl = resolveImgUrl(img.image_url);
          const thumbBtn = document.createElement('button');
          thumbBtn.type = 'button';
          thumbBtn.className = 'w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200 focus:ring-2 focus:ring-brand-500 cursor-pointer';
          thumbBtn.innerHTML = `<img src="${thumbUrl}" class="w-full h-full object-cover">`;
          thumbBtn.onclick = () => { featuredImg.src = thumbUrl; };
          thumbsContainer.appendChild(thumbBtn);
        });
      }
      featuredImg.src = primaryUrl;

      // Show Modal
      document.getElementById('property-view-modal').classList.remove('hidden');
    })
    .catch(() => {
      if (window.adminToast) adminToast.error('Network error loading property specifications.');
    });
  }

  function resolveImgUrl(url) {
    if (!url) return '';
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    return `<?= asset('') ?>${url}`;
  }

  function closePropertyModal() {
    document.getElementById('property-view-modal').classList.add('hidden');
  }

  // ── Delete Confirmation Dialog ──
  function confirmDeleteProperty(id, title) {
    document.getElementById('delete-property-id').value = id;
    document.getElementById('delete-property-title').textContent = title;
    document.getElementById('delete-confirm-modal').classList.remove('hidden');
  }

  function closeDeleteModal() {
    document.getElementById('delete-confirm-modal').classList.add('hidden');
  }
</script>

<!-- ── Shared Admin Layout Footer ── -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
