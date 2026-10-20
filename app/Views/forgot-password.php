<?php 
include __DIR__ . "/Includes/header.php"; 
if (session_status() === PHP_SESSION_NONE) session_start(); 
$roleName = ($roles === 'owners') ? 'Owner' : 'Guest';
?>

<style>
.fp-page-wrap {
  min-height: calc(100vh - 80px);
  padding: 110px 20px 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

.fp-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 26px;
  padding: 36px 32px;
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
}

.fp-icon-wrap {
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

.fp-title {
  font-size: 24px;
  font-weight: 900;
  color: #0F172A;
  margin: 0 0 6px;
  letter-spacing: -0.01em;
}

.fp-sub {
  font-size: 13.5px;
  color: #64748B;
  line-height: 1.5;
  margin: 0 0 24px;
}

.fp-group {
  margin-bottom: 20px;
}

.fp-label {
  display: block;
  font-size: 11px;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.fp-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.fp-input-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  font-size: 18px;
  pointer-events: none;
}

.fp-input {
  width: 100%;
  height: 48px;
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

.fp-input:focus {
  background: #ffffff;
  border-color: #1D4ED8;
  box-shadow: 0 0 0 3px rgba(22, 165, 222, 0.15);
}

.fp-btn {
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

.fp-btn:hover {
  background: #1E40AF;
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(2, 132, 199, 0.45);
}

.fp-back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #1E40AF;
  text-decoration: none;
  margin-top: 24px;
}

.fp-back-link:hover {
  text-decoration: underline;
}

.fp-alert-error {
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

.fp-alert-success {
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

<div class="fp-page-wrap">
  <div class="fp-card">
    <div class="fp-icon-wrap">
      <span class="material-symbols-outlined" style="font-size:26px;">mail_lock</span>
    </div>

    <h2 class="fp-title">Forgot Password?</h2>
    <p class="fp-sub">
      Enter your registered email address and we will send you a 6-digit verification code to reset your password.
    </p>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="fp-alert-error">
        <span class="material-symbols-outlined" style="font-size:18px;">error</span>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="fp-alert-success">
        <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
      </div>
    <?php endif; ?>

    <form action="<?= url('forgot-password-process') ?>" method="POST" onsubmit="return validateEmailForm(this)">
      <input type="hidden" name="role" value="<?= htmlspecialchars($roles) ?>">

      <div class="fp-group">
        <label class="fp-label" for="identity-input">Registered Email Address</label>
        <div class="fp-input-wrap">
          <span class="material-symbols-outlined fp-input-icon">mail</span>
          <input type="email" name="identity" id="identity-input" class="fp-input" 
                 placeholder="e.g. rahul@example.com" required autofocus autocomplete="email">
        </div>
      </div>

      <button type="submit" class="fp-btn" id="submit-btn">
        <span>Send Verification Code</span>
        <span class="material-symbols-outlined" style="font-size:18px;">send</span>
      </button>
    </form>

    <div style="text-align:center;">
      <a href="<?= $roles === 'owners' ? url('owner/login') : url('login') ?>" class="fp-back-link">
        <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
        <span>Back to Login</span>
      </a>
    </div>
  </div>
</div>

<script>
function validateEmailForm(form) {
  const email = form.querySelector('#identity-input').value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert('Please enter a valid email address.');
    form.querySelector('#identity-input').focus();
    return false;
  }
  return true;
}
</script>

<?php include __DIR__ . "/Includes/footer.php"; ?>