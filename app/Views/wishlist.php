<?php 
include __DIR__ . "/Includes/header.php"; 
use App\Helpers\CryptoHelper;

$myWishlist = $myWishlist ?? [];
$wishlistCount = count($myWishlist);
?>

<style>
.wl-page-wrap {
  min-height: calc(100vh - 72px);
  padding-top: 72px;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

/* Sub-Nav Bar
.wl-subnav-bar {
  background: #ffffff;
  border-bottom: 1px solid #E2E8F0;
  position: sticky;
  top: 72px;
  z-index: 40;
} */

.wl-subnav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  overflow-x: auto;
  scrollbar-width: none;
}
.wl-subnav-container::-webkit-scrollbar { display: none; }

.wl-subnav-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 16px;
  font-size: 13px;
  font-weight: 700;
  color: #64748B;
  text-decoration: none;
  border-bottom: 2px solid transparent;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.wl-subnav-link:hover { color: #0F172A; }
.wl-subnav-link.active { color: #1D4ED8; border-bottom-color: #1D4ED8; }
.wl-subnav-link .material-symbols-outlined { font-size: 18px; }

/* Page Hero */
.wl-hero-banner {
  background: #ffffff;
  border-bottom: 1px solid #E2E8F0;
  padding: 36px 0;
}

.wl-hero-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.wl-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fce7f3;
  color: #db2777;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 12px;
  border-radius: 20px;
  margin-bottom: 8px;
}

.wl-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 32px;
  font-weight: 900;
  color: #0F172A;
  margin: 0 0 4px;
  letter-spacing: -0.02em;
}

.wl-subtitle {
  font-size: 14px;
  color: #64748B;
  margin: 0;
}

/* Main Container */
.wl-main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px 60px;
}

/* Grid */
.wl-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
}

.wl-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  transition: all 0.25s ease;
  position: relative;
}

.wl-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
  border-color: #cbd5e1;
}

.wl-card-media {
  position: relative;
  height: 200px;
  overflow: hidden;
  background: #0F172A;
}

.wl-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.wl-card:hover .wl-card-img {
  transform: scale(1.06);
}

.wl-heart-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ec4899;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
  z-index: 2;
}

.wl-heart-btn:hover {
  transform: scale(1.1);
  background: #ffffff;
}

.wl-rating-badge {
  position: absolute;
  bottom: 14px;
  left: 14px;
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(8px);
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 800;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 4px;
}

.wl-card-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.wl-farm-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 18px;
  font-weight: 900;
  color: #0F172A;
  margin: 0;
  line-height: 1.3;
}

.wl-farm-loc {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12.5px;
  font-weight: 600;
  color: #64748B;
  margin: 0;
}

.wl-price-row {
  display: flex;
  align-items: baseline;
  gap: 4px;
  margin-top: 4px;
}

.wl-price-val {
  font-size: 22px;
  font-weight: 900;
  color: #1D4ED8;
}

.wl-price-unit {
  font-size: 12px;
  font-weight: 700;
  color: #94a3b8;
}

.wl-actions-row {
  margin-top: auto;
  display: flex;
  align-items: center;
  gap: 10px;
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
}

.wl-btn-view {
  flex: 1;
  height: 42px;
  background: #1D4ED8;
  color: #ffffff;
  font-size: 13px;
  font-weight: 800;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(22, 165, 222, 0.25);
  transition: all 0.2s ease;
}

.wl-btn-view:hover {
  background: #1E40AF;
}

.wl-btn-wa {
  width: 42px;
  height: 42px;
  background: #25d366;
  color: #ffffff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
  transition: all 0.2s ease;
}

.wl-btn-wa:hover {
  background: #1eb956;
}

.wl-empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 20px;
  background: #ffffff;
  border: 2px dashed #E2E8F0;
  border-radius: 24px;
}

/* Toast */
.wl-toast {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%) translateY(80px);
  background: #0F172A;
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 20px;
  border-radius: 30px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  transition: all 0.3s ease;
  opacity: 0;
  pointer-events: none;
  display: flex;
  align-items: center;
  gap: 8px;
}

.wl-toast.show {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

@media (max-width: 640px) {
  .wl-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<!-- ── Toast ── -->
<div class="wl-toast" id="wl-toast">
  <span class="material-symbols-outlined" id="wl-toast-icon" style="font-size:18px;">favorite</span>
  <span id="wl-toast-msg">Removed from wishlist</span>
</div>

<div class="wl-page-wrap">

  <!-- ── 1. Sticky User Sub-Nav Hub ── -->
  <!-- <div class="wl-subnav-bar">
    <div class="wl-subnav-container">
      <a href="<?= url('dashboard') ?>" class="wl-subnav-link">
        <span class="material-symbols-outlined">grid_view</span>
        <span>Overview</span>
      </a>
      <a href="<?= url('user/my-bookings') ?>" class="wl-subnav-link">
        <span class="material-symbols-outlined">calendar_month</span>
        <span>My Bookings</span>
      </a>
      <a href="<?= url('my-wishlist') ?>" class="wl-subnav-link active">
        <span class="material-symbols-outlined" style="color:#ec4899;">favorite</span>
        <span>Saved Farms</span>
      </a>
      <a href="<?= url('user/profile') ?>" class="wl-subnav-link">
        <span class="material-symbols-outlined">manage_accounts</span>
        <span>Profile &amp; Settings</span>
      </a>
      <a href="<?= url('contact') ?>" class="wl-subnav-link">
        <span class="material-symbols-outlined">support_agent</span>
        <span>Concierge Support</span>
      </a>
    </div>
  </div> -->

  <!-- ── 2. Page Hero ── -->
  <div class="wl-hero-banner">
    <div class="wl-hero-container">
      <div>
        <div class="wl-tag">
          <span class="material-symbols-outlined" style="font-size:14px;font-variation-settings:'FILL' 1;">favorite</span>
          <span>Saved Collection</span>
        </div>
        <h1 class="wl-title">My Wishlist</h1>
        <p class="wl-subtitle">Your personal wishlist of handpicked luxury farmhouses and weekend retreats.</p>
      </div>

      <a href="<?= url('farmhouses') ?>" style="display:inline-flex;align-items:center;gap:8px;background:#1D4ED8;color:#ffffff;font-size:13px;font-weight:800;padding:10px 20px;border-radius:14px;text-decoration:none;box-shadow:0 4px 14px rgba(22,165,222,0.3);">
        <span class="material-symbols-outlined" style="font-size:18px;">travel_explore</span>
        <span>Browse More Farms</span>
      </a>
    </div>
  </div>

  <!-- ── 3. Main Grid ── -->
  <div class="wl-main-container">
    <div class="wl-grid" id="wl-grid">
      <?php if (empty($myWishlist)): ?>
        <div class="wl-empty-state" id="wl-empty">
          <span class="material-symbols-outlined" style="font-size:54px;color:#94a3b8;margin-bottom:14px;display:block;">favorite_border</span>
          <h3 style="font-size:20px;font-weight:900;color:#0F172A;margin:0 0 6px;">Your wishlist is empty</h3>
          <p style="font-size:13.5px;color:#64748B;max-width:360px;margin:0 auto 20px;">
            Save farmhouses while exploring to compare prices, check availability, and plan your weekend gateway.
          </p>
          <a href="<?= url('farmhouses') ?>" class="wl-btn-view" style="display:inline-flex;max-width:240px;margin:0 auto;">
            <span class="material-symbols-outlined" style="font-size:16px;">explore</span>
            <span>Discover Farmhouses</span>
          </a>
        </div>
      <?php else: ?>
        <?php foreach ($myWishlist as $item):
          $encId = CryptoHelper::encrypt($item['id'] ?? 0);
          $price = !empty($item['price']) ? number_format($item['price']) : '5,000';
          $image = farmhouse_img_url($item['main_image'] ?? null, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?q=80&w=600');
          $city = htmlspecialchars($item['city'] ?? $item['location'] ?? 'India');
        ?>
          <div class="wl-card" data-wish-card data-enc-id="<?= htmlspecialchars($encId) ?>">
            <div class="wl-card-media">
              <img src="<?= $image ?>" alt="<?= htmlspecialchars($item['name'] ?? 'Farmhouse') ?>" class="wl-card-img">
              <button type="button" class="wl-heart-btn" onclick="toggleWishlistItem(event, this)" data-enc-id="<?= htmlspecialchars($encId) ?>" title="Remove from wishlist">
                <span class="material-symbols-outlined" style="font-size:20px;font-variation-settings:'FILL' 1;">favorite</span>
              </button>
              <div class="wl-rating-badge">
                <span class="material-symbols-outlined" style="font-size:14px;color:#facc15;font-variation-settings:'FILL' 1;">star</span>
                <span><?= htmlspecialchars($item['rating'] ?? '4.8') ?></span>
              </div>
            </div>

            <div class="wl-card-content">
              <div>
                <span style="background:#E0F2FE;color:#1E3A8A;font-size:10px;font-weight:800;padding:2px 8px;border-radius:12px;text-transform:uppercase;margin-bottom:4px;display:inline-block;">
                  <?= htmlspecialchars($item['category'] ?? $item['farmhouse_category'] ?? 'Farmhouse') ?>
                </span>
                <h3 class="wl-farm-title"><?= htmlspecialchars($item['name'] ?? $item['farmhouse_name'] ?? 'Farmhouse Title') ?></h3>
                <p class="wl-farm-loc">
                  <span class="material-symbols-outlined" style="font-size:15px;color:#1D4ED8;">location_on</span>
                  <span><?= $city ?></span>
                </p>
              </div>

              <div class="wl-price-row">
                <span class="wl-price-val">₹<?= $price ?></span>
                <span class="wl-price-unit">/ night</span>
              </div>

              <div class="wl-actions-row">
                <a href="<?= url('farmhouse_details?id=' . $encId) ?>" class="wl-btn-view">
                  <span>View Details &amp; Book</span>
                  <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>

                <a href="https://wa.me/919876543210?text=Hello,%20I%20am%20interested%20in%20<?= urlencode($item['name'] ?? 'Farmhouse') ?>" target="_blank" class="wl-btn-wa" title="Inquire on WhatsApp">
                  <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.835c1.405.836 3.125 1.352 4.953 1.353 5.174 0 9.389-4.215 9.391-9.389.001-2.507-.974-4.864-2.747-6.638s-4.132-2.747-6.638-2.747c-5.176 0-9.391 4.215-9.393 9.39-.001 1.893.562 3.736 1.629 5.308l-.999 3.646 3.737-.981-.132-.084z"/></svg>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
let wlToastTimer = null;

function toggleWishlistItem(e, btn) {
  e.preventDefault();
  e.stopPropagation();

  const encId = btn.dataset.encId;
  const card  = btn.closest('[data-wish-card]');

  fetch('<?= url("toggle_wishlist") ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ farmhouse_id: encId })
  })
  .then(res => res.json())
  .then(data => {
    if (data && data.success && data.action === 'removed') {
      if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        setTimeout(() => {
          card.remove();
          const remaining = document.querySelectorAll('[data-wish-card]').length;
          if (remaining === 0) {
            location.reload();
          }
        }, 250);
      }
      showWlToast('heart_minus', 'Removed from saved wishlist');
    } else {
      showWlToast('favorite', 'Wishlist updated');
    }
  })
  .catch(() => {
    showWlToast('wifi_off', 'Network issue. Try again.');
  });
}

function showWlToast(icon, message) {
  const toast    = document.getElementById('wl-toast');
  const toastIcon = document.getElementById('wl-toast-icon');
  const toastMsg  = document.getElementById('wl-toast-msg');

  if (!toast) return;
  toastIcon.textContent = icon;
  toastMsg.textContent  = message;
  toast.classList.add('show');

  if (wlToastTimer) clearTimeout(wlToastTimer);
  wlToastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}
</script>

<?php include __DIR__ . "/Includes/footer.php"; ?>