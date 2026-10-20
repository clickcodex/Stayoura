<?php
include __DIR__ . "/Includes/header.php"; 
if (session_status() === PHP_SESSION_NONE) session_start();
$redirectParam = htmlspecialchars($_GET['redirect'] ?? '');
?>

<style>
.auth-page-wrap {
  min-height: calc(100vh - 80px);
  padding-top: 80px;
  display: flex;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

.auth-hero-pane {
  flex: 1.1;
  position: relative;
  background: #0F172A;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 60px;
  color: #ffffff;
  overflow: hidden;
}

.auth-hero-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.35;
  filter: brightness(0.9);
}

.auth-hero-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.9) 100%);
}

.auth-brand-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(22, 165, 222, 0.15);
  border: 1px solid rgba(22, 165, 222, 0.4);
  color: #C9A227;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 6px 14px;
  border-radius: 20px;
  backdrop-filter: blur(8px);
}

.auth-hero-body {
  position: relative;
  z-index: 2;
  max-width: 520px;
}

.auth-hero-body h1 {
  font-size: 42px;
  font-weight: 900;
  line-height: 1.15;
  letter-spacing: -0.02em;
  margin: 0 0 16px;
}

.auth-hero-body h1 span {
  color: #C9A227;
}

.auth-hero-body p {
  font-size: 16px;
  color: #cbd5e1;
  line-height: 1.6;
  margin: 0;
}

.auth-hero-footer {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 16px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.auth-stat-col strong {
  display: block;
  font-size: 20px;
  font-weight: 900;
  color: #C9A227;
}

.auth-stat-col span {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.auth-form-pane {
  flex: 0.9;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px 60px;
  background: #F8FAFC;
}

.auth-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 26px;
  padding: 36px 32px;
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
}

.auth-tabs {
  display: flex;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 14px;
  margin-bottom: 24px;
}

.auth-tab-btn {
  flex: 1;
  padding: 10px 0;
  border: none;
  background: transparent;
  font-size: 13px;
  font-weight: 800;
  color: #64748B;
  border-radius: 10px;
  text-align: center;
  text-decoration: none;
  transition: all 0.2s ease;
  font-family: inherit;
  display: block;
}

.auth-tab-btn.active {
  background: #ffffff;
  color: #0F172A;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.auth-heading {
  margin-bottom: 20px;
}

.auth-heading h3 {
  font-size: 22px;
  font-weight: 900;
  color: #0F172A;
  letter-spacing: -0.01em;
  margin: 0 0 4px;
}

.auth-heading p {
  font-size: 13px;
  color: #64748B;
  margin: 0;
}

.auth-group {
  margin-bottom: 14px;
}

.auth-label {
  display: block;
  font-size: 11px;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 5px;
}

.auth-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.auth-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  font-size: 18px;
  pointer-events: none;
}

.auth-input {
  width: 100%;
  height: 46px;
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

.auth-input:focus {
  background: #ffffff;
  border-color: #1D4ED8;
  box-shadow: 0 0 0 3px rgba(22, 165, 222, 0.15);
}

.auth-input.has-eye {
  padding-right: 42px;
}

.auth-eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s ease;
}

.auth-eye-btn:hover {
  color: #0F172A;
}

.auth-error-msg {
  color: #dc2626;
  font-size: 11.5px;
  font-weight: 700;
  margin-top: 4px;
  display: none;
}

.auth-error-msg.show {
  display: block;
}

.auth-submit-btn {
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

.auth-submit-btn:hover {
  background: #1E40AF;
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(2, 132, 199, 0.45);
}

.auth-alert-error {
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
  margin-bottom: 16px;
}

.auth-alert-success {
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
  margin-bottom: 16px;
}

@media (max-width: 992px) {
  .auth-hero-pane {
    display: none;
  }
  .auth-page-wrap {
    justify-content: center;
    padding-top: 75px;
  }
}

@media (max-width: 480px) {
  .auth-card {
    padding: 26px 20px;
    border-radius: 20px;
  }
}
</style>

<div class="auth-page-wrap">

  <!-- ── LEFT HERO SHOWCASE (Desktop) ── -->
  <div class="auth-hero-pane">
    <img src="https://images.unsplash.com/photo-1500382017468-9049fee79a70?auto=format&fit=crop&w=1200&q=80" alt="Farmhouse Nature" class="auth-hero-img">
    <div class="auth-hero-gradient"></div>

    <div class="auth-brand-badge">
      <span class="material-symbols-outlined" style="font-size:16px;">villa</span>
      <span>Stayora Retreats</span>
    </div>

    <div class="auth-hero-body">
      <h1>Join India's Leading <span>Farm Retreat Platform.</span></h1>
      <p>Create your account in seconds to reserve private pools, lush lawns, and authentic countryside estates.</p>
    </div>

    <div class="auth-hero-footer">
      <div class="auth-stat-col">
        <strong>100+</strong>
        <span>Verified Farms</span>
      </div>
      <div style="width:1px;height:30px;background:rgba(255,255,255,0.15);"></div>
      <div class="auth-stat-col">
        <strong>10,000+</strong>
        <span>Happy Guests</span>
      </div>
      <div style="width:1px;height:30px;background:rgba(255,255,255,0.15);"></div>
      <div class="auth-stat-col">
        <strong>4.9★</strong>
        <span>Trust Rating</span>
      </div>
    </div>
  </div>

  <!-- ── RIGHT FORM PANEL ── -->
  <div class="auth-form-pane">
    <div class="auth-card">

      <!-- Segmented Tabs -->
      <div class="auth-tabs">
        <a href="<?= url('login') ?>" class="auth-tab-btn">
          Sign In
        </a>
        <a href="<?= url('register') ?>" class="auth-tab-btn active">
          Create Account
        </a>
      </div>

      <!-- Flash Notifications -->
      <?php if (isset($_SESSION['error'])): ?>
        <div class="auth-alert-error">
          <span class="material-symbols-outlined" style="font-size:18px;">error</span>
          <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="auth-alert-success">
          <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
          <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
      <?php endif; ?>

      <div class="auth-heading">
        <h3>Create Your Account</h3>
        <p>Start your journey with Stayora today.</p>
      </div>

      <form id="reg-form" action="<?= url('register') ?>" method="POST" novalidate onsubmit="return validateRegister(event)">
        <input type="hidden" name="role" value="users">
        <?php if ($redirectParam): ?>
          <input type="hidden" name="redirect" value="<?= $redirectParam ?>">
        <?php endif; ?>

        <div class="auth-group">
          <label class="auth-label">Full Name</label>
          <div class="auth-input-wrap">
            <span class="material-symbols-outlined auth-icon">person</span>
            <input type="text" name="name" id="reg-name" class="auth-input" placeholder="e.g. Rahul Sharma" required>
          </div>
          <p class="auth-error-msg" id="err-reg-name"></p>
        </div>

        <div class="auth-group">
          <label class="auth-label">Email Address</label>
          <div class="auth-input-wrap">
            <span class="material-symbols-outlined auth-icon">mail</span>
            <input type="email" name="email" id="reg-email" class="auth-input" placeholder="e.g. rahul@example.com" required>
          </div>
          <p class="auth-error-msg" id="err-reg-email"></p>
        </div>

        <div class="auth-group">
          <label class="auth-label">Phone Number</label>
          <div class="auth-input-wrap">
            <span class="material-symbols-outlined auth-icon">phone</span>
            <input type="tel" name="phone" id="reg-phone" class="auth-input" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
          </div>
          <p class="auth-error-msg" id="err-reg-phone"></p>
        </div>

        <div class="auth-group">
          <label class="auth-label">Password</label>
          <div class="auth-input-wrap">
            <span class="material-symbols-outlined auth-icon">lock</span>
            <input type="password" name="password" id="reg-pass" class="auth-input has-eye" placeholder="8+ characters (Letters &amp; Numbers)" required>
            <button type="button" class="auth-eye-btn" onclick="togglePassVisibility('reg-pass', this)" aria-label="Toggle password view">
              <span class="material-symbols-outlined" style="font-size:18px;">visibility_off</span>
            </button>
          </div>
          <p class="auth-error-msg" id="err-reg-pass"></p>
        </div>

        <div class="auth-group">
          <label class="auth-label">Confirm Password</label>
          <div class="auth-input-wrap">
            <span class="material-symbols-outlined auth-icon">lock_reset</span>
            <input type="password" name="password_confirmation" id="reg-cpass" class="auth-input has-eye" placeholder="Re-enter your password" required>
            <button type="button" class="auth-eye-btn" onclick="togglePassVisibility('reg-cpass', this)" aria-label="Toggle password view">
              <span class="material-symbols-outlined" style="font-size:18px;">visibility_off</span>
            </button>
          </div>
          <p class="auth-error-msg" id="err-reg-cpass"></p>
        </div>

        <button type="submit" class="auth-submit-btn" style="margin-top:16px;">
          <span>Register Now</span>
          <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
        </button>

        <!-- OR Social Divider -->
        <div style="display:flex;align-items:center;margin:20px 0;gap:12px;">
          <div style="flex:1;height:1px;background:#E2E8F0;"></div>
          <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Or register with</span>
          <div style="flex:1;height:1px;background:#E2E8F0;"></div>
        </div>

        <a href="<?= url('auth/google') ?>" class="google-auth-btn" style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px 16px;border:1px solid #cbd5e1;background:#ffffff;border-radius:12px;font-size:14px;font-weight:600;color:#0F172A;text-decoration:none;transition:all 0.2s;box-shadow:0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#F8FAFC';this.style.borderColor='#94a3b8'" onmouseout="this.style.background='#ffffff';this.style.borderColor='#cbd5e1'">
          <svg width="18" height="18" viewBox="0 0 18 18">
            <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z"/>
            <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/>
            <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/>
            <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z"/>
          </svg>
          <span>Sign up with Google</span>
        </a>
      </form>

    </div>
  </div>

</div>

<script>
function togglePassVisibility(inputId, btn) {
  const inp = document.getElementById(inputId);
  if (!inp) return;
  const icon = btn.querySelector('.material-symbols-outlined');
  if (inp.type === 'password') {
    inp.type = 'text';
    if (icon) icon.textContent = 'visibility';
  } else {
    inp.type = 'password';
    if (icon) icon.textContent = 'visibility_off';
  }
}

function validateRegister(e) {
  let valid = true;
  const name  = document.getElementById('reg-name');
  const email = document.getElementById('reg-email');
  const phone = document.getElementById('reg-phone');
  const pass  = document.getElementById('reg-pass');
  const cpass = document.getElementById('reg-cpass');

  function setErr(id, msg) {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = msg;
      el.classList.add('show');
    }
    valid = false;
  }

  function clearErr(id) {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = '';
      el.classList.remove('show');
    }
  }

  ['err-reg-name', 'err-reg-email', 'err-reg-phone', 'err-reg-pass', 'err-reg-cpass'].forEach(clearErr);

  if (!name.value.trim()) {
    setErr('err-reg-name', 'Full name is required.');
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email.value.trim())) {
    setErr('err-reg-email', 'Please enter a valid email address.');
  }

  const phonePattern = /^[0-9]{10}$/;
  if (!phonePattern.test(phone.value.trim())) {
    setErr('err-reg-phone', 'Please enter a valid 10-digit phone number.');
  }

  const passPattern = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/;
  if (!passPattern.test(pass.value)) {
    setErr('err-reg-pass', 'Password must be 8+ chars and contain both letters and numbers.');
  }

  if (pass.value !== cpass.value) {
    setErr('err-reg-cpass', 'Passwords do not match.');
  }

  if (!valid) {
    e.preventDefault();
    return false;
  }
  return true;
}
</script>

<?php include __DIR__ . "/Includes/footer.php"; ?>