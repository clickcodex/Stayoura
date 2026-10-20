<?php 
include __DIR__ . "/../Includes/header.php"; 
use App\Helpers\CryptoHelper;
$userData     = $userData ?? ['name' => 'Guest', 'profile_image' => ''];
$userRequests = $userRequests ?? []; 
$currentStatus = $_GET['status'] ?? 'all';
?>

<style>
/* ── Stayora My Bookings Page ── */
.bk-page-wrap {
  min-height: calc(100vh - 72px);
  padding-top: 72px;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

/* ── Compact Luxury Hero Showcase ── */
.bk-hero-banner {
  background: radial-gradient(circle at 85% 15%, rgba(0, 174, 239, 0.2) 0%, transparent 45%),
              radial-gradient(circle at 10% 85%, rgba(201, 162, 39, 0.12) 0%, transparent 40%),
              linear-gradient(135deg, #070e1b 0%, #0d172b 50%, #081120 100%);
  border-bottom: 1px solid rgba(0, 174, 239, 0.15);
  padding: 20px 0;
  position: relative;
  overflow: hidden;
}

.bk-hero-bg-glow {
  position: absolute;
  top: -40px;
  right: -40px;
  width: 240px;
  height: 240px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 174, 239, 0.18) 0%, transparent 70%);
  pointer-events: none;
}

.bk-hero-container {
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  position: relative;
  z-index: 2;
}

.bk-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(0, 174, 239, 0.14);
  border: 1px solid rgba(0, 174, 239, 0.35);
  color: #38bdf8;
  font-size: 10.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 4px;
}

.bk-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 20px;
  font-weight: 900;
  color: #ffffff;
  margin: 0 0 3px;
  letter-spacing: -0.02em;
  line-height: 1.25;
}

.bk-title span {
  background: linear-gradient(135deg, #FFFFFF 40%, #C9A227 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.bk-subtitle {
  font-size: 12.5px;
  color: #94a3b8;
  margin: 0;
  line-height: 1.4;
}

.bk-hero-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  font-size: 12px;
  font-weight: 800;
  padding: 8px 16px;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.bk-hero-btn:hover {
  background: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
  transform: translateY(-1px);
  color: #ffffff;
}

/* ── Main Container (Decreased Padding) ── */
.bk-main-container {
  max-width: 1240px;
  margin: 0 auto;
  padding: 16px 20px 48px;
}

/* ── Filter & Search Bar with Horizontal Slider ── */
.bk-filter-bar {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 16px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

/* Slider Track */
.bk-status-tabs-wrap {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  position: relative;
}

.bk-status-tabs {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: nowrap;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding: 3px 1px;
}

.bk-status-tabs::-webkit-scrollbar {
  display: none;
}

.bk-status-tab {
  flex-shrink: 0;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 7px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  color: #475569;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  transition: all 0.2s ease;
  user-select: none;
}

.bk-status-tab:hover {
  background: #f1f5f9;
  color: #0F172A;
  border-color: #cbd5e1;
}

.bk-status-tab.active {
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  border-color: transparent;
  box-shadow: 0 3px 10px rgba(0, 174, 239, 0.35);
}

/* Search Box */
.bk-search-form {
  position: relative;
  width: 260px;
  flex-shrink: 0;
}

.bk-search-input {
  width: 100%;
  height: 38px;
  padding: 0 12px 0 36px;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  font-size: 12.5px;
  font-family: inherit;
  background: #F8FAFC;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.bk-search-input:focus {
  border-color: #00AEEF;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(0, 174, 239, 0.12);
}

.bk-search-icon {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 17px;
  pointer-events: none;
}

/* ── Bookings Grid (Compact) ── */
.bk-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 18px;
}

.bk-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.bk-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
  border-color: #cbd5e1;
}

.bk-card-media {
  position: relative;
  height: 180px;
  overflow: hidden;
  background: #0F172A;
}

.bk-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.bk-card:hover .bk-card-img {
  transform: scale(1.04);
}

.bk-status-pill {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 11px;
  border-radius: 12px;
  font-size: 10.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(8px);
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.bk-status-pill.pending {
  background: rgba(254, 240, 138, 0.95);
  color: #854d0e;
  border: 1px solid #fde68a;
}

.bk-status-pill.approved,
.bk-status-pill.confirmed {
  background: rgba(220, 252, 231, 0.95);
  color: #166534;
  border: 1px solid #bbf7d0;
}

.bk-status-pill.checked_in {
  background: rgba(209, 250, 229, 0.95);
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.bk-status-pill.completed,
.bk-status-pill.checked_out {
  background: rgba(224, 242, 254, 0.95);
  color: #1E3A8A;
  border: 1px solid #bae6fd;
}

.bk-status-pill.rejected,
.bk-status-pill.cancelled,
.bk-status-pill.no_show {
  background: rgba(254, 226, 226, 0.95);
  color: #991b1b;
  border: 1px solid #fecaca;
}

.bk-card-content {
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.bk-cat-badge {
  background: #E0F2FE;
  color: #0284c7;
  font-size: 10px;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 10px;
  text-transform: uppercase;
  margin-bottom: 5px;
  display: inline-block;
  letter-spacing: 0.4px;
}

.bk-farm-name {
  font-family: 'Epilogue', sans-serif;
  font-size: 16.5px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 3px;
  line-height: 1.3;
}

.bk-location {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #64748B;
  margin: 0;
}

.bk-dates-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 8px 12px;
}

.bk-date-col {
  display: flex;
  flex-direction: column;
}

.bk-date-label {
  font-size: 9.5px;
  font-weight: 800;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.5px;
}

.bk-date-val {
  font-size: 12.5px;
  font-weight: 800;
  color: #0F172A;
  margin-top: 2px;
}

.bk-actions-row {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
}

.bk-details-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  font-size: 12px;
  font-weight: 800;
  padding: 8px 16px;
  border-radius: 10px;
  text-decoration: none;
  transition: all 0.2s ease;
  box-shadow: 0 3px 10px rgba(0, 174, 239, 0.25);
}

.bk-details-btn:hover {
  background: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
  transform: translateY(-1px);
  color: #ffffff;
}

.bk-cancel-btn {
  background: transparent;
  border: 1.5px solid #fca5a5;
  color: #dc2626;
  font-size: 11.5px;
  font-weight: 700;
  padding: 7px 12px;
  border-radius: 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
}

.bk-cancel-btn:hover {
  background: #fee2e2;
}

.bk-empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 50px 20px;
  background: #ffffff;
  border: 2px dashed #E2E8F0;
  border-radius: 20px;
}

/* ── Mobile Responsive Overrides ── */
@media (max-width: 860px) {
  .bk-filter-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 8px 10px;
  }
  .bk-status-tabs-wrap {
    width: 100%;
  }
  .bk-search-form {
    width: 100%;
    max-width: 100%;
  }
}

@media (max-width: 640px) {
  .bk-hero-banner {
    padding: 16px 0;
  }
  .bk-hero-container {
    gap: 12px;
    padding: 0 16px;
  }
  .bk-title {
    font-size: 18px;
  }
  .bk-subtitle {
    font-size: 11.5px;
  }
  .bk-hero-btn {
    width: 100%;
    justify-content: center;
    padding: 9px 14px;
    font-size: 12px;
  }
  .bk-main-container {
    padding: 12px 14px 40px;
  }
  .bk-filter-bar {
    border-radius: 14px;
    margin-bottom: 14px;
  }
  .bk-grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }
  .bk-card-media {
    height: 165px;
  }
  .bk-card-content {
    padding: 14px;
    gap: 10px;
  }
  .bk-status-tab {
    padding: 6px 12px;
    font-size: 11.5px;
  }
}
</style>

<div class="bk-page-wrap">

  <!-- ── 1. Compact Hero Banner ── -->
  <div class="bk-hero-banner">
    <div class="bk-hero-bg-glow"></div>
    <div class="bk-hero-container">
      <div>
        <div class="bk-tag">
          <span class="material-symbols-outlined" style="font-size:13px;">calendar_month</span>
          <span>Reservation History</span>
        </div>
        <h1 class="bk-title">My <span>Bookings</span></h1>
        <p class="bk-subtitle">View and monitor the confirmation status of your farmhouse stays.</p>
      </div>

      <a href="<?= url('farmhouses') ?>" class="bk-hero-btn">
        <span class="material-symbols-outlined" style="font-size:16px;">add_circle</span>
        <span>Book Another Farm</span>
      </a>
    </div>
  </div>

  <div class="bk-main-container">

    <!-- ── 2. Filter & Search Bar with Horizontal Slider ── -->
    <div class="bk-filter-bar">
      <div class="bk-status-tabs-wrap">
        <div class="bk-status-tabs" id="bkStatusSlider">
          <a href="?status=all<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="bk-status-tab <?= ($currentStatus === 'all') ? 'active' : '' ?>">All Requests</a>
          <a href="?status=approved<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="bk-status-tab <?= ($currentStatus === 'approved') ? 'active' : '' ?>">Confirmed</a>
          <a href="?status=completed<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="bk-status-tab <?= ($currentStatus === 'completed') ? 'active' : '' ?>">Completed</a>
          <a href="?status=pending<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="bk-status-tab <?= ($currentStatus === 'pending') ? 'active' : '' ?>">Pending</a>
          <a href="?status=cancelled<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="bk-status-tab <?= ($currentStatus === 'cancelled') ? 'active' : '' ?>">Cancelled</a>
          <a href="?status=rejected<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="bk-status-tab <?= ($currentStatus === 'rejected') ? 'active' : '' ?>">Rejected</a>
        </div>
      </div>

      <form method="GET" action="" class="bk-search-form">
        <?php if ($currentStatus !== 'all'): ?>
          <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
        <?php endif; ?>
        <span class="material-symbols-outlined bk-search-icon">search</span>
        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search by farm title…" class="bk-search-input">
      </form>
    </div>

    <!-- ── 3. Bookings Grid (Compact) ── -->
    <div class="bk-grid">
      <?php if (empty($userRequests)): ?>
        <div class="bk-empty-state">
          <span class="material-symbols-outlined" style="font-size:46px;color:#94a3b8;margin-bottom:12px;display:block;">event_busy</span>
          <h3 style="font-size:18px;font-weight:900;color:#0F172A;margin:0 0 5px;">No bookings found</h3>
          <p style="font-size:13px;color:#64748B;max-width:340px;margin:0 auto 18px;">
            <?= ($currentStatus !== 'all') ? 'No booking requests found under the selected status filter.' : 'You have not submitted any farmhouse booking requests yet.' ?>
          </p>
          <a href="<?= url('farmhouses') ?>" class="bk-details-btn" style="display:inline-flex;">
            <span class="material-symbols-outlined" style="font-size:16px;">explore</span>
            <span>Explore Farmhouses</span>
          </a>
        </div>
      <?php else: ?>
        <?php foreach ($userRequests as $request):
          $status = strtolower($request['status'] ?? 'pending');
          $encFarmId = CryptoHelper::encrypt($request['farmhouse_id'] ?? 0);
          $start = !empty($request['check_in']) ? date('d M Y', strtotime($request['check_in'])) : (isset($request['start_date']) ? date('d M Y', strtotime($request['start_date'])) : 'N/A');
          $end   = !empty($request['check_out']) ? date('d M Y', strtotime($request['check_out'])) : (isset($request['end_date']) ? date('d M Y', strtotime($request['end_date'])) : 'N/A');
          $reqImg = farmhouse_img_url($request['main_image'] ?? null, 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?q=80&w=400');
        ?>
          <div class="bk-card">
            <div class="bk-card-media">
              <img src="<?= $reqImg ?>" 
                   alt="<?= htmlspecialchars($request['farm_title'] ?? 'Farmhouse') ?>" class="bk-card-img" loading="lazy">
              <div class="bk-status-pill <?= $status ?>">
                ● <?= htmlspecialchars(ucfirst($status)) ?>
              </div>
            </div>

            <div class="bk-card-content">
              <div>
                <span class="bk-cat-badge">
                  <?= htmlspecialchars($request['farm_category'] ?? 'Farmhouse') ?>
                </span>
                <h3 class="bk-farm-name"><?= htmlspecialchars($request['farm_title'] ?? 'Farmhouse') ?></h3>
                <?php if (!empty($request['location'])): ?>
                  <p class="bk-location">
                    <span class="material-symbols-outlined" style="font-size:15px;color:#00AEEF;">location_on</span>
                    <span><?= htmlspecialchars($request['location']) ?></span>
                  </p>
                <?php endif; ?>
              </div>

              <div class="bk-dates-row">
                <div class="bk-date-col">
                  <span class="bk-date-label">Check In</span>
                  <span class="bk-date-val"><?= $start ?></span>
                </div>
                <div class="bk-date-col" style="border-left:1px solid #E2E8F0;padding-left:12px;">
                  <span class="bk-date-label">Check Out</span>
                  <span class="bk-date-val"><?= $end ?></span>
                </div>
              </div>

              <?php if (!empty($request['message'])): ?>
                <div style="font-size:11.5px;color:#64748B;background:#F8FAFC;padding:7px 10px;border-radius:10px;border:1px solid #E2E8F0;font-style:italic;">
                  &ldquo;<?= htmlspecialchars($request['message']) ?>&rdquo;
                </div>
              <?php endif; ?>

              <div class="bk-actions-row">
                <?php if ($status === 'pending'): ?>
                  <form action="<?= url('user/my-bookings') ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking request?')">
                    <input type="hidden" name="request_id" value="<?= $request['id'] ?? '' ?>">
                    <button type="submit" class="bk-cancel-btn">
                      <span class="material-symbols-outlined" style="font-size:14px;">close</span>
                      <span>Cancel</span>
                    </button>
                  </form>
                <?php else: ?>
                  <span style="font-size:11px;font-weight:700;color:#94a3b8;">
                    Ref #<?= htmlspecialchars($request['id'] ?? '') ?>
                  </span>
                <?php endif; ?>

                <a href="<?= url('user/booking?id=' . ($request['id'] ?? 0)) ?>" class="bk-details-btn">
                  <span>View Details</span>
                  <span class="material-symbols-outlined" style="font-size:15px;">arrow_forward</span>
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
// Horizontal drag-to-scroll & auto-focus for status slider tabs
document.addEventListener('DOMContentLoaded', function () {
  const slider = document.getElementById('bkStatusSlider');
  if (!slider) return;

  // Auto-scroll to active tab on page load
  const activeTab = slider.querySelector('.bk-status-tab.active');
  if (activeTab) {
    activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }

  // Mouse drag scrolling on desktop
  let isDown = false;
  let startX, scrollLeft;

  slider.addEventListener('mousedown', (e) => {
    isDown = true;
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });
  slider.addEventListener('mouseleave', () => { isDown = false; });
  slider.addEventListener('mouseup', () => { isDown = false; });
  slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 1.5;
    slider.scrollLeft = scrollLeft - walk;
  });
});
</script>

<?php include __DIR__ . "/../Includes/footer.php"; ?>