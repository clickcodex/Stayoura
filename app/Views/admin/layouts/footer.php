    <!-- ══════════════════════════════════════════════
         SYSTEM & SECURITY AUDIT FOOTER
         ══════════════════════════════════════════════ -->
    <footer class="pt-6 border-t border-slate-200 text-slate-400 text-xs flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Stayora Admin Suite v2.0 • Single-Session Secured • PHP 8.2 • MySQL Connected</span>
      </div>
      <div>
        <span>© <?= date('Y') ?> Stayora Luxury Farmstays. All rights reserved.</span>
      </div>
    </footer>

  </main><!-- /main -->

</div><!-- /flex-1 -->

  <!-- Shared Admin JavaScript Controllers -->
  <script>
    // ── 1. Live System Clock ──
    function updateLiveClock() {
      const el = document.getElementById('live-clock');
      if (!el) return;
      const now = new Date();
      const options = { 
        weekday: 'short', 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: false 
      };
      el.textContent = now.toLocaleDateString('en-US', options).replace(',', ' •');
    }
    setInterval(updateLiveClock, 1000);

    // ── 2. Mobile Sidebar Drawer Controller ──
    function toggleAdminSidebar() {
      const sidebar = document.getElementById('admin-sidebar');
      const backdrop = document.getElementById('sidebar-backdrop');
      if (!sidebar || !backdrop) return;

      const isClosed = sidebar.classList.contains('-translate-x-full');
      if (isClosed) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
      } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
      }
    }

    // ── 3. Global Toast & Action Helpers ──
    function handleAdminSignOut(e, href) {
      e.preventDefault();
      if (window.adminToast) {
        adminToast.warning('Terminating administrative terminal session securely...', 'Signing Out', 2000);
      }
      setTimeout(() => {
        window.location.href = href;
      }, 500);
    }

    function handleNotificationBell() {
      if (window.adminToast) {
        adminToast.info('System Audit Log: 3 operational notices require review.', 'Executive Alerts');
      }
    }

    function handleUpcomingModule(name) {
      if (window.adminToast) {
        adminToast.info(`${name} is scheduled for development in Phase 2. Current focus: Manage Properties.`, 'Module Preview');
      }
    }

    function copyToClipboard(text, label = 'Content') {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
          if (window.adminToast) {
            adminToast.success(`${label} copied to clipboard: "${text}"`, 'Copied');
          }
        }).catch(() => {
          if (window.adminToast) {
            adminToast.info(`${label}: ${text}`, 'Reference');
          }
        });
      } else {
        if (window.adminToast) {
          adminToast.info(`${label}: ${text}`, 'Reference');
        }
      }
    }
  </script>

  <!-- Include Admin Toast Notification Component -->
  <?php require_once __DIR__ . '/../toast.php'; ?>

</body>
</html>
