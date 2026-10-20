<?php
use App\Helpers\SiteHelper;

$settings = SiteHelper::getSettings();
$title = htmlspecialchars($settings['maintenance_notice_title'] ?? 'Scheduled System Maintenance');
$message = nl2br(htmlspecialchars($settings['maintenance_notice_message'] ?? 'Stayora is currently undergoing scheduled platform upgrades to serve you better. We will be back online shortly.'));
$estimatedEnd = !empty($settings['maintenance_estimated_end']) ? date('D, d M Y h:i A', strtotime($settings['maintenance_estimated_end'])) : null;
$phone = htmlspecialchars($settings['mobile_number'] ?? '+91-9111666415');
$email = htmlspecialchars($settings['email'] ?? 'contact@stayora.com');
$logoUrl = SiteHelper::getLogoUrl();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title><?= $title ?> — Stayora</title>
  <link rel="icon" type="image/png" href="<?= SiteHelper::getFaviconUrl() ?>" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { 700: '#1D4ED8', 600: '#2563EB', 500: '#3B82F6' }
          },
          fontFamily: {
            headline: ['Epilogue', 'sans-serif'],
            body: ['"Plus Jakarta Sans"', 'sans-serif']
          }
        }
      }
    }
  </script>
</head>
<body class="bg-slate-950 font-body text-slate-100 min-h-screen flex flex-col items-center justify-between p-6 antialiased selection:bg-brand-600 selection:text-white relative overflow-hidden">
  
  <!-- Subtle Ambient Glow -->
  <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-brand-700/20 blur-[130px] rounded-full pointer-events-none"></div>

  <!-- Header -->
  <header class="w-full max-w-4xl py-6 flex items-center justify-between border-b border-slate-800/80 z-10">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 p-1.5 flex items-center justify-center">
        <img src="<?= $logoUrl ?>" alt="Stayora" class="w-full h-full object-contain" onerror="this.src='https://ui-avatars.com/api/?name=Stayora&background=1D4ED8&color=fff&bold=true';" />
      </div>
      <div>
        <span class="font-headline font-black text-xl text-white tracking-tight leading-none">Stay<span class="text-brand-500">ora</span></span>
        <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5">Platform Status</span>
      </div>
    </div>
    
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
        Maintenance Mode
      </span>
    </div>
  </header>

  <!-- Main Card -->
  <main class="w-full max-w-xl my-auto py-12 z-10 text-center">
    <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-brand-700 to-sky-500 p-0.5 shadow-2xl shadow-brand-700/40 mb-8 flex items-center justify-center">
      <div class="w-full h-full bg-slate-900 rounded-[22px] flex items-center justify-center">
        <span class="material-symbols-outlined text-4xl text-brand-400">engineering</span>
      </div>
    </div>

    <h1 class="font-headline font-extrabold text-3xl sm:text-4xl text-white tracking-tight mb-4">
      <?= $title ?>
    </h1>

    <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-md mx-auto mb-8 font-medium">
      <?= $message ?>
    </p>

    <?php if ($estimatedEnd): ?>
      <div class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 mb-8">
        <span class="material-symbols-outlined text-base text-brand-400">schedule</span>
        <span>Expected Resumption: <strong class="text-white"><?= $estimatedEnd ?></strong></span>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md mx-auto text-left">
      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Direct Assistance</div>
        <a href="tel:<?= $phone ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors flex items-center gap-1.5">
          <span class="material-symbols-outlined text-base text-brand-400">call</span>
          <?= $phone ?>
        </a>
      </div>
      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Customer Concierge</div>
        <a href="mailto:<?= $email ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors flex items-center gap-1.5 truncate">
          <span class="material-symbols-outlined text-base text-brand-400">mail</span>
          <?= $email ?>
        </a>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="w-full max-w-4xl py-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-800/80 text-xs text-slate-400 z-10">
    <p>© <?= date('Y') ?> Stayora Luxury Farmstays &amp; Villas. All rights reserved.</p>
    <div class="flex items-center gap-4">
      <a href="<?= url('admin/login') ?>" class="text-slate-400 hover:text-white font-semibold transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">lock</span>
        Staff / Admin Login
      </a>
    </div>
  </footer>

</body>
</html>
