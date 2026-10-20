<?php
$pageTitle       = "Cookie Policy | Stayora";
$pageDescription = "Learn how Stayora uses cookies to remember your preferences and deliver an optimal browsing experience.";
$canonicalUrl    = absolute_url('cookie_policy');
include __DIR__ . "/Includes/header.php";
?>

<style>
/* ═══════════════════════════════════════════════════════════
   COOKIE POLICY — LUXURY DESIGN SYSTEM
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

.cookie-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #E2E8F0;
  margin: 20px 0;
}

.cookie-table th {
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

.cookie-table td {
  padding: 14px 18px;
  font-size: 13.5px;
  font-weight: 600;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}

.cookie-table tr:last-child td {
  border-bottom: none;
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
        <span class="material-symbols-outlined" style="font-size:16px;">cookie</span>
        <span>Transparency &amp; Tracking Policy</span>
      </div>

      <h1 class="legal-hero-title">
        Stayora <span>Cookie Policy</span>
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
          <li><a href="#what-are-cookies" class="legal-toc-link">1. What Are Cookies?</a></li>
          <li><a href="#types-used" class="legal-toc-link">2. Categories We Use</a></li>
          <li><a href="#cookie-table" class="legal-toc-link">3. Cookie Table Breakdown</a></li>
          <li><a href="#essential" class="legal-toc-link">4. Essential Cookies</a></li>
          <li><a href="#analytics" class="legal-toc-link">5. Analytics &amp; Performance</a></li>
          <li><a href="#managing" class="legal-toc-link">6. Managing Your Cookies</a></li>
          <li><a href="#contact" class="legal-toc-link">7. Questions &amp; Support</a></li>
        </ul>
      </div>
    </aside>

    <!-- Document Content -->
    <main class="legal-content">

      <!-- 1. What Are Cookies -->
      <section class="legal-card" id="what-are-cookies">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">info</span>
          </div>
          <h2 class="legal-card-title">1. What Are Cookies?</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Cookies are small text data files placed on your computer, smartphone, or tablet when you visit websites. They help websites remember your device, keep you signed in securely, save your farmhouse search filters, and enhance overall browsing performance.
          </p>
        </div>
      </section>

      <!-- 2. Categories -->
      <section class="legal-card" id="types-used">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">category</span>
          </div>
          <h2 class="legal-card-title">2. Categories of Cookies We Use</h2>
        </div>
        <div class="legal-card-body">
          <ul>
            <li><strong>Strictly Necessary / Authentication Cookies:</strong> Essential for you to browse Stayora, log into your user or owner workspace, and access secured booking pages.</li>
            <li><strong>Functional / Preference Cookies:</strong> Remembers your saved wishlist favorites, location filter preferences, and theme preferences.</li>
            <li><strong>Performance &amp; Analytics Cookies:</strong> Gathers anonymized aggregated data regarding website traffic, load speeds, and popular farmhouse listings to optimize our user experience.</li>
          </ul>
        </div>
      </section>

      <!-- 3. Cookie Table -->
      <section class="legal-card" id="cookie-table">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">table_chart</span>
          </div>
          <h2 class="legal-card-title">3. Detailed Cookie Breakdown</h2>
        </div>
        <div class="legal-card-body">
          <table class="cookie-table">
            <thead>
              <tr>
                <th>Cookie Name</th>
                <th>Category</th>
                <th>Purpose</th>
                <th>Duration</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><code>PHPSESSID</code></td>
                <td>Essential</td>
                <td>Maintains authenticated user session</td>
                <td>Session</td>
              </tr>
              <tr>
                <td><code>stayora_remember</code></td>
                <td>Functional</td>
                <td>Preserves "Remember Me" login status</td>
                <td>30 Days</td>
              </tr>
              <tr>
                <td><code>wishlist_items</code></td>
                <td>Functional</td>
                <td>Saves favorite farmhouses for guests</td>
                <td>90 Days</td>
              </tr>
              <tr>
                <td><code>_ga, _gid</code></td>
                <td>Analytics</td>
                <td>Anonymized Google traffic statistics</td>
                <td>2 Years</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 4. Essential -->
      <section class="legal-card" id="essential">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">verified_user</span>
          </div>
          <h2 class="legal-card-title">4. Essential &amp; Security Cookies</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Essential cookies cannot be switched off in our systems as they are required for basic site navigation, CSRF protection, and account security. You can set your browser to block them, but certain parts of the site will not function.
          </p>
        </div>
      </section>

      <!-- 5. Analytics -->
      <section class="legal-card" id="analytics">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">query_stats</span>
          </div>
          <h2 class="legal-card-title">5. Performance &amp; Analytics</h2>
        </div>
        <div class="legal-card-body">
          <p>
            These cookies help us understand how visitors interact with our pages, which pool villas are in highest demand, and detect any broken links or performance bottlenecks across mobile devices. All data is collected in aggregate, anonymized formats.
          </p>
        </div>
      </section>

      <!-- 6. Managing -->
      <section class="legal-card" id="managing">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">tune</span>
          </div>
          <h2 class="legal-card-title">6. How to Manage or Disable Cookies</h2>
        </div>
        <div class="legal-card-body">
          <p>
            Most modern web browsers allow you to control cookies through their browser settings. You can choose to block all cookies, delete existing cookies, or receive a prompt before a cookie is stored:
          </p>
          <ul>
            <li><strong>Google Chrome:</strong> Settings → Privacy &amp; Security → Third-Party Cookies.</li>
            <li><strong>Apple Safari:</strong> Preferences → Privacy → Manage Website Data.</li>
            <li><strong>Mozilla Firefox:</strong> Options → Privacy &amp; Security → Cookies and Site Data.</li>
          </ul>
        </div>
      </section>

      <!-- 7. Contact -->
      <section class="legal-card" id="contact">
        <div class="legal-card-header">
          <div class="legal-icon-wrap">
            <span class="material-symbols-outlined">contact_support</span>
          </div>
          <h2 class="legal-card-title">7. Questions &amp; Support</h2>
        </div>
        <div class="legal-card-body">
          <p>
            If you have questions about how Stayora uses cookies and tracking technologies, please contact our data privacy team at <a href="mailto:privacy@stayora.com" style="color:#1D4ED8;font-weight:700;text-decoration:none;">privacy@stayora.com</a>.
          </p>
        </div>
      </section>

    </main>

  </div>

</div>

<?php
include __DIR__ . "/Includes/footer.php";
?>
