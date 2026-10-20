<?php 
// session_start();
use App\Helpers\SiteHelper;
use App\Helpers\SeoHelper;
$globalSite = SiteHelper::getSettings(); 

// Prepare SEO metadata — views can define $pageTitle, $pageDescription, $pageKeywords, $pageRobots, $canonicalUrl, $ogImage, $customSchema, etc.
$seoMetadata = SeoHelper::resolveMetadata([
    'title'         => $pageTitle ?? null,
    'description'   => $pageDescription ?? ($metaDescription ?? null),
    'keywords'      => $pageKeywords ?? ($metaKeywords ?? null),
    'robots'        => $pageRobots ?? null,
    'canonical'     => $canonicalUrl ?? null,
    'og_image'      => $ogImage ?? null,
    'og_image_alt'  => $ogImageAlt ?? null,
    'og_type'       => $ogType ?? null,
    'custom_schema' => $customSchema ?? null,
    'breadcrumbs'   => $breadcrumbs ?? null
]);
?>

<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
$role = strtolower(rtrim((string)($_SESSION['role'] ?? 'user'), 's'));

$dashboardUrl = url('dashboard');
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />

  <?php SeoHelper::renderMetaTags($seoMetadata); ?>

    <link rel="stylesheet" href="<?= asset('assets/css/style.css?v=16') ?>">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  
  <link rel="icon" type="image/png" href="<?= SiteHelper::getFaviconUrl() ?>"/>
  <link rel="shortcut icon" href="<?= SiteHelper::getFaviconUrl() ?>"/>
  <link rel="apple-touch-icon" href="<?= SiteHelper::getFaviconUrl() ?>"/>

  <!-- Google Fonts Preconnect & Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

  <?php SeoHelper::renderSchemaJsonLd($seoMetadata); ?>

<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          accent: "<?= !empty($globalSite['accent_theme_color']) ? htmlspecialchars($globalSite['accent_theme_color']) : '#C9A227' ?>",
          "accent-hover": "#B5911F",
          "accent-container": "#FDF8E8",
          "on-accent": "#ffffff",
          /* Core Theme Colors */
          primary: "<?= !empty($globalSite['primary_theme_color']) ? htmlspecialchars($globalSite['primary_theme_color']) : '#1D4ED8' ?>",
          "primary-container": "#1E40AF",
          "primary-fixed": "#3B82F6",
          "on-primary": "#ffffff",
          "on-primary-container": "#ffffff",

          secondary: "<?= !empty($globalSite['secondary_theme_color']) ? htmlspecialchars($globalSite['secondary_theme_color']) : '#0284C7' ?>",
          "secondary-container": "#E0F2FE",
          "on-secondary": "#ffffff",
          "on-secondary-container": "#0A4D66",

          surface: "#FFFFFF",
          background: "#FFFFFF",

          "surface-container-lowest": "#FFFFFF",
          "surface-container-low": "#F7F7F7",
          "surface-container": "#EFEFEF",
          "surface-container-high": "#E8E8E8",

          "surface-variant": "#E3E3E3",

          "on-surface": "#0F172A",
          "on-background": "#0F172A",
          "on-surface-variant": "#64748B",

          outline: "#9AA4AA",
          "outline-variant": "#E2E8F0",

          error: "#BA1A1A",
          "on-error": "#FFFFFF",

          "surface-tint": "#1D4ED8",

          /* Extended Brand Colors */
          "brand-emerald": {
            50: "#f0fdf6",
            100: "#e1faed",
            200: "#bcf2d6",
            300: "#8be5b7",
            400: "#52d094",
            500: "#23a871",
            600: "#1b9b65",
          },
        },

        fontFamily: {
          headline: ["Epilogue", "sans-serif"],
          body: ["Plus Jakarta Sans", "sans-serif"],
        },
      },
    },
  };
</script>

<style>
/* ══════════════════════════════════════════════════════════
   STAYORA DYNAMIC HEADER — TRANSPARENT BLUR & SCROLL TO WHITE
   ══════════════════════════════════════════════════════════ */
#main-site-header {
  transition: background-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              border-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              box-shadow 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              backdrop-filter 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── STATE 1: TRANSPARENT BLUR (At Top of Page Over Hero) ── */
#main-site-header.is-transparent {
  background: rgba(15, 23, 42, 0.28) !important;
  backdrop-filter: blur(16px) saturate(180%) !important;
  -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.14) !important;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12) !important;
}

#main-site-header.is-transparent .header-logo-light { display: none !important; }
#main-site-header.is-transparent .header-logo-dark { display: block !important; }

#main-site-header.is-transparent .header-brand-title { color: #FFFFFF !important; }
#main-site-header.is-transparent .header-brand-accent { color: #38BDF8 !important; }

#main-site-header.is-transparent .header-nav-link {
  color: rgba(255, 255, 255, 0.9) !important;
}
#main-site-header.is-transparent .header-nav-link:hover {
  color: #FFFFFF !important;
  background: rgba(255, 255, 255, 0.14) !important;
}

#main-site-header.is-transparent .nav-search {
  background: rgba(255, 255, 255, 0.15) !important;
  border-color: rgba(255, 255, 255, 0.25) !important;
}
#main-site-header.is-transparent .nav-search input {
  color: #FFFFFF !important;
}
#main-site-header.is-transparent .nav-search input::placeholder {
  color: rgba(255, 255, 255, 0.7) !important;
}
#main-site-header.is-transparent .nav-search svg {
  stroke: rgba(255, 255, 255, 0.85) !important;
}
#main-site-header.is-transparent .nav-search:focus-within {
  border-color: #38BDF8 !important;
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
}

#main-site-header.is-transparent .header-btn-list {
  border: 1.5px solid rgba(255, 255, 255, 0.85) !important;
  color: #FFFFFF !important;
  background: rgba(255, 255, 255, 0.08) !important;
}
#main-site-header.is-transparent .header-btn-list:hover {
  background: #FFFFFF !important;
  color: #1D4ED8 !important;
  border-color: #FFFFFF !important;
}

#main-site-header.is-transparent .header-divider {
  background: rgba(255, 255, 255, 0.2) !important;
}

#main-site-header.is-transparent .header-btn-login {
  color: #FFFFFF !important;
}
#main-site-header.is-transparent .header-btn-login:hover {
  color: #38BDF8 !important;
  background: rgba(255, 255, 255, 0.12) !important;
}

#main-site-header.is-transparent .india-badge {
  background: rgba(255, 255, 255, 0.14) !important;
  border-color: rgba(255, 255, 255, 0.22) !important;
  backdrop-filter: blur(8px);
}
#main-site-header.is-transparent .india-badge .badge-text div:last-child {
  color: rgba(255, 255, 255, 0.88) !important;
}

#main-site-header.is-transparent .header-mobile-btn {
  background: rgba(255, 255, 255, 0.15) !important;
  border-color: rgba(255, 255, 255, 0.25) !important;
  color: #FFFFFF !important;
}
#main-site-header.is-transparent .header-mobile-btn span,
#main-site-header.is-transparent .header-mobile-btn svg {
  color: #FFFFFF !important;
  stroke: #FFFFFF !important;
}

#main-site-header.is-transparent .header-mobile-list-btn {
  background: rgba(255, 255, 255, 0.15) !important;
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: #FFFFFF !important;
}

#main-site-header.is-transparent .header-user-btn {
  color: #FFFFFF !important;
  border-color: rgba(255, 255, 255, 0.25) !important;
  background: rgba(255, 255, 255, 0.12) !important;
}
#main-site-header.is-transparent .header-user-btn .header-user-name {
  color: #FFFFFF !important;
}

/* ── STATE 2: SOLID WHITE (When Scrolled Down) ── */
#main-site-header.is-scrolled {
  background: rgba(255, 255, 255, 0.98) !important;
  backdrop-filter: blur(14px) !important;
  -webkit-backdrop-filter: blur(14px) !important;
  border-bottom: 1px solid rgba(226, 232, 240, 0.85) !important;
  box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08) !important;
}

#main-site-header.is-scrolled .header-logo-light { display: block !important; }
#main-site-header.is-scrolled .header-logo-dark { display: none !important; }

#main-site-header.is-scrolled .header-brand-title { color: #0F172A !important; }
#main-site-header.is-scrolled .header-brand-accent { color: #1D4ED8 !important; }

#main-site-header.is-scrolled .header-nav-link {
  color: #334155 !important;
}
#main-site-header.is-scrolled .header-nav-link:hover {
  color: #1D4ED8 !important;
  background: rgba(29, 78, 216, 0.06) !important;
}

#main-site-header.is-scrolled .nav-search {
  background: #F8FAFC !important;
  border: 1.5px solid #E2E8F0 !important;
}
#main-site-header.is-scrolled .nav-search input {
  color: #0F172A !important;
}
#main-site-header.is-scrolled .nav-search input::placeholder {
  color: #94A3B8 !important;
}
#main-site-header.is-scrolled .nav-search svg {
  stroke: #94A3B8 !important;
}
#main-site-header.is-scrolled .nav-search:focus-within {
  border-color: #1D4ED8 !important;
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12) !important;
}

#main-site-header.is-scrolled .header-btn-list {
  border: 2px solid #1D4ED8 !important;
  color: #1D4ED8 !important;
  background: transparent !important;
}
#main-site-header.is-scrolled .header-btn-list:hover {
  background: #1D4ED8 !important;
  color: #FFFFFF !important;
}

#main-site-header.is-scrolled .header-divider {
  background: #E2E8F0 !important;
}

#main-site-header.is-scrolled .header-btn-login {
  color: #334155 !important;
}
#main-site-header.is-scrolled .header-btn-login:hover {
  color: #1D4ED8 !important;
  background: rgba(29, 78, 216, 0.05) !important;
}

#main-site-header.is-scrolled .india-badge {
  background: #FFFFFF !important;
  border-color: #E2E8F0 !important;
}
#main-site-header.is-scrolled .india-badge .badge-text div:last-child {
  color: #555555 !important;
}

#main-site-header.is-scrolled .header-mobile-btn {
  background: #F1F5F9 !important;
  border-color: #E2E8F0 !important;
  color: #0F172A !important;
}
#main-site-header.is-scrolled .header-mobile-btn span,
#main-site-header.is-scrolled .header-mobile-btn svg {
  color: #0F172A !important;
  stroke: #0F172A !important;
}

#main-site-header.is-scrolled .header-mobile-list-btn {
  background: rgba(29, 78, 216, 0.1) !important;
  border-color: rgba(29, 78, 216, 0.3) !important;
  color: #1D4ED8 !important;
}

#main-site-header.is-scrolled .header-user-btn {
  color: #334155 !important;
  border-color: #E2E8F0 !important;
  background: #F8FAFC !important;
}
#main-site-header.is-scrolled .header-user-btn .header-user-name {
  color: #0F172A !important;
}
</style>
</head>

<body class="bg-background text-on-background antialiased">

<!-- ═══════════════════════════ HEADER ═══════════════════════════ -->
<header id="main-site-header" class="main-site-header fixed top-0 left-0 right-0 z-50 transition-all duration-300 is-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 h-[72px] flex items-center justify-between gap-4">

    <!-- ── Logo ── -->
    <a href="<?= url('home') ?>" class="flex items-center gap-2.5 shrink-0 group transition-transform hover:scale-[1.03]">
      <img src="<?= asset('assets/images/logo.webp') ?>" alt="Stayora" class="header-logo-light h-9 w-auto object-contain">
      <img src="<?= asset('assets/images/logo-dark.webp') ?>" alt="Stayora" class="header-logo-dark h-9 w-auto object-contain hidden">
      <span class="header-brand-title font-headline font-bold text-xl tracking-tight leading-none transition-colors">
        Stay<span class="header-brand-accent transition-colors">ora</span>
      </span>
    </a>

    <!-- ── Made in India badge (desktop only) ── -->
    <div class="india-badge hidden xl:flex shrink-0">
      <span class="text-xl leading-none">🇮🇳</span>
      <div class="badge-text">
        <div style="color:#f47920;font-size:9px;font-weight:800;letter-spacing:.5px;">#STARTUPINDIA</div>
        <div style="font-size:9px;font-weight:700;color:#555;letter-spacing:.4px;">MADE IN INDIA</div>
      </div>
    </div>

    <!-- ── Search Bar (desktop) ── -->
    <form method="GET" action="<?= url('farmhouses') ?>" class="nav-search header-search hidden md:flex flex-1 max-w-xs lg:max-w-sm transition-all" id="nav-search-form">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9AA4AA" stroke-width="2.5">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" name="search" placeholder="Search farmhouses…" autocomplete="off"/>
    </form>

    <!-- ── Desktop Nav Links ── -->
    <nav class="hidden lg:flex items-center gap-1 shrink-0">
      <a href="<?= url('farmhouses') ?>"    class="header-nav-link px-3 py-2 text-sm font-semibold rounded-lg transition-all">Farms</a>
      <a href="<?= url('why-choose-us') ?>" class="header-nav-link px-3 py-2 text-sm font-semibold rounded-lg transition-all">Why Choose Us</a>
      <a href="<?= url('about') ?>"         class="header-nav-link px-3 py-2 text-sm font-semibold rounded-lg transition-all">About</a>
      <a href="<?= url('contact') ?>"       class="header-nav-link px-3 py-2 text-sm font-semibold rounded-lg transition-all">Contact</a>
    </nav>

    <!-- ── Desktop CTA + Auth ── -->
    <div class="hidden lg:flex items-center gap-3 shrink-0">
      <?php if ($isLoggedIn): ?>
        <!-- Logged-in state with rich user dropdown -->
        <div class="relative group" id="user-menu-dropdown-wrap">
          <button type="button" class="header-user-btn flex items-center gap-2.5 px-3 py-1.5 rounded-full transition-all border border-transparent">
            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white shadow-xs overflow-hidden shrink-0">
              <?php 
                $imgName = $_SESSION['profile_image'] ?? '';
                $avatarUrl = '';
                if (!empty($imgName)) {
                    $avatarUrl = asset('assets/images/uploads/profiles/' . htmlspecialchars($imgName));
                }
              ?>
              <?php if (!empty($avatarUrl)): ?>
                <img src="<?= $avatarUrl ?>"
                     alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'">
              <?php else: ?>
                <span class="material-symbols-outlined text-sm">person</span>
              <?php endif; ?>
            </div>
            <div class="text-left">
              <span class="text-xs font-black text-slate-800 group-hover:text-primary block leading-tight transition-colors">
                <?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'User')[0]) ?>
              </span>
              <span class="text-[10px] font-bold text-primary block leading-tight uppercase tracking-wider">
                My Account
              </span>
            </div>
            <span class="material-symbols-outlined text-slate-400 text-sm group-hover:rotate-180 transition-transform duration-200">expand_more</span>
          </button>

          <!-- Dropdown Menu -->
          <div class="absolute right-0 top-full pt-2 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 transform origin-top-right">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl p-2 flex flex-col gap-1">
              <!-- User Info Bar -->
              <div class="p-3 bg-slate-50 rounded-xl mb-1 border border-slate-100">
                <div class="text-xs font-black text-slate-900 truncate">
                  <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                </div>
                <div class="text-[11px] text-slate-500 font-medium truncate">
                  <?= htmlspecialchars($_SESSION['user_email'] ?? $_SESSION['email'] ?? '') ?>
                </div>
              </div>

              <!-- Navigation links -->
              <a href="<?= url('dashboard') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-primary hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined text-[18px] text-primary">grid_view</span>
                <span>Dashboard Overview</span>
              </a>

              <a href="<?= url('user/my-bookings') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-primary hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined text-[18px] text-primary">calendar_month</span>
                <span>My Bookings</span>
              </a>

              <a href="<?= url('my-wishlist') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-primary hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined text-[18px] text-pink-500" style="font-variation-settings:'FILL' 1;">favorite</span>
                <span>Saved Farmhouses</span>
              </a>

              <a href="<?= url('user/profile') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-primary hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined text-[18px] text-primary">manage_accounts</span>
                <span>Profile &amp; Settings</span>
              </a>

              <div class="h-px bg-slate-100 my-1"></div>

              <a href="<?= url('contact') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-primary hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined text-[18px] text-slate-400">support_agent</span>
                <span>Concierge Support</span>
              </a>

              <div class="h-px bg-slate-100 my-1"></div>

              <a href="<?= url('logout') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 transition-all">
                <span class="material-symbols-outlined text-[18px] text-red-500">logout</span>
                <span>Sign Out</span>
              </a>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- Guest state -->
        <a href="<?= url('login') ?>"
           class="header-btn-login px-4 py-2 text-sm font-bold rounded-full transition-colors">
          Log In
        </a>
        <a href="<?= url('register') ?>"
           class="px-5 py-2 text-sm font-bold bg-primary text-white rounded-full shadow-sm shadow-primary/25 hover:bg-primary-container transition-all">
          Sign Up
        </a>
      <?php endif; ?>
    </div>

    <!-- ── Mobile Right Controls ── -->
    <div class="flex items-center gap-2 lg:hidden shrink-0">
      <!-- Mobile search toggle -->
      <button id="mobile-search-btn" type="button"
              class="header-mobile-btn w-9 h-9 rounded-xl active:scale-95 flex items-center justify-center transition-all border shadow-2xs"
              aria-label="Search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
      </button>


      <?php if ($isLoggedIn): ?>
        <!-- User avatar shortcut on mobile -->
        <a href="<?= $dashboardUrl ?>" class="w-8 h-8 rounded-full border border-primary/50 overflow-hidden shrink-0 flex items-center justify-center bg-primary text-white shadow-2xs">
          <?php if (!empty($_SESSION['profile_image'])): ?>
            <img src="<?= asset('assets/images/uploads/avatars/' . htmlspecialchars($_SESSION['profile_image'])) ?>"
                 alt="Profile" class="w-full h-full object-cover">
          <?php else: ?>
            <span class="material-symbols-outlined text-sm">person</span>
          <?php endif; ?>
        </a>
      <?php endif; ?>

      <!-- Hamburger Button (Light Style) -->
      <button id="mobile-menu-btn" type="button" onclick="toggleMobileNav()"
              class="header-mobile-btn w-9 h-9 rounded-xl active:scale-95 flex items-center justify-center transition-all border shadow-2xs"
              aria-label="Menu" aria-expanded="false">
        <span class="material-symbols-outlined text-[20px] text-slate-800 leading-none" id="menu-icon">menu</span>
      </button>
    </div>

  </div><!-- /max-w-7xl -->

  <!-- ── Mobile Search Bar (revealed on tap) ── -->
  <div id="mobile-search-bar" class="hidden px-4 pb-3 pt-1 bg-white border-b border-slate-200">
    <form method="GET" action="<?= url('farmhouses') ?>" class="w-full relative flex items-center">
      <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" name="search" placeholder="Search farmhouses by city, area, name…" 
             class="w-full bg-slate-100 border border-slate-200 text-slate-800 placeholder-slate-400 text-sm rounded-xl py-2 pl-10 pr-4 focus:outline-none focus:border-primary focus:bg-white transition-all" autocomplete="off"/>
    </form>
  </div>


  <!-- ── Dynamic Header Scroll Controller (Transparent Blur to Solid White) ── -->
  <script>
  (function() {
    function updateStayoraHeader() {
      var header = document.getElementById('main-site-header');
      if (!header) return;

      var hasDarkHero = document.querySelector('.hm-hero-wrap, .wcu-hero, .abt-hero, .contact-hero, .lyf-hero, .legal-hero, [data-hero-dark]');
      var scrollY = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || 0;

      if (hasDarkHero) {
        if (scrollY > 30) {
          header.classList.add('is-scrolled');
          header.classList.remove('is-transparent');
        } else {
          header.classList.add('is-transparent');
          header.classList.remove('is-scrolled');
        }
      } else {
        header.classList.add('is-scrolled');
        header.classList.remove('is-transparent');
      }
    }

    window.addEventListener('scroll', updateStayoraHeader, { passive: true });
    document.addEventListener('DOMContentLoaded', updateStayoraHeader);
    updateStayoraHeader();
  })();
  </script>

</header>

<!-- ══════════════════════════════════════════════
     GLOBAL MOBILE NAVIGATION SIDEBAR DRAWER
     ══════════════════════════════════════════════ -->
<div id="mob-nav-overlay" class="mob-nav-overlay" onclick="closeMobileNav()"></div>
<aside id="mob-nav-drawer" class="mob-nav-drawer" aria-label="Mobile Navigation">
  <div class="mob-nav-header">
    <a href="<?= url('home') ?>" class="flex items-center gap-2.5">
      <img src="<?= asset('assets/images/logo.webp') ?>" alt="Stayora Logo" class="h-9 w-auto object-contain">
      <span class="font-headline font-bold text-lg tracking-tight text-slate-900 leading-none">
        Stay<span class="text-primary">ora</span>
      </span>
    </a>
    <button type="button" class="mob-nav-close" onclick="closeMobileNav()" aria-label="Close menu">✕</button>
  </div>

  <div class="mob-nav-body">
    <?php if ($isLoggedIn): ?>
      <!-- User Profile Card -->
      <div class="mob-nav-user-card">
        <div class="w-11 h-11 rounded-full bg-primary flex items-center justify-center text-white shadow overflow-hidden shrink-0">
          <?php if (!empty($_SESSION['profile_image'])): ?>
            <img src="<?= asset('assets/images/uploads/avatars/' . htmlspecialchars($_SESSION['profile_image'])) ?>" alt="Profile" class="w-full h-full object-cover">
          <?php else: ?>
            <span class="material-symbols-outlined" style="font-size:22px;">person</span>
          <?php endif; ?>
        </div>
        <div style="min-width:0;flex:1;">
          <div style="font-size:14px;font-weight:800;color:#0F172A;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
          </div>
          <div style="font-size:11px;color:#0284C7;font-weight:700;text-transform:uppercase;">
            Guest Account
          </div>
        </div>
      </div>

      <!-- ACCOUNT SECTION -->
      <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px;margin-top:4px;">
        My Account
      </div>
      <nav class="mob-nav-links">
        <a href="<?= url('dashboard') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">grid_view</span>
          <span>Dashboard Overview</span>
        </a>
        <a href="<?= url('user/my-bookings') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">calendar_month</span>
          <span>My Bookings</span>
        </a>
        <a href="<?= url('my-wishlist') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined" style="color:#ec4899;font-variation-settings:'FILL' 1;">favorite</span>
          <span>Saved Wishlist</span>
        </a>
        <a href="<?= url('user/profile') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">manage_accounts</span>
          <span>Profile &amp; Settings</span>
        </a>
      </nav>

      <div style="height:1px;background:#f1f5f9;margin:4px 0;"></div>

      <!-- DISCOVER SECTION -->
      <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px;">
        Discover &amp; Support
      </div>
      <nav class="mob-nav-links">
        <a href="<?= url('home') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">home</span>
          <span>Home Page</span>
        </a>
        <a href="<?= url('farmhouses') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">villa</span>
          <span>Explore Farmhouses</span>
        </a>
        <a href="<?= url('why-choose-us') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">military_tech</span>
          <span>Why Choose Us</span>
        </a>
        <a href="<?= url('about') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">info</span>
          <span>About Us</span>
        </a>
        <a href="<?= url('contact') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">support_agent</span>
          <span>Contact Concierge</span>
        </a>
      </nav>

      <a href="<?= url('logout') ?>" class="mob-nav-link" style="color:#ef4444;margin-top:auto;justify-content:center;background:#fef2f2;border:1px solid #fee2e2;border-radius:12px;">
        <span class="material-symbols-outlined" style="color:#ef4444;">logout</span>
        <span>Sign Out</span>
      </a>

    <?php else: ?>
      <!-- Guest Navigation -->
      <nav class="mob-nav-links">
        <a href="<?= url('home') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">home</span>
          <span>Home</span>
        </a>
        <a href="<?= url('farmhouses') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">villa</span>
          <span>Explore Farmhouses</span>
        </a>
        <a href="<?= url('why-choose-us') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">military_tech</span>
          <span>Why Choose Us</span>
        </a>
        <a href="<?= url('blogs') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">article</span>
          <span>Travel Stories &amp; Blogs</span>
        </a>
        <a href="<?= url('about') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">info</span>
          <span>About Us</span>
        </a>
        <a href="<?= url('contact') ?>" class="mob-nav-link">
          <span class="material-symbols-outlined">support_agent</span>
          <span>Contact &amp; Support</span>
        </a>
      </nav>

      <div class="mob-nav-auth-grid">
        <a href="<?= url('login') ?>" class="mob-nav-auth-btn mob-nav-login-btn">Log In</a>
        <a href="<?= url('register') ?>" class="mob-nav-auth-btn mob-nav-signup-btn">Sign Up</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="mob-nav-footer">
    <span>🇮🇳</span>
    <span style="font-size:11px;font-weight:700;color:#f47920;">#StartupIndia</span>
    <span>• Made in India</span>
  </div>
</aside>