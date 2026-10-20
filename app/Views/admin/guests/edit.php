<?php
$pageTitle   = "Edit Profile: {$guest['name']} • Stayora Executive Console";
$currentPage = "guests";
$headerTitle = "Edit Guest Profile";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

function resolveEditAvatar(?string $img, ?string $avatarUrl = null): string {
    if (!empty($avatarUrl)) {
        return $avatarUrl;
    }
    if (empty($img) || $img === 'default_profile.png') {
        return '';
    }
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
        return $img;
    }
    return asset('assets/images/uploads/profiles/' . ltrim($img, '/'));
}
$currentAvatar = resolveEditAvatar($guest['profile_image'] ?? null, $guest['avatar_url'] ?? null);
?>

<div class="space-y-6 max-w-5xl mx-auto pb-12">

  <!-- ══════════════════════════════════════════════
       1. TOP ACTION BAR & BREADCRUMBS
       ══════════════════════════════════════════════ -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
        <a href="<?= url('admin/dashboard') ?>" class="hover:text-brand-700 transition-colors">Console</a>
        <span>/</span>
        <a href="<?= url('admin/guests') ?>" class="hover:text-brand-700 transition-colors">Guests &amp; Members</a>
        <span>/</span>
        <a href="<?= url('admin/guests/view/' . $guest['id']) ?>" class="hover:text-brand-700 transition-colors"><?= htmlspecialchars($guest['name']) ?></a>
        <span>/</span>
        <span class="text-slate-800">Edit</span>
      </div>
      <h1 class="font-headline font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2">
        <span>Edit Profile: <?= htmlspecialchars($guest['name']) ?></span>
        <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
          <?= htmlspecialchars($guest['guest_code'] ?: 'ID#' . $guest['id']) ?>
        </span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">
        Update personal contact information, residential address, emergency contact, and stay preferences.
      </p>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-2 shrink-0">
      <a href="<?= url('admin/guests/view/' . $guest['id']) ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
        <span class="material-symbols-outlined text-[17px]">visibility</span>
        <span>View Dossier</span>
      </a>
      <a href="<?= url('admin/guests') ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Directory</span>
      </a>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════
       2. EDIT FORM
       ══════════════════════════════════════════════ -->
  <form method="POST" action="<?= url('admin/guests/update') ?>" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="id" value="<?= $guest['id'] ?>">

    <!-- SECTION 1: ACCOUNT TYPE & CATEGORY -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">badge</span>
        <h2 class="font-headline font-bold text-base text-slate-900">1. Guest Category &amp; Classification</h2>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        
        <!-- Standard Guest -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="guest" <?= ($guest['guest_type'] ?? 'guest') === 'guest' ? 'checked' : '' ?> class="sr-only">
          <span class="material-symbols-outlined text-2xl text-slate-500 mb-1.5">person</span>
          <span class="font-bold text-xs text-slate-900">Standard Guest</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Leisure / walk-in stayer</span>
        </label>

        <!-- Registered Member -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="registered_member" <?= ($guest['guest_type'] ?? '') === 'registered_member' ? 'checked' : '' ?> class="sr-only">
          <span class="material-symbols-outlined text-2xl text-amber-500 mb-1.5">workspace_premium</span>
          <span class="font-bold text-xs text-slate-900">Club Member</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Tier privileges enrolled</span>
        </label>

        <!-- VIP Client -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="vip" <?= ($guest['guest_type'] ?? '') === 'vip' ? 'checked' : '' ?> class="sr-only">
          <span class="material-symbols-outlined text-2xl text-purple-600 mb-1.5">diamond</span>
          <span class="font-bold text-xs text-slate-900">VIP Concierge</span>
          <span class="text-[10px] text-slate-400 mt-0.5">High-profile retreat stayer</span>
        </label>

        <!-- Corporate Guest -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="corporate_guest" <?= ($guest['guest_type'] ?? '') === 'corporate_guest' ? 'checked' : '' ?> class="sr-only">
          <span class="material-symbols-outlined text-2xl text-blue-600 mb-1.5">business</span>
          <span class="font-bold text-xs text-slate-900">Corporate</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Company business account</span>
        </label>

      </div>
    </div>

    <!-- SECTION 2: PERSONAL IDENTITY & CONTACTS -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">contacts</span>
        <h2 class="font-headline font-bold text-base text-slate-900">2. Personal Profile &amp; Contact Coordinates</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Full Name -->
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name <span class="text-rose-500">*</span></label>
          <input type="text" name="name" value="<?= htmlspecialchars($guest['name']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Gender -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
          <select name="gender" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="">Select Gender...</option>
            <option value="male" <?= ($guest['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= ($guest['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other" <?= ($guest['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <!-- Primary Phone -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Primary Mobile Phone <span class="text-rose-500">*</span></label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($guest['phone']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Alternate Phone -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Alternate / WhatsApp Phone</label>
          <input type="tel" name="alternate_phone" value="<?= htmlspecialchars($guest['alternate_phone'] ?? '') ?>" placeholder="Optional backup number" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Email Address -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($guest['email'] ?? '') ?>" placeholder="e.g., guest@domain.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Date of Birth -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
          <input type="date" name="date_of_birth" value="<?= htmlspecialchars($guest['date_of_birth'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Avatar Upload -->
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Update Profile Photo / Avatar</label>
          <div class="flex items-center gap-4">
            <?php if ($currentAvatar): ?>
              <img src="<?= htmlspecialchars($currentAvatar) ?>" id="editAvatarPreview" alt="Current Avatar" class="w-12 h-12 rounded-full object-cover border border-slate-200 shrink-0">
            <?php else: ?>
              <div id="editAvatarPreviewText" class="w-12 h-12 rounded-full bg-brand-50 text-brand-700 font-headline font-black text-base flex items-center justify-center border border-brand-200 shrink-0">
                <?= strtoupper(substr($guest['name'], 0, 1)) ?>
              </div>
            <?php endif; ?>
            <input type="file" name="profile_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 3: RESIDENTIAL ADDRESS -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">home_pin</span>
        <h2 class="font-headline font-bold text-base text-slate-900">3. Residential Address</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Address Line 1</label>
          <input type="text" name="address_line1" value="<?= htmlspecialchars($guest['address_line1'] ?? '') ?>" placeholder="Flat, house no., building" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Address Line 2</label>
          <input type="text" name="address_line2" value="<?= htmlspecialchars($guest['address_line2'] ?? '') ?>" placeholder="Street, landmark" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
          <input type="text" name="city" value="<?= htmlspecialchars($guest['city'] ?? '') ?>" placeholder="e.g., Mumbai" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">State / Province</label>
          <input type="text" name="state" value="<?= htmlspecialchars($guest['state'] ?? '') ?>" placeholder="e.g., Maharashtra" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">PIN / Postal Code</label>
          <input type="text" name="pincode" value="<?= htmlspecialchars($guest['pincode'] ?? '') ?>" placeholder="e.g., 400001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
          <input type="text" name="country" value="<?= htmlspecialchars($guest['country'] ?? 'India') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

      </div>
    </div>

    <!-- SECTION 4: EMERGENCY CONTACT -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-rose-600">emergency</span>
        <h2 class="font-headline font-bold text-base text-slate-900">4. Emergency Contact (Stay Safety)</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Person</label>
          <input type="text" name="emergency_contact_name" value="<?= htmlspecialchars($guest['emergency_contact_name'] ?? '') ?>" placeholder="Name of family/friend" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Phone Number</label>
          <input type="tel" name="emergency_contact_phone" value="<?= htmlspecialchars($guest['emergency_contact_phone'] ?? '') ?>" placeholder="Emergency phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Relationship</label>
          <input type="text" name="emergency_contact_relation" value="<?= htmlspecialchars($guest['emergency_contact_relation'] ?? '') ?>" placeholder="e.g., Spouse, Parent" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

      </div>
    </div>

    <!-- SECTION 5: GOVERNMENT ID -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">id_card</span>
        <h2 class="font-headline font-bold text-base text-slate-900">5. Government Identity Reference</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Government ID Type</label>
          <select name="gov_id_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="">Select ID Type...</option>
            <option value="aadhaar" <?= ($guest['gov_id_type'] ?? '') === 'aadhaar' ? 'selected' : '' ?>>Aadhaar Card</option>
            <option value="pan" <?= ($guest['gov_id_type'] ?? '') === 'pan' ? 'selected' : '' ?>>Permanent Account Number (PAN)</option>
            <option value="passport" <?= ($guest['gov_id_type'] ?? '') === 'passport' ? 'selected' : '' ?>>Passport</option>
            <option value="driving_license" <?= ($guest['gov_id_type'] ?? '') === 'driving_license' ? 'selected' : '' ?>>Driving License</option>
            <option value="voter_id" <?= ($guest['gov_id_type'] ?? '') === 'voter_id' ? 'selected' : '' ?>>Voter ID Card</option>
            <option value="other" <?= ($guest['gov_id_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other Official ID</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Government ID Number</label>
          <input type="text" name="gov_id_number" value="<?= htmlspecialchars($guest['gov_id_number'] ?? '') ?>" placeholder="e.g., ID or Passport Number" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

      </div>
    </div>

    <!-- SECTION 6: PREFERENCES & ADMINISTRATIVE NOTES -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">room_service</span>
        <h2 class="font-headline font-bold text-base text-slate-900">6. Preferences &amp; Administrative Notes</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Special Stay Requests &amp; Preferences</label>
          <textarea name="special_requests" rows="4" placeholder="Dietary restrictions, preferred villa arrangements..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all"><?= htmlspecialchars($guest['special_requests'] ?? '') ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Administrative Staff Notes</label>
          <textarea name="admin_notes" rows="4" placeholder="Confidential staff notes..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all"><?= htmlspecialchars($guest['admin_notes'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <!-- FORM SUBMISSION -->
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
      <a href="<?= url('admin/guests/view/' . $guest['id']) ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all">
        Cancel
      </a>
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[17px]">save</span>
        <span>Save Profile Changes</span>
      </button>
    </div>

  </form>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
