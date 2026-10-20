<?php 
include __DIR__ . "/../Includes/header.php"; 
use App\Helpers\CryptoHelper;

$userData = $userData ?? ['name' => 'Guest', 'profile_image' => ''];

if (!isset($nightCount)) {
    $start = strtotime($booking['start_date'] ?? 'now');
    $end = strtotime($booking['end_date'] ?? 'now');
    $nightCount = max(1, round(($end - $start) / (60 * 60 * 24)));
}

$dbStatus = strtoupper($booking['status'] ?? 'PENDING');
$statusLabel = ($dbStatus == 'APPROVED' || $dbStatus == 'ACTIVE') ? 'CONFIRMED' : $dbStatus;

$adminPhone = htmlspecialchars($booking['admin_phone'] ?? '9876543210');
$adminWhatsapp = htmlspecialchars($booking['admin_whatsapp'] ?? '9876543210');
$encFarmId = CryptoHelper::encrypt($booking['farmhouse_id'] ?? 0);
?>

<style>
.bkv-page-wrap {
  min-height: calc(100vh - 72px);
  padding-top: 72px;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

.bkv-hero-banner {
  background: #ffffff;
  border-bottom: 1px solid #E2E8F0;
  padding: 32px 0;
}

.bkv-hero-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.bkv-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 28px;
  font-weight: 900;
  color: #0F172A;
  margin: 0 0 4px;
  letter-spacing: -0.02em;
}

.bkv-main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px 60px;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 32px;
  align-items: start;
}

.bkv-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  margin-bottom: 24px;
}

.bkv-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.bkv-status-badge.CONFIRMED {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.bkv-status-badge.COMPLETED {
  background: #E0F2FE;
  color: #1E3A8A;
  border: 1px solid #DBEAFE;
}

.bkv-status-badge.PENDING {
  background: #fef9c3;
  color: #854d0e;
  border: 1px solid #fef08a;
}

.bkv-status-badge.REJECTED,
.bkv-status-badge.CANCELLED {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.bkv-hero-img-wrap {
  position: relative;
  height: 280px;
  background: #0F172A;
}

.bkv-hero-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.bkv-dates-grid {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 18px;
  padding: 16px 20px;
  margin-top: 20px;
}

.bkv-night-pill {
  background: #0F172A;
  color: #ffffff;
  font-size: 11px;
  font-weight: 800;
  padding: 5px 12px;
  border-radius: 20px;
  text-transform: uppercase;
}

.bkv-btn-call {
  width: 100%;
  height: 48px;
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(0, 174, 239, 0.35);
  transition: all 0.2s ease;
}

.bkv-btn-call:hover {
  background: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
}

.bkv-btn-wa {
  width: 100%;
  height: 48px;
  background: #25d366;
  color: #ffffff;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
  transition: all 0.2s ease;
  margin-top: 10px;
}

.bkv-btn-wa:hover {
  background: #1eb956;
}

@media (max-width: 900px) {
  .bkv-main-container {
    grid-template-columns: 1fr;
  }
}
</style>

<div class="bkv-page-wrap">

  <!-- ── Hero Banner ── -->
  <div class="bkv-hero-banner">
    <div class="bkv-hero-container">
      <div>
        <a href="<?= url('user/my-bookings') ?>" style="display:inline-flex;align-items:center;gap:4px;font-size:12.5px;font-weight:700;color:#1E40AF;text-decoration:none;margin-bottom:8px;">
          <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
          <span>Back to All Bookings</span>
        </a>
        <h1 class="bkv-title">Booking Details</h1>
        <p style="font-size:13.5px;color:#64748B;margin:0;">Reference ID #<?= htmlspecialchars($booking['id'] ?? 'NA') ?></p>
      </div>

      <div class="bkv-status-badge <?= $statusLabel ?>">
        <span class="material-symbols-outlined" style="font-size:16px;">
          <?= ($statusLabel === 'CONFIRMED') ? 'check_circle' : (($statusLabel === 'PENDING') ? 'hourglass_top' : 'cancel') ?>
        </span>
        <span><?= $statusLabel ?></span>
      </div>
    </div>
  </div>

  <div class="bkv-main-container">

    <!-- ── Left Column: Farm & Stay Breakdown ── -->
    <div>

      <!-- Property Showcase Card -->
      <div class="bkv-card">
        <div class="bkv-hero-img-wrap">
          <img src="<?= htmlspecialchars(farmhouse_img_url($booking['main_image'] ?? null, 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?q=80&w=800')) ?>" 
               alt="<?= htmlspecialchars($booking['farm_title'] ?? 'Farmhouse') ?>" class="bkv-hero-img">
        </div>

        <div style="padding:28px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
              <span style="background:#E0F2FE;color:#1E3A8A;font-size:10px;font-weight:800;padding:2px 8px;border-radius:12px;text-transform:uppercase;margin-bottom:6px;display:inline-block;">
                <?= htmlspecialchars($booking['farm_category'] ?? 'Farmhouse') ?>
              </span>
              <h2 style="font-family:'Epilogue',sans-serif;font-size:22px;font-weight:900;color:#0F172A;margin:0 0 6px;">
                <?= htmlspecialchars($booking['farm_title'] ?? 'Farmhouse Title') ?>
              </h2>
              <?php if (!empty($booking['location'])): ?>
                <p style="display:flex;align-items:center;gap:4px;font-size:13px;color:#64748B;margin:0;font-weight:600;">
                  <span class="material-symbols-outlined" style="font-size:16px;color:#00AEEF;">location_on</span>
                  <span><?= htmlspecialchars($booking['location']) ?></span>
                </p>
              <?php endif; ?>
            </div>

            <div style="text-align:right;">
              <div style="font-size:24px;font-weight:900;color:#00AEEF;">₹<?= number_format($booking['price'] ?? 0) ?></div>
              <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;">Estimated Total</div>
            </div>
          </div>

          <!-- Dates Row -->
          <div class="bkv-dates-grid">
            <div>
              <div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;">Check-in</div>
              <div style="font-size:14px;font-weight:800;color:#0F172A;margin-top:2px;">
                <?= date('D, d M Y', strtotime($booking['start_date'] ?? 'now')) ?>
              </div>
            </div>

            <div style="text-align:center;">
              <div class="bkv-night-pill"><?= (int)$nightCount ?> Nights</div>
            </div>

            <div style="text-align:right;">
              <div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;">Check-out</div>
              <div style="font-size:14px;font-weight:800;color:#0F172A;margin-top:2px;">
                <?= date('D, d M Y', strtotime($booking['end_date'] ?? 'now')) ?>
              </div>
            </div>
          </div>

          <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <a href="<?= url('farmhouse_details?id=' . $encFarmId) ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:800;color:#00AEEF;text-decoration:none;">
              <span>View Full Farmhouse Listing</span>
              <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Notes / Special Requests -->
      <?php if (!empty($booking['message'])): ?>
        <div class="bkv-card" style="padding:24px;">
          <h3 style="font-size:14px;font-weight:800;color:#0F172A;margin:0 0 10px;display:flex;align-items:center;gap:6px;">
            <span class="material-symbols-outlined" style="font-size:18px;color:#00AEEF;">notes</span>
            <span>Your Notes &amp; Special Requests</span>
          </h3>
          <div style="background:#F8FAFC;padding:14px 18px;border-radius:14px;border-left:4px solid #00AEEF;font-size:13.5px;color:#334155;line-height:1.6;font-style:italic;">
            "<?= nl2br(htmlspecialchars($booking['message'])) ?>"
          </div>
        </div>
      <?php endif; ?>

    </div>

    <!-- ── Right Column: Guest & Contact Concierge ── -->
    <div>

      <!-- Guest Details Card -->
      <div class="bkv-card" style="padding:24px;">
        <h3 style="font-size:12px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin:0 0 16px;">
          Guest Information
        </h3>

        <div style="display:flex;flex-direction:column;gap:14px;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;border-radius:12px;background:#E0F2FE;color:#1E40AF;display:flex;align-items:center;justify-content:center;">
              <span class="material-symbols-outlined" style="font-size:18px;">person</span>
            </div>
            <div>
              <div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Name</div>
              <div style="font-size:13.5px;font-weight:800;color:#0F172A;"><?= htmlspecialchars($booking['name'] ?? 'Guest') ?></div>
            </div>
          </div>

          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;border-radius:12px;background:#E0F2FE;color:#1E40AF;display:flex;align-items:center;justify-content:center;">
              <span class="material-symbols-outlined" style="font-size:18px;">call</span>
            </div>
            <div>
              <div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Phone</div>
              <div style="font-size:13.5px;font-weight:800;color:#0F172A;">+91 <?= htmlspecialchars($booking['phone'] ?? 'N/A') ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Support Channels -->
      <div class="bkv-card" style="padding:24px;">
        <h3 style="font-size:12px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin:0 0 14px;">
          Direct Manager Support
        </h3>
        <p style="font-size:12.5px;color:#64748B;line-height:1.5;margin:0 0 16px;">
          Have questions about check-in timings, directions, or catering? Contact the host manager directly:
        </p>

        <a href="tel:<?= $adminPhone ?>" class="bkv-btn-call">
          <span class="material-symbols-outlined" style="font-size:18px;">call</span>
          <span>Call Manager</span>
        </a>

        <a href="https://wa.me/91<?= preg_replace('/\D/', '', $adminWhatsapp) ?>?text=Hello,%20I%20have%20an%20inquiry%20regarding%20booking%20ref:%20#<?= $booking['id'] ?? '' ?>" target="_blank" class="bkv-btn-wa">
          <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.835c1.405.836 3.125 1.352 4.953 1.353 5.174 0 9.389-4.215 9.391-9.389.001-2.507-.974-4.864-2.747-6.638s-4.132-2.747-6.638-2.747c-5.176 0-9.391 4.215-9.393 9.39-.001 1.893.562 3.736 1.629 5.308l-.999 3.646 3.737-.981-.132-.084z"/></svg>
          <span>WhatsApp Concierge</span>
        </a>
      </div>

    </div>

  </div>

</div>

<?php include __DIR__ . "/../Includes/footer.php"; ?>