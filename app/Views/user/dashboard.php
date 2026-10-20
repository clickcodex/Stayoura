<?php 
include __DIR__ . "/../Includes/header.php"; 
use App\Helpers\CryptoHelper;
?>

<style>
/* ═══════════════════════════════════════════════════════════
   STAYORA USER DASHBOARD — LUXURY BLUE THEME & PORTAL SYSTEM
   ═══════════════════════════════════════════════════════════ */

:root {
  --fl-primary: #00AEEF;
  --fl-primary-glow: #C9A227;
  --fl-primary-dark: #1E40AF;
  --fl-navy-dark: #070D1B;
  --fl-navy-slate: #0F172A;
  --fl-card-bg: #FFFFFF;
  --fl-card-border: rgba(0, 174, 239, 0.18);
  --fl-card-hover-border: rgba(0, 174, 239, 0.55);
  --fl-text-dark: #0F172A;
  --fl-text-muted: #64748B;
  --fl-status-green: #10B981;
  --fl-status-amber: #F59E0B;
  --fl-status-rose: #F43F5E;
  --fl-status-pink: #EC4899;
}

.ud-page-wrap {
  min-height: calc(100vh - 72px);
  padding-top: 72px;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
  overflow-x: hidden;
}


.ud-subnav-container {
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  gap: 6px;
  overflow-x: auto;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
}
.ud-subnav-container::-webkit-scrollbar { display: none; }

.ud-subnav-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 18px;
  font-size: 13.5px;
  font-weight: 700;
  color: #64748B;
  text-decoration: none;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.ud-subnav-link:hover {
  color: #00AEEF;
  background: rgba(0, 174, 239, 0.04);
}

.ud-subnav-link.active {
  color: #00AEEF;
  border-bottom-color: #00AEEF;
  background: rgba(0, 174, 239, 0.06);
  border-radius: 8px 8px 0 0;
}

.ud-subnav-link .material-symbols-outlined {
  font-size: 19px;
}

/* ── Hero Banner ── */
.ud-hero-banner {
  background: linear-gradient(135deg, #070D1B 0%, #0D1B36 50%, #0F244A 100%);
  color: #ffffff;
  padding: 42px 0;
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid rgba(0, 174, 239, 0.2);
}

.ud-hero-bg-glow {
  position: absolute;
  top: -60px;
  right: -60px;
  width: 420px;
  height: 420px;
  background: radial-gradient(circle, rgba(0, 174, 239, 0.3) 0%, rgba(56, 189, 248, 0.1) 40%, transparent 70%);
  pointer-events: none;
}

.ud-hero-bg-glow-2 {
  position: absolute;
  bottom: -60px;
  left: -60px;
  width: 320px;
  height: 320px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, transparent 70%);
  pointer-events: none;
}

.ud-hero-container {
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
  position: relative;
  z-index: 2;
}

.ud-hero-user-row {
  display: flex;
  align-items: center;
  gap: 22px;
}

.ud-hero-avatar-wrap {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: 24px;
  background: linear-gradient(135deg, #00AEEF, #C9A227);
  padding: 3px;
  box-shadow: 0 10px 25px rgba(0, 174, 239, 0.4);
  flex-shrink: 0;
}

.ud-hero-avatar {
  width: 100%;
  height: 100%;
  border-radius: 21px;
  object-fit: cover;
  background: #0F172A;
}

.ud-user-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(0, 174, 239, 0.16);
  border: 1px solid rgba(0, 174, 239, 0.45);
  color: #C9A227;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  padding: 4px 12px;
  border-radius: 20px;
  margin-bottom: 8px;
}

.ud-user-tag .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #C9A227;
  box-shadow: 0 0 8px #C9A227;
}

.ud-hero-name {
  font-family: 'Epilogue', sans-serif;
  font-size: 28px;
  font-weight: 900;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
  color: #ffffff;
  line-height: 1.2;
}

.ud-hero-name span {
  background: linear-gradient(135deg, #FFFFFF 40%, #C9A227 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.ud-hero-sub {
  font-size: 14px;
  color: #94a3b8;
  margin: 0;
  line-height: 1.5;
}

.ud-hero-ctas {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.ud-cta-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  font-size: 13.5px;
  font-weight: 800;
  padding: 12px 22px;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 6px 20px rgba(0, 174, 239, 0.4);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid rgba(56, 189, 248, 0.4);
}

.ud-cta-btn-primary:hover {
  background: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
  transform: translateY(-2px);
  box-shadow: 0 10px 26px rgba(0, 174, 239, 0.55);
  color: #ffffff;
}

.ud-cta-btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(10px);
  font-size: 13.5px;
  font-weight: 700;
  padding: 12px 20px;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.25s ease;
}

.ud-cta-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.18);
  border-color: rgba(255, 255, 255, 0.4);
  color: #ffffff;
  transform: translateY(-2px);
}

/* ── Main Layout Canvas ── */
.ud-canvas {
  max-width: 1240px;
  margin: 0 auto;
  padding: 34px 24px 60px;
}

/* ── 4 Metric KPI Cards ── */
.ud-kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 34px;
}

.ud-kpi-card {
  background: #ffffff;
  border: 1px solid var(--fl-card-border);
  border-top: 3px solid var(--fl-primary);
  border-radius: 20px;
  padding: 22px 20px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  overflow: hidden;
}

.ud-kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0, 174, 239, 0.12);
  border-color: var(--fl-card-hover-border);
}

.ud-kpi-card.kpi-amber {
  border-top-color: var(--fl-status-amber);
}
.ud-kpi-card.kpi-amber:hover {
  box-shadow: 0 12px 28px rgba(245, 158, 11, 0.15);
}

.ud-kpi-card.kpi-green {
  border-top-color: var(--fl-status-green);
}
.ud-kpi-card.kpi-green:hover {
  box-shadow: 0 12px 28px rgba(16, 185, 129, 0.15);
}

.ud-kpi-card.kpi-pink {
  border-top-color: var(--fl-status-pink);
}
.ud-kpi-card.kpi-pink:hover {
  box-shadow: 0 12px 28px rgba(236, 72, 153, 0.15);
}

.ud-kpi-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ud-kpi-icon-wrap.blue   { background: #e0f7fe; color: #00AEEF; }
.ud-kpi-icon-wrap.amber  { background: #fef3c7; color: #d97706; }
.ud-kpi-icon-wrap.green  { background: #dcfce7; color: #059669; }
.ud-kpi-icon-wrap.pink   { background: #fce7f3; color: #db2777; }

.ud-kpi-num {
  font-family: 'Epilogue', sans-serif;
  font-size: 28px;
  font-weight: 900;
  color: #0F172A;
  line-height: 1;
  margin-bottom: 5px;
}

.ud-kpi-label {
  font-size: 11.5px;
  font-weight: 800;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

/* ── Content Grid (Main + Aside) ── */
.ud-main-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 30px;
  align-items: start;
}

/* ── Section Card Containers ── */
.ud-card {
  background: #ffffff;
  border: 1px solid var(--fl-card-border);
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  margin-bottom: 30px;
  transition: all 0.2s ease;
}

.ud-card:hover {
  box-shadow: 0 8px 26px rgba(0, 0, 0, 0.05);
}

.ud-card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
}

.ud-card-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.ud-card-header-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 174, 239, 0.1);
  color: #00AEEF;
}

.ud-card-header-icon.pink {
  background: rgba(236, 72, 153, 0.1);
  color: #ec4899;
}

.ud-card-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.ud-card-action-link {
  font-size: 12.5px;
  font-weight: 800;
  color: #00AEEF;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 10px;
  background: rgba(0, 174, 239, 0.08);
  border: 1px solid rgba(0, 174, 239, 0.18);
  white-space: nowrap;
  flex-shrink: 0;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.ud-card-action-link:hover {
  background: #00AEEF;
  color: #ffffff;
  border-color: #00AEEF;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.25);
  transform: translateY(-1px);
}

/* ── Booking List Rows ── */
.ud-booking-row {
  display: flex;
  gap: 20px;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  transition: all 0.2s ease;
  align-items: center;
}

.ud-booking-row:last-child {
  border-bottom: none;
}

.ud-booking-row:hover {
  background: #F8FAFC;
}

.ud-booking-thumb {
  width: 96px;
  height: 96px;
  border-radius: 16px;
  object-fit: cover;
  flex-shrink: 0;
  background: #0F172A;
  border: 1px solid rgba(0, 174, 239, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.ud-booking-meta {
  flex: 1;
  min-width: 0;
}

.ud-booking-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 8px;
}

.ud-booking-farm-title {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ud-cat-pill {
  background: #e0f7fe;
  color: #0088bd;
  font-size: 10px;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.ud-status-pill {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.ud-status-pill.approved { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.ud-status-pill.pending  { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.ud-status-pill.rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

.ud-booking-dates-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: #475569;
  background: #f1f5f9;
  padding: 5px 12px;
  border-radius: 10px;
}

.ud-booking-status-dates-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin: 6px 0 4px;
}

.ud-booking-status-dates-row .ud-status-pill,
.ud-booking-status-dates-row .ud-booking-dates-badge {
  margin: 0;
  flex-shrink: 0;
}

.ud-booking-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.ud-btn-view-details {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  font-size: 12.5px;
  font-weight: 800;
  padding: 9px 16px;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
  transition: all 0.2s ease;
}

.ud-btn-view-details:hover {
  background: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
  transform: translateY(-1px);
  color: #ffffff;
}

/* ── Wishlist Grid ── */
.ud-wishlist-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  padding: 22px 24px;
}

.ud-wish-card {
  border: 1px solid var(--fl-card-border);
  border-radius: 20px;
  overflow: hidden;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.ud-wish-card:hover {
  transform: translateY(-3px);
  border-color: var(--fl-card-hover-border);
  box-shadow: 0 10px 25px rgba(0, 174, 239, 0.12);
}

.ud-wish-img-wrap {
  height: 145px;
  position: relative;
  background: #0F172A;
}

.ud-wish-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.ud-wish-card:hover .ud-wish-img {
  transform: scale(1.03);
}

.ud-wish-remove-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 34px;
  height: 34px;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ec4899;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
  z-index: 2;
}

.ud-wish-remove-btn:hover {
  transform: scale(1.12);
  background: #ffffff;
  color: #db2777;
}

.ud-wish-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.ud-wish-title {
  font-size: 15px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ud-wish-loc {
  font-size: 12px;
  color: #64748B;
  display: flex;
  align-items: center;
  gap: 4px;
}

.ud-wish-btn-book {
  margin-top: auto;
  text-align: center;
  background: rgba(0, 174, 239, 0.08);
  color: #00AEEF;
  border: 1px solid rgba(0, 174, 239, 0.25);
  font-size: 12px;
  font-weight: 800;
  padding: 8px 0;
  border-radius: 10px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.ud-wish-btn-book:hover {
  background: #00AEEF;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
}

/* ── Sidebar Widget Cards ── */
.ud-concierge-card {
  background: linear-gradient(140deg, #070D1B 0%, #0F2347 65%, #00AEEF 140%);
  color: #ffffff;
  border: 1px solid rgba(0, 174, 239, 0.35);
  border-radius: 22px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 10px 30px rgba(0, 174, 239, 0.15);
  position: relative;
  overflow: hidden;
}

.ud-concierge-icon-circle {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: rgba(0, 174, 239, 0.25);
  border: 1px solid rgba(0, 174, 239, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
}

.ud-concierge-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 18px;
  font-weight: 900;
  margin: 0 0 6px;
  color: #ffffff;
}

.ud-concierge-sub {
  font-size: 12.5px;
  color: rgba(255, 255, 255, 0.82);
  line-height: 1.5;
  margin: 0 0 18px;
}

.ud-wa-direct-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #25d366;
  color: #ffffff;
  font-size: 13.5px;
  font-weight: 800;
  padding: 12px;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 6px 16px rgba(37, 211, 102, 0.35);
  transition: all 0.2s ease;
}

.ud-wa-direct-btn:hover {
  background: #1eb956;
  transform: translateY(-2px);
  box-shadow: 0 8px 22px rgba(37, 211, 102, 0.45);
  color: #ffffff;
}

.ud-quick-links-card {
  background: #ffffff;
  border: 1px solid var(--fl-card-border);
  border-radius: 22px;
  padding: 22px;
  margin-bottom: 24px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.ud-quick-link-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  border-radius: 12px;
  color: #334155;
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 700;
  transition: all 0.2s ease;
}

.ud-quick-link-item:hover {
  background: #f0f9ff;
  color: #00AEEF;
  transform: translateX(4px);
}

.ud-quick-link-item .material-symbols-outlined {
  font-size: 19px;
  color: #94a3b8;
  transition: color 0.2s ease;
}

.ud-quick-link-item:hover .material-symbols-outlined {
  color: #00AEEF;
}

.ud-trust-card {
  background: #F8FAFC;
  border: 1px solid var(--fl-card-border);
  border-radius: 22px;
  padding: 22px;
}

.ud-trust-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
}

.ud-trust-item .material-symbols-outlined {
  font-size: 18px;
  color: #00AEEF;
}

/* Toast */
.ud-toast {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%) translateY(80px);
  background: #070D1B;
  border: 1px solid rgba(0, 174, 239, 0.4);
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  padding: 11px 22px;
  border-radius: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
  z-index: 9999;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  opacity: 0;
  pointer-events: none;
  display: flex;
  align-items: center;
  gap: 10px;
}

.ud-toast.show {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

/* ═══════════════════════════════════════════════════════════
   COMPREHENSIVE RESPONSIVENESS (DESKTOP, TABLET & MOBILE)
   ═══════════════════════════════════════════════════════════ */

/* Tablet (max-width: 1024px) */
@media (max-width: 1024px) {
  .ud-kpi-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  .ud-main-grid {
    grid-template-columns: 1fr;
    gap: 26px;
  }
}

/* Small Tablet / Large Phone (max-width: 768px) */
@media (max-width: 768px) {
  .ud-page-wrap {
    padding-top: 64px;
  }

  .ud-hero-banner {
    padding: 32px 0;
  }
  .ud-canvas {
    padding: 24px 16px 48px;
  }
  .ud-hero-name {
    font-size: 24px;
  }
  .ud-hero-ctas {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  .ud-cta-btn-primary, .ud-cta-btn-secondary {
    width: 100%;
    justify-content: center;
    padding: 11px 12px;
    font-size: 13px;
    white-space: nowrap;
    text-align: center;
    border-radius: 12px;
  }
}

/* Mobile Devices (max-width: 640px) */
@media (max-width: 640px) {
  .ud-hero-user-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .ud-hero-avatar-wrap {
    width: 68px;
    height: 68px;
    border-radius: 20px;
  }
  .ud-hero-avatar {
    border-radius: 17px;
  }
  .ud-hero-name {
    font-size: 22px;
  }
  .ud-hero-ctas {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  .ud-cta-btn-primary, .ud-cta-btn-secondary {
    width: 100%;
    justify-content: center;
    padding: 11px 8px;
    font-size: 12.5px;
    white-space: nowrap;
    text-align: center;
    border-radius: 12px;
  }
  .ud-cta-btn-primary .material-symbols-outlined,
  .ud-cta-btn-secondary .material-symbols-outlined {
    font-size: 16px !important;
  }
  .ud-kpi-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
  }
  .ud-kpi-card {
    padding: 14px 12px;
    gap: 10px;
    border-radius: 16px;
  }
  .ud-kpi-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 12px;
  }
  .ud-kpi-icon-wrap .material-symbols-outlined {
    font-size: 20px !important;
  }
  .ud-kpi-num {
    font-size: 22px;
  }
  .ud-kpi-label {
    font-size: 10px;
    letter-spacing: 0.3px;
  }
  .ud-wishlist-grid {
    grid-template-columns: 1fr;
    padding: 16px;
    gap: 14px;
  }
  .ud-wish-img-wrap {
    height: 160px;
  }
  .ud-booking-row {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
    gap: 14px;
  }
  .ud-booking-thumb {
    width: 100%;
    height: 160px;
    border-radius: 14px;
  }
  .ud-card-header {
    padding: 14px 16px;
    gap: 10px;
  }
  .ud-card-header-left {
    min-width: 0;
    flex: 1;
    gap: 8px;
  }
  .ud-card-title {
    font-size: 14.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .ud-card-action-link {
    font-size: 11.5px;
    padding: 6px 10px;
    white-space: nowrap !important;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .ud-card-action-link span {
    white-space: nowrap !important;
  }
  .ud-booking-top-row {
    margin-bottom: 4px;
  }
  .ud-booking-actions {
    width: 100%;
  }
  .ud-btn-view-details {
    width: 100%;
    justify-content: center;
    padding: 11px;
    font-size: 13px;
  }
}
</style>

<!-- ── Toast Notification ── -->
<div class="ud-toast" id="dash-toast">
  <span class="material-symbols-outlined" id="dash-toast-icon" style="font-size:18px;">favorite</span>
  <span id="dash-toast-msg">Wishlist updated</span>
</div>

<div class="ud-page-wrap">

  <!-- ── 2. Hero Greeting Showcase ── -->
  <div class="ud-hero-banner">
    <div class="ud-hero-bg-glow"></div>
    <div class="ud-hero-bg-glow-2"></div>
    <div class="ud-hero-container">
      <div class="ud-hero-user-row">
        <div class="ud-hero-avatar-wrap">
          <img src="<?= !empty($_SESSION['profile_image']) ? asset('assets/images/uploads/avatars/' . htmlspecialchars($_SESSION['profile_image'])) : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') . '&background=00AEEF&color=fff' ?>"
               alt="User Avatar" class="ud-hero-avatar">
        </div>
        <div>
          <h1 class="ud-hero-name">
            Welcome back, <span><?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Guest')[0]) ?></span>!
          </h1>
          <p class="ud-hero-sub">Manage your reservations, saved estates, and trip concierge from one central hub.</p>
        </div>
      </div>

      <div class="ud-hero-ctas">
        <a href="<?= url('farmhouses') ?>" class="ud-cta-btn-primary">
          <span class="material-symbols-outlined" style="font-size:18px;">travel_explore</span>
          <span>Explore Farmhouses</span>
        </a>
        <a href="<?= url('user/my-bookings') ?>" class="ud-cta-btn-secondary">
          <span class="material-symbols-outlined" style="font-size:18px;">receipt_long</span>
          <span>Booking History</span>
        </a>
      </div>
    </div>
  </div>

  <div class="ud-canvas">

    <!-- ── 3. 4-Stat Metric KPI Cards ── -->
    <div class="ud-kpi-grid">
      <!-- Total Bookings -->
      <div class="ud-kpi-card">
        <div class="ud-kpi-icon-wrap blue">
          <span class="material-symbols-outlined" style="font-size:24px;">fact_check</span>
        </div>
        <div>
          <div class="ud-kpi-num"><?= sprintf("%02d", $stats['total_bookings'] ?? 0) ?></div>
          <div class="ud-kpi-label">Total Stays</div>
        </div>
      </div>

      <!-- Pending Approval -->
      <div class="ud-kpi-card kpi-amber">
        <div class="ud-kpi-icon-wrap amber">
          <span class="material-symbols-outlined" style="font-size:24px;">hourglass_top</span>
        </div>
        <div>
          <div class="ud-kpi-num"><?= sprintf("%02d", $stats['pending_requests'] ?? 0) ?></div>
          <div class="ud-kpi-label">Pending Approval</div>
        </div>
      </div>

      <!-- Confirmed -->
      <div class="ud-kpi-card kpi-green">
        <div class="ud-kpi-icon-wrap green">
          <span class="material-symbols-outlined" style="font-size:24px;">verified</span>
        </div>
        <div>
          <div class="ud-kpi-num"><?= sprintf("%02d", $stats['approved_requests'] ?? 0) ?></div>
          <div class="ud-kpi-label">Confirmed Stays</div>
        </div>
      </div>

      <!-- Wishlist -->
      <div class="ud-kpi-card kpi-pink">
        <div class="ud-kpi-icon-wrap pink">
          <span class="material-symbols-outlined" style="font-size:24px;font-variation-settings:'FILL' 1;">favorite</span>
        </div>
        <div>
          <div class="ud-kpi-num" id="wishlist-stat-count"><?= sprintf("%02d", $stats['wishlist_count'] ?? 0) ?></div>
          <div class="ud-kpi-label">Saved Farms</div>
        </div>
      </div>
    </div>

    <!-- ── 4. Main Layout Grid ── -->
    <div class="ud-main-grid">

      <!-- Left Column: Bookings & Wishlist -->
      <div>

        <!-- Recent Booking Requests Card -->
        <div class="ud-card">
          <div class="ud-card-header">
            <div class="ud-card-header-left">
              <div class="ud-card-header-icon">
                <span class="material-symbols-outlined" style="font-size:20px;">calendar_month</span>
              </div>
              <h2 class="ud-card-title">Recent Booking Requests</h2>
            </div>
            <a href="<?= url('user/my-bookings') ?>" class="ud-card-action-link">
              <span>View All</span>
              <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
            </a>
          </div>

          <div>
            <?php if (empty($requests)): ?>
              <div style="padding:52px 24px;text-align:center;color:#64748B;">
                <div style="width:64px;height:64px;border-radius:20px;background:#f0f9ff;color:#00AEEF;display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;">
                  <span class="material-symbols-outlined" style="font-size:32px;">event_busy</span>
                </div>
                <p style="font-size:15px;font-weight:800;margin:0 0 8px;color:#0F172A;">No booking requests yet</p>
                <p style="font-size:13px;color:#64748B;margin:0 0 16px;">Explore our verified luxury estates and reserve your private weekend getaway.</p>
                <a href="<?= url('farmhouses') ?>" class="ud-cta-btn-primary" style="display:inline-flex;padding:10px 20px;font-size:13px;">
                  <span>Browse Available Farmhouses</span>
                  <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($requests as $req):
                $status = strtolower($req['status'] ?? 'pending');
                $start = isset($req['check_in']) ? date('d M Y', strtotime($req['check_in'])) : 'N/A';
                $end   = isset($req['check_out']) ? date('d M Y', strtotime($req['check_out'])) : 'N/A';
              ?>
                <div class="ud-booking-row">
                  <img src="<?= htmlspecialchars(farmhouse_img_url($req['main_image'] ?? null, 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?q=80&w=300')) ?>" 
                       alt="Farmhouse" class="ud-booking-thumb">

                  <div class="ud-booking-meta">
                    <div class="ud-booking-top-row">
                      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <h3 class="ud-booking-farm-title"><?= htmlspecialchars($req['farmhouse_name'] ?? 'Farmhouse') ?></h3>
                        <span class="ud-cat-pill">
                          <?= htmlspecialchars($req['farmhouse_category'] ?? 'Farmhouse') ?>
                        </span>
                      </div>
                    </div>

                    <div class="ud-booking-status-dates-row">
                      <span class="ud-status-pill <?= $status ?>">
                        <span class="material-symbols-outlined" style="font-size:14px;"><?= $status === 'approved' ? 'check_circle' : ($status === 'rejected' ? 'cancel' : 'schedule') ?></span>
                        <span><?= htmlspecialchars(ucfirst($status)) ?></span>
                      </span>
                      <div class="ud-booking-dates-badge">
                        <span class="material-symbols-outlined" style="font-size:15px;color:#00AEEF;">date_range</span>
                        <span><?= $start ?> &rarr; <?= $end ?></span>
                      </div>
                    </div>

                    <?php if (!empty($req['message'])): ?>
                      <div style="font-size:12.5px;color:#64748B;font-style:italic;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                        &ldquo;<?= htmlspecialchars($req['message']) ?>&rdquo;
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="ud-booking-actions">
                    <a href="<?= url('user/booking?id=' . ($req['id'] ?? 0)) ?>" class="ud-btn-view-details">
                      <span>Details</span>
                      <span class="material-symbols-outlined" style="font-size:15px;">arrow_forward</span>
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Saved Farmhouses (Wishlist) Card -->
        <div class="ud-card" id="wishlist">
          <div class="ud-card-header">
            <div class="ud-card-header-left">
              <div class="ud-card-header-icon pink">
                <span class="material-symbols-outlined" style="font-size:20px;font-variation-settings:'FILL' 1;">favorite</span>
              </div>
              <h2 class="ud-card-title">Saved Farmhouses</h2>
            </div>
            <a href="<?= url('wishlist') ?>" class="ud-card-action-link">
              <span>View Full Wishlist</span>
              <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
            </a>
          </div>

          <div class="ud-wishlist-grid" id="dash-wishlist-grid">
            <?php if (empty($wishlist)): ?>
              <div style="grid-column:1/-1;padding:48px 24px;text-align:center;color:#64748B;">
                <div style="width:64px;height:64px;border-radius:20px;background:#fdf2f8;color:#ec4899;display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;">
                  <span class="material-symbols-outlined" style="font-size:32px;">favorite_border</span>
                </div>
                <p style="font-size:15px;font-weight:800;margin:0 0 8px;color:#0F172A;">Your wishlist is empty</p>
                <p style="font-size:13px;color:#64748B;margin:0 0 16px;">Save your favorite farmhouses to track availability and plan upcoming trips.</p>
                <a href="<?= url('farmhouses') ?>" class="ud-cta-btn-primary" style="display:inline-flex;padding:10px 20px;font-size:13px;">
                  <span>Explore Farmhouses</span>
                  <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($wishlist as $item):
                $encId = CryptoHelper::encrypt($item['farmhouse_id']);
              ?>
                <div class="ud-wish-card" data-wishlist-card data-farmhouse-id="<?= (int)$item['farmhouse_id'] ?>" data-enc-id="<?= htmlspecialchars($encId) ?>">
                  <div class="ud-wish-img-wrap">
                    <img src="<?= htmlspecialchars(farmhouse_img_url($item['main_image'] ?? null, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?q=80&w=400')) ?>" 
                         alt="<?= htmlspecialchars($item['farmhouse_name'] ?? 'Farmhouse') ?>" class="ud-wish-img">
                    <button type="button" class="ud-wish-remove-btn" onclick="dashToggleWish(event, this)" data-enc-id="<?= htmlspecialchars($encId) ?>" title="Remove from saved">
                      <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1;">favorite</span>
                    </button>
                  </div>

                  <div class="ud-wish-body">
                    <span class="ud-cat-pill" style="align-self:flex-start;">
                      <?= htmlspecialchars($item['farmhouse_category'] ?? 'Farmhouse') ?>
                    </span>
                    <h3 class="ud-wish-title"><?= htmlspecialchars($item['farmhouse_name'] ?? 'Farmhouse') ?></h3>
                    <?php if (!empty($item['location'])): ?>
                      <div class="ud-wish-loc">
                        <span class="material-symbols-outlined" style="font-size:15px;color:#00AEEF;">location_on</span>
                        <span><?= htmlspecialchars($item['location']) ?></span>
                      </div>
                    <?php endif; ?>
                    <a href="<?= url('farmhouse_details?id=' . urlencode($encId)) ?>" class="ud-wish-btn-book">
                      View &amp; Book
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Right Column: Concierge & Quick Shortcuts -->
      <div>

        <!-- Direct Concierge Card -->
        <div class="ud-concierge-card">
          <div class="ud-concierge-icon-circle">
            <span class="material-symbols-outlined" style="font-size:24px;color:#ffffff;">support_agent</span>
          </div>
          <h3 class="ud-concierge-title">Dedicated Concierge</h3>
          <p class="ud-concierge-sub">Need help with custom event bookings, private pool villas, or special requests? Reach out anytime.</p>
          <a href="https://wa.me/919876543210?text=Hello%20Stayora%20Concierge,%20I%20need%20assistance" target="_blank" class="ud-wa-direct-btn">
            <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.835c1.405.836 3.125 1.352 4.953 1.353 5.174 0 9.389-4.215 9.391-9.389.001-2.507-.974-4.864-2.747-6.638s-4.132-2.747-6.638-2.747c-5.176 0-9.391 4.215-9.393 9.39-.001 1.893.562 3.736 1.629 5.308l-.999 3.646 3.737-.981-.132-.084z"/></svg>
            <span>WhatsApp Concierge</span>
          </a>
        </div>

        <!-- Quick Access Navigation -->
        <div class="ud-quick-links-card">
          <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.6px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">
            Quick Access
          </div>
          <div style="display:flex;flex-direction:column;gap:4px;">
            <a href="<?= url('user/my-bookings') ?>" class="ud-quick-link-item">
              <span class="material-symbols-outlined">calendar_month</span>
              <span>All Booking Requests</span>
            </a>
            <a href="<?= url('my-wishlist') ?>" class="ud-quick-link-item">
              <span class="material-symbols-outlined">favorite</span>
              <span>Saved Properties</span>
            </a>
            <a href="<?= url('user/profile') ?>" class="ud-quick-link-item">
              <span class="material-symbols-outlined">manage_accounts</span>
              <span>Profile &amp; Security</span>
            </a>
            <a href="<?= url('farmhouses') ?>" class="ud-quick-link-item">
              <span class="material-symbols-outlined">travel_explore</span>
              <span>Browse All Farmhouses</span>
            </a>
            <a href="<?= url('contact') ?>" class="ud-quick-link-item">
              <span class="material-symbols-outlined">support_agent</span>
              <span>Concierge Helpdesk</span>
            </a>
          </div>
        </div>

        <!-- Trust Features -->
        <div class="ud-trust-card">
          <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.6px;margin-bottom:14px;">
            The Stayora Standard
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="ud-trust-item">
              <span class="material-symbols-outlined">verified</span>
              <span>100% Verified Physical Listings</span>
            </div>
            <div class="ud-trust-item">
              <span class="material-symbols-outlined">money_off</span>
              <span>Zero Middleman Brokerage</span>
            </div>
            <div class="ud-trust-item">
              <span class="material-symbols-outlined">chat</span>
              <span>Direct Host WhatsApp Support</span>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

</div>

<script>
let dashToastTimer = null;

function dashToggleWish(e, btn) {
  e.preventDefault();
  e.stopPropagation();

  const encId = btn.dataset.encId;
  const card  = btn.closest('[data-wishlist-card]');

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
          updateWishlistStatCount(-1);
        }, 250);
      }
      showDashToast('heart_minus', 'Removed from saved farms');
    } else {
      showDashToast('favorite', 'Wishlist updated');
    }
  })
  .catch(() => {
    showDashToast('wifi_off', 'Network issue. Try again.');
  });
}

function updateWishlistStatCount(delta) {
  const el = document.getElementById('wishlist-stat-count');
  if (!el) return;
  const current = parseInt(el.textContent.trim()) || 0;
  const next    = Math.max(0, current + delta);
  el.textContent = String(next).padStart(2, '0');
}

function showDashToast(icon, message) {
  const toast    = document.getElementById('dash-toast');
  const toastIcon = document.getElementById('dash-toast-icon');
  const toastMsg  = document.getElementById('dash-toast-msg');

  if (!toast) return;
  toastIcon.textContent = icon;
  toastMsg.textContent  = message;
  toast.classList.add('show');

  if (dashToastTimer) clearTimeout(dashToastTimer);
  dashToastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}
</script>

<?php include __DIR__ . "/../Includes/footer.php"; ?>