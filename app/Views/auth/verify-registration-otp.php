<?php 
include __DIR__ . "/../Includes/header.php"; 
if (session_status() === PHP_SESSION_NONE) session_start(); 

$pending = $_SESSION['pending_registration'] ?? null;
if (!$pending) {
    redirect('register');
}
$phone = $pending['phone'] ?? '';
$maskedPhone = '+91 ' . substr($phone, 0, 2) . '******' . substr($phone, -2);
?>

<style>
.otp-page-wrap {
  min-height: calc(100vh - 80px);
  padding: 110px 20px 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

.otp-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 26px;
  padding: 36px 32px;
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
}

.otp-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: #E0F2FE;
  color: #1E40AF;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.otp-title {
  font-size: 24px;
  font-weight: 900;
  color: #0F172A;
  margin: 0 0 6px;
  letter-spacing: -0.01em;
}

.otp-sub {
  font-size: 13.5px;
  color: #64748B;
  line-height: 1.5;
  margin: 0 0 24px;
}

.otp-phone-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f1f5f9;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 700;
  color: #0F172A;
  font-size: 13px;
}

.otp-inputs-grid {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin: 24px 0;
}

.otp-digit-box {
  width: 48px;
  height: 54px;
  text-align: center;
  font-size: 24px;
  font-weight: 800;
  color: #0F172A;
  border: 2px solid #E2E8F0;
  border-radius: 12px;
  background: #F8FAFC;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.otp-digit-box:focus {
  border-color: #1D4ED8;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(22, 165, 222, 0.15);
}

.otp-btn {
  width: 100%;
  height: 48px;
  background: #1D4ED8;
  color: #ffffff;
  border: none;
  border-radius: 14px;
  font-size: 14.5px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 8px 20px rgba(22, 165, 222, 0.35);
  transition: all 0.25s ease;
}

.otp-btn:hover {
  background: #1E40AF;
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(2, 132, 199, 0.45);
}

.otp-resend-row {
  margin-top: 24px;
  text-align: center;
  font-size: 13px;
  color: #64748B;
}

.otp-resend-btn {
  background: none;
  border: none;
  color: #1E40AF;
  font-weight: 800;
  cursor: pointer;
  padding: 0;
  font-size: 13px;
  font-family: inherit;
  text-decoration: underline;
}

.otp-resend-btn:disabled {
  color: #94a3b8;
  cursor: not-allowed;
  text-decoration: none;
}

.otp-alert-error {
  background: #fef2f2;
  border: 1.5px solid #fca5a5;
  color: #991b1b;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 12.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 18px;
}

.otp-alert-success {
  background: #f0fdf4;
  border: 1.5px solid #86efac;
  color: #166534;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 12.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 18px;
}
</style>

<div class="otp-page-wrap">
  <div class="otp-card">
    <div class="otp-icon-wrap">
      <span class="material-symbols-outlined" style="font-size:28px;">sms</span>
    </div>

    <h2 class="otp-title">Verify Your Mobile</h2>
    <p class="otp-sub">
      We sent a 6-digit verification code via SMS to <span class="otp-phone-badge"><?= htmlspecialchars($maskedPhone) ?></span>. Enter the code below to complete registration.
    </p>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="otp-alert-error">
        <span class="material-symbols-outlined" style="font-size:18px;">error</span>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="otp-alert-success">
        <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
      </div>
    <?php endif; ?>

    <form id="otp-form" action="<?= url('verify-registration-otp') ?>" method="POST" onsubmit="return assembleOtp()">
      <input type="hidden" name="otp" id="combined-otp" value="">

      <!-- 6 Digit Input Boxes -->
      <div class="otp-inputs-grid">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit-box" autofocus required>
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit-box" required>
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit-box" required>
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit-box" required>
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit-box" required>
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit-box" required>
      </div>

      <button type="submit" class="otp-btn" id="verify-submit-btn">
        <span>Verify &amp; Create Account</span>
        <span class="material-symbols-outlined" style="font-size:18px;">check</span>
      </button>
    </form>

    <!-- Resend Countdown -->
    <div class="otp-resend-row">
      <span id="countdown-text">Didn't receive the SMS? Resend in <strong id="timer-sec">60</strong>s</span>
      <form id="resend-form" action="<?= url('resend-registration-otp') ?>" method="POST" style="display:inline;">
        <button type="submit" class="otp-resend-btn" id="resend-btn" style="display:none;">
          Resend OTP
        </button>
      </form>
    </div>

    <div style="text-align:center; margin-top:20px;">
      <a href="<?= url('register') ?>" style="font-size:12.5px; color:#64748B; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
        <span class="material-symbols-outlined" style="font-size:15px;">arrow_back</span>
        <span>Change mobile number / Restart</span>
      </a>
    </div>
  </div>
</div>

<script>
// Auto-advance & paste handler for OTP boxes
const digitBoxes = document.querySelectorAll('.otp-digit-box');
const combinedInput = document.getElementById('combined-otp');

digitBoxes.forEach((box, index) => {
  box.addEventListener('input', (e) => {
    const val = e.target.value;
    if (val.length > 0) {
      // Keep only first digit
      e.target.value = val.slice(-1);
      if (index < digitBoxes.length - 1) {
        digitBoxes[index + 1].focus();
      }
    }
  });

  box.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !e.target.value && index > 0) {
      digitBoxes[index - 1].focus();
    }
  });

  // Handle paste full 6-digit code
  box.addEventListener('paste', (e) => {
    e.preventDefault();
    const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
    if (/^\d{6}$/.test(pasteData)) {
      pasteData.split('').forEach((char, i) => {
        if (digitBoxes[i]) digitBoxes[i].value = char;
      });
      digitBoxes[5].focus();
    }
  });
});

function assembleOtp() {
  let fullOtp = '';
  digitBoxes.forEach(b => fullOtp += b.value.trim());
  if (fullOtp.length !== 6) {
    alert('Please enter all 6 digits of the verification code.');
    return false;
  }
  combinedInput.value = fullOtp;
  return true;
}

// 60s Resend Countdown Timer
let countdown = 60;
const timerSec = document.getElementById('timer-sec');
const countdownText = document.getElementById('countdown-text');
const resendBtn = document.getElementById('resend-btn');

const interval = setInterval(() => {
  countdown--;
  if (timerSec) timerSec.textContent = countdown;
  if (countdown <= 0) {
    clearInterval(interval);
    if (countdownText) countdownText.style.display = 'none';
    if (resendBtn) resendBtn.style.display = 'inline-block';
  }
}, 1000);
</script>

<?php include __DIR__ . "/../Includes/footer.php"; ?>
