<?php
$pageTitle       = "About Us | Stayora - India's Trusted Farmhouse & Villa Booking Platform";
$pageDescription = "Learn about Stayora's mission to connect travelers and event planners with extraordinary private farmhouses, pool villas, and agrarian getaways across India.";
$pageKeywords    = "about stayora, farmhouse rental company, villa booking platform india, vacation rental company";
$canonicalUrl    = absolute_url('about');
include __DIR__ . "/Includes/header.php"; 
?>

<style>
/* ═══════════════════════════════════════════════════════════
   ABOUT US PAGE — MODERN LUXURY DESIGN SYSTEM
   ═══════════════════════════════════════════════════════════ */

.abt-page-wrap {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
  overflow-x: hidden;
}

/* Hero Section */
.abt-hero {
  position: relative;
  min-height: 520px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0F172A;
  overflow: hidden;
  padding: 100px 20px 80px;
}

.abt-hero-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.38;
  transform: scale(1.05);
  transition: transform 10s ease;
}

.abt-hero-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.85) 100%);
}

.abt-hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 860px;
  margin: 0 auto;
}

.abt-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(22, 165, 222, 0.15);
  border: 1px solid rgba(22, 165, 222, 0.4);
  color: #C9A227;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 6px 16px;
  border-radius: 30px;
  margin-bottom: 20px;
  backdrop-filter: blur(8px);
}

.abt-hero h1 {
  font-size: 46px;
  font-weight: 900;
  color: #ffffff;
  line-height: 1.15;
  margin: 0 0 18px;
  letter-spacing: -0.02em;
}

.abt-hero h1 span {
  color: #C9A227;
}

.abt-hero p {
  font-size: 18px;
  color: #E2E8F0;
  line-height: 1.6;
  max-width: 680px;
  margin: 0 auto 30px;
  font-weight: 400;
}

.abt-hero-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
}

.abt-btn-primary {
  background: #1D4ED8;
  color: #ffffff;
  font-weight: 800;
  font-size: 14px;
  padding: 13px 26px;
  border-radius: 14px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 8px 25px rgba(22, 165, 222, 0.4);
  transition: all 0.25s ease;
}

.abt-btn-primary:hover {
  background: #1E40AF;
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(2, 132, 199, 0.5);
  color: #ffffff;
}

.abt-btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  font-weight: 700;
  font-size: 14px;
  padding: 13px 26px;
  border-radius: 14px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  backdrop-filter: blur(8px);
  transition: all 0.25s ease;
}

.abt-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

/* Stats Bar */
.abt-stats-wrap {
  max-width: 1140px;
  margin: -45px auto 60px;
  position: relative;
  z-index: 10;
  padding: 0 20px;
}

.abt-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 24px;
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.abt-stat-item {
  padding: 26px 20px;
  text-align: center;
  border-right: 1px solid #f1f5f9;
  transition: background 0.2s ease;
}

.abt-stat-item:last-child {
  border-right: none;
}

.abt-stat-item:hover {
  background: #F8FAFC;
}

.abt-stat-num {
  font-size: 34px;
  font-weight: 900;
  color: #1D4ED8;
  letter-spacing: -0.02em;
  margin-bottom: 4px;
}

.abt-stat-lbl {
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Who We Are & Story Section */
.abt-section {
  max-width: 1140px;
  margin: 0 auto;
  padding: 60px 20px;
}

.abt-dual-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  align-items: center;
}

.abt-tag {
  color: #1E40AF;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.abt-heading {
  font-size: 34px;
  font-weight: 900;
  color: #0F172A;
  line-height: 1.25;
  margin: 0 0 20px;
  letter-spacing: -0.02em;
}

.abt-p {
  font-size: 15px;
  color: #475569;
  line-height: 1.7;
  margin-bottom: 18px;
}

.abt-visual-box {
  position: relative;
}

.abt-img-main {
  width: 100%;
  height: 420px;
  object-fit: cover;
  border-radius: 24px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.abt-img-float {
  position: absolute;
  bottom: -25px;
  left: -25px;
  width: 200px;
  height: 200px;
  object-fit: cover;
  border-radius: 20px;
  border: 6px solid #ffffff;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

/* Mission & Vision */
.abt-mv-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-top: 40px;
}

.abt-mv-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 24px;
  padding: 36px 30px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.abt-mv-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #1D4ED8, #1E40AF);
}

.abt-mv-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 35px rgba(22, 165, 222, 0.12);
  border-color: #DBEAFE;
}

.abt-mv-icon {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  background: #E0F2FE;
  color: #1E40AF;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.abt-mv-title {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 12px;
}

/* Core Values / Why Choose Us */
.abt-values-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-top: 40px;
}

.abt-val-card {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 26px 20px;
  text-align: center;
  transition: all 0.25s ease;
}

.abt-val-card:hover {
  background: #ffffff;
  border-color: #1D4ED8;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(22, 165, 222, 0.1);
}

.abt-val-icon {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  color: #1D4ED8;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.abt-val-title {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 8px;
}

.abt-val-desc {
  font-size: 13px;
  color: #64748B;
  line-height: 1.5;
  margin: 0;
}

/* Host CTA Banner */
.abt-cta-wrap {
  max-width: 1140px;
  margin: 40px auto 80px;
  padding: 0 20px;
}

.abt-cta-box {
  background: linear-gradient(135deg, #0F172A 0%, #0F172A 100%);
  border-radius: 30px;
  padding: 50px 40px;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
}

.abt-cta-box::after {
  content: '';
  position: absolute;
  right: -50px;
  bottom: -50px;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(22, 165, 222, 0.25) 0%, rgba(22, 165, 222, 0) 70%);
  border-radius: 50%;
  pointer-events: none;
}

.abt-cta-left {
  max-width: 600px;
  position: relative;
  z-index: 2;
}

.abt-cta-title {
  font-size: 32px;
  font-weight: 900;
  line-height: 1.2;
  margin: 0 0 12px;
}

.abt-cta-sub {
  font-size: 15px;
  color: #94a3b8;
  line-height: 1.6;
  margin: 0;
}

.abt-cta-btn {
  background: #1D4ED8;
  color: #ffffff;
  font-weight: 800;
  font-size: 15px;
  padding: 15px 30px;
  border-radius: 16px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 8px 25px rgba(22, 165, 222, 0.4);
  transition: all 0.25s ease;
  white-space: nowrap;
  position: relative;
  z-index: 2;
}

.abt-cta-btn:hover {
  background: #1E40AF;
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(2, 132, 199, 0.5);
  color: #ffffff;
}

/* Responsive Overrides */
@media (max-width: 992px) {
  .abt-stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  .abt-stat-item:nth-child(2) {
    border-right: none;
  }
  .abt-stat-item:nth-child(-n+2) {
    border-bottom: 1px solid #f1f5f9;
  }
  .abt-dual-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  .abt-img-float {
    display: none;
  }
  .abt-values-grid {
    grid-template-columns: 1fr 1fr;
  }
  .abt-cta-box {
    flex-direction: column;
    text-align: center;
    padding: 40px 24px;
  }
  .abt-cta-left {
    max-width: 100%;
  }
  .abt-cta-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .abt-hero {
    min-height: 420px;
    padding: 85px 16px 60px;
  }
  .abt-hero h1 {
    font-size: 30px;
  }
  .abt-hero p {
    font-size: 15px;
  }
  .abt-stats-wrap {
    margin-top: -30px;
  }
  .abt-stat-num {
    font-size: 26px;
  }
  .abt-heading {
    font-size: 24px;
  }
  .abt-mv-grid {
    grid-template-columns: 1fr;
  }
  .abt-values-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<div class="abt-page-wrap">

  <!-- ── HERO SECTION ── -->
  <section class="abt-hero">
    <img src="<?= asset('assets/images/uploads/about.jpg') ?>" alt="Stayora Sanctuary Farmhouses" class="abt-hero-bg" onerror="this.src='https://images.unsplash.com/photo-1500382017468-9049fee79a70?auto=format&fit=crop&w=1400&q=80'">
    <div class="abt-hero-gradient"></div>

    <div class="abt-hero-content">
      <div class="abt-hero-badge">
        <span class="material-symbols-outlined" style="font-size:16px;">eco</span>
        <span>India's Direct Farmhouse Marketplace</span>
      </div>
      <h1>Sanctuary Away From The City. <span>Pure &amp; Direct.</span></h1>
      <p>We connect city travelers directly with authentic, handpicked farmhouses and private villas across India with zero hidden markups.</p>
      <div class="abt-hero-actions">
        <a href="<?= url('farmhouses') ?>" class="abt-btn-primary">
          <span class="material-symbols-outlined" style="font-size:18px;">explore</span>
          <span>Explore Farmhouses</span>
        </a>
        <a href="<?= url('contact') ?>" class="abt-btn-secondary">
          <span class="material-symbols-outlined" style="font-size:18px;">support_agent</span>
          <span>Contact Concierge</span>
        </a>
      </div>
    </div>
  </section>

  <!-- ── STATS BAR ── -->
  <div class="abt-stats-wrap">
    <div class="abt-stats-grid">
      <div class="abt-stat-item">
        <div class="abt-stat-num">100+</div>
        <div class="abt-stat-lbl">Verified Farmhouses</div>
      </div>
      <div class="abt-stat-item">
        <div class="abt-stat-num">10,000+</div>
        <div class="abt-stat-lbl">Happy Guests</div>
      </div>
      <div class="abt-stat-item">
        <div class="abt-stat-num">20+</div>
        <div class="abt-stat-lbl">Destination Cities</div>
      </div>
      <div class="abt-stat-item">
        <div class="abt-stat-num">4.9★</div>
        <div class="abt-stat-lbl">Guest Satisfaction</div>
      </div>
    </div>
  </div>

  <!-- ── WHO WE ARE & STORY ── -->
  <section class="abt-section">
    <div class="abt-dual-grid">
      <div>
        <div class="abt-tag">
          <span class="material-symbols-outlined" style="font-size:16px;">handshake</span>
          <span>Who We Are</span>
        </div>
        <h2 class="abt-heading">Cultivating Direct Connections Between Hosts &amp; Guests.</h2>
        <p class="abt-p">At Stayora, we believe the best weekend getaways happen when you're surrounded by open skies, fresh air, and lush orchards. We created a curated platform for travelers seeking the rustic luxury of authentic Indian farmhouses.</p>
        <p class="abt-p">By eliminating unnecessary brokerages, we ensure transparent rates for travelers while directly empowering farmhouse owners and supporting local rural economies.</p>
        
        <div style="display:flex;align-items:center;gap:14px;margin-top:24px;">
          <a href="<?= url('farmhouses') ?>" class="abt-btn-primary">
            <span>Find A Retreat</span>
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
          </a>
        </div>
      </div>

      <div class="abt-visual-box">
        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=900&q=80" alt="Farmhouse garden" class="abt-img-main">
        <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=500&q=80" alt="Luxury villa poolside" class="abt-img-float">
      </div>
    </div>
  </section>

  <!-- ── MISSION & VISION ── -->
  <section style="background:#F8FAFC;border-top:1px solid #E2E8F0;border-bottom:1px solid #E2E8F0;padding:70px 20px;">
    <div style="max-width:1140px;margin:0 auto;">
      <div style="text-align:center;max-width:650px;margin:0 auto 40px;">
        <div class="abt-tag" style="justify-content:center;">
          <span class="material-symbols-outlined" style="font-size:16px;">target</span>
          <span>Purpose &amp; Goals</span>
        </div>
        <h2 class="abt-heading" style="margin-bottom:10px;">Our Mission &amp; Vision</h2>
        <p class="abt-p" style="margin:0;">Building India's most trusted rural hospitality ecosystem.</p>
      </div>

      <div class="abt-mv-grid">
        <div class="abt-mv-card">
          <div class="abt-mv-icon">
            <span class="material-symbols-outlined" style="font-size:28px;">nature_people</span>
          </div>
          <h3 class="abt-mv-title">Our Mission</h3>
          <p class="abt-p" style="margin:0;">To inspire authentic farm retreats across India, providing city travelers with memorable nature escapes while helping property owners earn sustained income from their agricultural estates.</p>
        </div>

        <div class="abt-mv-card">
          <div class="abt-mv-icon">
            <span class="material-symbols-outlined" style="font-size:28px;">visibility</span>
          </div>
          <h3 class="abt-mv-title">Our Vision</h3>
          <p class="abt-p" style="margin:0;">To become India's premier platform for farmhouse stays and celebrations, setting the benchmark for property transparency, guest safety, and sustainable eco-tourism.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CORE VALUES / WHY CHOOSE US ── -->
  <section class="abt-section">
    <div style="text-align:center;max-width:650px;margin:0 auto 30px;">
      <div class="abt-tag" style="justify-content:center;">
        <span class="material-symbols-outlined" style="font-size:16px;">verified_user</span>
        <span>The Stayora Advantage</span>
      </div>
      <h2 class="abt-heading" style="margin-bottom:10px;">Why Travelers Choose Us</h2>
      <p class="abt-p" style="margin:0;">Every detail is engineered for simplicity, trust, and peace of mind.</p>
    </div>

    <div class="abt-values-grid">
      <div class="abt-val-card">
        <div class="abt-val-icon">
          <span class="material-symbols-outlined" style="font-size:24px;">verified</span>
        </div>
        <h4 class="abt-val-title">100% Verified Listings</h4>
        <p class="abt-val-desc">Every farmhouse is verified with real photos, accurate amenities, and checked host details.</p>
      </div>

      <div class="abt-val-card">
        <div class="abt-val-icon">
          <span class="material-symbols-outlined" style="font-size:24px;">payments</span>
        </div>
        <h4 class="abt-val-title">Zero Hidden Fees</h4>
        <p class="abt-val-desc">Direct owner pricing with complete transparency. No surprises at checkout.</p>
      </div>

      <div class="abt-val-card">
        <div class="abt-val-icon">
          <span class="material-symbols-outlined" style="font-size:24px;">chat</span>
        </div>
        <h4 class="abt-val-title">Instant WhatsApp Booking</h4>
        <p class="abt-val-desc">Confirm your reservation and talk to host or support within minutes.</p>
      </div>

      <div class="abt-val-card">
        <div class="abt-val-icon">
          <span class="material-symbols-outlined" style="font-size:24px;">support_agent</span>
        </div>
        <h4 class="abt-val-title">Dedicated Guest Support</h4>
        <p class="abt-val-desc">Our concierge team is available 7 days a week to ensure your stay is seamless.</p>
      </div>
    </div>
  </section>

  <!-- ── GETAWAY CTA BANNER ── -->
  <div class="abt-cta-wrap">
    <div class="abt-cta-box">
      <div class="abt-cta-left">
        <h2 class="abt-cta-title">Ready for Your Next Luxury Getaway?</h2>
        <p class="abt-cta-sub">Discover verified farmhouses, private pool villas, and nature retreats across India with 100% direct pricing and instant confirmation.</p>
      </div>
      <a href="<?= url('farmhouses') ?>" class="abt-cta-btn">
        <span class="material-symbols-outlined" style="font-size:20px;">travel_explore</span>
        <span>Explore Farmhouses</span>
      </a>
    </div>
  </div>

</div>

<?php include __DIR__ . "/Includes/footer.php"; ?>
