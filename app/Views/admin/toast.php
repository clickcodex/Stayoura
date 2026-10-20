<?php
/**
 * Stayora Admin Toast Notification System
 * Reusable, reactive, and responsive toast notification component for the Admin Panel.
 * Handles both PHP session flash messages and dynamic JavaScript notifications.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

$toastFlashSuccess = $_SESSION['success'] ?? null;
$toastFlashError   = $_SESSION['error'] ?? null;
$toastFlashWarning = $_SESSION['warning'] ?? null;
$toastFlashInfo    = $_SESSION['info'] ?? null;

// Clear session flashes so they are not shown again on refresh
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['warning'], $_SESSION['info']);
?>

<!-- ══════════════════════════════════════════════════════════════
     STAYORA ADMIN TOAST NOTIFICATION CONTAINER
     ══════════════════════════════════════════════════════════════ -->
<div 
  id="admin-toast-container" 
  class="fixed top-4 right-4 left-4 sm:left-auto sm:right-6 sm:top-6 z-[99999] flex flex-col gap-3 pointer-events-none max-w-none sm:max-w-md w-auto sm:w-full select-none"
  aria-live="polite" 
  aria-atomic="true">
</div>

<style>
  /* Toast Animation Keyframes */
  @keyframes toastSlideIn {
    0% {
      opacity: 0;
      transform: translateY(-20px) scale(0.96);
    }
    100% {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  @keyframes toastSlideOut {
    0% {
      opacity: 1;
      transform: translateY(0) scale(1);
      max-height: 120px;
      margin-bottom: 0.75rem;
    }
    100% {
      opacity: 0;
      transform: translateX(100%) scale(0.92);
      max-height: 0;
      margin-bottom: 0;
      padding-top: 0;
      padding-bottom: 0;
    }
  }

  .admin-toast-item {
    animation: toastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .admin-toast-item.toast-hiding {
    animation: toastSlideOut 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
  }

  .toast-progress-bar {
    transition: width linear;
  }
</style>

<script>
/**
 * Stayora Admin Toast Controller
 * Global API:
 *   adminToast.success(message, title, duration)
 *   adminToast.error(message, title, duration)
 *   adminToast.warning(message, title, duration)
 *   adminToast.info(message, title, duration)
 *   showToast(message, type, duration, title)
 */
(function() {
  const container = document.getElementById('admin-toast-container');

  const TOAST_THEMES = {
    success: {
      bg: 'bg-slate-900/95',
      border: 'border-emerald-500/40',
      badgeBg: 'bg-emerald-500/15',
      badgeBorder: 'border-emerald-500/30',
      iconColor: 'text-emerald-400',
      icon: 'check_circle',
      titleColor: 'text-emerald-400',
      defaultTitle: 'Success',
      progressBg: 'bg-emerald-500',
      glow: 'shadow-lg shadow-emerald-950/40'
    },
    error: {
      bg: 'bg-slate-900/95',
      border: 'border-rose-500/40',
      badgeBg: 'bg-rose-500/15',
      badgeBorder: 'border-rose-500/30',
      iconColor: 'text-rose-400',
      icon: 'error',
      titleColor: 'text-rose-400',
      defaultTitle: 'Error Detected',
      progressBg: 'bg-rose-500',
      glow: 'shadow-lg shadow-rose-950/40'
    },
    warning: {
      bg: 'bg-slate-900/95',
      border: 'border-amber-500/40',
      badgeBg: 'bg-amber-500/15',
      badgeBorder: 'border-amber-500/30',
      iconColor: 'text-amber-400',
      icon: 'warning',
      titleColor: 'text-amber-400',
      defaultTitle: 'Attention',
      progressBg: 'bg-amber-500',
      glow: 'shadow-lg shadow-amber-950/40'
    },
    info: {
      bg: 'bg-slate-900/95',
      border: 'border-blue-500/40',
      badgeBg: 'bg-blue-500/15',
      badgeBorder: 'border-blue-500/30',
      iconColor: 'text-blue-400',
      icon: 'info',
      titleColor: 'text-blue-400',
      defaultTitle: 'System Notification',
      progressBg: 'bg-blue-500',
      glow: 'shadow-lg shadow-blue-950/40'
    }
  };

  function createToast(message, type = 'success', duration = 4500, title = null) {
    if (!container) return;
    const theme = TOAST_THEMES[type] || TOAST_THEMES.info;
    const displayTitle = title || theme.defaultTitle;

    const toast = document.createElement('div');
    toast.className = `admin-toast-item pointer-events-auto relative overflow-hidden rounded-2xl ${theme.bg} ${theme.border} border backdrop-blur-xl p-4 text-slate-100 ${theme.glow} flex flex-col gap-2`;

    toast.innerHTML = `
      <div class="flex items-start gap-3">
        <!-- Status Icon -->
        <div class="w-8 h-8 rounded-xl ${theme.badgeBg} ${theme.badgeBorder} border flex items-center justify-center shrink-0 mt-0.5">
          <span class="material-symbols-outlined ${theme.iconColor} text-lg">${theme.icon}</span>
        </div>

        <!-- Content Details -->
        <div class="flex-1 min-w-0 pr-2">
          <div class="flex items-center gap-2">
            <h4 class="font-headline font-bold text-xs ${theme.titleColor} tracking-wide uppercase leading-none">
              ${displayTitle}
            </h4>
            <span class="text-[10px] text-slate-400 font-mono">Just now</span>
          </div>
          <p class="text-xs text-slate-300 mt-1 leading-relaxed font-normal break-words">
            ${message}
          </p>
        </div>

        <!-- Close Button -->
        <button 
          type="button" 
          class="toast-close-btn p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors shrink-0" 
          aria-label="Dismiss notification">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>

      <!-- Live Countdown Progress Bar -->
      <div class="w-full h-1 bg-slate-800 rounded-full overflow-hidden mt-1">
        <div class="toast-progress-bar h-full ${theme.progressBg} rounded-full w-full"></div>
      </div>
    `;

    container.appendChild(toast);

    // Audio Haptic (Optional Subtle UI Click / Tone if allowed)
    try {
      if (window.AudioContext || window.webkitAudioContext) {
        // Silent or clean tone if needed, no external files
      }
    } catch (e) {}

    const progressBar = toast.querySelector('.toast-progress-bar');
    const closeBtn = toast.querySelector('.toast-close-btn');

    let remainingTime = duration;
    let startTime = Date.now();
    let isPaused = false;
    let timerId = null;

    function startTimer() {
      if (progressBar) {
        progressBar.style.transitionDuration = `${remainingTime}ms`;
        // Trigger reflow then set width to 0
        void progressBar.offsetWidth;
        progressBar.style.width = '0%';
      }

      timerId = setTimeout(() => {
        dismissToast(toast);
      }, remainingTime);
    }

    function pauseTimer() {
      if (isPaused) return;
      isPaused = true;
      clearTimeout(timerId);
      const elapsed = Date.now() - startTime;
      remainingTime = Math.max(0, remainingTime - elapsed);

      if (progressBar) {
        const computedWidth = window.getComputedStyle(progressBar).width;
        progressBar.style.transitionDuration = '0ms';
        progressBar.style.width = computedWidth;
      }
    }

    function resumeTimer() {
      if (!isPaused || remainingTime <= 0) return;
      isPaused = false;
      startTime = Date.now();
      startTimer();
    }

    // Hover to Pause
    toast.addEventListener('mouseenter', pauseTimer);
    toast.addEventListener('mouseleave', resumeTimer);

    // Close button click
    closeBtn.addEventListener('click', () => {
      dismissToast(toast);
    });

    // Start auto-dismiss
    startTimer();

    return toast;
  }

  function dismissToast(toast) {
    if (!toast || toast.classList.contains('toast-hiding')) return;
    toast.classList.add('toast-hiding');
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 360);
  }

  // Define public global object
  window.adminToast = {
    show: (msg, type = 'info', duration = 4500, title = null) => createToast(msg, type, duration, title),
    success: (msg, title = 'Operation Successful', duration = 4500) => createToast(msg, 'success', duration, title),
    error: (msg, title = 'Error Encountered', duration = 5500) => createToast(msg, 'error', duration, title),
    warning: (msg, title = 'Security / System Notice', duration = 5000) => createToast(msg, 'warning', duration, title),
    info: (msg, title = 'System Update', duration = 4500) => createToast(msg, 'info', duration, title)
  };

  // Backwards compatible alias
  window.showToast = function(message, type = 'success', duration = 4500, title = null) {
    return createToast(message, type, duration, title);
  };

  // ── Auto-trigger Session Flashes on DOM Ready ──
  document.addEventListener('DOMContentLoaded', () => {
    <?php if (!empty($toastFlashSuccess)): ?>
      adminToast.success(<?= json_encode($toastFlashSuccess) ?>, 'Success');
    <?php endif; ?>

    <?php if (!empty($toastFlashError)): ?>
      adminToast.error(<?= json_encode($toastFlashError) ?>, 'Action Failed');
    <?php endif; ?>

    <?php if (!empty($toastFlashWarning)): ?>
      adminToast.warning(<?= json_encode($toastFlashWarning) ?>, 'Notice');
    <?php endif; ?>

    <?php if (!empty($toastFlashInfo)): ?>
      adminToast.info(<?= json_encode($toastFlashInfo) ?>, 'Information');
    <?php endif; ?>
  });

})();
</script>
