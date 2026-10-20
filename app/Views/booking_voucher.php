<?php
/**
 * Stayora Official Booking Confirmation Voucher & Tax Invoice (F73)
 */
$bookingIdPad = !empty($booking['booking_reference']) ? $booking['booking_reference'] : ('STAY-BK-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT));
$checkInDate  = date('D, d M Y', strtotime($booking['check_in']));
$checkOutDate = date('D, d M Y', strtotime($booking['check_out']));
$nights       = max(1, (int)round((strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400));
$siteName     = $siteSettings['site_name'] ?? 'Stayora';
$sitePhone    = $siteSettings['mobile_number'] ?? '8889000399';
$siteEmail    = $siteSettings['contact_email'] ?? 'support@stayora.com';
$addonsArray  = !empty($booking['addons_selected']) ? (is_string($booking['addons_selected']) ? json_decode($booking['addons_selected'], true) : $booking['addons_selected']) : [];
$isConfirmed  = in_array($booking['status'], ['approved', 'confirmed', 'checked_in', 'checked_out', 'completed']);
$isAdminView  = !empty($_SESSION['admin_logged_in']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Voucher #<?= $bookingIdPad ?> - <?= htmlspecialchars($booking['farmhouse_title'] ?? $siteName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  
  <style>
    :root {
      --primary: #1E40AF;
      --primary-dark: #1E3A8A;
      --success: #059669;
      --ink: #0F172A;
      --muted: #64748B;
      --surface: #F8FAFC;
      --border: #E2E8F0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #f1f5f9;
      color: var(--ink);
      line-height: 1.5;
      padding: 30px 15px;
    }
    .voucher-container {
      max-width: 820px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.08);
      overflow: hidden;
      border: 1px solid var(--border);
    }
    .voucher-header {
      background: linear-gradient(135deg, #0F172A 0%, #0F172A 100%);
      color: #ffffff;
      padding: 36px 40px 30px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 20px;
    }
    .voucher-brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .voucher-logo-icon {
      width: 44px;
      height: 44px;
      background: #1E40AF;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }
    .voucher-status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .status-confirmed { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .status-pending   { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .status-held      { background: #E0F2FE; color: #1E3A8A; border: 1px solid #DBEAFE; }

    .voucher-body {
      padding: 36px 40px;
    }
    .voucher-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 28px;
    }
    .info-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
    }
    .info-card h4 {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: var(--muted);
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .timeline-wrap {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      background: #f0fdf4;
      border: 1.5px solid #bbf7d0;
      border-radius: 18px;
      padding: 20px 24px;
      margin-bottom: 28px;
    }
    .timeline-col .tl-label {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      color: #166534;
      margin-bottom: 4px;
    }
    .timeline-col .tl-date {
      font-size: 16px;
      font-weight: 800;
      color: #0F172A;
    }
    .timeline-col .tl-time {
      font-size: 12px;
      font-weight: 600;
      color: #64748B;
    }
    .timeline-divider {
      text-align: center;
      padding: 0 16px;
    }
    .timeline-pill {
      background: #15803d;
      color: #fff;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11.5px;
      font-weight: 800;
      white-space: nowrap;
    }

    .bill-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    .bill-table th {
      background: #F8FAFC;
      color: var(--muted);
      font-size: 11.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      text-align: left;
      padding: 12px 14px;
      border-bottom: 1.5px solid var(--border);
    }
    .bill-table td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--border);
      font-size: 13.5px;
      color: var(--ink);
    }
    .bill-table tr.total-row td {
      border-top: 2px solid var(--ink);
      border-bottom: none;
      font-size: 16px;
      font-weight: 900;
      background: #fafafa;
    }

    .actions-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 40px;
      background: #F8FAFC;
      border-top: 1px solid var(--border);
      flex-wrap: wrap;
      gap: 12px;
    }
    .btn {
      padding: 10px 20px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: all 0.15s;
    }
    .btn-primary { background: #1E40AF; color: #fff; border: none; }
    .btn-primary:hover { background: #1E3A8A; }
    .btn-outline { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
    .btn-outline:hover { background: #f1f5f9; }

    @media print {
      body { background: #fff; padding: 0; }
      .voucher-container { box-shadow: none; border: none; max-width: 100%; }
      .actions-bar { display: none !important; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>

<div class="voucher-container">
  
  <!-- Header -->
  <div class="voucher-header">
    <div>
      <div class="voucher-brand">
        <div class="voucher-logo-icon">
          <span class="material-symbols-outlined" style="font-size:24px;">villa</span>
        </div>
        <div>
          <h2 style="font-size:20px;font-weight:900;letter-spacing:-0.5px;"><?= htmlspecialchars($siteName) ?></h2>
          <p style="font-size:11.5px;color:#94a3b8;">Premium Vacation &amp; Farmhouse Bookings</p>
        </div>
      </div>
      <div style="margin-top:16px;">
        <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Official Booking Reference</span>
        <h1 style="font-family:'JetBrains Mono',monospace;font-size:22px;font-weight:800;color:#C9A227;margin:2px 0 0;">
          #<?= $bookingIdPad ?>
        </h1>
      </div>
    </div>

    <div style="text-align:right;">
      <?php if (in_array($booking['status'], ['approved', 'confirmed'])): ?>
        <div class="voucher-status-pill status-confirmed">
          <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
          <span>Confirmed &amp; Approved</span>
        </div>
      <?php elseif ($booking['status'] === 'checked_in'): ?>
        <div class="voucher-status-pill status-confirmed" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;">
          <span class="material-symbols-outlined" style="font-size:16px;">hotel</span>
          <span>Checked In (In-House)</span>
        </div>
      <?php elseif (in_array($booking['status'], ['checked_out', 'completed'])): ?>
        <div class="voucher-status-pill" style="background:#f1f5f9;color:#334155;border-color:#cbd5e1;">
          <span class="material-symbols-outlined" style="font-size:16px;">task_alt</span>
          <span>Stay Completed</span>
        </div>
      <?php elseif (in_array($booking['status'], ['cancelled', 'rejected'])): ?>
        <div class="voucher-status-pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecaca;">
          <span class="material-symbols-outlined" style="font-size:16px;">cancel</span>
          <span><?= ucfirst($booking['status']) ?></span>
        </div>
      <?php elseif (!empty($booking['hold_expires_at']) && strtotime($booking['hold_expires_at']) > time()): ?>
        <div class="voucher-status-pill status-held">
          <span class="material-symbols-outlined" style="font-size:16px;">timer</span>
          <span>Inventory Held (15-min hold)</span>
        </div>
      <?php else: ?>
        <div class="voucher-status-pill status-pending">
          <span class="material-symbols-outlined" style="font-size:16px;">pending</span>
          <span><?= ucfirst($booking['status']) ?></span>
        </div>
      <?php endif; ?>

      <div style="font-size:11px;color:#94a3b8;margin-top:10px;">
        Generated: <?= date('d M Y, h:i A') ?><br>
        Booking Mode: <strong><?= ($booking['booking_type'] === 'complete') ? 'Entire Farmhouse' : 'Room-Wise Stay' ?></strong>
      </div>
    </div>
  </div>

  <!-- Body -->
  <div class="voucher-body">

    <!-- Timeline Checkin/Checkout -->
    <div class="timeline-wrap">
      <div class="timeline-col">
        <div class="tl-label">Check-In</div>
        <div class="tl-date"><?= $checkInDate ?></div>
        <div class="tl-time">12:00 PM Afternoon</div>
      </div>

      <div class="timeline-divider">
        <div class="timeline-pill"><?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?></div>
        <span class="material-symbols-outlined" style="font-size:22px;color:#15803d;display:block;margin-top:4px;">arrow_forward</span>
      </div>

      <div class="timeline-col" style="text-align:right;">
        <div class="tl-label">Check-Out</div>
        <div class="tl-date"><?= $checkOutDate ?></div>
        <div class="tl-time">11:00 AM Morning</div>
      </div>
    </div>

    <!-- 2 Column Details -->
    <div class="voucher-grid-2">
      <!-- Property Card -->
      <div class="info-card">
        <h4><span class="material-symbols-outlined" style="font-size:16px;color:#1E40AF;">home_pin</span> Property Information</h4>
        <h3 style="font-size:16px;font-weight:800;color:#0F172A;margin-bottom:4px;">
          <?= htmlspecialchars($booking['farmhouse_title'] ?? 'Luxury Estate') ?>
        </h3>
        <p style="font-size:12.5px;color:#475569;margin-bottom:8px;">
          <?= htmlspecialchars($booking['farmhouse_address'] ?: ($booking['farmhouse_location'] ?? '')) ?>
        </p>
        <div style="display:flex;align-items:center;gap:12px;font-size:12px;color:#1E40AF;font-weight:700;">
          <?php if (!empty($booking['google_map_link'])): ?>
            <a href="<?= htmlspecialchars($booking['google_map_link']) ?>" target="_blank" style="color:#1E40AF;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
              <span class="material-symbols-outlined" style="font-size:14px;">map</span>
              <span>Open in Google Maps</span>
            </a>
          <?php endif; ?>
          <span>•</span>
          <span>Helpline: <?= htmlspecialchars($booking['primary_phone'] ?: $sitePhone) ?></span>
        </div>
      </div>

      <!-- Guest Card -->
      <div class="info-card">
        <h4><span class="material-symbols-outlined" style="font-size:16px;color:#1E40AF;">person</span> Primary Guest Information</h4>
        <div style="font-size:15px;font-weight:800;color:#0F172A;">
          <?= htmlspecialchars($booking['user_name'] ?? 'Guest') ?>
        </div>
        <div style="font-size:12.5px;color:#475569;margin:3px 0;">
          📞 <?= htmlspecialchars($booking['user_phone'] ?? 'N/A') ?> &nbsp;|&nbsp; ✉️ <?= htmlspecialchars($booking['user_email'] ?? 'N/A') ?>
        </div>
        <div style="font-size:12px;color:#059669;font-weight:700;margin-top:8px;">
          👥 <?= (int)$booking['guests'] ?> Registered Guests &nbsp;•&nbsp; 🛏️ <?= (int)$booking['rooms'] ?> Room(s)
          <?php if (!empty($booking['room_type_name'])): ?>
            (<?= htmlspecialchars($booking['room_type_name']) ?>)
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Payment & Verification Status Dossier (F38, F40) -->
    <?php
      $payMethod = strtolower($booking['payment_method'] ?? 'upi');
      $payMethodLabel = match($payMethod) {
          'bank_transfer'   => '🏦 Direct Bank Transfer (NEFT/IMPS)',
          'pay_at_property' => '🏨 Pay at Property (On Arrival)',
          default           => '⚡ UPI Transfer'
      };
      $payStatus = strtolower($booking['payment_status'] ?? 'unpaid');
      $payStatusBg = match($payStatus) {
          'paid'                 => '#ecfdf5',
          'pending_verification' => '#fffbeb',
          'refunded'             => '#eff6ff',
          default                => '#fef2f2'
      };
      $payStatusColor = match($payStatus) {
          'paid'                 => '#059669',
          'pending_verification' => '#d97706',
          'refunded'             => '#2563eb',
          default                => '#dc2626'
      };
      $payStatusBorder = match($payStatus) {
          'paid'                 => '#a7f3d0',
          'pending_verification' => '#fde68a',
          'refunded'             => '#bfdbfe',
          default                => '#fecaca'
      };
      $payStatusText = match($payStatus) {
          'paid'                 => 'Verified & Paid',
          'pending_verification' => 'Payment Verification Pending',
          'refunded'             => 'Payment Refunded',
          default                => 'Unpaid / Settle on Arrival'
      };
    ?>
    <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div>
        <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748B;letter-spacing:0.5px;display:block;">Payment Method (F38)</span>
        <strong style="font-size:13.5px;color:#0F172A;"><?= $payMethodLabel ?></strong>
        <?php if (!empty($booking['utr_number'])): ?>
          <span style="font-size:11.5px;color:#475569;margin-left:8px;font-family:monospace;background:#f1f5f9;padding:2px 6px;border-radius:6px;">UTR: <?= htmlspecialchars($booking['utr_number']) ?></span>
        <?php endif; ?>
      </div>
      <div>
        <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748B;letter-spacing:0.5px;display:block;text-align:right;">Payment Reconciliation (F40)</span>
        <span style="display:inline-block;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:800;color:<?= $payStatusColor ?>;background:<?= $payStatusBg ?>;border:1px solid <?= $payStatusBorder ?>;">
          ● <?= $payStatusText ?>
        </span>
      </div>
    </div>

    <!-- Transparent Financial Ledger & Breakdown -->
    <h4 style="font-size:12px;font-weight:800;text-transform:uppercase;color:#64748B;letter-spacing:0.5px;margin-bottom:8px;">
      Financial Ledger &amp; Price Breakdown
    </h4>

    <table class="bill-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Rate / Units</th>
          <th style="text-align:right;">Amount (INR)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>Accommodation Tariff (<?= $nights ?> Nights)</strong><br>
            <span style="font-size:11.5px;color:#64748B;">
              <?= ($booking['booking_type'] === 'complete') ? 'Entire Farmhouse Estate' : ((int)$booking['rooms'] . ' × ' . ($booking['room_type_name'] ?: 'Room')) ?>
              <?php if (!empty($booking['weekend_nights'])): ?>
                (includes <?= (int)$booking['weekend_nights'] ?> weekend surge night<?= $booking['weekend_nights'] > 1 ? 's' : '' ?>)
              <?php endif; ?>
            </span>
          </td>
          <td><?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?></td>
          <td style="text-align:right;font-weight:700;">
            ₹<?= number_format((float)$booking['price'] - (float)$booking['addon_charges'] - (float)$booking['cleaning_fee'] - (float)$booking['security_deposit'] + (float)$booking['discount_amount']) ?>
          </td>
        </tr>

        <?php if (!empty($booking['discount_amount']) && (float)$booking['discount_amount'] > 0): ?>
        <tr>
          <td style="color:#059669;">
            <strong>Discount Applied</strong>
            <?php if (!empty($booking['coupon_code'])): ?>
              <span style="font-size:11.5px;">(Coupon: <code><?= htmlspecialchars($booking['coupon_code']) ?></code>)</span>
            <?php endif; ?>
          </td>
          <td style="color:#059669;">Special Promotion</td>
          <td style="text-align:right;font-weight:700;color:#059669;">
            - ₹<?= number_format((float)$booking['discount_amount']) ?>
          </td>
        </tr>
        <?php endif; ?>

        <?php if (!empty($addonsArray)): ?>
          <?php foreach ($addonsArray as $ad): ?>
          <tr>
            <td>
              <strong>Addon: <?= htmlspecialchars($ad['name']) ?></strong>
            </td>
            <td><?= htmlspecialchars($ad['price_type'] ?? 'per_stay') ?></td>
            <td style="text-align:right;font-weight:700;">
              + ₹<?= number_format((float)$ad['price']) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php elseif (!empty($booking['addon_charges']) && (float)$booking['addon_charges'] > 0): ?>
          <tr>
            <td><strong>Selected Addon Services</strong></td>
            <td>Extra Services</td>
            <td style="text-align:right;font-weight:700;">+ ₹<?= number_format((float)$booking['addon_charges']) ?></td>
          </tr>
        <?php endif; ?>

        <?php if (!empty($booking['cleaning_fee']) && (float)$booking['cleaning_fee'] > 0): ?>
        <tr>
          <td><strong>Cleaning &amp; Sanitization Fee</strong></td>
          <td>Fixed per stay</td>
          <td style="text-align:right;font-weight:700;">+ ₹<?= number_format((float)$booking['cleaning_fee']) ?></td>
        </tr>
        <?php endif; ?>

        <tr>
          <td>
            <strong>Stayora Marketplace Platform Fee</strong><br>
            <span style="font-size:11px;color:#059669;">Initial promotional zero-fee architecture</span>
          </td>
          <td>0% Commission</td>
          <td style="text-align:right;font-weight:700;color:#059669;">₹0 (Free)</td>
        </tr>

        <?php if (!empty($booking['security_deposit']) && (float)$booking['security_deposit'] > 0): ?>
        <tr>
          <td>
            <strong>Refundable Security Deposit</strong><br>
            <span style="font-size:11px;color:#64748B;">100% refundable at check-out following property inspection</span>
          </td>
          <td>Refundable Deposit</td>
          <td style="text-align:right;font-weight:700;">+ ₹<?= number_format((float)$booking['security_deposit']) ?></td>
        </tr>
        <?php endif; ?>

        <tr class="total-row">
          <td colspan="2">
            <strong>TOTAL AMOUNT PAYABLE / CHARGED</strong><br>
            <span style="font-size:11px;font-weight:500;color:#64748B;">Includes accommodation tariff, selected addons, and refundable deposit</span>
          </td>
          <td style="text-align:right;color:#1E40AF;font-size:20px;">
            ₹<?= number_format((float)$booking['price']) ?>
          </td>
        </tr>
        <?php if (isset($booking['amount_paid'])): ?>
        <tr>
          <td colspan="2" style="font-weight:700;color:#059669;">
            Amount Paid / Settled to Date
          </td>
          <td style="text-align:right;font-weight:800;color:#059669;font-size:15px;">
            ₹<?= number_format((float)$booking['amount_paid']) ?>
          </td>
        </tr>
        <tr style="background:#fffbeb;">
          <td colspan="2" style="font-weight:800;color:#b45309;">
            Outstanding Balance Due
          </td>
          <td style="text-align:right;font-weight:900;color:#b45309;font-size:16px;">
            ₹<?= number_format(max(0, (float)$booking['balance_amount'])) ?>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Notice Card -->
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:16px;margin-top:20px;font-size:12px;color:#1e40af;line-height:1.6;">
      <strong>Check-In Instructions:</strong> Please present a government-issued photo ID (Aadhaar / Driving License / Passport) for all adult guests at the reception / entrance gate. For assistance or late arrival notifications, contact property management or WhatsApp support at <strong><?= htmlspecialchars($sitePhone) ?></strong>.
    </div>

  </div>

  <!-- Actions Bar -->
  <div class="actions-bar">
    <?php if ($isAdminView): ?>
      <a href="<?= url('admin/bookings/view/' . $booking['id']) ?>" class="btn btn-outline">
        <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
        <span>Back to Booking Details</span>
      </a>
    <?php else: ?>
      <a href="<?= url('user/bookings') ?>" class="btn btn-outline">
        <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
        <span>My Bookings</span>
      </a>
    <?php endif; ?>

    <div style="display:flex;gap:10px;">
      <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $sitePhone) ?>?text=<?= urlencode("Hi Stayora, I have a question regarding my booking #{$bookingIdPad}") ?>" target="_blank" class="btn btn-outline" style="color:#16a34a;">
        <span class="material-symbols-outlined" style="font-size:16px;">chat</span>
        <span>WhatsApp Support</span>
      </a>

      <button type="button" onclick="window.print()" class="btn btn-primary">
        <span class="material-symbols-outlined" style="font-size:16px;">print</span>
        <span>Print / Save Voucher as PDF</span>
      </button>
    </div>
  </div>

</div>

</body>
</html>
