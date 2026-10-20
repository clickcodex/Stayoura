<?php
$pageTitle       = "Terms & Conditions | Stayora";
$pageDescription = "Review the official terms and conditions for booking, staying, and listing vacation farmhouses on Stayora.";
$canonicalUrl    = absolute_url('terms_conditions');
include __DIR__ . "/Includes/header.php";
?>

<style>
/* ═══════════════════════════════════════════════════════════
   LEGAL / POLICY PAGES — LUXURY DESIGN SYSTEM
   ═══════════════════════════════════════════════════════════ */

.legal-page-wrap {
  min-height: calc(100vh - 72px);
  padding-top: 72px;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

/* ── Hero Section ── */
.legal-hero {
  position: relative;
  background: 
    linear-gradient(180deg, rgba(15, 23, 42, 0.82) 0%, rgba(15, 23, 42, 0.60) 45%, rgba(15, 23, 42, 0.92) 100%),
    url('<?= asset('assets/images/uploads/luxury_pool_hero.jpg') ?>') center 35% / cover no-repeat;
  color: #ffffff;
  padding: 85px 0 100px;
  overflow: hidden;
  text-align: center;
  box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.5);
}

.legal-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: #C9A227;
  font-size: 11.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 6px 16px;
  border-radius: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.legal-hero-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 42px;
  font-weight: 900;
  line-height: 1.15;
  letter-spacing: -0.02em;
  margin: 0 auto 16px;
  max-width: 800px;
  color: #ffffff;
  text-shadow: 0 2px 20px rgba(0,0,0,0.6);
}

.legal-hero-title span {
  background: linear-gradient(135deg, #38BDF8 0%, #60A5FA 50%, #93C5FD 100%);
  filter: drop-shadow(0 2px 14px rgba(2, 132, 199, 0.45));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.legal-hero-meta {
  font-size: 14px;
  color: #cbd5e1;
  font-weight: 600;
  text-shadow: 0 1px 10px rgba(0,0,0,0.5);
}

/* ── Content Layout ── */
.legal-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 24px 100px;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 48px;
  align-items: start;
}

/* Sticky TOC */
.legal-toc-card {
  position: sticky;
  top: 96px;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 24px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.legal-toc-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 14px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #0F172A;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
}

.legal-toc-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.legal-toc-link {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: #64748B;
  text-decoration: none;
  padding: 8px 12px;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.legal-toc-link:hover {
  background: #f0f9ff;
  color: #1D4ED8;
  transform: translateX(3px);
}

/* Document Body & Cards */
.legal-content {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.legal-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 28px;
  padding: 36px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.legal-card-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
}

.legal-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: rgba(22, 165, 222, 0.12);
  color: #1D4ED8;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.legal-card-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 20px;
  font-weight: 900;
  color: #0F172A;
  margin: 0;
}

.legal-card-body {
  font-size: 14.5px;
  color: #475569;
  line-height: 1.7;
}

.legal-card-body p {
  margin: 0 0 16px;
}

.legal-card-body p:last-child {
  margin-bottom: 0;
}

.legal-card-body ul {
  margin: 12px 0 16px;
  padding-left: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.legal-card-body li {
  line-height: 1.6;
}

.legal-highlight-box {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 18px;
  padding: 18px 22px;
  margin: 20px 0;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  color: #166534;
  font-size: 13.5px;
  font-weight: 600;
}

.legal-highlight-box.blue {
  background: #f0f9ff;
  border-color: #DBEAFE;
  color: #1E3A8A;
}

@media (max-width: 992px) {
  .legal-container {
    grid-template-columns: 1fr;
    gap: 32px;
    padding: 40px 16px 80px;
  }
  .legal-toc-card {
    display: none;
  }
  .legal-card {
    padding: 24px;
    border-radius: 20px;
  }
  .legal-hero-title {
    font-size: 32px;
  }
}
</style>

<div class="legal-page-wrap">

  <!-- ── 1. Hero Section ── -->
  <section class="legal-hero">
    <div style="position:relative;z-index:2;max-width:900px;margin:0 auto;padding:0 20px;">
      <div class="legal-hero-badge">
        <span class="material-symbols-outlined" style="font-size:16px;">article</span>
        <span>Platform Terms &amp; Agreement</span>
      </div>

      <h1 class="legal-hero-title">
        Terms of <span>Service &amp; Conditions</span>
      </h1>

      <p class="legal-hero-meta">
        Effective Date: January 1, 2026 &nbsp;•&nbsp; Last Updated: August 2026
      </p>
    </div>
  </section>

  <!-- ── 2. Content Container ── -->
  <div class="legal-container">

    <!-- Sticky Table of Contents -->
    <aside>
      <div class="legal-toc-card">
        <div class="legal-toc-title">Table of Contents</div>
        <ul class="legal-toc-list">
          <li><a href="#acceptance" class="legal-toc-link">1. Acceptance of Terms</a></li>
          <li><a href="#guest-rules" class="legal-toc-link">2. Guest Booking Terms</a></li>
          <li><a href="#host-terms" class="legal-toc-link">3. Host Partnership Rules</a></li>
          <li><a href="#pricing-payments" class="legal-toc-link">4. Pricing &amp; Security Deposits</a></li>
          <li><a href="#house-rules" class="legal-toc-link">5. Property Code of Conduct</a></li>
          <li><a href="#cancellation" class="legal-toc-link">6. Cancellation &amp; Refunds</a></li>
          <li><a href="#liability" class="legal-toc-link">7. Limitation of Liability</a></li>
          <li><a href="#disputes" class="legal-toc-link">8. Governing Law &amp; Jurisdiction</a></li>
        </ul>
      </div>
    </aside>

    <!-- Document Content -->
    <main class="legal-content">

      <!-- 1. Acceptance -->
      <section class="legal-card" id="acceptance">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">gavel</span>
          </div>
          <h2 class="legal-card-title">1. Acceptance of Platform Terms</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Welcome to <strong>Stayora</strong>. By accessing our website (<strong>stayora.com</strong>), registering as a guest user, submitting a booking inquiry, or applying as a host partner, you agree to be bound by these Terms of Service.
          </p>
          <p>
            If you do not agree to these terms, please refrain from using the platform. We reserve the right to modify these terms periodically, and your continued usage signifies your assent to the revised provisions.
          </p>
        </div>
      </section>

      <!-- 2. Guest Booking Terms -->
      <section class="legal-card" id="guest-rules">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">person_pin</span>
          </div>
          <h2 class="legal-card-title">2. Guest Booking &amp; Reservation Terms</h2>
        </div>
        <div class="legal-card-body">
          <p>Guests booking private farmhouses through Stayora acknowledge that:</p>
          <ul>
            <li><strong>Age Requirement:</strong> The primary booking organizer must be at least 18 years of age and present a valid government-issued photo ID (Aadhaar / Driving License / Passport) upon check-in.</li>
            <li><strong>Guest Count Accuracy:</strong> The total number of day or night occupants must not exceed the approved capacity listed during the reservation confirmation.</li>
            <li><strong>Direct Verification:</strong> Stayora acts as a verified marketplace and concierge liaison connecting guests directly with private estate hosts.</li>
          </ul>
        </div>
      </section>

      <!-- 3. Host Partnership Rules -->
      <section class="legal-card" id="host-terms">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">villa</span>
          </div>
          <h2 class="legal-card-title">3. Host Partnership &amp; Quality Standards</h2>
        </div>
        <div class="legal-card-body">
          <p>Property owners listing farmhouses and pool villas warrant that:</p>
          <ul>
            <li>They possess full legal title, ownership, or authorized power of attorney to lease and host guests on the registered premises.</li>
            <li>All property photographs, bedroom counts, pool conditions, and amenities listed are authentic, accurately represented, and maintained in clean working order.</li>
            <li>The property meets standard safety guidelines, including functional perimeter lighting, clean water supply, and emergency contact numbers.</li>
          </ul>
        </div>
      </section>

      <!-- 4. Pricing & Payments -->
      <section class="legal-card" id="pricing-payments">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">payments</span>
          </div>
          <h2 class="legal-card-title">4. Pricing, Payments &amp; Security Deposits</h2>
        </div>
        <div class="legal-card-body">
          <p>
            All listed rental tariffs are denominated in Indian Rupees (₹). Tariff rates vary based on weekdays, weekends, and peak holiday seasons.
          </p>
          <div class="legal-highlight-box blue">
            <span class="material-symbols-outlined" style="font-size:20px;">lock</span>
            <div>
              <strong>Security Deposits:</strong> Certain premium villas may require a refundable security deposit at check-in to cover accidental property damages, refunded in full at checkout following room inspection.
            </div>
          </div>
        </div>
      </section>

      <!-- 5. House Rules & Conduct -->
      <section class="legal-card" id="house-rules">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">rule</span>
          </div>
          <h2 class="legal-card-title">5. Property Code of Conduct &amp; Safety</h2>
        </div>
        <div class="legal-card-body">
          <p>To ensure a pleasant stay for everyone and respect the surrounding neighborhood:</p>
          <ul>
            <li><strong>Prohibited Substances:</strong> Illegal narcotics, commercial gambling, and hazardous fireworks are strictly banned across all Stayora listed premises.</li>
            <li><strong>Swimming Pool Safety:</strong> Children must be supervised by adults at all times around pool areas. Diving in shallow areas is strictly prohibited.</li>
            <li><strong>Loud Music Deadlines:</strong> In adherence to local municipal regulations, outdoor acoustic decibel limits and quiet hours (typically 10:00 PM onwards) must be respected.</li>
          </ul>
        </div>
      </section>

      <!-- 6. Cancellation -->
      <section class="legal-card" id="cancellation">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">event_busy</span>
          </div>
          <h2 class="legal-card-title">6. Cancellation &amp; Refund Policy</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Cancellations and rescheduling requests are subject to Stayora's standardized policy guidelines.
          </p>
          <p>
            For a comprehensive breakdown of cancellation timelines, full refund eligibility, and emergency weather provisions, please review our dedicated <a href="<?= url('cancellation-policy') ?>" style="color:#1D4ED8;font-weight:700;text-decoration:none;">Cancellation &amp; Refund Policy</a>.
          </p>
        </div>
      </section>

      <!-- 7. Liability -->
      <section class="legal-card" id="liability">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">security</span>
          </div>
          <h2 class="legal-card-title">7. Limitation of Liability</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Stayora provides a platform connecting guests with independent hosts. While Stayora conducts verification audits, Stayora shall not be held liable for personal injury, unforeseen electrical utility outages, or loss of personal belongings caused by third-party conduct or force majeure events.
          </p>
        </div>
      </section>

      <!-- 8. Disputes -->
      <section class="legal-card" id="disputes">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">balance</span>
          </div>
          <h2 class="legal-card-title">8. Governing Law &amp; Jurisdiction</h2>
        </div>
        <div class="legal-card-body">
          <p>
            These Terms of Service are governed by and construed in accordance with the laws of the Republic of India. Any disputes arising out of these terms shall be subject to the exclusive jurisdiction of the competent courts in Indore, Madhya Pradesh, India.
          </p>
          <div style="background:#F8FAFC;padding:20px;border-radius:16px;border:1px solid #E2E8F0;margin-top:16px;">
            <p style="margin:0 0 4px;"><strong>Legal Inquiries:</strong> legal@stayora.com</p>
            <p style="margin:0;"><strong>Concierge Support:</strong> +91 98765 43210 (Mon-Sun, 9 AM - 9 PM)</p>
          </div>
        </div>
      </section>

    </main>

  </div>

</div>

<?php
include __DIR__ . "/Includes/footer.php";
?>