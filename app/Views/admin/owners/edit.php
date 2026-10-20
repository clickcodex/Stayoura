<?php
$owner = $owner ?? [];
$ownerId = (int)($owner['id'] ?? 0);
$pageTitle   = "Edit Owner: " . htmlspecialchars($owner['name'] ?? 'Partner') . " • Stayora Executive Console";
$currentPage = "owners";
$headerTitle = "Edit Owner Profile";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

// Helper to resolve avatar
function resolveOwnerAvatarSrc(?string $img): string {
    if (empty($img)) {
        return '';
    }
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
        return $img;
    }
    return asset('assets/images/uploads/avatars/' . ltrim($img, '/'));
}
$currentAvatarUrl = resolveOwnerAvatarSrc($owner['profile_image'] ?? null);
$currentType = $owner['owner_type'] ?? 'individual';
?>

<div class="max-w-5xl mx-auto space-y-6 pb-12">

  <!-- ══════════════════════════════════════════════
       1. TOP HEADER & BREADCRUMBS
       ══════════════════════════════════════════════ -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs">
    <div class="flex items-center gap-3">
      <a href="<?= url('admin/owners') ?>" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors shrink-0">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
      </a>
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-0.5">
          <a href="<?= url('admin/owners') ?>" class="hover:text-brand-700 transition-colors">Property Owners</a>
          <span>/</span>
          <a href="<?= url('admin/owners/view/' . $ownerId) ?>" class="hover:text-brand-700 transition-colors">#<?= $ownerId ?></a>
          <span>/</span>
          <span class="text-slate-800">Edit Profile</span>
        </div>
        <h1 class="font-headline font-black text-xl sm:text-2xl text-slate-900 tracking-tight">
          Edit Owner: <?= htmlspecialchars($owner['name']) ?>
        </h1>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <a href="<?= url('admin/owners/view/' . $ownerId) ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
        <span class="material-symbols-outlined text-[17px]">badge</span>
        <span>View Dossier</span>
      </a>
      <button type="button" onclick="document.getElementById('owner-edit-form').requestSubmit()" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
        <span class="material-symbols-outlined text-base">save</span>
        <span>Save Changes</span>
      </button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       2. EDIT FORM
       ══════════════════════════════════════════════ -->
  <form id="owner-edit-form" method="POST" action="<?= url('admin/owners/update') ?>" enctype="multipart/form-data" onsubmit="submitEditForm(event)" class="space-y-6">
    <input type="hidden" name="id" value="<?= $ownerId ?>">

    <!-- Section 1: Entity Classification & Identity -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-6">
      <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">badge</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Identity &amp; Classification</h3>
          <p class="text-xs text-slate-400">Owner entity designation and name coordinates</p>
        </div>
      </div>

      <!-- Entity Type Selector -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          Owner Entity Type <span class="text-rose-500">*</span>
        </label>
        <div class="grid grid-cols-2 gap-3 max-w-md">
          <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all entity-type-option <?= $currentType === 'individual' ? 'border-brand-500 bg-brand-50/50' : 'border-slate-200' ?>" id="type-opt-individual">
            <input type="radio" name="owner_type" value="individual" <?= $currentType === 'individual' ? 'checked' : '' ?> onchange="toggleEntityType('individual')" class="text-brand-600 focus:ring-brand-500">
            <div>
              <div class="font-bold text-xs sm:text-sm text-slate-900">Individual Host</div>
              <div class="text-[10px] text-slate-400">Private property owner</div>
            </div>
          </label>

          <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all entity-type-option <?= $currentType === 'organization' ? 'border-brand-500 bg-brand-50/50' : 'border-slate-200' ?>" id="type-opt-organization">
            <input type="radio" name="owner_type" value="organization" <?= $currentType === 'organization' ? 'checked' : '' ?> onchange="toggleEntityType('organization')" class="text-brand-600 focus:ring-brand-500">
            <div>
              <div class="font-bold text-xs sm:text-sm text-slate-900">Organization / LLP</div>
              <div class="text-[10px] text-slate-400">Company or hospitality group</div>
            </div>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        
        <!-- Full Name -->
        <div class="md:col-span-6">
          <label for="owner-name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" id="lbl-name">
            <?= $currentType === 'organization' ? 'Authorized Contact Person' : 'Individual Full Name' ?> <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            name="name" 
            id="owner-name" 
            required 
            maxlength="100"
            value="<?= htmlspecialchars($owner['name'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Business / Organization Name -->
        <div class="md:col-span-6" id="business-name-container">
          <label for="owner-business-name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" id="lbl-business-name">
            Registered Business Name <?= $currentType === 'organization' ? '<span class="text-rose-500">*</span>' : '<span class="text-slate-400 font-normal">(Optional for Individual)</span>' ?>
          </label>
          <input 
            type="text" 
            name="business_name" 
            id="owner-business-name" 
            maxlength="150"
            value="<?= htmlspecialchars($owner['business_name'] ?? '') ?>"
            placeholder="e.g., Singhania Luxury Escapes Pvt Ltd" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Profile Avatar or Logo Upload -->
        <div class="md:col-span-12">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
            Profile Image or Organization Logo
          </label>
          <div class="flex items-center gap-4">
            <div id="avatar-preview-box" class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0 shadow-2xs">
              <?php if (!empty($currentAvatarUrl)): ?>
                <img src="<?= $currentAvatarUrl ?>" alt="<?= htmlspecialchars($owner['name']) ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <span class="material-symbols-outlined text-2xl">person</span>
              <?php endif; ?>
            </div>
            <div>
              <input 
                type="file" 
                name="avatar" 
                id="avatar-input" 
                accept="image/png, image/jpeg, image/webp" 
                onchange="previewAvatar(this)" 
                class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer cursor-pointer"
              />
              <p class="text-[11px] text-slate-400 mt-1">Leave empty to keep current picture. Accepts JPG, PNG, WEBP up to 5 MB.</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Section 2: Contact Coordinates -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">contact_phone</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Contact Coordinates</h3>
          <p class="text-xs text-slate-400">Direct phone numbers and administrative correspondence email</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <!-- Primary Phone -->
        <div>
          <label for="owner-phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Primary Phone Number <span class="text-rose-500">*</span>
          </label>
          <input 
            type="tel" 
            name="phone" 
            id="owner-phone" 
            required 
            maxlength="20"
            value="<?= htmlspecialchars($owner['phone'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Alternate Phone -->
        <div>
          <label for="owner-alt-phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Alternate / Emergency Phone
          </label>
          <input 
            type="tel" 
            name="alternate_phone" 
            id="owner-alt-phone" 
            maxlength="20"
            value="<?= htmlspecialchars($owner['alternate_phone'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Email Address -->
        <div>
          <label for="owner-email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Email Address <span class="text-rose-500">*</span>
          </label>
          <input 
            type="email" 
            name="email" 
            id="owner-email" 
            required 
            maxlength="100"
            value="<?= htmlspecialchars($owner['email'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

      </div>
    </div>

    <!-- Section 3: Physical Address & Location -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">location_on</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Address &amp; Location</h3>
          <p class="text-xs text-slate-400">Physical residence or corporate headquarters</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        
        <!-- Street Address -->
        <div class="md:col-span-4">
          <label for="owner-address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Street Address / Suite
          </label>
          <input 
            type="text" 
            name="address" 
            id="owner-address" 
            value="<?= htmlspecialchars($owner['address'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- City -->
        <div>
          <label for="owner-city" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            City <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            name="city" 
            id="owner-city" 
            required 
            value="<?= htmlspecialchars($owner['city'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- State -->
        <div>
          <label for="owner-state" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            State
          </label>
          <input 
            type="text" 
            name="state" 
            id="owner-state" 
            value="<?= htmlspecialchars($owner['state'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Pincode -->
        <div>
          <label for="owner-pincode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Postal / PIN Code
          </label>
          <input 
            type="text" 
            name="pincode" 
            id="owner-pincode" 
            value="<?= htmlspecialchars($owner['pincode'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Country -->
        <div>
          <label for="owner-country" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Country
          </label>
          <input 
            type="text" 
            name="country" 
            id="owner-country" 
            value="<?= htmlspecialchars($owner['country'] ?? 'India') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

      </div>
    </div>

    <!-- Section 4: Legal & Identification Coordinates -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">verified</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Legal &amp; Business Identification</h3>
          <p class="text-xs text-slate-400">Government ID details for verification and compliance</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- ID Type -->
        <div>
          <label for="owner-gov-id-type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Identification Document Type
          </label>
          <select 
            name="gov_id_type" 
            id="owner-gov-id-type" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="">Select ID Type...</option>
            <option value="PAN Card" <?= ($owner['gov_id_type'] ?? '') === 'PAN Card' ? 'selected' : '' ?>>PAN Card (Permanent Account Number)</option>
            <option value="Aadhaar Card" <?= ($owner['gov_id_type'] ?? '') === 'Aadhaar Card' ? 'selected' : '' ?>>Aadhaar Card</option>
            <option value="Passport" <?= ($owner['gov_id_type'] ?? '') === 'Passport' ? 'selected' : '' ?>>Passport</option>
            <option value="GSTIN / CIN" <?= ($owner['gov_id_type'] ?? '') === 'GSTIN / CIN' ? 'selected' : '' ?>>GSTIN / Corporate CIN</option>
            <option value="LLPIN" <?= ($owner['gov_id_type'] ?? '') === 'LLPIN' ? 'selected' : '' ?>>LLPIN (Limited Liability Partnership)</option>
            <option value="Other" <?= ($owner['gov_id_type'] ?? '') === 'Other' ? 'selected' : '' ?>>Other Official Proof</option>
          </select>
        </div>

        <!-- ID Number -->
        <div>
          <label for="owner-gov-id-number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Identification / Registration Number
          </label>
          <input 
            type="text" 
            name="gov_id_number" 
            id="owner-gov-id-number" 
            value="<?= htmlspecialchars($owner['gov_id_number'] ?? '') ?>"
            placeholder="e.g., ABCPO1234F or 07AAACS1234F1Z8" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

      </div>
    </div>

    <!-- Section 5: Banking & Settlement Coordinates -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">account_balance</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Banking &amp; Settlement Coordinates</h3>
          <p class="text-xs text-slate-400">Account coordinates for host disbursements and settlements</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <!-- Bank Name -->
        <div>
          <label for="owner-bank-name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Bank Name
          </label>
          <input 
            type="text" 
            name="bank_name" 
            id="owner-bank-name" 
            value="<?= htmlspecialchars($owner['bank_name'] ?? '') ?>"
            placeholder="e.g., HDFC Bank, ICICI Bank" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Account Holder -->
        <div>
          <label for="owner-bank-holder" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Account Holder Name
          </label>
          <input 
            type="text" 
            name="bank_account_holder" 
            id="owner-bank-holder" 
            value="<?= htmlspecialchars($owner['bank_account_holder'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Account Number -->
        <div>
          <label for="owner-bank-account" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Account Number
          </label>
          <input 
            type="text" 
            name="bank_account_number" 
            id="owner-bank-account" 
            value="<?= htmlspecialchars($owner['bank_account_number'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- IFSC Code -->
        <div>
          <label for="owner-bank-ifsc" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            IFSC Code
          </label>
          <input 
            type="text" 
            name="bank_ifsc" 
            id="owner-bank-ifsc" 
            maxlength="20"
            value="<?= htmlspecialchars($owner['bank_ifsc'] ?? '') ?>"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all uppercase"
          />
        </div>

        <!-- UPI ID -->
        <div class="md:col-span-2">
          <label for="owner-upi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            UPI ID / VPA
          </label>
          <input 
            type="text" 
            name="upi_id" 
            id="owner-upi" 
            value="<?= htmlspecialchars($owner['upi_id'] ?? '') ?>"
            placeholder="e.g., vikram.oberoi@okhdfcbank" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          />
        </div>

      </div>
    </div>

    <!-- Section 6: Operational Status & Admin Notes -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
        </span>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900">Status &amp; Administrative Notes</h3>
          <p class="text-xs text-slate-400">Account status, verification standing, and internal team remarks</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- Account Status -->
        <div>
          <label for="owner-status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Account Status <span class="text-rose-500">*</span>
          </label>
          <select 
            name="status" 
            id="owner-status" 
            required 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="active" <?= ($owner['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active (Fully operational)</option>
            <option value="pending" <?= ($owner['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
            <option value="inactive" <?= ($owner['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="suspended" <?= ($owner['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
          </select>
        </div>

        <!-- KYC Status -->
        <div>
          <label for="owner-kyc-status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            KYC Verification Standing <span class="text-rose-500">*</span>
          </label>
          <select 
            name="kyc_status" 
            id="owner-kyc-status" 
            required 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-xs sm:text-sm font-semibold focus:bg-white focus:border-brand-500 focus:outline-none transition-all">
            <option value="pending" <?= ($owner['kyc_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Document Verification</option>
            <option value="verified" <?= ($owner['kyc_status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified &amp; Approved</option>
            <option value="rejected" <?= ($owner['kyc_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected / Incomplete</option>
          </select>
        </div>

        <!-- Internal Notes -->
        <div class="md:col-span-2">
          <label for="owner-notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Internal Administrative Notes
          </label>
          <textarea 
            name="admin_notes" 
            id="owner-notes" 
            rows="3" 
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"><?= htmlspecialchars($owner['admin_notes'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <!-- Bottom Submit Buttons -->
    <div class="flex items-center justify-end gap-3 pt-3">
      <a href="<?= url('admin/owners/view/' . $ownerId) ?>" class="px-5 py-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
        Cancel
      </a>
      <button type="submit" id="edit-submit-btn" class="px-6 py-3 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-2">
        <span class="material-symbols-outlined text-base">save</span>
        <span>Save Changes</span>
      </button>
    </div>

  </form>

</div>

<script>
  // ── Entity Type Dynamic Toggle ──
  function toggleEntityType(type) {
    const isOrg = (type === 'organization');
    const optInd = document.getElementById('type-opt-individual');
    const optOrg = document.getElementById('type-opt-organization');
    const lblName = document.getElementById('lbl-name');
    const lblBiz = document.getElementById('lbl-business-name');
    const bizInput = document.getElementById('owner-business-name');

    if (isOrg) {
      optOrg.classList.add('border-brand-500', 'bg-brand-50/50');
      optInd.classList.remove('border-brand-500', 'bg-brand-50/50');
      lblName.innerHTML = 'Authorized Contact Person <span class="text-rose-500">*</span>';
      lblBiz.innerHTML = 'Registered Organization Name <span class="text-rose-500">*</span>';
      bizInput.required = true;
    } else {
      optInd.classList.add('border-brand-500', 'bg-brand-50/50');
      optOrg.classList.remove('border-brand-500', 'bg-brand-50/50');
      lblName.innerHTML = 'Individual Full Name <span class="text-rose-500">*</span>';
      lblBiz.innerHTML = 'Registered Business Name <span class="text-slate-400 font-normal">(Optional for Individual)</span>';
      bizInput.required = false;
    }
  }

  // Initialize toggle state
  toggleEntityType('<?= $currentType ?>');

  // ── Avatar Upload Preview ──
  function previewAvatar(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const box = document.getElementById('avatar-preview-box');
        box.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  // ── AJAX Form Submission ──
  function submitEditForm(e) {
    e.preventDefault();
    const btn = document.getElementById('edit-submit-btn');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">refresh</span> Saving...';

    const form = document.getElementById('owner-edit-form');
    const formData = new FormData(form);

    fetch('<?= url('admin/owners/update?format=json') ?>', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = origText;
      if (data.success) {
        if (window.adminToast) {
          adminToast.success(data.message || 'Owner profile updated successfully.');
        }
        setTimeout(() => {
          window.location.href = data.redirect || '<?= url('admin/owners/view/' . $ownerId) ?>';
        }, 600);
      } else {
        if (window.adminToast) {
          adminToast.error(data.message || 'Error updating owner profile.');
        } else {
          alert(data.message || 'Error updating owner profile.');
        }
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = origText;
      if (window.adminToast) adminToast.error('Network error during owner profile update.');
    });
  }
</script>

<!-- ── Shared Admin Layout Footer ── -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
