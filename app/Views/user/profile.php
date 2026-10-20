<?php 
include __DIR__ . "/../Includes/header.php"; 

$user = $user ?? ['name' => '', 'email' => '', 'phone' => '', 'status' => 'active', 'created_at' => date('Y-m-d')];
$booking_count  = (int)($booking_count ?? 0);
$wishlist_count = (int)($wishlist_count ?? 0);

// Flash Error & Success Resolution
$error_code = $_GET['error'] ?? null;
$msg_code   = $_GET['msg'] ?? null;

$flash_error = null;
if (!empty($_SESSION['error'])) {
    $flash_error = $_SESSION['error'];
    unset($_SESSION['error']);
} elseif ($error_code === 'name_required') {
    $flash_error = "Full Name is required (minimum 3 characters).";
} elseif ($error_code === 'invalid_email') {
    $flash_error = "Please enter a valid email address.";
} elseif ($error_code === 'invalid_phone') {
    $flash_error = "Phone number must be exactly 10 digits.";
} elseif ($error_code === 'email_exists') {
    $flash_error = "This email address is already associated with another account.";
} elseif ($error_code === 'phone_exists') {
    $flash_error = "This phone number is already registered to another account.";
} elseif ($error_code === 'password_required') {
    $flash_error = "Please enter both your current password and a new password.";
} elseif ($error_code === 'password_length') {
    $flash_error = "New password must be at least 6 characters long.";
} elseif ($error_code === 'password_mismatch') {
    $flash_error = "New password and Confirm Password do not match.";
} elseif ($error_code === 'current_password_incorrect') {
    $flash_error = "The current password you provided is incorrect. Please try again.";
} elseif ($error_code === 'password_failed') {
    $flash_error = "Failed to update your password. Please try again.";
}

$flash_success = null;
if (!empty($_SESSION['success'])) {
    $flash_success = $_SESSION['success'];
    unset($_SESSION['success']);
} elseif ($msg_code === 'updated') {
    $flash_success = "Your profile information has been saved successfully.";
} elseif ($msg_code === 'password_updated') {
    $flash_success = "Your security password was changed successfully.";
}

// Avatar resolution
$userProfileImg = $user['profile_image'] ?? ($_SESSION['profile_image'] ?? '');
$avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=00AEEF&color=fff';

if (!empty($userProfileImg)) {
    $basePath = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../../..');
    if ($basePath && file_exists($basePath . '/assets/images/uploads/profiles/' . $userProfileImg)) {
        $avatarUrl = asset('assets/images/uploads/profiles/' . htmlspecialchars($userProfileImg));
    } elseif ($basePath && file_exists($basePath . '/assets/images/uploads/avatars/' . $userProfileImg)) {
        $avatarUrl = asset('assets/images/uploads/avatars/' . htmlspecialchars($userProfileImg));
    } else {
        $avatarUrl = asset('assets/images/uploads/profiles/' . htmlspecialchars($userProfileImg));
    }
}
?>

<style>
/* ── Stayora Account Center Design System ── */
.prof-page-wrap {
  min-height: calc(100vh - 72px);
  padding-top: 72px;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0F172A;
}

/* ── Luxury Hero Showcase ── */
.prof-hero-banner {
  background: radial-gradient(circle at 85% 15%, rgba(0, 174, 239, 0.22) 0%, transparent 45%),
              radial-gradient(circle at 10% 85%, rgba(201, 162, 39, 0.15) 0%, transparent 40%),
              linear-gradient(135deg, #070e1b 0%, #0d172b 50%, #081120 100%);
  border-bottom: 1px solid rgba(0, 174, 239, 0.15);
  padding: 42px 0;
  position: relative;
  overflow: hidden;
}

.prof-hero-bg-glow {
  position: absolute;
  top: -60px;
  right: -60px;
  width: 320px;
  height: 320px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 174, 239, 0.2) 0%, transparent 70%);
  pointer-events: none;
}

.prof-hero-bg-glow-2 {
  position: absolute;
  bottom: -40px;
  left: 20%;
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(201, 162, 39, 0.12) 0%, transparent 70%);
  pointer-events: none;
}

.prof-hero-container {
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

.prof-hero-left {
  display: flex;
  align-items: center;
  gap: 22px;
}

.prof-hero-avatar-box {
  width: 84px;
  height: 84px;
  border-radius: 24px;
  background: linear-gradient(135deg, #00AEEF 0%, #C9A227 100%);
  padding: 3px;
  box-shadow: 0 10px 25px rgba(0, 174, 239, 0.35);
  flex-shrink: 0;
}

.prof-hero-avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 21px;
  object-fit: cover;
  background: #0F172A;
}

.prof-tag-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(0, 174, 239, 0.14);
  border: 1px solid rgba(0, 174, 239, 0.35);
  color: #38bdf8;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  padding: 4px 12px;
  border-radius: 20px;
  margin-bottom: 8px;
}

.prof-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 30px;
  font-weight: 900;
  color: #ffffff;
  margin: 0 0 6px;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.prof-title span {
  background: linear-gradient(135deg, #FFFFFF 40%, #C9A227 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.prof-subtitle {
  font-size: 14px;
  color: #94a3b8;
  margin: 0;
  line-height: 1.5;
}

.prof-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(10px);
  font-size: 13.5px;
  font-weight: 700;
  padding: 11px 20px;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.prof-back-btn:hover {
  background: rgba(255, 255, 255, 0.16);
  border-color: rgba(255, 255, 255, 0.35);
  color: #ffffff;
  transform: translateY(-2px);
}

/* ── Main Layout Canvas ── */
.prof-main-container {
  max-width: 1240px;
  margin: 0 auto;
  padding: 32px 24px 70px;
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 30px;
  align-items: start;
}

/* ── Flash Alert Messages ── */
.prof-alert {
  padding: 14px 18px;
  border-radius: 14px;
  font-size: 13.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 22px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
}

.prof-alert.success {
  background: #f0fdf4;
  border: 1px solid #86efac;
  color: #15803d;
}

.prof-alert.error {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  color: #b91c1c;
}

.prof-alert-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.prof-alert-close {
  background: transparent;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 2px;
  display: flex;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.prof-alert-close:hover {
  opacity: 1;
}

/* ── Card Containers ── */
.prof-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  margin-bottom: 26px;
  transition: all 0.2s ease;
}

.prof-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.prof-card-header {
  padding: 20px 26px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
}

.prof-card-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.prof-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(0, 174, 239, 0.1);
  color: #00AEEF;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.prof-card-icon.amber {
  background: rgba(201, 162, 39, 0.12);
  color: #b45309;
}

.prof-card-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.prof-card-sub {
  font-size: 12.5px;
  color: #64748B;
  margin: 2px 0 0;
}

.prof-card-body {
  padding: 26px;
}

/* ── Interactive Avatar Upload Row ── */
.prof-avatar-uploader {
  display: flex;
  align-items: center;
  gap: 22px;
  padding: 20px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  margin-bottom: 24px;
}

.prof-avatar-wrap {
  position: relative;
  width: 82px;
  height: 82px;
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
  border: 2px solid #ffffff;
  flex-shrink: 0;
  cursor: pointer;
}

.prof-avatar-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.25s ease;
}

.prof-avatar-wrap:hover .prof-avatar-preview {
  transform: scale(1.05);
}

.prof-avatar-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
  color: #ffffff;
}

.prof-avatar-wrap:hover .prof-avatar-overlay {
  opacity: 1;
}

.prof-avatar-info {
  flex: 1;
  min-width: 0;
}

.prof-upload-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #00AEEF;
  color: #ffffff;
  font-size: 12.5px;
  font-weight: 800;
  padding: 9px 18px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.25);
  border: none;
}

.prof-upload-btn:hover {
  background: #1E40AF;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(30, 64, 175, 0.3);
}

/* ── Form Inputs ── */
.prof-fields-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.prof-group {
  margin-bottom: 20px;
}

.prof-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11.5px;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 7px;
}

.prof-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.prof-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  font-size: 20px;
  pointer-events: none;
  transition: color 0.2s ease;
}

.prof-input {
  width: 100%;
  height: 48px;
  padding: 0 16px 0 44px;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  font-size: 14px;
  font-family: inherit;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-sizing: border-box;
}

.prof-input:focus {
  border-color: #00AEEF;
  box-shadow: 0 0 0 3px rgba(0, 174, 239, 0.16);
}

.prof-input:focus + .prof-icon,
.prof-input-wrap:focus-within .prof-icon {
  color: #00AEEF;
}

.prof-input.readonly {
  background: #f1f5f9;
  color: #475569;
  cursor: not-allowed;
  border-color: #E2E8F0;
  padding-right: 120px;
}

.prof-verified-badge {
  position: absolute;
  right: 12px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #dcfce7;
  color: #15803d;
  font-size: 10.5px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 8px;
  border: 1px solid #bbf7d0;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

/* Password Toggle Button */
.prof-pass-toggle {
  position: absolute;
  right: 12px;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.15s ease;
}

.prof-pass-toggle:hover {
  color: #00AEEF;
  background: #f0f9ff;
}

/* Submit Buttons */
.prof-submit-btn {
  width: 100%;
  height: 48px;
  background: linear-gradient(135deg, #00AEEF 0%, #1E40AF 100%);
  color: #ffffff;
  border: none;
  border-radius: 13px;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 6px 18px rgba(0, 174, 239, 0.35);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  margin-top: 10px;
}

.prof-submit-btn:hover {
  background: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(30, 64, 175, 0.45);
}

.prof-submit-btn.dark {
  background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.25);
}

.prof-submit-btn.dark:hover {
  background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.35);
}

/* ── Sidebar Cards ── */
.prof-stat-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 16px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 16px;
  margin-bottom: 12px;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s ease;
}

.prof-stat-box:hover {
  background: #f0f9ff;
  border-color: #bae6fd;
  transform: translateY(-1px);
}

.prof-stat-num {
  font-family: 'Epilogue', sans-serif;
  font-size: 24px;
  font-weight: 900;
  color: #0F172A;
  line-height: 1;
}

.prof-stat-label {
  font-size: 11px;
  font-weight: 800;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 4px;
}

.prof-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  color: #334155;
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 700;
  transition: all 0.15s ease;
  margin-bottom: 4px;
}

.prof-nav-item:hover {
  background: rgba(0, 174, 239, 0.08);
  color: #00AEEF;
}

.prof-nav-item.active {
  background: #f0f9ff;
  color: #00AEEF;
  border: 1px solid #bae6fd;
}

.prof-nav-item .material-symbols-outlined {
  font-size: 20px;
  color: #94a3b8;
  transition: color 0.15s ease;
}

.prof-nav-item:hover .material-symbols-outlined,
.prof-nav-item.active .material-symbols-outlined {
  color: #00AEEF;
}

/* Dedicated Concierge Box */
.prof-concierge-card {
  background: radial-gradient(circle at 90% 10%, rgba(0, 174, 239, 0.2) 0%, transparent 60%),
              linear-gradient(135deg, #070e1b 0%, #0d172b 100%);
  border: 1px solid rgba(0, 174, 239, 0.2);
  border-radius: 22px;
  padding: 22px;
  color: #ffffff;
  margin-bottom: 24px;
}

.prof-concierge-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 12px;
  background: #25D366;
  color: #ffffff;
  font-size: 13px;
  font-weight: 800;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
  transition: all 0.2s ease;
  margin-top: 14px;
}

.prof-concierge-btn:hover {
  background: #20BA5A;
  transform: translateY(-1px);
  color: #ffffff;
}

/* Danger Box */
.prof-danger-box {
  background: #fef2f2;
  border: 1px solid #fee2e2;
  border-radius: 20px;
  padding: 18px 20px;
}

.prof-danger-title {
  font-size: 11px;
  font-weight: 800;
  color: #dc2626;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

/* ── Responsive Queries ── */
@media (max-width: 992px) {
  .prof-main-container {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}

@media (max-width: 640px) {
  .prof-hero-banner {
    padding: 30px 0;
  }
  .prof-hero-left {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }
  .prof-hero-avatar-box {
    width: 68px;
    height: 68px;
    border-radius: 18px;
  }
  .prof-hero-avatar-img {
    border-radius: 15px;
  }
  .prof-title {
    font-size: 24px;
  }
  .prof-hero-container {
    gap: 16px;
  }
  .prof-back-btn {
    width: 100%;
    justify-content: center;
    padding: 10px 16px;
    font-size: 13px;
  }
  .prof-main-container {
    padding: 20px 16px 50px;
  }
  .prof-card-header {
    padding: 16px;
  }
  .prof-card-body {
    padding: 18px;
  }
  .prof-fields-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .prof-avatar-uploader {
    flex-direction: column;
    text-align: center;
    gap: 14px;
    padding: 16px;
  }
  .prof-avatar-info {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .prof-input {
    font-size: 15px;
  }
}
</style>

<div class="prof-page-wrap">

  <!-- ── 1. Luxury Hero Banner Showcase ── -->
  <div class="prof-hero-banner">
    <div class="prof-hero-bg-glow"></div>
    <div class="prof-hero-bg-glow-2"></div>
    
    <div class="prof-hero-container">
      <div class="prof-hero-left">
        <div class="prof-hero-avatar-box">
          <img src="<?= $avatarUrl ?>" alt="User Avatar" class="prof-hero-avatar-img" id="heroAvatarImg">
        </div>
        <div>
          <div class="prof-tag-pill">
            <span class="material-symbols-outlined" style="font-size:13px;">verified_user</span>
            <span>Account Center &bull; Verified Member</span>
          </div>
          <h1 class="prof-title">Account <span>Settings</span></h1>
          <p class="prof-subtitle">Manage your profile information, password credentials, and account preferences.</p>
        </div>
      </div>

      <a href="<?= url('dashboard') ?>" class="prof-back-btn">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
        <span>Back to Dashboard</span>
      </a>
    </div>
  </div>

  <!-- ── 2. Main Layout Canvas ── -->
  <div class="prof-main-container">

    <!-- ── Left Column: Form Controls ── -->
    <div>

      <!-- Flash Feedback Alerts -->
      <?php if ($flash_success): ?>
        <div class="prof-alert success" id="flash-success-box">
          <div class="prof-alert-left">
            <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
            <span><?= htmlspecialchars($flash_success) ?></span>
          </div>
          <button type="button" class="prof-alert-close" onclick="document.getElementById('flash-success-box').style.display='none'">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
          </button>
        </div>
      <?php endif; ?>

      <?php if ($flash_error): ?>
        <div class="prof-alert error" id="flash-error-box">
          <div class="prof-alert-left">
            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
            <span><?= htmlspecialchars($flash_error) ?></span>
          </div>
          <button type="button" class="prof-alert-close" onclick="document.getElementById('flash-error-box').style.display='none'">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
          </button>
        </div>
      <?php endif; ?>

      <!-- ── Card 1: Personal Information ── -->
      <form action="<?= url('profile/update') ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateProfile(this)" class="prof-card">
        <div class="prof-card-header">
          <div class="prof-card-header-left">
            <div class="prof-card-icon">
              <span class="material-symbols-outlined">person</span>
            </div>
            <div>
              <h2 class="prof-card-title">Personal Information</h2>
              <p class="prof-card-sub">Update your public profile name and direct mobile contact details.</p>
            </div>
          </div>
        </div>

        <div class="prof-card-body">

          <!-- Avatar Uploader Row -->
          <div class="prof-avatar-uploader">
            <div class="prof-avatar-wrap" onclick="document.getElementById('profileImgInput').click()">
              <img id="avatarPreview" class="prof-avatar-preview" src="<?= $avatarUrl ?>" alt="Profile Photo">
              <div class="prof-avatar-overlay">
                <span class="material-symbols-outlined" style="font-size:24px;">photo_camera</span>
              </div>
            </div>
            <div class="prof-avatar-info">
              <p style="font-size:13.5px;font-weight:800;color:#0F172A;margin:0 0 6px;">Profile Photo</p>
              <label class="prof-upload-btn" for="profileImgInput">
                <span class="material-symbols-outlined" style="font-size:16px;">upload</span>
                <span>Change Photo</span>
                <input type="file" id="profileImgInput" name="profile_image" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewAvatar(event)" style="display:none;">
              </label>
              <p style="font-size:11.5px;color:#94a3b8;margin:8px 0 0;">Supported formats: JPG, PNG, WEBP (Max 2MB)</p>
            </div>
          </div>

          <!-- Name & Phone 2-Column Grid -->
          <div class="prof-fields-grid">
            <div class="prof-group" style="margin-bottom:0;">
              <label class="prof-label" for="nameInput">Full Name</label>
              <div class="prof-input-wrap">
                <span class="material-symbols-outlined prof-icon">badge</span>
                <input type="text" name="name" id="nameInput" class="prof-input" value="<?= htmlspecialchars($user['name']) ?>" placeholder="e.g. Rahul Sharma" required minlength="3">
              </div>
            </div>

            <div class="prof-group" style="margin-bottom:0;">
              <label class="prof-label" for="phoneInput">Mobile Number</label>
              <div class="prof-input-wrap">
                <span class="material-symbols-outlined prof-icon">phone</span>
                <input type="tel" name="phone" id="phoneInput" class="prof-input" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="10-digit mobile number" pattern="[0-9]{10}" maxlength="10" required>
              </div>
            </div>
          </div>

          <!-- Email Address (Read-only ID) -->
          <div class="prof-group">
            <label class="prof-label">
              <span>Email Address</span>
              <span style="text-transform:none;color:#94a3b8;font-weight:600;font-size:11px;">Primary login identifier</span>
            </label>
            <div class="prof-input-wrap">
              <span class="material-symbols-outlined prof-icon">mail</span>
              <input type="email" name="email" class="prof-input readonly" value="<?= htmlspecialchars($user['email']) ?>" readonly>
              <span class="prof-verified-badge">
                <span class="material-symbols-outlined" style="font-size:13px;">verified</span>
                <span>Verified</span>
              </span>
            </div>
          </div>

          <button type="submit" class="prof-submit-btn">
            <span class="material-symbols-outlined" style="font-size:18px;">save</span>
            <span>Save Profile Changes</span>
          </button>

        </div>
      </form>

      <!-- ── Card 2: Security & Password ── -->
      <form action="<?= url('profile/change-password') ?>" method="POST" onsubmit="return validatePassword(this)" class="prof-card">
        <div class="prof-card-header">
          <div class="prof-card-header-left">
            <div class="prof-card-icon amber">
              <span class="material-symbols-outlined">lock_reset</span>
            </div>
            <div>
              <h2 class="prof-card-title">Security &amp; Password</h2>
              <p class="prof-card-sub">Change your login password to maintain complete account safety.</p>
            </div>
          </div>
        </div>

        <div class="prof-card-body">
          
          <!-- Current Password -->
          <div class="prof-group">
            <label class="prof-label" for="currPass">Current Password</label>
            <div class="prof-input-wrap">
              <span class="material-symbols-outlined prof-icon">key</span>
              <input type="password" name="current_password" id="currPass" class="prof-input" placeholder="Enter your current password" required>
              <button type="button" class="prof-pass-toggle" onclick="togglePasswordVisibility('currPass', this)" title="Show/Hide Password">
                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
              </button>
            </div>
          </div>

          <!-- New & Confirm Password Grid -->
          <div class="prof-fields-grid">
            <div class="prof-group" style="margin-bottom:0;">
              <label class="prof-label" for="newPass">New Password</label>
              <div class="prof-input-wrap">
                <span class="material-symbols-outlined prof-icon">lock</span>
                <input type="password" name="new_password" id="newPass" class="prof-input" placeholder="At least 6 characters" minlength="6" required>
                <button type="button" class="prof-pass-toggle" onclick="togglePasswordVisibility('newPass', this)" title="Show/Hide Password">
                  <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                </button>
              </div>
            </div>

            <div class="prof-group" style="margin-bottom:0;">
              <label class="prof-label" for="confirmPass">Confirm Password</label>
              <div class="prof-input-wrap">
                <span class="material-symbols-outlined prof-icon">check_circle</span>
                <input type="password" name="confirm_password" id="confirmPass" class="prof-input" placeholder="Re-type new password" minlength="6" required>
                <button type="button" class="prof-pass-toggle" onclick="togglePasswordVisibility('confirmPass', this)" title="Show/Hide Password">
                  <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                </button>
              </div>
            </div>
          </div>

          <p style="font-size:12px;color:#64748B;margin:8px 0 16px;">
            <span class="material-symbols-outlined" style="font-size:14px;vertical-align:-2px;color:#00AEEF;">info</span>
            Must contain at least 6 characters. A mix of letters, numbers, and symbols is recommended.
          </p>

          <button type="submit" class="prof-submit-btn dark">
            <span class="material-symbols-outlined" style="font-size:18px;">shield</span>
            <span>Update Account Password</span>
          </button>
        </div>
      </form>

    </div>

    <!-- ── Right Column: Sidebar ── -->
    <div>

      <!-- Account Overview Card -->
      <div class="prof-card">
        <div class="prof-card-header">
          <div class="prof-card-header-left">
            <div class="prof-card-icon">
              <span class="material-symbols-outlined">insights</span>
            </div>
            <div>
              <h2 class="prof-card-title">Account Overview</h2>
            </div>
          </div>
        </div>
        <div class="prof-card-body" style="padding:20px;">
          
          <a href="<?= url('user/my-bookings') ?>" class="prof-stat-box" title="View Booking History">
            <div>
              <div class="prof-stat-num"><?= sprintf("%02d", $booking_count) ?></div>
              <div class="prof-stat-label">Farm Stays</div>
            </div>
            <span class="material-symbols-outlined" style="font-size:28px;color:#1E40AF;">villa</span>
          </a>

          <a href="<?= url('wishlist') ?>" class="prof-stat-box" title="View Saved Farmhouses">
            <div>
              <div class="prof-stat-num"><?= sprintf("%02d", $wishlist_count) ?></div>
              <div class="prof-stat-label">Saved Estates</div>
            </div>
            <span class="material-symbols-outlined" style="font-size:28px;color:#ec4899;">favorite</span>
          </a>

          <div class="prof-stat-box">
            <div>
              <div class="prof-stat-label" style="margin-top:0;margin-bottom:4px;">Membership Status</div>
              <span style="display:inline-block;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:800;text-transform:uppercase;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
                <?= htmlspecialchars(strtoupper($user['status'] ?? 'ACTIVE')) ?>
              </span>
            </div>
            <span class="material-symbols-outlined" style="font-size:28px;color:#22c55e;">verified</span>
          </div>

          <div class="prof-stat-box" style="margin-bottom:0;">
            <div>
              <div class="prof-stat-label" style="margin-top:0;margin-bottom:2px;">Member Since</div>
              <div style="font-size:14px;font-weight:800;color:#0F172A;"><?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?></div>
            </div>
            <span class="material-symbols-outlined" style="font-size:28px;color:#94a3b8;">calendar_today</span>
          </div>

        </div>
      </div>

      <!-- Quick Navigation -->
      <div class="prof-card">
        <div class="prof-card-header">
          <div class="prof-card-header-left">
            <div class="prof-card-icon">
              <span class="material-symbols-outlined">menu_open</span>
            </div>
            <div>
              <h2 class="prof-card-title">Quick Links</h2>
            </div>
          </div>
        </div>
        <div class="prof-card-body" style="padding:14px;">
          <a href="<?= url('dashboard') ?>" class="prof-nav-item">
            <span class="material-symbols-outlined">grid_view</span>
            <span>Dashboard Overview</span>
          </a>
          <a href="<?= url('user/my-bookings') ?>" class="prof-nav-item">
            <span class="material-symbols-outlined">calendar_month</span>
            <span>My Bookings</span>
          </a>
          <a href="<?= url('wishlist') ?>" class="prof-nav-item">
            <span class="material-symbols-outlined" style="color:#ec4899;">favorite</span>
            <span>Saved Farmhouses</span>
          </a>
          <a href="<?= url('farmhouses') ?>" class="prof-nav-item">
            <span class="material-symbols-outlined">travel_explore</span>
            <span>Explore Farmhouses</span>
          </a>
          <a href="<?= url('user/profile') ?>" class="prof-nav-item active">
            <span class="material-symbols-outlined">manage_accounts</span>
            <span>Account Center</span>
          </a>
        </div>
      </div>

      <!-- Dedicated Concierge Card -->
      <div class="prof-concierge-card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
          <span class="material-symbols-outlined" style="font-size:24px;color:#38bdf8;">support_agent</span>
          <h3 style="font-size:16px;font-weight:800;margin:0;color:#ffffff;">Stayora Concierge</h3>
        </div>
        <p style="font-size:12.5px;color:#94a3b8;margin:0;line-height:1.5;">Need help with reservations, special events, or account questions? Our team is available 24/7.</p>
        <a href="https://wa.me/919876543210?text=Hello%20Stayora%20Concierge,%20I%20need%20assistance" target="_blank" class="prof-concierge-btn">
          <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.835c1.405.836 3.125 1.352 4.953 1.353 5.174 0 9.389-4.215 9.391-9.389.001-2.507-.974-4.864-2.747-6.638s-4.132-2.747-6.638-2.747c-5.176 0-9.391 4.215-9.393 9.39-.001 1.893.562 3.736 1.629 5.308l-.999 3.646 3.737-.981-.132-.084z"/></svg>
          <span>WhatsApp Concierge</span>
        </a>
      </div>

      <!-- Sign Out Box -->
      <div class="prof-danger-box">
        <div class="prof-danger-title">Session Management</div>
        <p style="font-size:12px;color:#64748B;margin:0 0 10px;">End your current user session on this device securely.</p>
        <a href="<?= url('logout') ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:800;color:#dc2626;text-decoration:none;">
          <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
          <span>Sign Out of Account</span>
        </a>
      </div>

    </div>

  </div>

</div>

<script>
// Live avatar preview on upload selection
function previewAvatar(event) {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('Selected image exceeds the 2MB size limit. Please choose a smaller photo.');
      event.target.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      const prev = document.getElementById('avatarPreview');
      if (prev) prev.src = e.target.result;
      const heroPrev = document.getElementById('heroAvatarImg');
      if (heroPrev) heroPrev.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}

// 10-Digit numeric filter for mobile number input
const phoneEl = document.getElementById('phoneInput');
if (phoneEl) {
  phoneEl.addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
  });
}

// Show/Hide password toggle function
function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const icon = btn.querySelector('.material-symbols-outlined');
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) icon.textContent = 'visibility_off';
  } else {
    input.type = 'password';
    if (icon) icon.textContent = 'visibility';
  }
}

// Form 1: Profile Validation
function validateProfile(form) {
  const name = form.name.value.trim();
  if (name.length < 3) {
    alert('Please enter a valid full name (minimum 3 characters).');
    form.name.focus();
    return false;
  }
  const phone = form.phone.value.trim();
  if (!/^[0-9]{10}$/.test(phone)) {
    alert('Please enter a valid 10-digit mobile number.');
    form.phone.focus();
    return false;
  }
  return true;
}

// Form 2: Password Validation
function validatePassword(form) {
  const curr = form.current_password.value;
  const next = form.new_password.value;
  const conf = form.confirm_password.value;

  if (!curr) {
    alert('Please enter your current password.');
    form.current_password.focus();
    return false;
  }
  if (next.length < 6) {
    alert('New password must be at least 6 characters long.');
    form.new_password.focus();
    return false;
  }
  if (next !== conf) {
    alert('New password and Confirm Password do not match. Please re-check.');
    form.confirm_password.focus();
    return false;
  }
  return true;
}
</script>

<?php include __DIR__ . "/../Includes/footer.php"; ?>