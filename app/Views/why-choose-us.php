<?php
$pageTitle       = "Why Choose Us | Stayora - 100% Verified Farmhouses & Stays";
$pageDescription = "Discover why thousands choose Stayora: 100% verified private properties, direct owner booking rates, transparent pricing, and 24/7 dedicated guest support.";
$pageKeywords    = "why stayora, verified farmhouses, trusted farmhouse booking, best pool villas india";
$canonicalUrl    = absolute_url('why-choose-us');
include __DIR__ . "/Includes/header.php";
?>

<style>
/* ═══════════════════════════════════════════════════════════
   WHY CHOOSE US — LUXURY BRAND TRUST & ADVANTAGES
   ═══════════════════════════════════════════════════════════ */

.wcu-page-wrap {
  min-height: 100vh;
  padding-top: 0;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
  overflow-x: hidden;
}

/* ── Hero Section ── */
.wcu-hero {
  position: relative;
  background: 
    linear-gradient(180deg, rgba(15, 23, 42, 0.80) 0%, rgba(15, 23, 42, 0.52) 45%, rgba(15, 23, 42, 0.90) 100%),
    url('<?= asset('assets/images/uploads/luxury_pool_hero.jpg') ?>') center 35% / cover no-repeat;
  color: #ffffff;
  padding: 135px 0 105px;
  overflow: hidden;
  text-align: center;
  box-shadow: inset 0 0 100px rgba(0,0,0,0.5);
}

.wcu-hero-mesh {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 75% 25%, rgba(22, 165, 222, 0.25) 0%, transparent 50%),
              radial-gradient(circle at 25% 75%, rgba(2, 132, 199, 0.15) 0%, transparent 50%);
  pointer-events: none;
}

.wcu-hero-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 24px;
  position: relative;
  z-index: 2;
}

.wcu-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(56, 189, 248, 0.35);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: #38BDF8;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 6px 18px;
  border-radius: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.wcu-hero-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 46px;
  font-weight: 900;
  line-height: 1.18;
  letter-spacing: -0.02em;
  margin: 0 auto 18px;
  max-width: 900px;
  color: #ffffff;
  text-shadow: 0 2px 20px rgba(0,0,0,0.6);
}

.wcu-hero-title span {
  background: linear-gradient(135deg, #38BDF8 0%, #60A5FA 50%, #93C5FD 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 2px 14px rgba(2, 132, 199, 0.45));
  font-weight: 900;
}

.wcu-hero-sub {
  font-size: 17px;
  color: #E2E8F0;
  line-height: 1.6;
  max-width: 720px;
  margin: 0 auto 36px;
  font-weight: 500;
  text-shadow: 0 1px 10px rgba(0,0,0,0.5);
}

.wcu-hero-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 48px;
}

.wcu-hero-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: #1D4ED8;
  color: #ffffff;
  font-size: 15px;
  font-weight: 800;
  padding: 14px 28px;
  border-radius: 16px;
  text-decoration: none;
  box-shadow: 0 10px 25px rgba(22, 165, 222, 0.4);
  transition: all 0.25s ease;
}

.wcu-hero-btn-primary:hover {
  background: #1E40AF;
  transform: translateY(-2px);
}

.wcu-hero-btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  padding: 14px 26px;
  border-radius: 16px;
  text-decoration: none;
  transition: all 0.25s ease;
}

.wcu-hero-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.18);
  transform: translateY(-2px);
}

/* ── Trust Metrics Row ── */
.wcu-metrics-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  max-width: 1040px;
  margin: 0 auto;
}

.wcu-metric-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 20px 16px;
  text-align: center;
}

.wcu-metric-val {
  font-family: 'Epilogue', sans-serif;
  font-size: 28px;
  font-weight: 900;
  color: #C9A227;
  line-height: 1;
  margin-bottom: 6px;
}

.wcu-metric-lbl {
  font-size: 11.5px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ── Core Value Pillars (6 Grid) ── */
.wcu-pillars-section {
  max-width: 1200px;
  margin: 80px auto;
  padding: 0 24px;
}

.wcu-section-heading {
  text-align: center;
  max-width: 680px;
  margin: 0 auto 48px;
}

.wcu-section-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #E0F2FE;
  color: #1E40AF;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 4px 14px;
  border-radius: 20px;
  margin-bottom: 12px;
}

.wcu-section-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 34px;
  font-weight: 900;
  color: #0F172A;
  margin: 0 0 10px;
  letter-spacing: -0.02em;
}

.wcu-section-sub {
  font-size: 15px;
  color: #64748B;
  margin: 0;
}

.wcu-pillars-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.wcu-pillar-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 22px;
  padding: 30px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
  transition: all 0.25s ease;
}

.wcu-pillar-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
  border-color: #cbd5e1;
}

.wcu-pillar-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: #E0F2FE;
  color: #1E40AF;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
}

.wcu-pillar-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 8px;
}

.wcu-pillar-desc {
  font-size: 13.5px;
  color: #64748B;
  line-height: 1.6;
  margin: 0;
}

/* ── Comparison Section ── */
.wcu-compare-section {
  background: #ffffff;
  border-top: 1px solid #E2E8F0;
  border-bottom: 1px solid #E2E8F0;
  padding: 80px 0;
  margin-bottom: 80px;
}

.wcu-compare-container {
  max-width: 1060px;
  margin: 0 auto;
  padding: 0 24px;
}

.wcu-compare-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid #E2E8F0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.wcu-compare-table th {
  padding: 18px 24px;
  font-size: 14px;
  font-weight: 800;
  text-align: left;
}

.wcu-compare-table th.stayora-col {
  background: #1D4ED8;
  color: #ffffff;
  text-align: center;
  font-size: 15px;
}

.wcu-compare-table th.other-col {
  background: #f1f5f9;
  color: #64748B;
  text-align: center;
}

.wcu-compare-table td {
  padding: 16px 24px;
  font-size: 13.5px;
  border-bottom: 1px solid #f1f5f9;
}

.wcu-compare-table tr:last-child td {
  border-bottom: none;
}

.wcu-compare-table td.feature-title {
  font-weight: 700;
  color: #0F172A;
}

.wcu-compare-table td.stayora-val {
  text-align: center;
  font-weight: 800;
  color: #1E40AF;
  background: rgba(22, 165, 222, 0.04);
}

.wcu-compare-table td.other-val {
  text-align: center;
  color: #94a3b8;
}

/* ── Quality Standards Grid ── */
.wcu-standards-section {
  max-width: 1200px;
  margin: 0 auto 90px;
  padding: 0 24px;
}

.wcu-standards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.wcu-standard-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 24px 20px;
  text-align: center;
  transition: all 0.2s ease;
}

.wcu-standard-card:hover {
  border-color: #1D4ED8;
  transform: translateY(-2px);
}

.wcu-standard-icon {
  font-size: 36px;
  color: #1D4ED8;
  margin-bottom: 12px;
  display: block;
}

.wcu-standard-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 6px;
}

.wcu-standard-desc {
  font-size: 12.5px;
  color: #64748B;
  line-height: 1.5;
  margin: 0;
}

/* ── Testimonials ── */
.wcu-reviews-section {
  max-width: 1200px;
  margin: 0 auto 90px;
  padding: 0 24px;
}

.wcu-reviews-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.wcu-review-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 22px;
  padding: 28px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.wcu-review-quote {
  font-size: 14px;
  color: #334155;
  line-height: 1.6;
  font-style: italic;
  margin-bottom: 20px;
}

.wcu-review-user {
  display: flex;
  align-items: center;
  gap: 12px;
  border-top: 1px solid #f1f5f9;
  padding-top: 16px;
}

.wcu-review-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  object-fit: cover;
}

.wcu-review-name {
  font-size: 14px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.wcu-review-role {
  font-size: 11.5px;
  color: #64748B;
  margin: 0;
}

/* ── CTA Banner ── */
.wcu-cta-section {
  max-width: 1100px;
  margin: 0 auto 100px;
  padding: 0 24px;
}

.wcu-cta-card {
  background: linear-gradient(135deg, #0F172A 0%, #0F172A 100%);
  color: #ffffff;
  border-radius: 28px;
  padding: 50px 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
  position: relative;
  overflow: hidden;
}

.wcu-cta-card::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, rgba(22, 165, 222, 0.3) 0%, transparent 70%);
  pointer-events: none;
}

.wcu-cta-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 28px;
  font-weight: 900;
  margin: 0 0 10px;
  color: #ffffff;
}

.wcu-cta-sub {
  font-size: 14.5px;
  color: #94a3b8;
  line-height: 1.6;
  max-width: 540px;
  margin: 0;
}

.wcu-cta-btns {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  flex-shrink: 0;
  position: relative;
  z-index: 2;
}

/* Responsive */
@media (max-width: 1024px) {
  .wcu-hero-title { font-size: 38px; }
  .wcu-metrics-row { grid-template-columns: repeat(2, 1fr); }
  .wcu-pillars-grid { grid-template-columns: repeat(2, 1fr); }
  .wcu-standards-grid { grid-template-columns: repeat(2, 1fr); }
  .wcu-reviews-grid { grid-template-columns: 1fr; }
  .wcu-cta-card { flex-direction: column; text-align: center; }
  .wcu-cta-btns { justify-content: center; }
}

@media (max-width: 640px) {
  .wcu-hero-title { font-size: 30px; }
  .wcu-metrics-row { grid-template-columns: 1fr; }
  .wcu-pillars-grid { grid-template-columns: 1fr; }
  .wcu-standards-grid { grid-template-columns: 1fr; }
  .wcu-cta-card { padding: 32px 24px; }
}
</style>

<div class="wcu-page-wrap">

  <!-- ── 1. Hero Section ── -->
  <section class="wcu-hero">
    <div class="wcu-hero-mesh"></div>
    <div class="wcu-hero-container">
      <div class="wcu-hero-badge">
        <span class="material-symbols-outlined" style="font-size:16px;">verified</span>
        <span>India's Trusted Farmhouse Network</span>
      </div>

      <h1 class="wcu-hero-title">
        Why Stayora is India's <span>#1 Farmhouse Discovery</span> Platform
      </h1>

      <p class="wcu-hero-sub">
        We curate handpicked, verified private farmhouses and luxury pool villas across India. Enjoy transparent direct pricing, zero hidden brokerage, and seamless hospitality.
      </p>

      <div class="wcu-hero-actions">
        <a href="<?= url('farmhouses') ?>" class="wcu-hero-btn-primary">
          <span class="material-symbols-outlined" style="font-size:18px;">travel_explore</span>
          <span>Explore Verified Farmhouses</span>
        </a>

        <a href="<?= url('contact') ?>" class="wcu-hero-btn-secondary">
          <span class="material-symbols-outlined" style="font-size:18px;">support_agent</span>
          <span>Contact Concierge</span>
        </a>
      </div>

      <!-- Metrics Row -->
      <div class="wcu-metrics-row">
        <div class="wcu-metric-card">
          <div class="wcu-metric-val">100%</div>
          <div class="wcu-metric-lbl">Physically Verified Farms</div>
        </div>
        <div class="wcu-metric-card">
          <div class="wcu-metric-val">10,000+</div>
          <div class="wcu-metric-lbl">Happy Guests Hosted</div>
        </div>
        <div class="wcu-metric-card">
          <div class="wcu-metric-val">0%</div>
          <div class="wcu-metric-lbl">Hidden Brokerage</div>
        </div>
        <div class="wcu-metric-card">
          <div class="wcu-metric-val">4.9 ★</div>
          <div class="wcu-metric-lbl">Average Stay Rating</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── 2. 6 Core Pillars ── -->
  <section class="wcu-pillars-section">
    <div class="wcu-section-heading">
      <div class="wcu-section-tag">
        <span class="material-symbols-outlined" style="font-size:14px;">military_tech</span>
        <span>The Stayora Standard</span>
      </div>
      <h2 class="wcu-section-title">Built on Trust, Quality &amp; Transparency</h2>
      <p class="wcu-section-sub">We eliminate the uncertainty of rural weekend bookings by setting strict quality benchmarks for every listing.</p>
    </div>

    <div class="wcu-pillars-grid">
      <!-- 1 -->
      <div class="wcu-pillar-card">
        <div class="wcu-pillar-icon">
          <span class="material-symbols-outlined" style="font-size:26px;">fact_check</span>
        </div>
        <h3 class="wcu-pillar-title">100% Verified Properties</h3>
        <p class="wcu-pillar-desc">Every farmhouse is physically audited by our on-ground team to guarantee pristine hygiene, working pools, and genuine photos.</p>
      </div>

      <!-- 2 -->
      <div class="wcu-pillar-card">
        <div class="wcu-pillar-icon">
          <span class="material-symbols-outlined" style="font-size:26px;">price_check</span>
        </div>
        <h3 class="wcu-pillar-title">Zero Hidden Fees</h3>
        <p class="wcu-pillar-desc">What you see is what you pay. Transparent per-night pricing with direct host rates and no inflated broker commissions.</p>
      </div>

      <!-- 3 -->
      <div class="wcu-pillar-card">
        <div class="wcu-pillar-icon">
          <span class="material-symbols-outlined" style="font-size:26px;">chat</span>
        </div>
        <h3 class="wcu-pillar-title">Direct WhatsApp Concierge</h3>
        <p class="wcu-pillar-desc">Connect directly with farm managers on WhatsApp for instant confirmation, customized catering menus, and directions.</p>
      </div>

      <!-- 4 -->
      <div class="wcu-pillar-card">
        <div class="wcu-pillar-icon">
          <span class="material-symbols-outlined" style="font-size:26px;">pool</span>
        </div>
        <h3 class="wcu-pillar-title">Private Pools &amp; Lawns</h3>
        <p class="wcu-pillar-desc">All featured properties come with private swimming pools, manicured party lawns, and peaceful open-air spaces for your family.</p>
      </div>

      <!-- 5 -->
      <div class="wcu-pillar-card">
        <div class="wcu-pillar-icon">
          <span class="material-symbols-outlined" style="font-size:26px;">security</span>
        </div>
        <h3 class="wcu-pillar-title">Gated Privacy &amp; Safety</h3>
        <p class="wcu-pillar-desc">Perimeter boundary walls, on-site caretakers, and verified locations ensure total peace of mind for families and women groups.</p>
      </div>

      <!-- 6 -->
      <div class="wcu-pillar-card">
        <div class="wcu-pillar-icon">
          <span class="material-symbols-outlined" style="font-size:26px;">support_agent</span>
        </div>
        <h3 class="wcu-pillar-title">24/7 Ground Assistance</h3>
        <p class="wcu-pillar-desc">From check-in coordination to midnight emergency support, our concierge team is always just a quick phone call away.</p>
      </div>
    </div>
  </section>

  <!-- ── 3. Stayora vs Others Comparison ── -->
  <section class="wcu-compare-section">
    <div class="wcu-compare-container">
      <div class="wcu-section-heading">
        <div class="wcu-section-tag">
          <span class="material-symbols-outlined" style="font-size:14px;">compare_arrows</span>
          <span>Clear Difference</span>
        </div>
        <h2 class="wcu-section-title">Stayora vs Traditional Booking Methods</h2>
        <p class="wcu-section-sub">See how we compare against unverified classified ads and generic booking sites.</p>
      </div>

      <div style="overflow-x:auto;">
        <table class="wcu-compare-table">
          <thead>
            <tr>
              <th style="background:#F8FAFC;width:40%;">Key Features</th>
              <th class="stayora-col" style="width:30%;">Stayora Guarantee</th>
              <th class="other-col" style="width:30%;">Local Brokers / Portals</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="feature-title">On-Ground Physical Verification</td>
              <td class="stayora-val">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;vertical-align:middle;">check_circle</span>
                <span>100% Audited</span>
              </td>
              <td class="other-val">Rarely / Unverified</td>
            </tr>
            <tr>
              <td class="feature-title">Transparent Direct Pricing</td>
              <td class="stayora-val">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;vertical-align:middle;">check_circle</span>
                <span>Zero Broker Markup</span>
              </td>
              <td class="other-val">15% - 30% Added Markup</td>
            </tr>
            <tr>
              <td class="feature-title">Direct WhatsApp Host Access</td>
              <td class="stayora-val">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;vertical-align:middle;">check_circle</span>
                <span>Instant Connection</span>
              </td>
              <td class="other-val">Blocked / Middleman Only</td>
            </tr>
            <tr>
              <td class="feature-title">Swimming Pool &amp; Hygiene Standards</td>
              <td class="stayora-val">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;vertical-align:middle;">check_circle</span>
                <span>Strict Cleanliness Protocol</span>
              </td>
              <td class="other-val">Hit or Miss</td>
            </tr>
            <tr>
              <td class="feature-title">Screened Family &amp; Corporate Guests</td>
              <td class="stayora-val">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;vertical-align:middle;">check_circle</span>
                <span>Government ID Checked</span>
              </td>
              <td class="other-val">Unfiltered Guests</td>
            </tr>
            <tr>
              <td class="feature-title">Real-Time Host Calendar</td>
              <td class="stayora-val">
                <span class="material-symbols-outlined" style="color:#1D4ED8;font-size:18px;vertical-align:middle;">check_circle</span>
                <span>Live Availability</span>
              </td>
              <td class="other-val">Double Booking Risk</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── 4. Quality Standards Checklist ── -->
  <section class="wcu-standards-section">
    <div class="wcu-section-heading">
      <div class="wcu-section-tag">
        <span class="material-symbols-outlined" style="font-size:14px;">task_alt</span>
        <span>Quality Checklist</span>
      </div>
      <h2 class="wcu-section-title">What We Require From Every Farmhouse</h2>
      <p class="wcu-section-sub">Before any property goes live on Stayora, it must satisfy our mandatory comfort and safety checklist.</p>
    </div>

    <div class="wcu-standards-grid">
      <div class="wcu-standard-card">
        <span class="material-symbols-outlined wcu-standard-icon">ac_unit</span>
        <h3 class="wcu-standard-title">Furnished AC Bedrooms</h3>
        <p class="wcu-standard-desc">Spacious air-conditioned rooms with fresh linens, en-suite bathrooms, and clean bath amenities.</p>
      </div>

      <div class="wcu-standard-card">
        <span class="material-symbols-outlined wcu-standard-icon">water</span>
        <h3 class="wcu-standard-title">Filtered Clean Pools</h3>
        <p class="wcu-standard-desc">Regularly sanitized swimming pools with clear water, safety depths, and pool-side loungers.</p>
      </div>

      <div class="wcu-standard-card">
        <span class="material-symbols-outlined wcu-standard-icon">electric_bolt</span>
        <h3 class="wcu-standard-title">Power &amp; Water Backup</h3>
        <p class="wcu-standard-desc">Uninterrupted potable water and inverter/generator power backup for a smooth stay.</p>
      </div>

      <div class="wcu-standard-card">
        <span class="material-symbols-outlined wcu-standard-icon">local_parking</span>
        <h3 class="wcu-standard-title">Ample Secure Parking</h3>
        <p class="wcu-standard-desc">Gated on-site parking accommodating multiple cars, SUVs, and mini-buses safely.</p>
      </div>
    </div>
  </section>

  <!-- ── 5. Guest Testimonials ── -->
  <section class="wcu-reviews-section">
    <div class="wcu-section-heading">
      <div class="wcu-section-tag">
        <span class="material-symbols-outlined" style="font-size:14px;">reviews</span>
        <span>Verified Reviews</span>
      </div>
      <h2 class="wcu-section-title">Loved by Discerning Guests Across India</h2>
      <p class="wcu-section-sub">Real stories from families, couples, and corporate teams.</p>
    </div>

    <div class="wcu-reviews-grid">
      <!-- 1 -->
      <div class="wcu-review-card">
        <div class="wcu-review-quote">
          "Booking our weekend family reunion in Jaipur through Stayora was the smoothest experience. The villa and pool were even better than the photos, and the caretaker prepared amazing local food!"
        </div>
        <div class="wcu-review-user">
          <img src="https://ui-avatars.com/api/?name=Rohit+Mehta&background=16A5DE&color=fff" alt="Rohit Mehta" class="wcu-review-avatar">
          <div>
            <p class="wcu-review-name">Rohit Mehta</p>
            <p class="wcu-review-role">Family Stay • Jaipur</p>
          </div>
        </div>
      </div>

      <!-- 2 -->
      <div class="wcu-review-card">
        <div class="wcu-review-quote">
          "Finding a luxury private estate with a clean swimming pool for our anniversary celebration was effortless on Stayora. Transparent rates and instant manager confirmation made all the difference!"
        </div>
        <div class="wcu-review-user">
          <img src="https://ui-avatars.com/api/?name=Ananya+Deshmukh&background=0284c7&color=fff" alt="Ananya Deshmukh" class="wcu-review-avatar">
          <div>
            <p class="wcu-review-name">Ananya Deshmukh</p>
            <p class="wcu-review-role">Anniversary Stay • Udaipur</p>
          </div>
        </div>
      </div>

      <!-- 3 -->
      <div class="wcu-review-card">
        <div class="wcu-review-quote">
          "We hosted a 25-person startup offsite. The direct WhatsApp support helped us coordinate music rules, BBQ, and invoice details in 10 minutes. Zero brokerage hassle!"
        </div>
        <div class="wcu-review-user">
          <img src="https://ui-avatars.com/api/?name=Siddharth+Rao&background=16a34a&color=fff" alt="Siddharth Rao" class="wcu-review-avatar">
          <div>
            <p class="wcu-review-name">Siddharth Rao</p>
            <p class="wcu-review-role">Corporate Retreat • Hyderabad</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── 6. Bottom CTA Card ── -->
  <section class="wcu-cta-section">
    <div class="wcu-cta-card">
      <div>
        <div class="wcu-hero-badge" style="margin-bottom:12px;">
          <span class="material-symbols-outlined" style="font-size:14px;">travel_explore</span>
          <span>Ready for Your Next Escape?</span>
        </div>
        <h2 class="wcu-cta-title">Discover Handpicked Private Farmhouses</h2>
        <p class="wcu-cta-sub">
          Book private pool villas, nature retreats, and estates with transparent direct rates and instant manager confirmation.
        </p>
      </div>

      <div class="wcu-cta-btns">
        <a href="<?= url('farmhouses') ?>" class="wcu-hero-btn-primary">
          <span>Explore Farmhouses</span>
          <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
        </a>

        <a href="<?= url('contact') ?>" class="wcu-hero-btn-secondary">
          <span class="material-symbols-outlined" style="font-size:18px;">support_agent</span>
          <span>Contact Concierge</span>
        </a>
      </div>
    </div>
  </section>

</div>

<?php
include __DIR__ . "/Includes/footer.php";
?>