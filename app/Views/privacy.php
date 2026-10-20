<?php
$pageTitle       = "Privacy Policy | Stayora";
$pageDescription = "Learn how Stayora securely handles customer data, contact details, payment privacy, and platform account information.";
$canonicalUrl    = absolute_url('privacy');
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
        <span class="material-symbols-outlined" style="font-size:16px;">shield_lock</span>
        <span>Trust, Safety &amp; Data Security</span>
      </div>

      <h1 class="legal-hero-title">
        Stayora <span>Privacy Policy</span>
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
          <li><a href="#intro" class="legal-toc-link">1. Introduction &amp; Scope</a></li>
          <li><a href="#collect" class="legal-toc-link">2. Information We Collect</a></li>
          <li><a href="#usage" class="legal-toc-link">3. How We Use Data</a></li>
          <li><a href="#sharing" class="legal-toc-link">4. Data Sharing &amp; Hosts</a></li>
          <li><a href="#security" class="legal-toc-link">5. Data Protection &amp; SSL</a></li>
          <li><a href="#cookies" class="legal-toc-link">6. Cookies &amp; Tracking</a></li>
          <li><a href="#rights" class="legal-toc-link">7. Your Privacy Rights</a></li>
          <li><a href="#contact" class="legal-toc-link">8. Contact Data Officer</a></li>
        </ul>
      </div>
    </aside>

    <!-- Document Content -->
    <main class="legal-content">

      <!-- 1. Intro -->
      <section class="legal-card" id="intro">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">gavel</span>
          </div>
          <h2 class="legal-card-title">1. Introduction &amp; Scope</h2>
        </div>
        <div class="legal-card-body">
          <p>
            At <strong>Stayora</strong> (operated as an authorized property management and farmhouse retreat discovery portal in India), we value the trust you place in us when sharing your personal information.
          </p>
          <p>
            This Privacy Policy sets out how Stayora collects, protects, uses, and shares your personal data when you visit our website (<strong>stayora.com</strong>), book a private estate, or list a property as a verified farmhouse host partner.
          </p>
          <div class="legal-highlight-box blue">
            <span class="material-symbols-outlined" style="font-size:20px;">info</span>
            <div>
              <strong>Zero-Spam Pledge:</strong> We never sell, rent, or lease your personal contact details or phone number to third-party telemarketing networks.
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Information We Collect -->
      <section class="legal-card" id="collect">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">folder_shared</span>
          </div>
          <h2 class="legal-card-title">2. Information We Collect</h2>
        </div>
        <div class="legal-card-body">
          <p>We collect information to provide seamless booking reservations and verified property hosting:</p>
          <ul>
            <li><strong>Guest Account &amp; Reservation Details:</strong> Full Name, WhatsApp mobile number, email address, guest group size, check-in dates, and special occasion requests.</li>
            <li><strong>Host Property Information:</strong> Property title, location, GPS coordinates, property amenities, owner identification, caretaker phone numbers, and bank account settlement details for booking payouts.</li>
            <li><strong>Automated Device &amp; Usage Data:</strong> IP address, device browser type, page visit logs, and referring URLs to optimize page load speeds and prevent unauthorized fraudulent access.</li>
          </ul>
        </div>
      </section>

      <!-- 3. How We Use Data -->
      <section class="legal-card" id="usage">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">settings_suggest</span>
          </div>
          <h2 class="legal-card-title">3. How We Use Your Data</h2>
        </div>
        <div class="legal-card-body">
          <p>Your information is used strictly for legitimate marketplace functions:</p>
          <ul>
            <li>Facilitating reservation inquiries and direct WhatsApp concierge booking confirmations.</li>
            <li>Enabling property hosts to prepare their villas, arrange on-ground caretaker hospitality, and verify guest count.</li>
            <li>Conducting property safety audits, verification checks, and host payout settlements.</li>
            <li>Sending crucial booking updates, invoice receipts, and customer support responses.</li>
          </ul>
        </div>
      </section>

      <!-- 4. Sharing & Disclosure -->
      <section class="legal-card" id="sharing">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">handshake</span>
          </div>
          <h2 class="legal-card-title">4. Data Sharing &amp; Host Safeguards</h2>
        </div>
        <div class="legal-card-body">
          <p>
            When a guest places a booking inquiry or reservation for a private farmhouse, necessary stay details (e.g., Guest Name, WhatsApp Number, and Stay Dates) are shared securely with the assigned farmhouse host partner and property manager to coordinate check-in.
          </p>
          <div class="legal-highlight-box">
            <span class="material-symbols-outlined" style="font-size:20px;">verified_user</span>
            <div>
              <strong>Privacy Protection Rule:</strong> Private guest demographic data is never exposed in publicly accessible API endpoints or indexable search engine records.
            </div>
          </div>
        </div>
      </section>

      <!-- 5. Security & SSL -->
      <section class="legal-card" id="security">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">lock_clock</span>
          </div>
          <h2 class="legal-card-title">5. Data Security &amp; Encryption</h2>
        </div>
        <div class="legal-card-body">
          <p>
            We implement 256-bit SSL encryption, parameterized SQL queries, and encrypted cryptographic session cookies (CryptoHelper) to safeguard user passwords and confidential information against data breaches or unauthorized access.
          </p>
        </div>
      </section>

      <!-- 6. Cookies -->
      <section class="legal-card" id="cookies">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">cookie</span>
          </div>
          <h2 class="legal-card-title">6. Cookies &amp; Local Storage</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Stayora utilizes essential session cookies and local storage tokens to keep you logged into your dashboard, save your favorite wishlist farmhouses, and remember your search preferences. Read our dedicated <a href="<?= url('cookie-policy') ?>" style="color:#1D4ED8;font-weight:700;text-decoration:none;">Cookie Policy</a> for full details.
          </p>
        </div>
      </section>

      <!-- 7. Your Rights -->
      <section class="legal-card" id="rights">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">account_circle</span>
          </div>
          <h2 class="legal-card-title">7. Your Rights &amp; Choices</h2>
        </div>
        <div class="legal-card-body">
          <p>Under Indian Information Technology (IT Act &amp; DPDP Guidelines), you have the right to:</p>
          <ul>
            <li>Access and review your stored account information from your Profile settings.</li>
            <li>Update or rectify inaccurate profile credentials and phone numbers anytime.</li>
            <li>Request full deletion of your user account and personal inquiry records by contacting our support team.</li>
          </ul>
        </div>
      </section>

      <!-- 8. Contact -->
      <section class="legal-card" id="contact">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">mail</span>
          </div>
          <h2 class="legal-card-title">8. Contact Our Data Protection Officer</h2>
        </div>
        <div class="legal-card-body">
          <p>
            If you have questions regarding this Privacy Policy or wish to exercise your data rights, reach out to our privacy compliance team:
          </p>
          <div style="background:#F8FAFC;padding:20px;border-radius:16px;border:1px solid #E2E8F0;margin-top:16px;">
            <p style="margin:0 0 6px;"><strong>Email:</strong> privacy@stayora.com / support@stayora.com</p>
            <p style="margin:0 0 6px;"><strong>Support Helpline:</strong> +91 98765 43210</p>
            <p style="margin:0;"><strong>Office:</strong> Stayora Technologies, Startup India Hub, Madhya Pradesh, India</p>
          </div>
        </div>
      </section>

    </main>

  </div>

</div>

<?php
include __DIR__ . "/Includes/footer.php";
?>