<?php
// Ensure session is started for the success/error messages to work
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle       = "Contact Customer Support & Booking Inquiries | Stayora";
$pageDescription = "Get in touch with Stayora for farmhouse bookings, owner listings, partnerships, and customer support. Call, WhatsApp, or message us today.";
$pageKeywords    = "contact stayora, farmhouse customer support, list farmhouse contact, book farmhouse help";
$canonicalUrl    = absolute_url('contact');

include __DIR__ . "/Includes/header.php"; 

$phoneNum = htmlspecialchars($globalSite['mobile_number'] ?? '+91 88890 00399');
$emailAdd = htmlspecialchars($globalSite['email'] ?? 'support@stayora.com');
$address  = htmlspecialchars($globalSite['address'] ?? 'Stayora HQ, Near Bypass Road, Indore, MP 452010');
$waNum    = htmlspecialchars($globalSite['whatsapp_number'] ?? preg_replace('/[^0-9]/', '', $phoneNum));
?>

<style>
/* ═══════════════════════════════════════════════════════════
   CONTACT US PAGE — MODERN LUXURY DESIGN SYSTEM
   ═══════════════════════════════════════════════════════════ */

.cnt-page-wrap {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
  max-width: 1200px;
  margin: 0 auto;
  padding: 100px 20px 80px;
}

/* Breadcrumb */
.cnt-breadcrumb {
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.cnt-breadcrumb a {
  color: #1E40AF;
  text-decoration: none;
  transition: color 0.15s;
}

.cnt-breadcrumb a:hover {
  color: #1E3A8A;
  text-decoration: underline;
}

/* Header Section */
.cnt-header {
  margin-bottom: 40px;
}

.cnt-tag {
  color: #1E40AF;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 8px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.cnt-title {
  font-size: 38px;
  font-weight: 900;
  color: #0F172A;
  line-height: 1.15;
  letter-spacing: -0.02em;
  margin: 0 0 10px;
}

.cnt-sub {
  font-size: 16px;
  color: #64748B;
  max-width: 650px;
  line-height: 1.6;
  margin: 0;
}

/* Main Grid */
.cnt-main-grid {
  display: grid;
  grid-template-columns: 420px 1fr;
  gap: 36px;
  align-items: start;
}

/* Contact Cards Left Panel */
.cnt-left-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cnt-info-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 20px 22px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  transition: all 0.25s ease;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
  text-decoration: none;
  color: inherit;
}

.cnt-info-card:hover {
  border-color: #DBEAFE;
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(22, 165, 222, 0.08);
}

.cnt-icon-box {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  background: #E0F2FE;
  color: #1E40AF;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.cnt-card-label {
  font-size: 11px;
  font-weight: 800;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}

.cnt-card-val {
  font-size: 15px;
  font-weight: 800;
  color: #0F172A;
  line-height: 1.4;
}

/* WhatsApp Priority Card */
.cnt-wa-card {
  background: linear-gradient(135deg, #0F172A 0%, #0F172A 100%);
  border-radius: 22px;
  padding: 24px;
  color: #ffffff;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
}

.cnt-wa-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.cnt-wa-head h4 {
  font-size: 17px;
  font-weight: 800;
  margin: 0;
}

.cnt-wa-p {
  font-size: 13px;
  color: #94a3b8;
  line-height: 1.5;
  margin: 0 0 18px;
}

.cnt-wa-btn {
  background: #22c55e;
  color: #ffffff;
  font-weight: 800;
  font-size: 14px;
  padding: 12px 20px;
  border-radius: 12px;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.35);
}

.cnt-wa-btn:hover {
  background: #16a34a;
  transform: translateY(-1px);
  color: #ffffff;
}

/* Right Form Card */
.cnt-form-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 24px;
  padding: 36px 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}

.cnt-form-title {
  font-size: 22px;
  font-weight: 900;
  color: #0F172A;
  margin: 0 0 6px;
}

.cnt-form-sub {
  font-size: 13.5px;
  color: #64748B;
  margin: 0 0 24px;
}

.cnt-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.cnt-input-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.cnt-input-lbl {
  font-size: 11.5px;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cnt-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.cnt-input-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  pointer-events: none;
  font-size: 18px;
}

.cnt-input {
  width: 100%;
  height: 46px;
  padding: 0 14px 0 42px;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  font-size: 13.5px;
  font-family: inherit;
  color: #0F172A;
  background: #F8FAFC;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.cnt-input:focus {
  background: #ffffff;
  border-color: #1D4ED8;
  box-shadow: 0 0 0 3px rgba(22, 165, 222, 0.15);
}

.cnt-textarea {
  width: 100%;
  padding: 12px 14px 12px 42px;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  font-size: 13.5px;
  font-family: inherit;
  color: #0F172A;
  background: #F8FAFC;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
  resize: vertical;
  min-height: 120px;
}

.cnt-textarea:focus {
  background: #ffffff;
  border-color: #1D4ED8;
  box-shadow: 0 0 0 3px rgba(22, 165, 222, 0.15);
}

.cnt-submit-btn {
  width: 100%;
  height: 50px;
  background: #1D4ED8;
  color: #ffffff;
  border: none;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 8px 20px rgba(22, 165, 222, 0.35);
  transition: all 0.25s ease;
  margin-top: 10px;
}

.cnt-submit-btn:hover {
  background: #1E40AF;
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(2, 132, 199, 0.45);
}

/* Alert Boxes */
.cnt-alert-success {
  background: #f0fdf4;
  border: 1.5px solid #86efac;
  color: #166534;
  padding: 14px 16px;
  border-radius: 14px;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.cnt-alert-error {
  background: #fef2f2;
  border: 1.5px solid #fca5a5;
  color: #991b1b;
  padding: 14px 16px;
  border-radius: 14px;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

/* Responsive Overrides */
@media (max-width: 992px) {
  .cnt-main-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }
}

@media (max-width: 768px) {
  .cnt-page-wrap {
    padding: 85px 14px 60px;
  }
  .cnt-title {
    font-size: 28px;
  }
  .cnt-form-card {
    padding: 24px 18px;
    border-radius: 20px;
  }
  .cnt-form-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}
</style>

<div class="cnt-page-wrap">

  <!-- Breadcrumb -->
  <div class="cnt-breadcrumb">
    <a href="<?= url('/') ?>">Home</a>
    <span style="color:#cbd5e1;">›</span>
    <span>Contact &amp; Support</span>
  </div>

  <!-- Header Section -->
  <div class="cnt-header">
    <div class="cnt-tag">
      <span class="material-symbols-outlined" style="font-size:16px;">support_agent</span>
      <span>We're Here To Help</span>
    </div>
    <h1 class="cnt-title">Contact &amp; Guest Support</h1>
    <p class="cnt-sub">Have questions about a farmhouse, booking inquiries, or want to list your estate? Reach out to our concierge team directly.</p>
  </div>

  <!-- Main 2-Column Grid -->
  <div class="cnt-main-grid">

    <!-- ══ LEFT PANEL: CONTACT INFO CARDS ══ -->
    <div class="cnt-left-panel">

      <!-- Direct Phone -->
      <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phoneNum) ?>" class="cnt-info-card">
        <div class="cnt-icon-box">
          <span class="material-symbols-outlined">call</span>
        </div>
        <div>
          <div class="cnt-card-label">Direct Phone Line</div>
          <div class="cnt-card-val"><?= $phoneNum ?></div>
          <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Available Mon – Sun, 9:00 AM – 9:00 PM</div>
        </div>
      </a>

      <!-- Direct Email -->
      <a href="mailto:<?= $emailAdd ?>" class="cnt-info-card">
        <div class="cnt-icon-box">
          <span class="material-symbols-outlined">mail</span>
        </div>
        <div>
          <div class="cnt-card-label">Email Inquiries</div>
          <div class="cnt-card-val"><?= $emailAdd ?></div>
          <div style="font-size:12px;color:#94a3b8;margin-top:2px;">We usually respond within 2-4 hours</div>
        </div>
      </a>

      <!-- Regional Head Office -->
      <div class="cnt-info-card">
        <div class="cnt-icon-box">
          <span class="material-symbols-outlined">location_on</span>
        </div>
        <div>
          <div class="cnt-card-label">Regional Office</div>
          <div class="cnt-card-val"><?= $address ?></div>
        </div>
      </div>

      <!-- Priority WhatsApp Chat Box -->
      <div class="cnt-wa-card">
        <div class="cnt-wa-head">
          <span class="material-symbols-outlined" style="font-size:22px;color:#4ade80;">chat</span>
          <h4>Instant WhatsApp Support</h4>
        </div>
        <p class="cnt-wa-p">Need an immediate answer regarding dates, group booking discounts, or property availability? Chat with us live.</p>
        <a href="https://wa.me/91<?= $waNum ?>?text=<?= urlencode('Hello Stayora team, I have an inquiry regarding farmhouse stays.') ?>" target="_blank" rel="noopener" class="cnt-wa-btn">
          <span class="material-symbols-outlined" style="font-size:18px;">chat</span>
          <span>Message on WhatsApp</span>
        </a>
      </div>

    </div>

    <!-- ══ RIGHT PANEL: INQUIRY FORM ══ -->
    <div class="cnt-form-card">
      <h3 class="cnt-form-title">Send Us a Message</h3>
      <p class="cnt-form-sub">Fill in your requirements below and our team will get in touch promptly.</p>

      <!-- Messages -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="cnt-alert-success">
          <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
          <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="cnt-alert-error">
          <span class="material-symbols-outlined" style="font-size:20px;">error</span>
          <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
      <?php endif; ?>

      <form action="<?= url('contact-submit') ?>" method="POST">
        <div class="cnt-form-grid">
          <div class="cnt-input-group">
            <label class="cnt-input-lbl">Your Full Name</label>
            <div class="cnt-input-wrap">
              <span class="material-symbols-outlined cnt-input-icon">person</span>
              <input type="text" name="name" class="cnt-input" placeholder="e.g. Rahul Sharma" required>
            </div>
          </div>

          <div class="cnt-input-group">
            <label class="cnt-input-lbl">Phone Number</label>
            <div class="cnt-input-wrap">
              <span class="material-symbols-outlined cnt-input-icon">phone</span>
              <input type="tel" name="phone" class="cnt-input" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
            </div>
          </div>
        </div>

        <div class="cnt-form-grid">
          <div class="cnt-input-group">
            <label class="cnt-input-lbl">Email Address</label>
            <div class="cnt-input-wrap">
              <span class="material-symbols-outlined cnt-input-icon">mail</span>
              <input type="email" name="email" class="cnt-input" placeholder="e.g. rahul@example.com" required>
            </div>
          </div>

          <div class="cnt-input-group">
            <label class="cnt-input-lbl">Preferred City / Destination</label>
            <div class="cnt-input-wrap">
              <span class="material-symbols-outlined cnt-input-icon">explore</span>
              <input type="text" name="destination" class="cnt-input" placeholder="e.g. Indore, Surat, Delhi...">
            </div>
          </div>
        </div>

        <div class="cnt-input-group" style="margin-bottom:20px;">
          <label class="cnt-input-lbl">Your Message &amp; Trip Requirements</label>
          <div class="cnt-input-wrap">
            <span class="material-symbols-outlined cnt-input-icon" style="top:14px;align-self:flex-start;">edit_note</span>
            <textarea name="message" class="cnt-textarea" placeholder="Tell us about your expected travel dates, number of guests, or any specific amenities (swimming pool, lawn, party sound system)..." required></textarea>
          </div>
        </div>

        <button type="submit" class="cnt-submit-btn">
          <span>Send Your Request</span>
          <span class="material-symbols-outlined" style="font-size:18px;">send</span>
        </button>
      </form>
    </div>

  </div>

</div>

<?php include __DIR__ . "/Includes/footer.php"; ?>