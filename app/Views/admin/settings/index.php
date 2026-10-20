<?php
$currentPage = 'settings';
$pageTitle = 'Platform Settings — Stayora Executive Console';
require_once ROOT_PATH . '/app/Views/admin/layouts/header.php';
?>

<div class="flex min-h-screen bg-slate-950 text-slate-100 font-body">
  <?php require_once ROOT_PATH . '/app/Views/admin/layouts/sidebar.php'; ?>

  <!-- Main Content Wrapper -->
  <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    
    <!-- Top Bar / Page Header -->
    <header class="sticky top-0 z-30 bg-slate-900/90 backdrop-blur-md border-b border-slate-800/80 px-6 py-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <div>
          <div class="flex items-center gap-3">
            <h1 class="font-headline font-black text-xl text-white tracking-tight">Platform Settings</h1>
            <?php if (!empty($settings['maintenance_mode'])): ?>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                Maintenance Active
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Live &amp; Operational
              </span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-400 mt-1">Centralized controls for platform identity, policies, media limits, notifications, and security.</p>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-2 flex-wrap">
          <!-- Maintenance Quick Toggle Form -->
          <form action="<?= url('admin/settings/toggle-maintenance') ?>" method="POST" onsubmit="return confirmMaintenanceToggle(event, <?= !empty($settings['maintenance_mode']) ? 'true' : 'false' ?>)">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm <?= !empty($settings['maintenance_mode']) ? 'bg-amber-500 text-slate-950 hover:bg-amber-400' : 'bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700' ?>">
              <span class="material-symbols-outlined text-[17px]"><?= !empty($settings['maintenance_mode']) ? 'power_settings_new' : 'build' ?></span>
              <span><?= !empty($settings['maintenance_mode']) ? 'Deactivate Maintenance' : 'Activate Maintenance' ?></span>
            </button>
          </form>

          <!-- Purge Cache -->
          <a href="<?= url('admin/settings/clear-cache?tab=' . urlencode($tab)) ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700/80 transition-all" title="Purge in-memory runtime cache">
            <span class="material-symbols-outlined text-[17px]">cached</span>
            <span>Flush Cache</span>
          </a>

          <!-- Export JSON -->
          <a href="<?= url('admin/settings/export') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700/80 transition-all" title="Export settings backup JSON">
            <span class="material-symbols-outlined text-[17px]">download</span>
            <span>Export</span>
          </a>
        </div>

      </div>

      <!-- Flash Notifications -->
      <?php if (!empty($_SESSION['success'])): ?>
        <div class="mt-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-medium flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <span><?= $_SESSION['success'] ?></span>
          </div>
          <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white cursor-pointer"><span class="material-symbols-outlined text-sm">close</span></button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="mt-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-medium flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-base">error</span>
            <span><?= $_SESSION['error'] ?></span>
          </div>
          <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white cursor-pointer"><span class="material-symbols-outlined text-sm">close</span></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
    </header>

    <!-- Main Container Grid -->
    <div class="p-6 max-w-7xl w-full mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Left Navigation Column / Categories List -->
        <div class="lg:col-span-1">
          <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-3 shadow-xl sticky top-24">
            <div class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              Configuration Domains
            </div>
            <nav class="space-y-1">
              <?php foreach ($categories as $catKey => $catMeta): ?>
                <a href="<?= url('admin/settings?tab=' . $catKey) ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $tab === $catKey ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white font-bold shadow-md shadow-brand-700/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                  <span class="material-symbols-outlined text-[19px] <?= $tab === $catKey ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors"><?= $catMeta['icon'] ?></span>
                  <span class="truncate"><?= $catMeta['label'] ?></span>
                </a>
              <?php endforeach; ?>

              <div class="pt-3 mt-3 border-t border-slate-800/80 px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                Governance &amp; Metrics
              </div>

              <!-- Audit History Tab -->
              <a href="<?= url('admin/settings?tab=audit') ?>" 
                 class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $tab === 'audit' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white font-bold shadow-md shadow-brand-700/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                <span class="material-symbols-outlined text-[19px] <?= $tab === 'audit' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">history</span>
                <span>Audit Ledger</span>
              </a>

              <!-- System Telemetry Tab -->
              <a href="<?= url('admin/settings?tab=system') ?>" 
                 class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group <?= $tab === 'system' ? 'bg-gradient-to-r from-brand-700 to-brand-600 text-white font-bold shadow-md shadow-brand-700/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                <span class="material-symbols-outlined text-[19px] <?= $tab === 'system' ? 'text-white' : 'text-slate-400 group-hover:text-brand-400' ?> transition-colors">dns</span>
                <span>System Telemetry</span>
              </a>
            </nav>
          </div>
        </div>

        <!-- Right Content Column / Active Tab Panel -->
        <div class="lg:col-span-3">

          <!-- ════════════════════════════════════════════════════════════
               1. GENERAL SETTINGS TAB
               ════════════════════════════════════════════════════════════ -->
          <?php if ($tab === 'general'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">General Platform Settings</h2>
                  <p class="text-xs text-slate-400">Configure global platform branding identity, legal business titles, and website URL.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('general')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-general">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="general">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Platform Name <span class="text-brand-500">*</span></label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    <p class="text-[11px] text-slate-400 mt-1">Appears across all guest emails, vouchers, and meta titles.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Marketing Tagline</label>
                    <input type="text" name="tagline" value="<?= htmlspecialchars($settings['tagline'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    <p class="text-[11px] text-slate-400 mt-1">Brand proposition statement shown on landing headers.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Canonical Website URL <span class="text-brand-500">*</span></label>
                    <input type="url" name="website_url" value="<?= htmlspecialchars($settings['website_url'] ?? '') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    <p class="text-[11px] text-slate-400 mt-1">Primary production domain used for absolute links and emails.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Platform Operational Status</label>
                    <select name="default_platform_status" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                      <option value="Active" <?= ($settings['default_platform_status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active (Full Operations)</option>
                      <option value="Seasonal Hold" <?= ($settings['default_platform_status'] ?? '') === 'Seasonal Hold' ? 'selected' : '' ?>>Seasonal Hold (Inquiries Only)</option>
                      <option value="Private Beta" <?= ($settings['default_platform_status'] ?? '') === 'Private Beta' ? 'selected' : '' ?>>Private Beta (Invitation Only)</option>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Controls general booking intake behavior.</p>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-300 mb-1.5">Global Footer Copyright Notice</label>
                  <textarea name="footer_text" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all"><?= htmlspecialchars($settings['footer_text'] ?? '') ?></textarea>
                  <p class="text-[11px] text-slate-400 mt-1">Rendered at the bottom of public pages and booking vouchers.</p>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save General Settings</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               2. BRANDING & APPEARANCE TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'branding'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Platform Branding &amp; Studio</h2>
                  <p class="text-xs text-slate-400">Manage logos, browser favicon, default fallback media, and brand palette hex codes.</p>
                </div>
              </div>

              <!-- Live Media Preview Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                
                <!-- Light Header Logo -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 flex flex-col items-center text-center">
                  <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-2">Light Header Logo</div>
                  <div class="w-full h-24 bg-white/90 rounded-lg p-2 flex items-center justify-center mb-3 border border-slate-200/20">
                    <img id="preview-logo" src="<?= \App\Helpers\SiteHelper::getLogoUrl() ?>" alt="Logo Light" class="max-h-full max-w-full object-contain">
                  </div>
                  <input type="file" id="file-logo" accept="image/*" class="hidden" onchange="uploadAsset('logo', this)">
                  <button type="button" onclick="document.getElementById('file-logo').click()" class="w-full px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition-colors">
                    Upload New
                  </button>
                </div>

                <!-- Dark Footer Logo -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 flex flex-col items-center text-center">
                  <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-2">Dark Footer Logo</div>
                  <div class="w-full h-24 bg-slate-900 rounded-lg p-2 flex items-center justify-center mb-3 border border-slate-800">
                    <img id="preview-logo_dark" src="<?= \App\Helpers\SiteHelper::getDarkLogoUrl() ?>" alt="Logo Dark" class="max-h-full max-w-full object-contain">
                  </div>
                  <input type="file" id="file-logo_dark" accept="image/*" class="hidden" onchange="uploadAsset('logo_dark', this)">
                  <button type="button" onclick="document.getElementById('file-logo_dark').click()" class="w-full px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition-colors">
                    Upload New
                  </button>
                </div>

                <!-- Browser Favicon -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 flex flex-col items-center text-center">
                  <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-2">Browser Favicon</div>
                  <div class="w-full h-24 bg-slate-900/60 rounded-lg p-2 flex items-center justify-center mb-3 border border-slate-800">
                    <img id="preview-favicon" src="<?= \App\Helpers\SiteHelper::getFaviconUrl() ?>" alt="Favicon" class="w-10 h-10 object-contain">
                  </div>
                  <input type="file" id="file-favicon" accept="image/*" class="hidden" onchange="uploadAsset('favicon', this)">
                  <button type="button" onclick="document.getElementById('file-favicon').click()" class="w-full px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition-colors">
                    Upload New
                  </button>
                </div>

              </div>

              <!-- Theme Palette Colors Form -->
              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 pt-4 border-t border-slate-800/80 settings-form" id="form-branding">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="branding">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Primary Theme Color</label>
                    <div class="flex items-center gap-2">
                      <input type="color" id="primary_theme_color_picker" value="<?= htmlspecialchars($settings['primary_theme_color'] ?? '#1D4ED8') ?>" onchange="document.getElementById('primary_theme_color').value = this.value" class="w-10 h-10 rounded-xl bg-transparent border border-slate-800 cursor-pointer">
                      <input type="text" id="primary_theme_color" name="primary_theme_color" value="<?= htmlspecialchars($settings['primary_theme_color'] ?? '#1D4ED8') ?>" onchange="document.getElementById('primary_theme_color_picker').value = this.value" class="flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Stayora Royal Blue theme default.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Secondary Theme Color</label>
                    <div class="flex items-center gap-2">
                      <input type="color" id="secondary_theme_color_picker" value="<?= htmlspecialchars($settings['secondary_theme_color'] ?? '#0284C7') ?>" onchange="document.getElementById('secondary_theme_color').value = this.value" class="w-10 h-10 rounded-xl bg-transparent border border-slate-800 cursor-pointer">
                      <input type="text" id="secondary_theme_color" name="secondary_theme_color" value="<?= htmlspecialchars($settings['secondary_theme_color'] ?? '#0284C7') ?>" onchange="document.getElementById('secondary_theme_color_picker').value = this.value" class="flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Sky blue secondary accent.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Luxury Accent Color</label>
                    <div class="flex items-center gap-2">
                      <input type="color" id="accent_theme_color_picker" value="<?= htmlspecialchars($settings['accent_theme_color'] ?? '#C9A227') ?>" onchange="document.getElementById('accent_theme_color').value = this.value" class="w-10 h-10 rounded-xl bg-transparent border border-slate-800 cursor-pointer">
                      <input type="text" id="accent_theme_color" name="accent_theme_color" value="<?= htmlspecialchars($settings['accent_theme_color'] ?? '#C9A227') ?>" onchange="document.getElementById('accent_theme_color_picker').value = this.value" class="flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Gold luxury highlight color.</p>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Theme Colors</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               3. CONTACT INFORMATION TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'contact'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Contact &amp; Concierge Details</h2>
                  <p class="text-xs text-slate-400">Customer care telephone numbers, WhatsApp concierge, and corporate physical address.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('contact')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-contact">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="contact">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Customer Support Phone <span class="text-brand-500">*</span></label>
                    <input type="text" name="mobile_number" value="<?= htmlspecialchars($settings['mobile_number'] ?? '') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Click-to-call number in header and footer.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Official WhatsApp Concierge <span class="text-brand-500">*</span></label>
                    <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Used by WhatsAppGatewayService for 1-click confirmation links.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Customer Support Email <span class="text-brand-500">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Inquiries and guest support routing.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Google Maps Link</label>
                    <input type="url" name="google_map_link" value="<?= htmlspecialchars($settings['google_map_link'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Corporate headquarters maps location.</p>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-300 mb-1.5">Corporate Business Address</label>
                  <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-500"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                </div>

                <div class="border-t border-slate-800/80 pt-4">
                  <div class="text-xs font-bold text-slate-300 mb-3">Social Media Profiles</div>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-[11px] font-semibold text-slate-400 mb-1">Instagram URL</label>
                      <input type="url" name="instagram_link" value="<?= htmlspecialchars($settings['instagram_link'] ?? '') ?>" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    </div>
                    <div>
                      <label class="block text-[11px] font-semibold text-slate-400 mb-1">Facebook URL</label>
                      <input type="url" name="facebook_link" value="<?= htmlspecialchars($settings['facebook_link'] ?? '') ?>" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    </div>
                    <div>
                      <label class="block text-[11px] font-semibold text-slate-400 mb-1">YouTube Channel URL</label>
                      <input type="url" name="youtube_link" value="<?= htmlspecialchars($settings['youtube_link'] ?? '') ?>" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    </div>
                    <div>
                      <label class="block text-[11px] font-semibold text-slate-400 mb-1">X / Twitter URL</label>
                      <input type="url" name="twitter_link" value="<?= htmlspecialchars($settings['twitter_link'] ?? '') ?>" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Contact Details</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               4. REGIONAL, LOCALIZATION & CURRENCY TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'localization'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Regional, Localization &amp; Currency</h2>
                  <p class="text-xs text-slate-400">Timezone, date formats, and display currency formatting preferences.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('localization')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <!-- Notice on Currency Conversions -->
              <div class="p-3.5 rounded-xl bg-brand-900/40 border border-brand-700/50 text-xs text-brand-200 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-base text-brand-400 shrink-0 mt-0.5">info</span>
                <div>
                  <strong>Financial Data Integrity Guarantee:</strong> Changing the platform display currency modifies presentation formatting only. It will never silently alter historical booking records, guest payments, or database amounts.
                </div>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-localization">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="localization">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Operational Timezone</label>
                    <select name="timezone" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <?php 
                      $tzList = ['Asia/Kolkata' => 'Asia/Kolkata (IST +5:30)', 'UTC' => 'UTC (+0:00)', 'Asia/Dubai' => 'Asia/Dubai (GST +4:00)', 'Europe/London' => 'Europe/London (GMT/BST)', 'America/New_York' => 'America/New_York (EST/EDT)'];
                      foreach ($tzList as $tzVal => $tzLabel): ?>
                        <option value="<?= $tzVal ?>" <?= ($settings['timezone'] ?? 'Asia/Kolkata') === $tzVal ? 'selected' : '' ?>><?= $tzLabel ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Global Date Format</label>
                    <select name="date_format" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="d M Y" <?= ($settings['date_format'] ?? '') === 'd M Y' ? 'selected' : '' ?>>20 Oct 2026 (d M Y)</option>
                      <option value="M d, Y" <?= ($settings['date_format'] ?? '') === 'M d, Y' ? 'selected' : '' ?>>Oct 20, 2026 (M d, Y)</option>
                      <option value="Y-m-d" <?= ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>2026-10-20 (ISO Y-m-d)</option>
                      <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>20/10/2026 (d/m/Y)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Clock Time Format</label>
                    <select name="time_format" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="12h" <?= ($settings['time_format'] ?? '') === '12h' ? 'selected' : '' ?>>12-Hour (01:30 PM)</option>
                      <option value="24h" <?= ($settings['time_format'] ?? '') === '24h' ? 'selected' : '' ?>>24-Hour (13:30)</option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-2 border-t border-slate-800/80">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Default Currency Code</label>
                    <input type="text" name="default_currency" value="<?= htmlspecialchars($settings['default_currency'] ?? 'INR') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">ISO 4217 standard (e.g. INR, USD, EUR, AED).</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Display glyph (e.g. ₹, $, €, £).</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Symbol Placement</label>
                    <select name="currency_position" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="prefix" <?= ($settings['currency_position'] ?? '') === 'prefix' ? 'selected' : '' ?>>Prefix (₹4,500)</option>
                      <option value="suffix" <?= ($settings['currency_position'] ?? '') === 'suffix' ? 'selected' : '' ?>>Suffix (4,500 ₹)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Number Separator Style</label>
                    <select name="thousand_separator" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="indian" <?= ($settings['thousand_separator'] ?? '') === 'indian' ? 'selected' : '' ?>>Indian Lakhs / Crores (1,25,000)</option>
                      <option value="standard" <?= ($settings['thousand_separator'] ?? '') === 'standard' ? 'selected' : '' ?>>Standard International (125,000)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Decimal Precision</label>
                    <select name="decimal_precision" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="0" <?= ((int)($settings['decimal_precision'] ?? 0)) === 0 ? 'selected' : '' ?>>0 Decimals (Whole Integer ₹5,000)</option>
                      <option value="2" <?= ((int)($settings['decimal_precision'] ?? 0)) === 2 ? 'selected' : '' ?>>2 Decimals (₹5,000.00)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Live Formatter Preview</label>
                    <div class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs font-extrabold text-emerald-400 flex items-center justify-between">
                      <span>Preview (145000):</span>
                      <span><?= \App\Helpers\SiteHelper::formatCurrency(145000) ?></span>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Localization Preferences</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               5. BOOKING & STAY RULES TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'bookings'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Booking &amp; Stay Rules</h2>
                  <p class="text-xs text-slate-400">Establish platform-wide check-in hours, duration limits, and reservation policies.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('bookings')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-bookings">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="bookings">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Standard Check-In Time (HH:MM)</label>
                    <input type="time" name="default_checkin_time" value="<?= htmlspecialchars($settings['default_checkin_time'] ?? '13:00') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Default guest arrival window (e.g. 13:00 / 1:00 PM).</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Standard Check-Out Time (HH:MM)</label>
                    <input type="time" name="default_checkout_time" value="<?= htmlspecialchars($settings['default_checkout_time'] ?? '11:00') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Default guest departure deadline (e.g. 11:00 / 11:00 AM).</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Minimum Stay Duration (Nights)</label>
                    <input type="number" name="min_booking_nights" value="<?= (int)($settings['min_booking_nights'] ?? 1) ?>" min="1" max="30" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Maximum Consecutive Stay (Nights)</label>
                    <input type="number" name="max_booking_nights" value="<?= (int)($settings['max_booking_nights'] ?? 30) ?>" min="1" max="365" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Advance Booking Horizon (Days)</label>
                    <input type="number" name="advance_booking_days" value="<?= (int)($settings['advance_booking_days'] ?? 180) ?>" min="1" max="730" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Max calendar days into the future guests can reserve.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Cancellation Grace Window (Hours)</label>
                    <input type="number" name="cancellation_grace_hours" value="<?= (int)($settings['cancellation_grace_hours'] ?? 24) ?>" min="0" max="168" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Free cancellation period after reservation creation.</p>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 space-y-3">
                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Auto-Confirm New Bookings</div>
                      <div class="text-[11px] text-slate-400">If enabled, bookings bypass the host approval queue and transition straight to confirmed.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="auto_confirm_bookings" value="1" <?= !empty($settings['auto_confirm_bookings']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Booking Policies</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               6. PROPERTY MANAGEMENT TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'properties'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Property Management Settings</h2>
                  <p class="text-xs text-slate-400">Listing moderation requirements, media upload limits, and default estate statuses.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('properties')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-properties">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="properties">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Max Property Images Allowed</label>
                    <input type="number" name="max_property_images" value="<?= (int)($settings['max_property_images'] ?? 15) ?>" min="1" max="50" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Maximum gallery photos permitted per farmhouse estate.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Max Upload Size (MB per Image)</label>
                    <input type="number" name="max_upload_size_mb" value="<?= (int)($settings['max_upload_size_mb'] ?? 5) ?>" min="1" max="30" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Single asset size limit enforced during property submission.</p>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 space-y-3">
                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Require Admin Moderation for New Listings</div>
                      <div class="text-[11px] text-slate-400">Newly registered owner estates remain in 'pending' until verified and approved by admin.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="property_approval_required" value="1" <?= !empty($settings['property_approval_required']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Property Rules</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               7. GUEST & MEMBER RULES TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'guests'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Guest &amp; Member Rules</h2>
                  <p class="text-xs text-slate-400">Guest registration intake, KYC identity verification policies, and membership defaults.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('guests')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-guests">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="guests">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Default Membership Tier for New Users</label>
                    <select name="default_membership_tier" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="Regular" <?= ($settings['default_membership_tier'] ?? '') === 'Regular' ? 'selected' : '' ?>>Regular Tier (Standard Guest)</option>
                      <option value="Silver" <?= ($settings['default_membership_tier'] ?? '') === 'Silver' ? 'selected' : '' ?>>Silver Tier</option>
                      <option value="Gold" <?= ($settings['default_membership_tier'] ?? '') === 'Gold' ? 'selected' : '' ?>>Gold Tier (VIP Concierge)</option>
                      <option value="Platinum" <?= ($settings['default_membership_tier'] ?? '') === 'Platinum' ? 'selected' : '' ?>>Platinum Tier (Ultra Luxury Club)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Max Document Uploads Per Profile</label>
                    <input type="number" name="max_guest_documents" value="<?= (int)($settings['max_guest_documents'] ?? 5) ?>" min="1" max="20" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 space-y-3">
                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Allow Public Guest Sign-ups</div>
                      <div class="text-[11px] text-slate-400">If disabled, new registrations on the public portal are closed (invitation only).</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="guest_registration_enabled" value="1" <?= !empty($settings['guest_registration_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Mandatory Government ID Verification</div>
                      <div class="text-[11px] text-slate-400">Requires guests to upload valid government ID (Aadhaar / Passport) prior to check-in.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="guest_id_verification_required" value="1" <?= !empty($settings['guest_id_verification_required']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Guest Rules</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               8. REVIEW & MODERATION TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'reviews'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Review &amp; Rating Moderation</h2>
                  <p class="text-xs text-slate-400">Customer feedback policies, auto-publication thresholds, and host response rules.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('reviews')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-reviews">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="reviews">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Minimum Rating for Auto-Publish (Stars)</label>
                    <input type="number" step="0.1" name="review_min_rating_auto_publish" value="<?= htmlspecialchars((string)($settings['review_min_rating_auto_publish'] ?? '4.5')) ?>" min="1.0" max="5.0" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Applicable only if Auto-Publish is toggled on.</p>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 space-y-3">
                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Require Verified Stay to Review</div>
                      <div class="text-[11px] text-slate-400">Only guests with a completed checkout record can submit property reviews.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="review_verified_only" value="1" <?= !empty($settings['review_verified_only']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Auto-Approve Customer Reviews</div>
                      <div class="text-[11px] text-slate-400">If disabled, all reviews require manual approval in the Guest Reviews console.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="review_auto_approve" value="1" <?= !empty($settings['review_auto_approve']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Allow Host / Admin Responses</div>
                      <div class="text-[11px] text-slate-400">Permits property hosts and admins to post official responses under reviews.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="review_allow_host_response" value="1" <?= !empty($settings['review_allow_host_response']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Allow Photo Attachments in Reviews</div>
                      <div class="text-[11px] text-slate-400">Guests can upload real photos of their stay with their review.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="review_photos_allowed" value="1" <?= !empty($settings['review_photos_allowed']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Review Policies</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               9. NOTIFICATION SETTINGS TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'notifications'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Notification &amp; Channel Toggles</h2>
                  <p class="text-xs text-slate-400">Enable or disable communication delivery channels and operational trigger events.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('notifications')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-notifications">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="notifications">

                <div class="text-xs font-bold text-slate-300 mb-2">Master Delivery Channels</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  
                  <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <div>
                      <div class="text-xs font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-400 text-base">mail</span> Email
                      </div>
                      <div class="text-[11px] text-slate-400 mt-0.5">Transactional HTML mail</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="email_notifications_enabled" value="1" <?= !empty($settings['email_notifications_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>

                  <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <div>
                      <div class="text-xs font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-400 text-base">chat</span> WhatsApp
                      </div>
                      <div class="text-[11px] text-slate-400 mt-0.5">1-click &amp; Cloud API</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="whatsapp_notifications_enabled" value="1" <?= !empty($settings['whatsapp_notifications_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                  </div>

                  <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <div>
                      <div class="text-xs font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-400 text-base">sms</span> SMS Driver
                      </div>
                      <div class="text-[11px] text-slate-400 mt-0.5">MSG91 / Twilio SMS</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="sms_notifications_enabled" value="1" <?= !empty($settings['sms_notifications_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                    </label>
                  </div>

                </div>

                <div class="pt-4 border-t border-slate-800/80">
                  <div class="text-xs font-bold text-slate-300 mb-3">Event Notification Triggers</div>
                  
                  <?php 
                  $ev = json_decode($settings['notification_events'] ?? '{}', true) ?: [];
                  $eventList = [
                    'booking_created'  => ['label' => 'New Booking Request Received', 'desc' => 'Alert guest & team when a new stay booking is initiated.'],
                    'booking_approved' => ['label' => 'Booking Approved & Confirmed', 'desc' => 'Issue official digital booking voucher with QR code.'],
                    'booking_cancelled'=> ['label' => 'Booking Cancelled Advisory', 'desc' => 'Dispatch cancellation notice and refund status.'],
                    'checkin_reminder' => ['label' => 'Check-In Day Reminder', 'desc' => 'Send directions, host contact, and check-in briefing 24h prior.'],
                    'review_submitted' => ['label' => 'New Guest Review Alert', 'desc' => 'Notify administrative moderation queue of incoming feedback.'],
                    'payment_verified' => ['label' => 'Payment Receipt Reconciled', 'desc' => 'Issue verified receipt with UTR reference transaction code.']
                  ];
                  ?>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php foreach ($eventList as $eKey => $eMeta): ?>
                      <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                          <div class="text-xs font-bold text-white"><?= $eMeta['label'] ?></div>
                          <div class="text-[11px] text-slate-400"><?= $eMeta['desc'] ?></div>
                        </div>
                        <input type="checkbox" name="notification_events[<?= $eKey ?>]" value="1" <?= !empty($ev[$eKey]) ? 'checked' : '' ?> class="w-4 h-4 rounded text-brand-600 bg-slate-900 border-slate-700">
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Notification Toggles</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               10. EMAIL COMMUNICATION TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'email'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Email &amp; Communication Configuration</h2>
                  <p class="text-xs text-slate-400">Configure outgoing email identity, reply-to routing, footer signature, and dispatch test.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('email')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <!-- Environment Status Card -->
              <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Environment SMTP Driver Status</div>
                  <div class="text-sm font-bold text-white mt-0.5">
                    Host: <span class="text-brand-400"><?= htmlspecialchars(getenv('MAIL_HOST') ?: 'smtp.hostinger.com') ?></span> &bull; Port: <span class="text-brand-400"><?= (int)(getenv('MAIL_PORT') ?: 465) ?></span> (<?= htmlspecialchars(getenv('MAIL_ENCRYPTION') ?: 'ssl') ?>)
                  </div>
                  <div class="text-[11px] text-slate-400 mt-1">Credentials securely loaded from <code>.env</code> file. Password masked for protection.</div>
                </div>
                
                <!-- Live Interactive Test Email Button -->
                <form action="<?= url('admin/settings/test-email') ?>" method="POST" class="shrink-0 flex items-center gap-2">
                  <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                  <input type="hidden" name="recipient_email" value="<?= htmlspecialchars($adminEmail) ?>">
                  <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition-all border border-slate-700 flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base text-emerald-400">send</span>
                    <span>Send Test Email</span>
                  </button>
                </form>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-email">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="email">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Platform Sender Name <span class="text-brand-500">*</span></label>
                    <input type="text" name="email_sender_name" value="<?= htmlspecialchars($settings['email_sender_name'] ?? 'Stayora Luxury Hospitality') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Friendly display name in guest email clients.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Outgoing Sender Email Address <span class="text-brand-500">*</span></label>
                    <input type="email" name="email_sender_address" value="<?= htmlspecialchars($settings['email_sender_address'] ?? 'noreply@stayora.com') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">FROM email address header.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Reply-To Email Address <span class="text-brand-500">*</span></label>
                    <input type="email" name="email_reply_to" value="<?= htmlspecialchars($settings['email_reply_to'] ?? 'concierge@stayora.com') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Recipient of direct replies from guests.</p>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-300 mb-1.5">Rich HTML Email Signature / Footer</label>
                  <textarea name="email_signature_html" rows="3" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white"><?= htmlspecialchars($settings['email_signature_html'] ?? '') ?></textarea>
                  <p class="text-[11px] text-slate-400 mt-1">Appended to booking vouchers, receipts, and approval notifications.</p>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Email Configuration</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               11. SYSTEM & MAINTENANCE TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'maintenance'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">System Availability &amp; Maintenance Mode</h2>
                  <p class="text-xs text-slate-400">Put platform into scheduled maintenance mode with custom public notice and whitelist rules.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('maintenance')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <!-- Anti-Lockout Safeguard Banner -->
              <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-700/50 text-xs text-emerald-200 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-base text-emerald-400 shrink-0 mt-0.5">verified_user</span>
                <div>
                  <strong>Admin Lockout Immunity:</strong> When Maintenance Mode is active, authenticated administrators logged into the Executive Console and all <code>/admin/*</code> routes are <strong>strictly exempt from restrictions</strong>. You can always access this dashboard to toggle maintenance back off.
                </div>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-maintenance">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="maintenance">

                <!-- Big Maintenance Toggle -->
                <div class="p-5 rounded-2xl <?= !empty($settings['maintenance_mode']) ? 'bg-amber-500/10 border-2 border-amber-500/40' : 'bg-slate-950 border border-slate-800' ?> flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl <?= !empty($settings['maintenance_mode']) ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-400' ?> flex items-center justify-center">
                      <span class="material-symbols-outlined text-2xl">construction</span>
                    </div>
                    <div>
                      <div class="text-sm font-bold text-white">Maintenance Mode Status</div>
                      <div class="text-xs text-slate-400 mt-0.5">
                        <?= !empty($settings['maintenance_mode']) ? 'Active — Public visitors see the luxury maintenance downtime screen.' : 'Inactive — Platform is live and open for all traffic.' ?>
                      </div>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-14 h-7 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-500"></div>
                  </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Public Notice Headline</label>
                    <input type="text" name="maintenance_notice_title" value="<?= htmlspecialchars($settings['maintenance_notice_title'] ?? 'Scheduled System Maintenance') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Estimated Resumption Datetime (Optional)</label>
                    <input type="datetime-local" name="maintenance_estimated_end" value="<?= !empty($settings['maintenance_estimated_end']) ? date('Y-m-d\TH:i', strtotime($settings['maintenance_estimated_end'])) : '' ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Displays expected return time to visitors.</p>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-300 mb-1.5">Public Notice Message</label>
                  <textarea name="maintenance_notice_message" rows="3" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white"><?= htmlspecialchars($settings['maintenance_notice_message'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-800/80">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Whitelisted Bypass IP Addresses</label>
                    <input type="text" name="maintenance_allowed_ips" value="<?= htmlspecialchars($settings['maintenance_allowed_ips'] ?? '') ?>" placeholder="e.g. 127.0.0.1, 192.168.1.10" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Comma-separated client IPs allowed to bypass maintenance.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Default Admin Table Pagination Size</label>
                    <select name="default_pagination_size" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                      <option value="10" <?= ((int)($settings['default_pagination_size'] ?? 10)) === 10 ? 'selected' : '' ?>>10 records per page</option>
                      <option value="25" <?= ((int)($settings['default_pagination_size'] ?? 10)) === 25 ? 'selected' : '' ?>>25 records per page</option>
                      <option value="50" <?= ((int)($settings['default_pagination_size'] ?? 10)) === 50 ? 'selected' : '' ?>>50 records per page</option>
                      <option value="100" <?= ((int)($settings['default_pagination_size'] ?? 10)) === 100 ? 'selected' : '' ?>>100 records per page</option>
                    </select>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Maintenance Settings</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               12. SECURITY & ACCESS TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'security'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Security &amp; Session Access Policies</h2>
                  <p class="text-xs text-slate-400">Configure administrative session lifetimes, account lockout thresholds, and device security.</p>
                </div>
                <button type="button" onclick="triggerCategoryReset('security')" class="text-xs text-slate-400 hover:text-amber-400 font-semibold flex items-center gap-1 transition-colors">
                  <span class="material-symbols-outlined text-sm">restart_alt</span> Reset to Baseline
                </button>
              </div>

              <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-5 settings-form" id="form-security">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="category" value="security">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Session Inactivity Timeout (Minutes)</label>
                    <input type="number" name="session_timeout_minutes" value="<?= (int)($settings['session_timeout_minutes'] ?? 120) ?>" min="5" max="1440" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Automatic admin sign-out duration on idle.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Max Failed Login Attempts</label>
                    <input type="number" name="max_login_attempts" value="<?= (int)($settings['max_login_attempts'] ?? 5) ?>" min="1" max="20" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Failed password attempts before lockout.</p>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Account Lockout Duration (Minutes)</label>
                    <input type="number" name="account_lockout_minutes" value="<?= (int)($settings['account_lockout_minutes'] ?? 15) ?>" min="1" max="1440" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Duration admin is blocked after threshold.</p>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 space-y-3">
                  <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                      <div class="text-xs font-bold text-white">Strict Single-Device Active Session</div>
                      <div class="text-[11px] text-slate-400">Enforces only one active login per administrator account via MySQL <code>active_session_id</code>. Subsequent logins invalidate previous devices.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="enforce_single_session" value="1" <?= !empty($settings['enforce_single_session']) ? 'checked' : '' ?> class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-800/80">
                  <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-lg shadow-brand-700/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Save Security Policies</span>
                  </button>
                </div>
              </form>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               13. AUDIT LEDGER TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'audit'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-4">
                <div>
                  <h2 class="font-headline font-bold text-lg text-white">Platform Settings Audit Ledger</h2>
                  <p class="text-xs text-slate-400">Immutable chronological record of administrator configuration changes and before/after values.</p>
                </div>

                <!-- Category Filter -->
                <form method="GET" action="<?= url('admin/settings') ?>" class="flex items-center gap-2">
                  <input type="hidden" name="tab" value="audit">
                  <select name="audit_category" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $ck => $cm): ?>
                      <option value="<?= $ck ?>" <?= ($auditCategory ?? '') === $ck ? 'selected' : '' ?>><?= $cm['label'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </div>

              <?php if (empty($auditLogs)): ?>
                <div class="text-center py-12 text-slate-500">
                  <span class="material-symbols-outlined text-4xl mb-2 text-slate-600">history</span>
                  <p class="text-xs">No configuration change records found in audit ledger.</p>
                </div>
              <?php else: ?>
                <div class="overflow-x-auto">
                  <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-800">
                      <tr>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Administrator</th>
                        <th class="py-3 px-4">Domain / Key</th>
                        <th class="py-3 px-4">Previous Value</th>
                        <th class="py-3 px-4">New Value</th>
                        <th class="py-3 px-4">IP Address</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                      <?php foreach ($auditLogs as $log): ?>
                        <tr class="hover:bg-slate-800/30 transition-colors">
                          <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                            <?= date('M d, Y • h:i A', strtotime($log['created_at'])) ?>
                          </td>
                          <td class="py-3 px-4 font-bold text-white whitespace-nowrap">
                            <?= htmlspecialchars($log['admin_name'] ?? 'System') ?>
                          </td>
                          <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 mr-1.5"><?= htmlspecialchars($log['category']) ?></span>
                            <span class="font-mono text-slate-300"><?= htmlspecialchars($log['setting_key']) ?></span>
                          </td>
                          <td class="py-3 px-4 text-rose-400/90 max-w-xs truncate" title="<?= htmlspecialchars($log['old_value'] ?? '') ?>">
                            <?= htmlspecialchars(substr((string)($log['old_value'] ?? '—'), 0, 45)) ?>
                          </td>
                          <td class="py-3 px-4 text-emerald-400 max-w-xs truncate" title="<?= htmlspecialchars($log['new_value'] ?? '') ?>">
                            <?= htmlspecialchars(substr((string)($log['new_value'] ?? '—'), 0, 45)) ?>
                          </td>
                          <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                            <?= htmlspecialchars($log['ip_address'] ?? '') ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>

                <div class="text-xs text-slate-400 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                  <span>Showing <?= count($auditLogs) ?> of <?= $totalAuditCount ?> total audit events</span>
                </div>
              <?php endif; ?>
            </div>

          <!-- ════════════════════════════════════════════════════════════
               14. SYSTEM TELEMETRY TAB
               ════════════════════════════════════════════════════════════ -->
          <?php elseif ($tab === 'system'): ?>
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl space-y-6">
              <div class="border-b border-slate-800/80 pb-4">
                <h2 class="font-headline font-bold text-lg text-white">System Diagnostics &amp; Telemetry</h2>
                <p class="text-xs text-slate-400">Read-only server environment information, runtime limits, and active extensions.</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($systemInfo as $infoKey => $infoVal): ?>
                  <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400"><?= ucwords(str_replace('_', ' ', $infoKey)) ?></div>
                    <div class="text-sm font-bold text-white mt-1 break-all font-mono"><?= htmlspecialchars((string)$infoVal) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>
</div>

<!-- Sticky Unsaved Changes Notification Bar -->
<div id="unsaved-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 border border-brand-500/50 shadow-2xl shadow-brand-900/50 px-6 py-3.5 rounded-2xl z-50 hidden flex items-center gap-4 transition-all animate-bounce">
  <div class="flex items-center gap-2 text-xs font-semibold text-white">
    <span class="material-symbols-outlined text-brand-400 text-lg">edit_note</span>
    <span>You have unsaved changes in this category!</span>
  </div>
  <div class="flex items-center gap-2">
    <button type="button" onclick="window.location.reload()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Discard</button>
    <button type="button" onclick="submitActiveForm()" class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold">Save Now</button>
  </div>
</div>

<!-- Reset Category Modal -->
<div id="reset-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs hidden flex items-center justify-center p-4">
  <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl text-center">
    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto mb-4 flex items-center justify-center">
      <span class="material-symbols-outlined text-3xl">restart_alt</span>
    </div>
    <h3 class="font-headline font-bold text-lg text-white mb-2">Reset Category to Factory Baseline?</h3>
    <p class="text-xs text-slate-300 leading-relaxed mb-6">
      This operation will restore factory defaults for the selected category (<strong id="modal-cat-name" class="text-amber-400"></strong>). 
      Existing bookings, properties, reviews, and guest records will NOT be modified.
    </p>
    <form action="<?= url('admin/settings/reset-category') ?>" method="POST" class="flex items-center justify-center gap-3">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="category" id="modal-reset-category-input" value="">
      <button type="button" onclick="closeResetModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">Cancel</button>
      <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black">Confirm Reset</button>
    </form>
  </div>
</div>

<script>
// Unsaved Changes Detection
let hasUnsavedChanges = false;
const activeForm = document.querySelector('.settings-form');
const unsavedBar = document.getElementById('unsaved-bar');

if (activeForm) {
  activeForm.addEventListener('input', () => {
    if (!hasUnsavedChanges) {
      hasUnsavedChanges = true;
      if (unsavedBar) unsavedBar.classList.remove('hidden');
    }
  });
}

function submitActiveForm() {
  if (activeForm) {
    activeForm.submit();
  }
}

// Reset Category Modal
function triggerCategoryReset(category) {
  document.getElementById('modal-cat-name').textContent = category.toUpperCase();
  document.getElementById('modal-reset-category-input').value = category;
  document.getElementById('reset-modal').classList.remove('hidden');
}

function closeResetModal() {
  document.getElementById('reset-modal').classList.add('hidden');
}

// Confirm Maintenance Toggle
function confirmMaintenanceToggle(e, isCurrentlyActive) {
  const msg = isCurrentlyActive 
    ? 'Are you sure you want to deactivate Maintenance Mode and reopen the platform to public guests?' 
    : 'Are you sure you want to activate Maintenance Mode? Public visitors will be redirected to the maintenance downtime screen.';
  return confirm(msg);
}

// AJAX Branding Asset Upload
async function uploadAsset(type, inputElem) {
  if (!inputElem.files || !inputElem.files[0]) return;
  const file = inputElem.files[0];

  const formData = new FormData();
  formData.append('csrf_token', '<?= $csrfToken ?>');
  formData.append('type', type);
  formData.append('asset_file', file);

  try {
    const res = await fetch('<?= url('admin/settings/upload-branding') ?>', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      const previewImg = document.getElementById('preview-' + type);
      if (previewImg) {
        previewImg.src = data.file_url + '?t=' + new Date().getTime();
      }
      alert('✓ ' + data.message);
    } else {
      alert('Error: ' + (data.error || 'Upload failed'));
    }
  } catch (err) {
    alert('Failed to upload branding asset: ' + err.message);
  }
}
</script>

<?php require_once ROOT_PATH . '/app/Views/admin/layouts/footer.php'; ?>
