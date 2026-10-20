<?php
$pageTitle   = "Amenities & Rules Management • Stayora Executive Console";
$currentPage = "amenities_rules";
$headerTitle = "Amenities & Rules Management";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../partials/icon_picker_modal.php';

$amenities           = $amenities ?? [];
$rulePresets         = $rulePresets ?? [];
$totalAmenities      = $totalAmenities ?? count($amenities);
$totalRules          = $totalRules ?? count($rulePresets);
$totalAmenityUses    = $totalAmenityUses ?? 0;
$totalRuleUses       = $totalRuleUses ?? 0;
$amenitiesByCategory = $amenitiesByCategory ?? [];
$activeTab           = $activeTab ?? 'amenities';

// Category color mappings
$catStyles = [
    'general'    => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500'],
    'bedroom'    => ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'dot' => 'bg-indigo-500'],
    'kitchen'    => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
    'dining'     => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'dot' => 'bg-orange-500'],
    'leisure'    => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
    'wellness'   => ['bg' => 'bg-teal-50 text-teal-700 border-teal-200', 'dot' => 'bg-teal-500'],
    'technology' => ['bg' => 'bg-violet-50 text-violet-700 border-violet-200', 'dot' => 'bg-violet-500'],
    'outdoor'    => ['bg' => 'bg-lime-50 text-lime-800 border-lime-200', 'dot' => 'bg-lime-600'],
];
?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER
     ═══════════════════════════════════════════════════════════════════════════ -->
<main class="flex-1 overflow-y-auto space-y-6 mt-0 bg-slate-50/50">

  <!-- Breadcrumb & Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1.5" aria-label="Breadcrumb">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-slate-600 transition-colors">Admin</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-brand-700 font-bold">Amenities &amp; Rules</span>
      </nav>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-700/20">
          <span class="material-symbols-outlined text-xl">tune</span>
        </div>
        <div>
          <h1 class="font-headline font-bold text-xl sm:text-2xl text-slate-900 tracking-tight leading-none">
            Amenities &amp; Property Rules
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Centrally manage property features, convenience amenities, and standard guest stay policies.
          </p>
        </div>
      </div>
    </div>

    <!-- Right Action Button (Dynamically toggles based on active tab) -->
    <div class="flex items-center gap-2.5">
      <button 
        type="button" 
        id="btn-header-add"
        onclick="handleHeaderAddClick()" 
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-800 hover:to-brand-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-brand-700/25 transition-all cursor-pointer">
        <span class="material-symbols-outlined text-lg">add</span>
        <span id="btn-header-add-text">Add New Amenity</span>
      </button>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════════════════════════
       EXECUTIVE KPI METRIC CARDS
       ═══════════════════════════════════════════════════════════════════════════ -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- Total Amenities -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Amenities</p>
        <p class="font-headline font-black text-2xl text-slate-900 mt-1" id="kpi-amenities-count"><?= $totalAmenities ?></p>
        <p class="text-[11px] text-slate-500 mt-0.5"><?= count($amenitiesByCategory) ?> distinct categories</p>
      </div>
      <div class="w-11 h-11 rounded-xl bg-blue-50 text-brand-700 border border-blue-100 flex items-center justify-center">
        <span class="material-symbols-outlined text-2xl">checklist</span>
      </div>
    </div>

    <!-- Total Rule Presets -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Policy Rules</p>
        <p class="font-headline font-black text-2xl text-slate-900 mt-1" id="kpi-rules-count"><?= $totalRules ?></p>
        <p class="text-[11px] text-slate-500 mt-0.5">Master stay templates</p>
      </div>
      <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center justify-center">
        <span class="material-symbols-outlined text-2xl">gavel</span>
      </div>
    </div>

    <!-- Active Amenity Links -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Amenity Links</p>
        <p class="font-headline font-black text-2xl text-emerald-600 mt-1"><?= $totalAmenityUses ?></p>
        <p class="text-[11px] text-slate-500 mt-0.5">Active farmhouse assignments</p>
      </div>
      <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
        <span class="material-symbols-outlined text-2xl">sync_alt</span>
      </div>
    </div>

    <!-- Visual Icon Library -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Icon System</p>
        <p class="font-headline font-black text-2xl text-slate-900 mt-1">130+</p>
        <p class="text-[11px] text-slate-500 mt-0.5">Material Symbols Outlined</p>
      </div>
      <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center">
        <span class="material-symbols-outlined text-2xl">category</span>
      </div>
    </div>

  </div>

  <!-- ═══════════════════════════════════════════════════════════════════════════
       DUAL TAB NAVIGATION CONTROLLER
       ═══════════════════════════════════════════════════════════════════════════ -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    
    <!-- Tab Bar -->
    <div class="flex items-center justify-between border-b border-slate-200 px-4 sm:px-6 pt-3 bg-slate-50/60">
      <div class="flex items-center gap-2 sm:gap-4 -mb-px" role="tablist">
        
        <!-- Tab 1: Amenities -->
        <button 
          type="button" 
          id="tab-btn-amenities"
          onclick="switchTab('amenities')" 
          role="tab"
          aria-selected="<?= $activeTab === 'amenities' ? 'true' : 'false' ?>"
          class="tab-nav-btn pb-3 px-3 text-xs sm:text-sm font-bold border-b-2 flex items-center gap-2 transition-all cursor-pointer <?= $activeTab === 'amenities' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
          <span class="material-symbols-outlined text-lg">checklist</span>
          <span>Amenities Management</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100/70 text-brand-800" id="badge-tab-amenities-count">
            <?= $totalAmenities ?>
          </span>
        </button>

        <!-- Tab 2: Property Rules -->
        <button 
          type="button" 
          id="tab-btn-rules"
          onclick="switchTab('rules')" 
          role="tab"
          aria-selected="<?= $activeTab === 'rules' ? 'true' : 'false' ?>"
          class="tab-nav-btn pb-3 px-3 text-xs sm:text-sm font-bold border-b-2 flex items-center gap-2 transition-all cursor-pointer <?= $activeTab === 'rules' ? 'border-purple-600 text-purple-700' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
          <span class="material-symbols-outlined text-lg">gavel</span>
          <span>Property Rules Management</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100/70 text-purple-800" id="badge-tab-rules-count">
            <?= $totalRules ?>
          </span>
        </button>

      </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════════
         PANEL 1: AMENITIES MANAGEMENT
         ═════════════════════════════════════════════════════════════════════════ -->
    <div id="tab-panel-amenities" class="<?= $activeTab === 'amenities' ? '' : 'hidden' ?> p-4 sm:p-6 space-y-5">
      
      <!-- Filter Bar: Search + Category Pills -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
        
        <!-- Live Search Input -->
        <div class="relative w-full lg:w-80">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
          <input 
            type="text" 
            id="amenity-search-input" 
            oninput="filterAmenities()" 
            placeholder="Search amenities (e.g. pool, wifi, ac)..." 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
          <button 
            type="button" 
            id="amenity-search-clear" 
            onclick="clearAmenitySearch()" 
            class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer">
            <span class="material-symbols-outlined text-base">cancel</span>
          </button>
        </div>

        <!-- Category Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar" id="amenity-category-pills">
          <button 
            type="button" 
            data-cat="all" 
            onclick="setAmenityCategory('all')" 
            class="amenity-cat-pill px-3 py-1.5 rounded-full text-xs font-bold transition-all bg-brand-700 text-white shadow-2xs cursor-pointer">
            All Categories (<span id="count-cat-all"><?= $totalAmenities ?></span>)
          </button>
          <?php 
          $allCats = ['general', 'bedroom', 'kitchen', 'leisure', 'wellness', 'technology', 'dining', 'outdoor'];
          foreach ($allCats as $c): 
            $cCount = count($amenitiesByCategory[$c] ?? []);
          ?>
            <button 
              type="button" 
              data-cat="<?= $c ?>" 
              onclick="setAmenityCategory('<?= $c ?>')" 
              class="amenity-cat-pill px-3 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
              <?= ucfirst($c) ?> (<?= $cCount ?>)
            </button>
          <?php endforeach; ?>
        </div>

      </div>

      <!-- Amenities Interactive Grid -->
      <div id="amenities-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
        <?php foreach ($amenities as $am): 
          $c = strtolower($am['category'] ?? 'general');
          $cStyle = $catStyles[$c] ?? $catStyles['general'];
          $uses = (int)($am['usage_count'] ?? 0);
          $icon = !empty($am['icon_class']) ? $am['icon_class'] : 'check_circle';
        ?>
          <div 
            class="amenity-card bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-brand-300 hover:shadow-md transition-all flex flex-col justify-between group"
            id="amenity-card-<?= $am['id'] ?>"
            data-id="<?= $am['id'] ?>"
            data-name="<?= htmlspecialchars(strtolower($am['name'])) ?>"
            data-category="<?= htmlspecialchars($c) ?>"
            data-icon="<?= htmlspecialchars($icon) ?>"
            data-uses="<?= $uses ?>">
            
            <div>
              <div class="flex items-start justify-between gap-2">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-brand-700 group-hover:bg-brand-50 group-hover:border-brand-200 border border-slate-200 flex items-center justify-center shrink-0 transition-all">
                  <span class="material-symbols-outlined text-xl amenity-icon-glyph"><?= htmlspecialchars($icon) ?></span>
                </div>
                <div class="flex items-center gap-1">
                  <!-- Edit Button -->
                  <button 
                    type="button" 
                    onclick="openEditAmenityModal(<?= $am['id'] ?>)" 
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-brand-700 hover:bg-brand-50 transition-colors flex items-center justify-center cursor-pointer" 
                    title="Edit Amenity">
                    <span class="material-symbols-outlined text-[16px]">edit</span>
                  </button>
                  <!-- Delete Button -->
                  <button 
                    type="button" 
                    onclick="openDeleteAmenityModal(<?= $am['id'] ?>)" 
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors flex items-center justify-center cursor-pointer" 
                    title="Delete Amenity">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                  </button>
                </div>
              </div>

              <div class="mt-2.5">
                <h4 class="font-headline font-bold text-xs sm:text-sm text-slate-900 amenity-name-text truncate" title="<?= htmlspecialchars($am['name']) ?>">
                  <?= htmlspecialchars($am['name']) ?>
                </h4>
                <div class="mt-1 flex items-center gap-2">
                  <span class="amenity-category-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border <?= $cStyle['bg'] ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $cStyle['dot'] ?>"></span>
                    <span><?= ucfirst($c) ?></span>
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
              <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">villa</span>
                <span class="amenity-usage-text"><?= $uses > 0 ? "Equipped in {$uses} " . ($uses === 1 ? 'farmhouse' : 'farmhouses') : "Not yet assigned" ?></span>
              </span>
              <span class="text-[10px] font-mono font-semibold text-slate-300">#<?= $am['id'] ?></span>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- Empty Search State -->
      <div id="amenities-empty-state" class="hidden p-10 text-center rounded-2xl border-2 border-dashed border-slate-200">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
          <span class="material-symbols-outlined text-2xl">search_off</span>
        </div>
        <h4 class="font-headline font-bold text-sm text-slate-800">No amenities found</h4>
        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
          No amenities matched your search or category filter. Try clearing filters or create a new amenity.
        </p>
        <button 
          type="button" 
          onclick="openAddAmenityModal()" 
          class="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-700 text-white text-xs font-bold shadow-2xs hover:bg-brand-800 transition-all cursor-pointer">
          <span class="material-symbols-outlined text-sm">add</span>
          <span>Add Amenity</span>
        </button>
      </div>

    </div>

    <!-- ═════════════════════════════════════════════════════════════════════════
         PANEL 2: PROPERTY RULES MANAGEMENT
         ═════════════════════════════════════════════════════════════════════════ -->
    <div id="tab-panel-rules" class="<?= $activeTab === 'rules' ? '' : 'hidden' ?> p-4 sm:p-6 space-y-5">
      
      <!-- Filter Bar: Search Input -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
        
        <!-- Live Search Input -->
        <div class="relative w-full sm:w-80">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
          <input 
            type="text" 
            id="rule-search-input" 
            oninput="filterRules()" 
            placeholder="Search policies (e.g. pets, smoking, music)..." 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-xs sm:text-sm font-medium focus:bg-white focus:border-purple-500 focus:outline-none transition-all"
          />
          <button 
            type="button" 
            id="rule-search-clear" 
            onclick="clearRuleSearch()" 
            class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer">
            <span class="material-symbols-outlined text-base">cancel</span>
          </button>
        </div>

        <div class="text-xs text-slate-400 font-medium">
          Showing <span id="rules-shown-count" class="font-bold text-slate-700"><?= count($rulePresets) ?></span> standard rule templates
        </div>

      </div>

      <!-- Property Rules Grid -->
      <div id="rules-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        <?php foreach ($rulePresets as $r): 
          $icon = !empty($r['icon_class']) ? $r['icon_class'] : 'gavel';
          $uses = (int)($r['usage_count'] ?? 0);
          $desc = !empty($r['description']) ? $r['description'] : '';
        ?>
          <div 
            class="rule-card bg-white p-4 rounded-2xl border border-slate-200 hover:border-purple-300 hover:shadow-md transition-all flex flex-col justify-between group"
            id="rule-card-<?= $r['id'] ?>"
            data-id="<?= $r['id'] ?>"
            data-name="<?= htmlspecialchars(strtolower($r['rule_name'])) ?>"
            data-desc="<?= htmlspecialchars(strtolower($desc)) ?>"
            data-icon="<?= htmlspecialchars($icon) ?>"
            data-uses="<?= $uses ?>">
            
            <div>
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl rule-icon-glyph"><?= htmlspecialchars($icon) ?></span>
                  </div>
                  <div class="min-w-0">
                    <h4 class="font-headline font-bold text-xs sm:text-sm text-slate-900 rule-name-text truncate" title="<?= htmlspecialchars($r['rule_name']) ?>">
                      <?= htmlspecialchars($r['rule_name']) ?>
                    </h4>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400 mt-0.5">
                      <span class="material-symbols-outlined text-xs">verified</span>
                      <span>Master Policy Preset</span>
                    </span>
                  </div>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                  <!-- Edit Button -->
                  <button 
                    type="button" 
                    onclick="openEditRuleModal(<?= $r['id'] ?>)" 
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-purple-700 hover:bg-purple-50 transition-colors flex items-center justify-center cursor-pointer" 
                    title="Edit Rule Preset">
                    <span class="material-symbols-outlined text-[16px]">edit</span>
                  </button>
                  <!-- Delete Button -->
                  <button 
                    type="button" 
                    onclick="openDeleteRuleModal(<?= $r['id'] ?>)" 
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors flex items-center justify-center cursor-pointer" 
                    title="Delete Rule Preset">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                  </button>
                </div>
              </div>

              <!-- Standard Policy Guidance Note -->
              <div class="mt-3">
                <p class="text-xs text-slate-500 rule-desc-text line-clamp-2 <?= empty($desc) ? 'italic text-slate-400' : '' ?>">
                  <?= !empty($desc) ? htmlspecialchars($desc) : 'No default policy guidance specified.' ?>
                </p>
              </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
              <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">villa</span>
                <span class="rule-usage-text"><?= $uses > 0 ? "Used by {$uses} " . ($uses === 1 ? 'farmhouse' : 'farmhouses') : "Available for assignment" ?></span>
              </span>
              <span class="text-[10px] font-mono font-semibold text-slate-300">#<?= $r['id'] ?></span>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- Empty Rules Search State -->
      <div id="rules-empty-state" class="hidden p-10 text-center rounded-2xl border-2 border-dashed border-slate-200">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
          <span class="material-symbols-outlined text-2xl">search_off</span>
        </div>
        <h4 class="font-headline font-bold text-sm text-slate-800">No policy rules found</h4>
        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
          No rules matched your search query. Try clearing the search or add a new policy rule.
        </p>
        <button 
          type="button" 
          onclick="openAddRuleModal()" 
          class="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-700 text-white text-xs font-bold shadow-2xs hover:bg-purple-800 transition-all cursor-pointer">
          <span class="material-symbols-outlined text-sm">add</span>
          <span>Add Rule Preset</span>
        </button>
      </div>

    </div>

  </div>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MODAL 1: ADD / EDIT AMENITY MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div id="amenity-form-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    
    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 flex items-center justify-center">
          <span class="material-symbols-outlined text-xl" id="amenity-modal-badge-icon">checklist</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900" id="amenity-modal-title">Add New Amenity</h3>
          <p class="text-xs text-slate-400" id="amenity-modal-subtitle">Configure master catalog amenity</p>
        </div>
      </div>
      <button type="button" onclick="closeAmenityModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <span class="material-symbols-outlined text-xl">close</span>
      </button>
    </div>

    <form id="amenity-form" onsubmit="handleAmenityFormSubmit(event)" class="p-5 space-y-4">
      <input type="hidden" name="id" id="amenity-id-input" value="0" />

      <!-- Amenity Name -->
      <div>
        <label for="amenity-name-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Amenity Name <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="name" 
          id="amenity-name-input" 
          required 
          maxlength="50"
          placeholder="e.g. Infinity Swimming Pool, High-Speed Wi-Fi" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
        />
        <p class="text-[11px] text-slate-400 mt-1">Must be unique across the master amenity catalog.</p>
      </div>

      <!-- Amenity Category -->
      <div>
        <label for="amenity-category-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Category <span class="text-rose-500">*</span>
        </label>
        <select 
          name="category" 
          id="amenity-category-input" 
          required
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
          <option value="general">General (Standard Amenities)</option>
          <option value="bedroom">Bedroom &amp; Sleeping</option>
          <option value="kitchen">Kitchen &amp; Cooking</option>
          <option value="dining">Dining &amp; Bar</option>
          <option value="leisure">Leisure, Sports &amp; Pool</option>
          <option value="wellness">Wellness, Spa &amp; Lawn</option>
          <option value="technology">Technology &amp; Entertainment</option>
          <option value="outdoor">Outdoor &amp; Scenic Views</option>
        </select>
      </div>

      <!-- Icon Selection -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Visual Icon <span class="text-rose-500">*</span>
        </label>
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-200 text-brand-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl" id="amenity-form-icon-preview">check_circle</span>
          </div>
          <div class="flex-1">
            <input type="hidden" name="icon_class" id="amenity-icon-class-input" value="check_circle" />
            <button 
              type="button" 
              onclick="openAmenityIconPicker()" 
              class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 hover:border-brand-300 bg-slate-50 hover:bg-white text-xs font-bold text-slate-700 flex items-center justify-between transition-all cursor-pointer">
              <span class="flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-brand-700">palette</span>
                <span id="amenity-form-icon-label">Browse Visual Icons</span>
              </span>
              <span class="text-[10px] font-mono font-bold text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-200" id="amenity-form-icon-code">check_circle</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeAmenityModal()" 
          class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
          Cancel
        </button>
        <button 
          type="submit" 
          id="btn-amenity-submit"
          class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md shadow-brand-700/20 transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-sm" id="btn-amenity-submit-icon">save</span>
          <span id="btn-amenity-submit-text">Save Amenity</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MODAL 2: ADD / EDIT RULE PRESET MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div id="rule-form-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    
    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center">
          <span class="material-symbols-outlined text-xl" id="rule-modal-badge-icon">gavel</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900" id="rule-modal-title">Add Rule Preset</h3>
          <p class="text-xs text-slate-400" id="rule-modal-subtitle">Configure master policy template</p>
        </div>
      </div>
      <button type="button" onclick="closeRuleModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <span class="material-symbols-outlined text-xl">close</span>
      </button>
    </div>

    <form id="rule-form" onsubmit="handleRuleFormSubmit(event)" class="p-5 space-y-4">
      <input type="hidden" name="id" id="rule-id-input" value="0" />

      <!-- Rule Name -->
      <div>
        <label for="rule-name-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Rule / Policy Title <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="rule_name" 
          id="rule-name-input" 
          required 
          maxlength="100"
          placeholder="e.g. Pets Allowed, Quiet Hours (10 PM), Outside Food" 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs sm:text-sm font-semibold focus:bg-white focus:border-purple-500 focus:outline-none transition-all"
        />
        <p class="text-[11px] text-slate-400 mt-1">Short, distinct policy headline.</p>
      </div>

      <!-- Icon Selection -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Visual Icon <span class="text-rose-500">*</span>
        </label>
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl" id="rule-form-icon-preview">gavel</span>
          </div>
          <div class="flex-1">
            <input type="hidden" name="icon_class" id="rule-icon-class-input" value="gavel" />
            <button 
              type="button" 
              onclick="openRuleIconPicker()" 
              class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 hover:border-purple-300 bg-slate-50 hover:bg-white text-xs font-bold text-slate-700 flex items-center justify-between transition-all cursor-pointer">
              <span class="flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-purple-700">palette</span>
                <span id="rule-form-icon-label">Browse Visual Icons</span>
              </span>
              <span class="text-[10px] font-mono font-bold text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-200" id="rule-form-icon-code">gavel</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Default Policy Description -->
      <div>
        <label for="rule-description-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Standard Policy Guidance / Note
        </label>
        <textarea 
          name="description" 
          id="rule-description-input" 
          rows="3" 
          maxlength="255"
          placeholder="e.g. Valid government-issued photo ID is mandatory for all adult guests upon arrival."
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-purple-500 focus:outline-none transition-all"></textarea>
        <p class="text-[11px] text-slate-400 mt-1">Optional standard guidance text that autofills when this rule is selected on a property.</p>
      </div>

      <!-- Action Buttons -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeRuleModal()" 
          class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
          Cancel
        </button>
        <button 
          type="submit" 
          id="btn-rule-submit"
          class="px-5 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold shadow-md shadow-purple-700/20 transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-sm" id="btn-rule-submit-icon">save</span>
          <span id="btn-rule-submit-text">Save Rule Preset</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MODAL 3: SAFE DELETION CONFIRMATION MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div id="delete-confirm-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    <div class="p-6 text-center space-y-3">
      <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 mx-auto flex items-center justify-center shadow-xs">
        <span class="material-symbols-outlined text-3xl">warning</span>
      </div>
      <h3 class="font-headline font-bold text-base text-slate-900" id="delete-modal-title">Confirm Deletion</h3>
      <p class="text-xs text-slate-500" id="delete-modal-message">
        Are you sure you want to permanently delete this item?
      </p>

      <!-- Safeguard Usage Alert Box -->
      <div id="delete-modal-usage-warning" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-left flex items-start gap-2.5 text-xs text-amber-800">
        <span class="material-symbols-outlined text-base text-amber-600 shrink-0 mt-0.5">shield</span>
        <div id="delete-modal-usage-text">
          Notice: This record is currently assigned to properties.
        </div>
      </div>
    </div>

    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
      <button 
        type="button" 
        onclick="closeDeleteModal()" 
        class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer">
        Cancel
      </button>
      <button 
        type="button" 
        id="btn-confirm-delete"
        onclick="executeDelete()" 
        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/25 transition-all flex items-center gap-1.5 cursor-pointer">
        <span class="material-symbols-outlined text-sm">delete_forever</span>
        <span>Delete Permanently</span>
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     FLOATING TOAST NOTIFICATION CONTAINER
     ═══════════════════════════════════════════════════════════════════════════ -->
<div id="toast-container" class="fixed bottom-5 right-5 z-[80] space-y-2 pointer-events-none"></div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MANAGEMENT SCRIPT
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
  // ── Global State ──
  let activeTabState = '<?= $activeTab ?>';
  let activeAmenityCat = 'all';
  let deleteTarget = { type: null, id: null };

  // ── Toast Notification Helper ──
  function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-xl text-xs sm:text-sm font-bold border transition-all transform translate-y-4 opacity-0 ${
      type === 'success' 
        ? 'bg-slate-900 text-white border-slate-800' 
        : type === 'error'
        ? 'bg-rose-600 text-white border-rose-500'
        : 'bg-amber-600 text-white border-amber-500'
    }`;
    const icon = type === 'success' ? 'check_circle' : (type === 'error' ? 'error' : 'warning');
    toast.innerHTML = `
      <span class="material-symbols-outlined text-lg">${icon}</span>
      <span>${message}</span>
    `;
    container.appendChild(toast);
    
    setTimeout(() => {
      toast.classList.remove('translate-y-4', 'opacity-0');
    }, 10);

    setTimeout(() => {
      toast.classList.add('opacity-0', 'translate-y-2');
      setTimeout(() => toast.remove(), 250);
    }, 4000);
  }

  // ── Tab Switching Logic ──
  function switchTab(tab) {
    activeTabState = tab;
    const btnAmenities = document.getElementById('tab-btn-amenities');
    const btnRules = document.getElementById('tab-btn-rules');
    const panelAmenities = document.getElementById('tab-panel-amenities');
    const panelRules = document.getElementById('tab-panel-rules');
    const headerAddText = document.getElementById('btn-header-add-text');
    const headerAddBtn = document.getElementById('btn-header-add');

    if (tab === 'amenities') {
      btnAmenities.classList.add('border-brand-600', 'text-brand-700');
      btnAmenities.classList.remove('border-transparent', 'text-slate-500');
      btnRules.classList.remove('border-purple-600', 'text-purple-700');
      btnRules.classList.add('border-transparent', 'text-slate-500');

      panelAmenities.classList.remove('hidden');
      panelRules.classList.add('hidden');

      headerAddText.textContent = 'Add New Amenity';
      headerAddBtn.className = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-800 hover:to-brand-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-brand-700/25 transition-all cursor-pointer';
    } else {
      btnRules.classList.add('border-purple-600', 'text-purple-700');
      btnRules.classList.remove('border-transparent', 'text-slate-500');
      btnAmenities.classList.remove('border-brand-600', 'text-brand-700');
      btnAmenities.classList.add('border-transparent', 'text-slate-500');

      panelRules.classList.remove('hidden');
      panelAmenities.classList.add('hidden');

      headerAddText.textContent = 'Add Rule Preset';
      headerAddBtn.className = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-700 to-purple-600 hover:from-purple-800 hover:to-purple-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-purple-700/25 transition-all cursor-pointer';
    }

    // Update URL history without page reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
  }

  function handleHeaderAddClick() {
    if (activeTabState === 'amenities') {
      openAddAmenityModal();
    } else {
      openAddRuleModal();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ── AMENITIES MANAGEMENT CONTROLLER
  // ═══════════════════════════════════════════════════════════════════════════

  function filterAmenities() {
    const query = document.getElementById('amenity-search-input').value.toLowerCase().trim();
    const clearBtn = document.getElementById('amenity-search-clear');
    clearBtn.classList.toggle('hidden', query === '');

    const cards = document.querySelectorAll('#amenities-grid .amenity-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const name = card.dataset.name || '';
      const cat = card.dataset.category || '';
      
      const matchesSearch = query === '' || name.includes(query) || cat.includes(query);
      const matchesCat = activeAmenityCat === 'all' || cat === activeAmenityCat;

      if (matchesSearch && matchesCat) {
        card.classList.remove('hidden');
        visibleCount++;
      } else {
        card.classList.add('hidden');
      }
    });

    const emptyState = document.getElementById('amenities-empty-state');
    emptyState.classList.toggle('hidden', visibleCount > 0);
  }

  function clearAmenitySearch() {
    document.getElementById('amenity-search-input').value = '';
    filterAmenities();
  }

  function setAmenityCategory(cat) {
    activeAmenityCat = cat;
    document.querySelectorAll('.amenity-cat-pill').forEach(pill => {
      if (pill.dataset.cat === cat) {
        pill.className = 'amenity-cat-pill px-3 py-1.5 rounded-full text-xs font-bold transition-all bg-brand-700 text-white shadow-2xs cursor-pointer';
      } else {
        pill.className = 'amenity-cat-pill px-3 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer';
      }
    });
    filterAmenities();
  }

  // ── Open Amenity Modals ──
  function openAddAmenityModal() {
    document.getElementById('amenity-form').reset();
    document.getElementById('amenity-id-input').value = '0';
    document.getElementById('amenity-modal-title').textContent = 'Add New Amenity';
    document.getElementById('amenity-modal-subtitle').textContent = 'Create a master catalog amenity available across all properties';
    document.getElementById('amenity-icon-class-input').value = 'check_circle';
    document.getElementById('amenity-form-icon-preview').textContent = 'check_circle';
    document.getElementById('amenity-form-icon-code').textContent = 'check_circle';
    document.getElementById('btn-amenity-submit-text').textContent = 'Save Amenity';

    document.getElementById('amenity-form-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('amenity-name-input').focus(), 100);
  }

  function openEditAmenityModal(id) {
    const card = document.getElementById(`amenity-card-${id}`);
    if (!card) return;

    fetch(`<?= url('admin/amenities-rules/amenity/get') ?>?id=${id}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !data.amenity) {
          showToast(data.message || 'Could not fetch amenity details', 'error');
          return;
        }
        const am = data.amenity;
        document.getElementById('amenity-id-input').value = am.id;
        document.getElementById('amenity-name-input').value = am.name;
        document.getElementById('amenity-category-input').value = (am.category || 'general').toLowerCase();
        
        const icon = am.icon_class || 'check_circle';
        document.getElementById('amenity-icon-class-input').value = icon;
        document.getElementById('amenity-form-icon-preview').textContent = icon;
        document.getElementById('amenity-form-icon-code').textContent = icon;

        document.getElementById('amenity-modal-title').textContent = 'Edit Amenity';
        document.getElementById('amenity-modal-subtitle').textContent = `Updating #${am.id} • Equipped in ${am.usage_count || 0} properties`;
        document.getElementById('btn-amenity-submit-text').textContent = 'Update Amenity';

        document.getElementById('amenity-form-modal').classList.remove('hidden');
      })
      .catch(err => {
        showToast('Network error while fetching amenity', 'error');
      });
  }

  function closeAmenityModal() {
    document.getElementById('amenity-form-modal').classList.add('hidden');
  }

  function openAmenityIconPicker() {
    const current = document.getElementById('amenity-icon-class-input').value || 'check_circle';
    StayoraIconPicker.open({
      currentIcon: current,
      onSelect: function(iconName, item) {
        document.getElementById('amenity-icon-class-input').value = iconName;
        document.getElementById('amenity-form-icon-preview').textContent = iconName;
        document.getElementById('amenity-form-icon-code').textContent = iconName;
      }
    });
  }

  function handleAmenityFormSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-amenity-submit');
    const btnText = document.getElementById('btn-amenity-submit-text');
    const originalText = btnText.textContent;
    btn.disabled = true;
    btnText.textContent = 'Saving...';

    const formData = new FormData(document.getElementById('amenity-form'));

    fetch(`<?= url('admin/amenities-rules/amenity/save') ?>`, {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btnText.textContent = originalText;

      if (!data.success) {
        showToast(data.message || 'Validation error', 'error');
        return;
      }

      showToast(data.message || 'Saved successfully', 'success');
      closeAmenityModal();

      // Reload page to reflect fresh sorting and category counts
      setTimeout(() => window.location.reload(), 400);
    })
    .catch(err => {
      btn.disabled = false;
      btnText.textContent = originalText;
      showToast('An unexpected error occurred while saving', 'error');
    });
  }

  // ── Delete Amenity Confirmation ──
  function openDeleteAmenityModal(id) {
    const card = document.getElementById(`amenity-card-${id}`);
    if (!card) return;
    const name = card.querySelector('.amenity-name-text')?.textContent?.trim() || 'this amenity';
    const uses = parseInt(card.dataset.uses || '0');

    deleteTarget = { type: 'amenity', id: id };

    document.getElementById('delete-modal-title').textContent = `Delete Amenity "${name}"?`;
    document.getElementById('delete-modal-message').textContent = 'This action will remove the amenity from the master catalog.';

    const warnBox = document.getElementById('delete-modal-usage-warning');
    const warnText = document.getElementById('delete-modal-usage-text');
    if (uses > 0) {
      warnBox.classList.remove('hidden');
      warnText.innerHTML = `<strong>Safeguard Notice:</strong> This amenity is currently equipped in <strong>${uses}</strong> farmhouse ${uses === 1 ? 'estate' : 'estates'}. Deleting it will safely unlink it without breaking property records.`;
    } else {
      warnBox.classList.add('hidden');
    }

    document.getElementById('delete-confirm-modal').classList.remove('hidden');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ── RULES MANAGEMENT CONTROLLER
  // ═══════════════════════════════════════════════════════════════════════════

  function filterRules() {
    const query = document.getElementById('rule-search-input').value.toLowerCase().trim();
    const clearBtn = document.getElementById('rule-search-clear');
    clearBtn.classList.toggle('hidden', query === '');

    const cards = document.querySelectorAll('#rules-grid .rule-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const name = card.dataset.name || '';
      const desc = card.dataset.desc || '';
      
      const matches = query === '' || name.includes(query) || desc.includes(query);

      if (matches) {
        card.classList.remove('hidden');
        visibleCount++;
      } else {
        card.classList.add('hidden');
      }
    });

    document.getElementById('rules-shown-count').textContent = visibleCount;
    const emptyState = document.getElementById('rules-empty-state');
    emptyState.classList.toggle('hidden', visibleCount > 0);
  }

  function clearRuleSearch() {
    document.getElementById('rule-search-input').value = '';
    filterRules();
  }

  function openAddRuleModal() {
    document.getElementById('rule-form').reset();
    document.getElementById('rule-id-input').value = '0';
    document.getElementById('rule-modal-title').textContent = 'Add Rule Preset';
    document.getElementById('rule-modal-subtitle').textContent = 'Create a master stay policy template for properties';
    document.getElementById('rule-icon-class-input').value = 'gavel';
    document.getElementById('rule-form-icon-preview').textContent = 'gavel';
    document.getElementById('rule-form-icon-code').textContent = 'gavel';
    document.getElementById('btn-rule-submit-text').textContent = 'Save Rule Preset';

    document.getElementById('rule-form-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('rule-name-input').focus(), 100);
  }

  function openEditRuleModal(id) {
    const card = document.getElementById(`rule-card-${id}`);
    if (!card) return;

    fetch(`<?= url('admin/amenities-rules/rule/get') ?>?id=${id}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !data.rule) {
          showToast(data.message || 'Could not fetch rule preset details', 'error');
          return;
        }
        const r = data.rule;
        document.getElementById('rule-id-input').value = r.id;
        document.getElementById('rule-name-input').value = r.rule_name;
        document.getElementById('rule-description-input').value = r.description || '';
        
        const icon = r.icon_class || 'gavel';
        document.getElementById('rule-icon-class-input').value = icon;
        document.getElementById('rule-form-icon-preview').textContent = icon;
        document.getElementById('rule-form-icon-code').textContent = icon;

        document.getElementById('rule-modal-title').textContent = 'Edit Rule Preset';
        document.getElementById('rule-modal-subtitle').textContent = `Updating #${r.id} • Assigned in ${r.usage_count || 0} farmhouses`;
        document.getElementById('btn-rule-submit-text').textContent = 'Update Rule Preset';

        document.getElementById('rule-form-modal').classList.remove('hidden');
      })
      .catch(err => {
        showToast('Network error while fetching rule preset', 'error');
      });
  }

  function closeRuleModal() {
    document.getElementById('rule-form-modal').classList.add('hidden');
  }

  function openRuleIconPicker() {
    const current = document.getElementById('rule-icon-class-input').value || 'gavel';
    StayoraIconPicker.open({
      currentIcon: current,
      onSelect: function(iconName, item) {
        document.getElementById('rule-icon-class-input').value = iconName;
        document.getElementById('rule-form-icon-preview').textContent = iconName;
        document.getElementById('rule-form-icon-code').textContent = iconName;
      }
    });
  }

  function handleRuleFormSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-rule-submit');
    const btnText = document.getElementById('btn-rule-submit-text');
    const originalText = btnText.textContent;
    btn.disabled = true;
    btnText.textContent = 'Saving...';

    const formData = new FormData(document.getElementById('rule-form'));

    fetch(`<?= url('admin/amenities-rules/rule/save') ?>`, {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btnText.textContent = originalText;

      if (!data.success) {
        showToast(data.message || 'Validation error', 'error');
        return;
      }

      showToast(data.message || 'Saved successfully', 'success');
      closeRuleModal();

      setTimeout(() => window.location.reload(), 400);
    })
    .catch(err => {
      btn.disabled = false;
      btnText.textContent = originalText;
      showToast('An unexpected error occurred while saving', 'error');
    });
  }

  // ── Delete Rule Confirmation ──
  function openDeleteRuleModal(id) {
    const card = document.getElementById(`rule-card-${id}`);
    if (!card) return;
    const name = card.querySelector('.rule-name-text')?.textContent?.trim() || 'this rule preset';
    const uses = parseInt(card.dataset.uses || '0');

    deleteTarget = { type: 'rule', id: id };

    document.getElementById('delete-modal-title').textContent = `Delete Rule Preset "${name}"?`;
    document.getElementById('delete-modal-message').textContent = 'This will remove the policy template from the master catalog.';

    const warnBox = document.getElementById('delete-modal-usage-warning');
    const warnText = document.getElementById('delete-modal-usage-text');
    if (uses > 0) {
      warnBox.classList.remove('hidden');
      warnText.innerHTML = `<strong>Note:</strong> <strong>${uses}</strong> farmhouse ${uses === 1 ? 'estate currently has' : 'estates currently have'} this custom rule enabled. Removing this preset will not delete their configured policy, but new properties will no longer see it as a default preset.`;
    } else {
      warnBox.classList.add('hidden');
    }

    document.getElementById('delete-confirm-modal').classList.remove('hidden');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ── COMMON DELETION EXECUTION
  // ═══════════════════════════════════════════════════════════════════════════

  function closeDeleteModal() {
    document.getElementById('delete-confirm-modal').classList.add('hidden');
    deleteTarget = { type: null, id: null };
  }

  function executeDelete() {
    if (!deleteTarget.type || !deleteTarget.id) return;

    const btn = document.getElementById('btn-confirm-delete');
    btn.disabled = true;
    btn.innerHTML = `<span class="material-symbols-outlined text-sm animate-spin">refresh</span><span>Deleting...</span>`;

    const endpoint = deleteTarget.type === 'amenity' 
      ? `<?= url('admin/amenities-rules/amenity/delete') ?>`
      : `<?= url('admin/amenities-rules/rule/delete') ?>`;

    const formData = new FormData();
    formData.append('id', deleteTarget.id);

    fetch(endpoint, {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = `<span class="material-symbols-outlined text-sm">delete_forever</span><span>Delete Permanently</span>`;

      if (!data.success) {
        showToast(data.message || 'Failed to delete record', 'error');
        return;
      }

      showToast(data.message || 'Deleted successfully', 'success');
      closeDeleteModal();

      setTimeout(() => window.location.reload(), 400);
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = `<span class="material-symbols-outlined text-sm">delete_forever</span><span>Delete Permanently</span>`;
      showToast('A network error occurred while deleting', 'error');
    });
  }

  // Handle keyboard ESC to close modals
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeAmenityModal();
      closeRuleModal();
      closeDeleteModal();
    }
  });
</script>

<?php 
// ── Shared Admin Layout Footer ──
require_once __DIR__ . '/../layouts/footer.php'; 
?>
