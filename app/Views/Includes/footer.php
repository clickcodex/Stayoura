
<!-- ═══════════════════════════ FOOTER ═══════════════════════════ -->
<footer class="bg-inverse-surface text-inverse-on-surface" style="background:#0F172A;">
  
  <!-- Top accent bar -->
  <div class="h-1 w-full" style="background: linear-gradient(90deg, #1D4ED8 0%, #0284C7 50%, #38BDF8 100%);"></div>

  <div class="max-w-7xl mx-auto px-4 md:px-8">

    <!-- ── Main Grid ── -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pt-16 pb-12 border-b" style="border-color:rgba(255,255,255,0.08)">

      <!-- Brand Column (spans 2 on lg) -->
      <div class="lg:col-span-2 pr-0 lg:pr-8">

        <!-- Logo -->
        <a href="<?= url('home') ?>" class="flex items-center gap-2.5 mb-5 group w-fit">
          <img src="<?= SiteHelper::getDarkLogoUrl() ?>" alt="<?= htmlspecialchars($globalSite['site_name'] ?? 'Stayora') ?> Logo" class="h-10 w-auto object-contain">
          <span style="font-family:'Epilogue',sans-serif;font-weight:800;font-size:1.3rem;color:#fff;letter-spacing:-.5px;">
            <?= htmlspecialchars($globalSite['site_name'] ?? 'Stayora') ?>
          </span>
        </a>

        <p style="color:rgba(255,255,255,.6);font-size:.875rem;line-height:1.7;max-width:300px;" class="mb-6">
          Discover handpicked luxury farmhouses, private pool villas, and tranquil retreat stays across India with Stayora.
        </p>

        <!-- Contact Details -->
        <ul class="space-y-3 mb-8">
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-base" style="color:#38BDF8;">call</span>
            <a href="tel:<?= htmlspecialchars($globalSite['mobile_number'] ?? '') ?>"
               style="color:rgba(255,255,255,.75);font-size:.85rem;" class="hover:text-white transition-colors">
              <?= htmlspecialchars($globalSite['mobile_number'] ?? 'N/A') ?>
            </a>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-base" style="color:#38BDF8;">mail</span>
            <a href="mailto:<?= htmlspecialchars($globalSite['email'] ?? '') ?>"
               style="color:rgba(255,255,255,.75);font-size:.85rem;" class="hover:text-white transition-colors">
              <?= htmlspecialchars($globalSite['email'] ?? 'N/A') ?>
            </a>
          </li>
          <li class="flex items-start gap-3">
            <span class="material-symbols-outlined text-base mt-0.5" style="color:#38BDF8;">location_on</span>
            <span style="color:rgba(255,255,255,.75);font-size:.85rem;line-height:1.6;">
              <?= htmlspecialchars($globalSite['address'] ?? 'N/A') ?>
            </span>
          </li>
        </ul>

        <!-- Social Icons -->
        <div class="flex items-center gap-3">
          <!-- Facebook -->
          <a href="<?= htmlspecialchars($globalSite['facebook_link'] ?? '#') ?>" target="_blank" rel="noopener"
             title="Facebook"
             style="width:38px;height:38px;border-radius:9999px;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;transition:background .2s,transform .2s;"
             onmouseover="this.style.background='#1D4ED8';this.style.transform='translateY(-2px)'"
             onmouseout="this.style.background='rgba(255,255,255,.07)';this.style.transform='translateY(0)'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="color:#fff;">
              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
            </svg>
          </a>

          <!-- Instagram -->
          <a href="<?= htmlspecialchars($globalSite['instagram_link'] ?? '#') ?>" target="_blank" rel="noopener"
             title="Instagram"
             style="width:38px;height:38px;border-radius:9999px;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;transition:background .2s,transform .2s;"
             onmouseover="this.style.background='#E1306C';this.style.transform='translateY(-2px)'"
             onmouseout="this.style.background='rgba(255,255,255,.07)';this.style.transform='translateY(0)'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
              <circle cx="12" cy="12" r="4"/>
              <circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/>
            </svg>
          </a>

          <!-- WhatsApp -->
          <a href="https://wa.me/<?= preg_replace('/\D/', '', $globalSite['mobile_number'] ?? '') ?>" target="_blank" rel="noopener"
             title="WhatsApp"
             style="width:38px;height:38px;border-radius:9999px;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;transition:background .2s,transform .2s;"
             onmouseover="this.style.background='#25D366';this.style.transform='translateY(-2px)'"
             onmouseout="this.style.background='rgba(255,255,255,.07)';this.style.transform='translateY(0)'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            </svg>
          </a>

          <!-- Email -->
          <a href="mailto:<?= htmlspecialchars($globalSite['email'] ?? '') ?>"
             title="Email"
             style="width:38px;height:38px;border-radius:9999px;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;transition:background .2s,transform .2s;"
             onmouseover="this.style.background='#1D4ED8';this.style.transform='translateY(-2px)'"
             onmouseout="this.style.background='rgba(255,255,255,.07)';this.style.transform='translateY(0)'">
            <span class="material-symbols-outlined text-base" style="color:#fff;font-size:17px;">mail</span>
          </a>
        </div>

      </div>

      <!-- Explore -->
      <div>
        <h4 style="font-family:'Epilogue',sans-serif;font-weight:700;font-size:1rem;color:#fff;margin-bottom:1.25rem;letter-spacing:.3px;">
          Explore
        </h4>
        <ul class="space-y-3">
          <?php foreach ([
            ['All Farmhouses', '/farmhouses'],
            ['Guest Login', '/login'],
          ] as [$label, $u]): ?>
          <li>
            <a href="<?= url($u) ?>"
               style="color:rgba(255,255,255,.6);font-size:.85rem;transition:color .15s,padding-left .15s;display:inline-block;"
               onmouseover="this.style.color='#38BDF8';this.style.paddingLeft='4px'"
               onmouseout="this.style.color='rgba(255,255,255,.6)';this.style.paddingLeft='0'">
              <?= $label ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Company -->
      <div>
        <h4 style="font-family:'Epilogue',sans-serif;font-weight:700;font-size:1rem;color:#fff;margin-bottom:1.25rem;letter-spacing:.3px;">
          Company
        </h4>
        <ul class="space-y-3">
          <?php foreach ([
            ['About Us',          'about',           false],
            ['Why Choose Us',     'why-choose-us',   false],
            ['Contact Support',   'contact',          false],
          ] as [$label, $u, $highlight]): ?>
          <li>
            <a href="<?= url($u) ?>"
               style="color:<?= $highlight ? '#C9A227' : 'rgba(255,255,255,.6)' ?>;font-size:.85rem;font-weight:<?= $highlight ? '700' : '400' ?>;transition:color .15s,padding-left .15s;display:inline-block;"
               onmouseover="this.style.color='#38BDF8';this.style.paddingLeft='4px'"
               onmouseout="this.style.color='<?= $highlight ? '#C9A227' : 'rgba(255,255,255,.6)' ?>'; this.style.paddingLeft='0'">
              <?= $label ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Legal -->
      <div>
        <h4 style="font-family:'Epilogue',sans-serif;font-weight:700;font-size:1rem;color:#fff;margin-bottom:1.25rem;letter-spacing:.3px;">
          Legal
        </h4>
        <ul class="space-y-3">
          <?php foreach ([
            ['Privacy Policy',     'privacy'],
            ['Terms of Service',   'terms_conditions'],
            ['Cancellation Policy','cancellation-policy'],
            ['Cookie Policy',      'cookie-policy'],
          ] as [$label, $u]): ?>
          <li>
            <a href="<?= url($u) ?>"
               style="color:rgba(255,255,255,.6);font-size:.85rem;transition:color .15s,padding-left .15s;display:inline-block;"
               onmouseover="this.style.color='#38BDF8';this.style.paddingLeft='4px'"
               onmouseout="this.style.color='rgba(255,255,255,.6)';this.style.paddingLeft='0'">
              <?= $label ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>

        <!-- Trust Badge -->
        <div style="margin-top:2rem;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:12px;border:1px solid rgba(255,255,255,.08);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <span style="font-size:18px;">🇮🇳</span>
            <span style="color:#f47920;font-size:10px;font-weight:800;letter-spacing:.6px;">#STARTUPINDIA</span>
          </div>
          <div style="color:rgba(255,255,255,.5);font-size:10px;font-weight:600;letter-spacing:.5px;">
            PROUDLY MADE IN INDIA
          </div>
        </div>
      </div>

    </div><!-- /grid -->

    <!-- ── Bottom Bar ── -->
    <div class="py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p style="color:rgba(255,255,255,.4);font-size:.8rem;">
        <?= !empty($globalSite['footer_text']) ? htmlspecialchars($globalSite['footer_text']) : '© ' . date('Y') . ' Stayora. All rights reserved.' ?>
      </p>
      <div class="flex items-center gap-2">
      </div>
      <div class="flex items-center gap-4">
        <a href="<?= url('privacy') ?>"          style="color:rgba(255,255,255,.4);font-size:.75rem;" class="hover:text-white transition-colors">Privacy</a>
        <a href="<?= url('terms_conditions') ?>" style="color:rgba(255,255,255,.4);font-size:.75rem;" class="hover:text-white transition-colors">Terms</a>
      </div>
    </div>

  </div><!-- /max-w-7xl -->
</footer>
<!-- ═══════════════════════════ /FOOTER ══════════════════════════ -->
  
  
  
  
<script>
  // ── Global Mobile Navigation Sidebar Drawer ──
  function openMobileNav() {
    const overlay = document.getElementById('mob-nav-overlay');
    const drawer = document.getElementById('mob-nav-drawer');
    const menuIcon = document.getElementById('menu-icon');
    const menuBtn = document.getElementById('mobile-menu-btn');
    if (overlay && drawer) {
      overlay.classList.add('open');
      drawer.classList.add('open');
      document.body.style.overflow = 'hidden';
      if (menuIcon) menuIcon.textContent = 'close';
      if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
    }
  }

  function closeMobileNav() {
    const overlay = document.getElementById('mob-nav-overlay');
    const drawer = document.getElementById('mob-nav-drawer');
    const menuIcon = document.getElementById('menu-icon');
    const menuBtn = document.getElementById('mobile-menu-btn');
    if (overlay && drawer) {
      overlay.classList.remove('open');
      drawer.classList.remove('open');
      document.body.style.overflow = '';
      if (menuIcon) menuIcon.textContent = 'menu';
      if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    }
  }

  function toggleMobileNav() {
    const drawer = document.getElementById('mob-nav-drawer');
    if (drawer && drawer.classList.contains('open')) {
      closeMobileNav();
    } else {
      openMobileNav();
    }
  }

  // ── Mobile search toggle ──
  const searchBtn = document.getElementById('mobile-search-btn');
  const mobileSearchBar = document.getElementById('mobile-search-bar');

  if (searchBtn && mobileSearchBar) {
    searchBtn.addEventListener('click', () => {
      mobileSearchBar.classList.toggle('hidden');
      if (!mobileSearchBar.classList.contains('hidden')) {
        const inp = mobileSearchBar.querySelector('input');
        if (inp) inp.focus();
      }
    });
  }

  // ── Header shadow on scroll ──
  const header = document.querySelector('header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('shadow-md', window.scrollY > 10);
    }, { passive: true });
  }
</script>

</body>
</html>