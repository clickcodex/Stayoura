<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow, noarchive" />
  <title><?= $pageTitle ?? 'Admin Login • Stayora Executive Console' ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/webp" href="<?= asset('assets/images/logo.webp') ?>" />
  <link rel="shortcut icon" href="<?= asset('assets/images/logo.webp') ?>" />

  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a',
            },
            dark: {
              900: '#060B19',
              850: '#0B132B',
              800: '#0F1E40',
              700: '#1A2952',
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
      background-color: #060B19;
      color: #E2E8F0;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .font-headline {
      font-family: 'Epilogue', sans-serif;
    }
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      line-height: 1;
    }
    /* Animated Gradient Background Orbs */
    .bg-orb-1 {
      position: absolute;
      width: 550px;
      height: 550px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(29, 78, 216, 0.28) 0%, rgba(15, 23, 42, 0) 70%);
      top: -120px;
      left: -120px;
      filter: blur(80px);
      pointer-events: none;
      animation: floatOrb 18s ease-in-out infinite alternate;
    }
    .bg-orb-2 {
      position: absolute;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(15, 23, 42, 0) 70%);
      bottom: -150px;
      right: -100px;
      filter: blur(90px);
      pointer-events: none;
      animation: floatOrb2 22s ease-in-out infinite alternate;
    }
    @keyframes floatOrb {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(80px, 60px) scale(1.1); }
    }
    @keyframes floatOrb2 {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(-60px, -70px) scale(1.15); }
    }
    /* Glassmorphism Panel */
    .glass-card {
      background: rgba(15, 23, 42, 0.78);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.65), 0 0 40px -10px rgba(29, 78, 216, 0.25);
    }
    .glass-input {
      background: rgba(11, 19, 43, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #FFFFFF;
      transition: all 0.2s ease;
    }
    .glass-input:focus {
      background: rgba(15, 26, 59, 0.95);
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.22);
      outline: none;
    }
    /* Pulse animation */
    .pulse-ring {
      animation: pulseDot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulseDot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: .4; transform: scale(1.2); }
    }
  </style>
</head>

<body class="relative flex items-center justify-center p-4 sm:p-6 md:p-10 select-none">

  <!-- Ambient Lighting Effects -->
  <div class="bg-orb-1"></div>
  <div class="bg-orb-2"></div>

  <!-- Background Subtle Grid Pattern -->
  <div class="fixed inset-0 pointer-events-none opacity-[0.03]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 28px 28px;"></div>

  <!-- Center Card Container -->
  <div class="relative w-full max-w-[460px] z-10 my-auto">

    <!-- Top Badge & Header Branding -->
    <div class="text-center mb-6">
      <a href="<?= url('') ?>" class="inline-flex items-center gap-3 group transition-transform duration-300 hover:scale-[1.02] text-decoration-none mb-3">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-400 p-0.5 shadow-lg shadow-brand-700/30 flex items-center justify-center">
          <div class="w-full h-full bg-dark-900 rounded-[14px] flex items-center justify-center overflow-hidden p-1.5">
            <img src="<?= asset('assets/images/logo.webp') ?>" alt="Stayora" class="w-full h-full object-contain" onerror="this.src='https://ui-avatars.com/api/?name=Stayora&background=1D4ED8&color=fff&bold=true';" />
          </div>
        </div>
        <div class="text-left">
          <span class="font-headline font-extrabold text-2xl text-white tracking-tight leading-none block">
            Stay<span class="text-brand-500">ora</span>
          </span>
          <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-1 block">
            Executive Operations
          </span>
        </div>
      </a>

      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-900/60 border border-brand-500/30 text-brand-300 text-[11px] font-semibold tracking-wide shadow-sm">
        <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-ring"></span>
        <span>SUPER ADMIN CONSOLE</span>
      </div>
    </div>

    <!-- Main Glassmorphic Login Card -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 relative">

      <!-- Card Title -->
      <div class="mb-6 text-center sm:text-left">
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-white tracking-tight">
          System Authentication
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
          Provide your administrative credentials to access platform controls.
        </p>
      </div>


      <!-- Login Form -->
      <form action="<?= url('admin/login-submit') ?>" method="POST" id="admin-login-form" class="space-y-4" onsubmit="handleAdminSubmit(event)">
        
        <!-- Email Input -->
        <div>
          <label for="admin-email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
            Admin Identity / Email
          </label>
          <div class="relative flex items-center">
            <span class="absolute left-3.5 text-slate-400 material-symbols-outlined text-lg pointer-events-none">mail</span>
            <input 
              type="email" 
              name="email" 
              id="admin-email" 
              required
              autocomplete="email"
              placeholder="admin@stayora.com" 
              value="<?= htmlspecialchars($_SESSION['admin_prefill_email'] ?? 'admin@stayora.com') ?>"
              class="glass-input w-full rounded-xl py-3 pl-11 pr-4 text-sm font-medium placeholder-slate-500"
            />
          </div>
        </div>

        <!-- Password Input with Show/Hide Toggle -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label for="admin-password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
              Secret Key / Password
            </label>
            <button type="button" onclick="openForgotModal()" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">
              Need assistance?
            </button>
          </div>
          <div class="relative flex items-center">
            <span class="absolute left-3.5 text-slate-400 material-symbols-outlined text-lg pointer-events-none">lock</span>
            <input 
              type="password" 
              name="password" 
              id="admin-password" 
              required
              autocomplete="current-password"
              placeholder="••••••••••••" 
              value="Admin@123"
              class="glass-input w-full rounded-xl py-3 pl-11 pr-11 text-sm font-medium placeholder-slate-500"
            />
            <button 
              type="button" 
              id="toggle-password-btn" 
              onclick="togglePasswordVisibility()" 
              class="absolute right-3 p-1 text-slate-400 hover:text-white transition-colors focus:outline-none"
              aria-label="Toggle password visibility">
              <span class="material-symbols-outlined text-lg" id="password-eye-icon">visibility</span>
            </button>
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input 
              type="checkbox" 
              name="remember" 
              id="admin-remember" 
              value="1" 
              checked
              class="w-4 h-4 rounded bg-dark-900 border-slate-700 text-brand-600 focus:ring-brand-500 focus:ring-offset-dark-900"
            />
            <span class="text-xs text-slate-300 font-medium">Remember terminal for 30 days</span>
          </label>

          <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            SSL 256-Bit
          </span>
        </div>

        <!-- Submit Button with Spinner -->
        <div class="pt-2">
          <button 
            type="submit" 
            id="admin-submit-btn"
            class="w-full py-3.5 px-6 rounded-xl font-headline font-bold text-sm tracking-wide text-white bg-gradient-to-r from-brand-700 via-brand-600 to-indigo-600 hover:from-brand-600 hover:to-indigo-500 active:scale-[0.99] transition-all duration-200 shadow-lg shadow-brand-700/30 flex items-center justify-center gap-2 cursor-pointer">
            <span id="submit-text">Verify &amp; Enter Console</span>
            <span class="material-symbols-outlined text-base transition-transform group-hover:translate-x-1" id="submit-icon">arrow_forward</span>
            <!-- Spinner (Hidden by default) -->
            <svg id="submit-spinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
          </button>
        </div>

      </form>

      <!-- Quick Demo Credentials Quick-Fill Card -->
      <div class="mt-6 pt-5 border-t border-slate-800/80">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
          <span>Quick Demo Access</span>
          <span class="text-[10px] text-brand-400 font-semibold cursor-pointer hover:underline" onclick="fillCredentials('admin@stayora.com', 'Admin@123')">Click to Autofill</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <button 
            type="button" 
            onclick="fillCredentials('admin@stayora.com', 'Admin@123')"
            class="p-2.5 rounded-xl bg-slate-900/90 hover:bg-brand-900/40 border border-slate-800 hover:border-brand-500/40 text-left transition-all group">
            <div class="text-xs font-bold text-white group-hover:text-brand-300">Stayora HQ</div>
            <div class="text-[10px] text-slate-400 truncate">admin@stayora.com</div>
          </button>

          <button 
            type="button" 
            onclick="fillCredentials('admin@gmail.com', 'Admin@123')"
            class="p-2.5 rounded-xl bg-slate-900/90 hover:bg-brand-900/40 border border-slate-800 hover:border-brand-500/40 text-left transition-all group">
            <div class="text-xs font-bold text-white group-hover:text-brand-300">Executive Demo</div>
            <div class="text-[10px] text-slate-400 truncate">admin@gmail.com</div>
          </button>
        </div>
      </div>

    </div><!-- /glass-card -->

    <!-- Security Badges Footer -->
    <div class="mt-6 text-center space-y-2">
      <div class="flex items-center justify-center gap-4 text-[11px] text-slate-400">
        <span class="flex items-center gap-1">
          <span class="material-symbols-outlined text-[14px] text-emerald-400">lock</span>
          <span>Encrypted Gateway</span>
        </span>
        <span>•</span>
        <span class="flex items-center gap-1">
          <span class="material-symbols-outlined text-[14px] text-brand-400">devices</span>
          <span>Single Active Terminal</span>
        </span>
      </div>

      <div>
        <a href="<?= url('') ?>" class="text-xs font-semibold text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm">west</span>
          <span>Return to Stayora Public Guest Website</span>
        </a>
      </div>
    </div>

  </div><!-- /max-w-[460px] -->

  <!-- Modal: Assistance / Recovery Info -->
  <div id="forgot-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-sm w-full rounded-2xl p-6 border border-slate-700 shadow-2xl relative">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-brand-400 text-xl">admin_panel_settings</span>
          <h3 class="font-headline font-bold text-white text-base">Administrative Access Help</h3>
        </div>
        <button type="button" onclick="closeForgotModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
      </div>
      <p class="text-xs text-slate-300 leading-relaxed mb-4">
        Administrative credential resets require authorization from the platform Super Administrator to ensure database integrity and security.
      </p>
      <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 text-[11px] text-slate-300 space-y-1 mb-5">
        <div><strong>Default Email:</strong> <code class="text-brand-300">admin@stayora.com</code></div>
        <div><strong>Default Password:</strong> <code class="text-brand-300">Admin@123</code></div>
        <div><strong>Contact:</strong> <span class="text-slate-400">devops@stayora.com</span></div>
      </div>
      <button type="button" onclick="closeForgotModal()" class="w-full py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white font-bold text-xs transition-colors">
        Got It, Return to Login
      </button>
    </div>
  </div>

  <script>
    // ── Toggle Password Visibility ──
    function togglePasswordVisibility() {
      const input = document.getElementById('admin-password');
      const icon = document.getElementById('password-eye-icon');
      if (!input || !icon) return;

      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        input.type = 'password';
        icon.textContent = 'visibility';
      }
    }

    // ── Autofill Credentials Helper ──
    function fillCredentials(email, pwd) {
      const emailInput = document.getElementById('admin-email');
      const pwdInput = document.getElementById('admin-password');
      if (emailInput && pwdInput) {
        emailInput.value = email;
        pwdInput.value = pwd;
        emailInput.focus();
        if (window.adminToast) {
          adminToast.success(`Autofilled credentials for ${email}`, 'Credentials Applied');
        }
      }
    }

    // ── Form Validation & Submit Animation ──
    function handleAdminSubmit(e) {
      const emailInput = document.getElementById('admin-email');
      const pwdInput = document.getElementById('admin-password');
      const email = (emailInput ? emailInput.value : '').trim();
      const password = pwdInput ? pwdInput.value : '';

      if (!email) {
        e.preventDefault();
        if (window.adminToast) {
          adminToast.error('Please enter your administrative email address.', 'Missing Identity');
        }
        if (emailInput) emailInput.focus();
        return false;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        if (window.adminToast) {
          adminToast.error('Please enter a valid administrative email address format.', 'Invalid Email Format');
        }
        if (emailInput) emailInput.focus();
        return false;
      }

      if (!password) {
        e.preventDefault();
        if (window.adminToast) {
          adminToast.error('Please enter your administrator account password.', 'Missing Password');
        }
        if (pwdInput) pwdInput.focus();
        return false;
      }

      const btn = document.getElementById('admin-submit-btn');
      const text = document.getElementById('submit-text');
      const icon = document.getElementById('submit-icon');
      const spinner = document.getElementById('submit-spinner');

      if (btn && text && icon && spinner) {
        btn.disabled = true;
        btn.classList.add('opacity-80', 'cursor-not-allowed');
        text.textContent = 'Authenticating Terminal...';
        icon.classList.add('hidden');
        spinner.classList.remove('hidden');
      }

      if (window.adminToast) {
        adminToast.info('Validating credentials against Stayora Gateway...', 'Authentication in Progress', 3000);
      }
      return true;
    }

    // ── Help Modal ──
    function openForgotModal() {
      const modal = document.getElementById('forgot-modal');
      if (modal) modal.classList.remove('hidden');
    }
    function closeForgotModal() {
      const modal = document.getElementById('forgot-modal');
      if (modal) modal.classList.add('hidden');
    }
  </script>

  <!-- Include Admin Toast Notification Component -->
  <?php require_once __DIR__ . '/toast.php'; ?>

</body>
</html>
