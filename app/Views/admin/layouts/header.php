<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle   = $pageTitle ?? 'Stayora Executive Console';
$currentPage = $currentPage ?? 'dashboard';
$adminName   = htmlspecialchars($admin['name'] ?? $_SESSION['user_name'] ?? 'Administrator');
$adminEmail  = htmlspecialchars($admin['email'] ?? $_SESSION['user_email'] ?? 'admin@stayora.com');
$adminAvatar = !empty($admin['profile_image']) ? asset('assets/images/uploads/avatars/' . htmlspecialchars($admin['profile_image'])) : '';
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow, noarchive" />
  <title><?= $pageTitle ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= \App\Helpers\SiteHelper::getFaviconUrl() ?>" />
  <link rel="shortcut icon" href="<?= \App\Helpers\SiteHelper::getFaviconUrl() ?>" />

  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS & Chart.js -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#eff6ff',
              100: '#dbeafe',
              200: '#bfdbfe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8', // Stayora Royal Blue
              800: '#1e40af',
              900: '#1e3a8a',
            },
            slateDark: {
              900: '#070C1E',
              850: '#0B132B',
              800: '#0F1A3A',
              700: '#172554',
            }
          },
          fontFamily: {
            headline: ['Epilogue', 'sans-serif'],
            body: ['"Plus Jakarta Sans"', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .font-headline {
      font-family: 'Epilogue', sans-serif;
    }
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F1F5F9;
    }
    ::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94A3B8;
    }
    #admin-sidebar {
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .dashboard-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dashboard-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.08), 0 4px 10px -2px rgba(15, 23, 42, 0.04);
    }
  </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased flex min-h-screen">

  <!-- Include Shared Sidebar Navigation -->
  <?php require __DIR__ . '/sidebar.php'; ?>

  <!-- ══════════════════════════════════════════════
       MAIN CONTENT WRAPPER
       ══════════════════════════════════════════════ -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    <!-- Top Navigation Bar -->
    <header class="h-20 bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-8 flex items-center justify-between shadow-2xs">
      
      <!-- Left side: Hamburger & Title -->
      <div class="flex items-center gap-3 sm:gap-4">
        <button type="button" onclick="toggleAdminSidebar()" class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Toggle Navigation">
          <span class="material-symbols-outlined text-2xl">menu</span>
        </button>

        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-headline font-extrabold text-lg sm:text-xl text-slate-900 tracking-tight leading-none">
              <?= $headerTitle ?? ($currentPage === 'properties' ? 'Manage Properties' : 'Executive Dashboard') ?>
            </h1>
            <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              Live Node
            </span>
          </div>
          <p class="text-[11px] text-slate-500 font-medium hidden sm:block mt-1">
            Stayora Platform Operations &amp; Verification Terminal
          </p>
        </div>
      </div>

      <!-- Right side controls -->
      <div class="flex items-center gap-2 sm:gap-4">
        
        <!-- Live System Clock -->
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100/80 border border-slate-200 text-xs font-semibold text-slate-700">
          <span class="material-symbols-outlined text-sm text-slate-500">schedule</span>
          <span id="live-clock"><?= date('D, M j, Y • H:i:s') ?></span>
        </div>

        <!-- View Live Site Button -->
        <a href="<?= url('') ?>" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
          <span class="material-symbols-outlined text-sm text-slate-500">visibility</span>
          <span>View Site</span>
        </a>

        <!-- Notifications Bell -->
        <div class="relative group">
          <button type="button" onclick="handleNotificationBell()" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors relative cursor-pointer" aria-label="Notifications">
            <span class="material-symbols-outlined text-xl">notifications</span>
            <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-brand-600 ring-2 ring-white"></span>
          </button>
        </div>

        <!-- Admin Profile Mini-Dropdown -->
        <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-200">
          <div class="w-9 h-9 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center shadow-xs overflow-hidden shrink-0">
            <?php if (!empty($adminAvatar)): ?>
              <img src="<?= $adminAvatar ?>" alt="Avatar" class="w-full h-full object-cover">
            <?php else: ?>
              <span><?= strtoupper(substr($adminName, 0, 1)) ?></span>
            <?php endif; ?>
          </div>
          <div class="hidden xl:block text-left">
            <span class="text-xs font-bold text-slate-900 block leading-tight truncate max-w-[130px]">
              <?= $adminName ?>
            </span>
            <span class="text-[10px] text-slate-400 font-semibold block">Super Admin</span>
          </div>
        </div>

      </div>

    </header>

    <!-- Main Content Area Starts -->
    <main class="flex-1 p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8 overflow-y-auto max-w-[1600px] w-full mx-auto">
