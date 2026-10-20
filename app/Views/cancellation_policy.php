<?php
$pageTitle       = "Cancellation & Refund Policy | Stayora";
$pageDescription = "Understand Stayora's transparent booking cancellation, date rescheduling, and refund terms.";
$canonicalUrl    = absolute_url('cancellation_policy');
include __DIR__ . "/Includes/header.php";
?>

<style>
/* ═══════════════════════════════════════════════════════════
   CANCELLATION & REFUND POLICY — LUXURY DESIGN SYSTEM
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

.refund-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #E2E8F0;
  margin: 20px 0;
}

.refund-table th {
  background: #F8FAFC;
  color: #0F172A;
  font-family: 'Epilogue', sans-serif;
  font-weight: 800;
  font-size: 12.5px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 14px 18px;
  text-align: left;
  border-bottom: 1px solid #E2E8F0;
}

.refund-table td {
  padding: 14px 18px;
  font-size: 13.5px;
  font-weight: 600;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}

.refund-table tr:last-child td {
  border-bottom: none;
}

.refund-badge-green {
  display: inline-block;
  background: #dcfce7;
  color: #15803d;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
}

.refund-badge-amber {
  display: inline-block;
  background: #fef3c7;
  color: #b45309;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
}

.refund-badge-rose {
  display: inline-block;
  background: #fee2e2;
  color: #b91c1c;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
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
        <span class="material-symbols-outlined" style="font-size:16px;">receipt_long</span>
        <span>Transparent Rescheduling &amp; Refunds</span>
      </div>

      <h1 class="legal-hero-title">
        Cancellation &amp; <span>Refund Policy</span>
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
          <li><a href="#overview" class="legal-toc-link">1. Policy Overview</a></li>
          <li><a href="#matrix" class="legal-toc-link">2. Refund Timeline Matrix</a></li>
          <li><a href="#full-refund" class="legal-toc-link">3. Full Refund Window</a></li>
          <li><a href="#partial-refund" class="legal-toc-link">4. Partial Refund Window</a></li>
          <li><a href="#rescheduling" class="legal-toc-link">5. Date Rescheduling</a></li>
          <li><a href="#force-majeure" class="legal-toc-link">6. Severe Weather &amp; Events</a></li>
          <li><a href="#processing" class="legal-toc-link">7. Payout Processing Time</a></li>
          <li><a href="#support" class="legal-toc-link">8. Requesting Cancellation</a></li>
        </ul>
      </div>
    </aside>

    <!-- Document Content -->
    <main class="legal-content">

      <!-- 1. Overview -->
      <section class="legal-card" id="overview">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">info</span>
          </div>
          <h2 class="legal-card-title">1. Policy Overview</h2>
        </div>
        <div class="legal-card-body">
          <p>
            At <strong>Stayora</strong>, we understand that travel plans and family occasions can change unexpectedly. Because private farmhouses and estate villas are booked exclusively for your single group (preventing other guests from reserving that weekend), our cancellation terms balance guest flexibility with host commitments.
          </p>
          <p>
            This policy applies uniformly across all verified farmhouse reservations confirmed via the Stayora platform or authorized WhatsApp Concierge.
          </p>
        </div>
      </section>

      <!-- 2. Matrix -->
      <section class="legal-card" id="matrix">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">table_chart</span>
          </div>
          <h2 class="legal-card-title">2. Standard Refund Timeline Matrix</h2>
        </div>
        <div class="legal-card-body">
          <table class="refund-table">
            <thead>
              <tr>
                <th>Cancellation Notice</th>
                <th>Eligible Refund</th>
                <th>Rescheduling Option</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>7+ Days</strong> before check-in</td>
                <td><span class="refund-badge-green">100% Full Refund</span></td>
                <td>Free date change within 90 days</td>
              </tr>
              <tr>
                <td><strong>3 to 6 Days</strong> before check-in</td>
                <td><span class="refund-badge-amber">50% Partial Refund</span></td>
                <td>Date change subject to host approval</td>
              </tr>
              <tr>
                <td><strong>Less than 72 Hours</strong> before check-in</td>
                <td><span class="refund-badge-rose">Non-Refundable</span></td>
                <td>Subject to villa emergency policy</td>
              </tr>
              <tr>
                <td><strong>No-Show on Check-In Date</strong></td>
                <td><span class="refund-badge-rose">0% Refund</span></td>
                <td>Not applicable</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 3. Full Refund -->
      <section class="legal-card" id="full-refund">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">check_circle</span>
          </div>
          <h2 class="legal-card-title">3. Full Refund Eligibility (7+ Days)</h2>
        </div>
        <div class="legal-card-body">
          <p>
            If you cancel your reservation at least <strong>7 full days (168 hours)</strong> prior to the scheduled check-in time, you are eligible for a <strong>100% refund</strong> of the booking advance paid, minus standard third-party payment gateway transaction fees (if applicable).
          </p>
        </div>
      </section>

      <!-- 4. Partial Refund -->
      <section class="legal-card" id="partial-refund">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">hourglass_bottom</span>
          </div>
          <h2 class="legal-card-title">4. Partial Refund Window (3 to 6 Days)</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Cancellations made between 3 and 6 days before check-in receive a <strong>50% refund</strong>. The remaining 50% is disbursed to the property host to compensate for blocked calendar inventory, staffing preparation, and catering arrangements.
          </p>
        </div>
      </section>

      <!-- 5. Rescheduling -->
      <section class="legal-card" id="rescheduling">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">event_repeat</span>
          </div>
          <h2 class="legal-card-title">5. Date Rescheduling &amp; Modifications</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Need to change your stay date instead of cancelling?
          </p>
          <ul>
            <li>Guests may request a date change at least 5 days prior to check-in without penalty, subject to property calendar availability.</li>
            <li>If shifting to a date with a higher weekend or peak holiday tariff, the guest shall pay the price differential.</li>
          </ul>
        </div>
      </section>

      <!-- 6. Force Majeure -->
      <section class="legal-card" id="force-majeure">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">cloud_sync</span>
          </div>
          <h2 class="legal-card-title">6. Force Majeure &amp; Emergency Incidents</h2>
        </div>
        <div class="legal-card-body">
          <p>
            In the rare event of severe weather disasters (floods, cyclones), government curfew orders, or acute property malfunction (e.g., sudden electrical grid breakdown), Stayora will offer a <strong>100% booking credit voucher</strong> valid for 12 months or facilitate a full refund.
          </p>
        </div>
      </section>

      <!-- 7. Processing Time -->
      <section class="legal-card" id="processing">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">payments</span>
          </div>
          <h2 class="legal-card-title">7. Refund Processing &amp; Payout Timelines</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Approved refunds are initiated within <strong>24 to 48 business hours</strong>. Depending on your banking institution, funds typically reflect in your original payment method (UPI / Credit Card / Net Banking) within <strong>5 to 7 business days</strong>.
          </p>
        </div>
      </section>

      <!-- 8. Requesting -->
      <section class="legal-card" id="support">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">support_agent</span>
          </div>
          <h2 class="legal-card-title">8. How to Request Cancellation</h2>
        </div>
        <div class="legal-card-body">
          <p>
            To cancel or modify your reservation, contact your designated Stayora Concierge with your Booking ID:
          </p>
          <div style="background:#F8FAFC;padding:20px;border-radius:16px;border:1px solid #E2E8F0;margin-top:16px;">
            <p style="margin:0 0 6px;"><strong>WhatsApp Concierge:</strong> +91 98765 43210 (Fastest Response)</p>
            <p style="margin:0 0 6px;"><strong>Email:</strong> bookings@stayora.com</p>
            <p style="margin:0;"><strong>Hours:</strong> Monday to Sunday, 9:00 AM - 10:00 PM</p>
          </div>
        </div>
      </section>

    </main>

  </div>

</div>

<?php
include __DIR__ . "/Includes/footer.php";
?>
