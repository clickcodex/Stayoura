<?php
$pageTitle   = "Register New Guest • Stayora Executive Console";
$currentPage = "guests";
$headerTitle = "Register Guest / Member";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

$suggestedGuestCode = $suggestedGuestCode ?? 'STY-G-' . rand(10000, 99999);
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
        <span class="text-slate-800">Register</span>
      </div>
      <h1 class="font-headline font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2">
        <span>Register New Guest Profile</span>
        <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
          <?= htmlspecialchars($suggestedGuestCode) ?>
        </span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">
        Create a guest profile with personal contact coordinates, emergency contact, KYC identity, and membership privileges.
      </p>
    </div>

    <!-- Back to Directory -->
    <a href="<?= url('admin/guests') ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs shrink-0">
      <span class="material-symbols-outlined text-[17px]">arrow_back</span>
      <span>Back to Directory</span>
    </a>
  </div>

  <!-- ══════════════════════════════════════════════
       2. REGISTRATION FORM
       ══════════════════════════════════════════════ -->
  <form method="POST" action="<?= url('admin/guests/store') ?>" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="guest_code" value="<?= htmlspecialchars($suggestedGuestCode) ?>">

    <!-- SECTION 1: ACCOUNT TYPE & CATEGORY -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">badge</span>
        <h2 class="font-headline font-bold text-base text-slate-900">1. Guest Category &amp; Classification</h2>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        
        <!-- Standard Guest -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="guest" checked onchange="handleCategoryChange()" class="sr-only">
          <span class="material-symbols-outlined text-2xl text-slate-500 mb-1.5">person</span>
          <span class="font-bold text-xs text-slate-900">Standard Guest</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Walk-in or leisure stayer</span>
        </label>

        <!-- Registered Member -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="registered_member" onchange="handleCategoryChange()" class="sr-only">
          <span class="material-symbols-outlined text-2xl text-amber-500 mb-1.5">workspace_premium</span>
          <span class="font-bold text-xs text-slate-900">Club Member</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Enrolled in tiers</span>
        </label>

        <!-- VIP Client -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="vip" onchange="handleCategoryChange()" class="sr-only">
          <span class="material-symbols-outlined text-2xl text-purple-600 mb-1.5">diamond</span>
          <span class="font-bold text-xs text-slate-900">VIP Concierge</span>
          <span class="text-[10px] text-slate-400 mt-0.5">High-profile retreat stayer</span>
        </label>

        <!-- Corporate Guest -->
        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 hover:border-brand-500 has-checked:border-brand-700 has-checked:bg-brand-50/30">
          <input type="radio" name="guest_type" value="corporate_guest" onchange="handleCategoryChange()" class="sr-only">
          <span class="material-symbols-outlined text-2xl text-blue-600 mb-1.5">business</span>
          <span class="font-bold text-xs text-slate-900">Corporate</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Company offsite account</span>
        </label>

      </div>
    </div>

    <!-- SECTION 2: PERSONAL IDENTITY & CONTACTS -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">contacts</span>
        <h2 class="font-headline font-bold text-base text-slate-900">2. Personal Profile &amp; Contact Details</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Full Name -->
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name <span class="text-rose-500">*</span></label>
          <input type="text" name="name" required placeholder="e.g., Rajesh Kumar Sharma" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Gender -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
          <select name="gender" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="">Select Gender...</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>

        <!-- Primary Phone -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Primary Mobile Phone <span class="text-rose-500">*</span></label>
          <input type="tel" name="phone" required placeholder="e.g., 9876543210" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Alternate Phone -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Alternate / WhatsApp Phone</label>
          <input type="tel" name="alternate_phone" placeholder="Optional backup number" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Email Address -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
          <input type="email" name="email" placeholder="e.g., guest@domain.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Date of Birth -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
          <input type="date" name="date_of_birth" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <!-- Account Status -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Initial Status</label>
          <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="active">Active (Verified / Immediate Booking)</option>
            <option value="blocked">Blocked (Under Review)</option>
          </select>
        </div>

        <!-- Profile Avatar Upload -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Profile Photo / Avatar</label>
          <input type="file" name="profile_image" accept="image/*" onchange="previewAvatar(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
          <div id="avatarPreviewContainer" class="hidden mt-2">
            <img id="avatarPreviewImg" src="" alt="Preview" class="w-12 h-12 rounded-full object-cover border border-slate-200">
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 3: RESIDENTIAL & POSTAL ADDRESS -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">home_pin</span>
        <h2 class="font-headline font-bold text-base text-slate-900">3. Residential Address</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Address Line 1</label>
          <input type="text" name="address_line1" placeholder="Flat, house no., building, apartment" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Address Line 2 (Optional)</label>
          <input type="text" name="address_line2" placeholder="Street, sector, landmark" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
          <input type="text" name="city" placeholder="e.g., Mumbai, New Delhi" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">State / Province</label>
          <input type="text" name="state" placeholder="e.g., Maharashtra, Goa" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">PIN / Postal Code</label>
          <input type="text" name="pincode" placeholder="e.g., 400001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
          <input type="text" name="country" value="India" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

      </div>
    </div>

    <!-- SECTION 4: EMERGENCY CONTACT COORDINATES -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-rose-600">emergency</span>
        <h2 class="font-headline font-bold text-base text-slate-900">4. Emergency Contact (Stay Safety)</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Person</label>
          <input type="text" name="emergency_contact_name" placeholder="Name of family/friend" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Phone Number</label>
          <input type="tel" name="emergency_contact_phone" placeholder="Valid emergency contact phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Relationship</label>
          <input type="text" name="emergency_contact_relation" placeholder="e.g., Spouse, Parent, Sibling" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

      </div>
    </div>

    <!-- SECTION 5: GOVERNMENT ID & INITIAL KYC -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">id_card</span>
        <h2 class="font-headline font-bold text-base text-slate-900">5. Government Identity Verification</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Government ID Type</label>
          <select name="gov_id_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="">Select ID Type...</option>
            <option value="aadhaar">Aadhaar Card</option>
            <option value="pan">Permanent Account Number (PAN)</option>
            <option value="passport">Passport</option>
            <option value="driving_license">Driving License</option>
            <option value="voter_id">Voter ID Card</option>
            <option value="other">Other Official Government ID</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Government ID Number</label>
          <input type="text" name="gov_id_number" placeholder="e.g., XXXX-XXXX-1234 / Passport #" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Initial Verification Status</label>
          <select name="kyc_status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="unverified">Unverified (Pending Document Upload)</option>
            <option value="pending">Pending Document Review</option>
            <option value="verified">Verified (Identity Confirmed)</option>
          </select>
        </div>

      </div>
    </div>

    <!-- SECTION 6: MEMBERSHIP SETTINGS (DYNAMIC) -->
    <div id="membershipSection" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-amber-600">workspace_premium</span>
        <h2 class="font-headline font-bold text-base text-slate-900">6. Membership Tier &amp; Club Enrollment</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Membership Tier</label>
          <select name="membership_tier" id="selectMembershipTier" onchange="handleTierChange()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            <option value="none">None (Standard Leisure Guest)</option>
            <option value="silver">Silver Member (Standard Perks)</option>
            <option value="gold">Gold Member (Priority Concierge)</option>
            <option value="platinum">Platinum Member (Exclusive Privileges)</option>
            <option value="vip">VIP Concierge (Highest Tier)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Membership Number</label>
          <input type="text" name="membership_number" id="inputMembershipNumber" placeholder="Auto-generated if empty" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-mono">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Start / Enrollment Date</label>
          <input type="date" name="membership_start_date" value="<?= date('Y-m-d') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Expiry / Renewal Date</label>
          <input type="date" name="membership_expiry_date" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
        </div>

      </div>
    </div>

    <!-- SECTION 7: PREFERENCES & ADMINISTRATIVE STAFF NOTES -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="material-symbols-outlined text-brand-700">room_service</span>
        <h2 class="font-headline font-bold text-base text-slate-900">7. Guest Preferences &amp; Administrative Notes</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Special Stay Requests &amp; Preferences</label>
          <textarea name="special_requests" rows="3" placeholder="Dietary restrictions (vegan/gluten-free), poolside preference, early check-in habits..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all"></textarea>
          <span class="text-[10px] text-slate-400 mt-0.5 block">Shared with property hosts and concierge team for personalized hospitality.</span>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Internal Administrative Staff Notes</label>
          <textarea name="admin_notes" rows="3" placeholder="Confidential staff notes, corporate affiliations, high net-worth indicator..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all"></textarea>
          <span class="text-[10px] text-rose-500 mt-0.5 block">Strictly confidential — visible only to platform administrators.</span>
        </div>

      </div>
    </div>

    <!-- FORM SUBMISSION BUTTONS -->
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
      <a href="<?= url('admin/guests') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all">
        Cancel
      </a>
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[17px]">check_circle</span>
        <span>Register Guest Profile</span>
      </button>
    </div>

  </form>

</div>

<script>
function handleCategoryChange() {
  const selectedType = document.querySelector('input[name="guest_type"]:checked').value;
  const tierSelect = document.getElementById('selectMembershipTier');

  if (selectedType === 'vip') {
    tierSelect.value = 'vip';
  } else if (selectedType === 'registered_member') {
    if (tierSelect.value === 'none') {
      tierSelect.value = 'silver';
    }
  } else if (selectedType === 'corporate_guest') {
    if (tierSelect.value === 'none') {
      tierSelect.value = 'gold';
    }
  }
}

function handleTierChange() {
  const tier = document.getElementById('selectMembershipTier').value;
  const numInput = document.getElementById('inputMembershipNumber');
  if (tier !== 'none' && !numInput.value) {
    const prefixes = { silver: 'SLV', gold: 'GLD', platinum: 'PLT', vip: 'VIP' };
    const pfx = prefixes[tier] || 'MBR';
    numInput.value = 'STY-' + pfx + '-' + Math.floor(100 + Math.random() * 900);
  }
}

function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('avatarPreviewImg').src = e.target.result;
      document.getElementById('avatarPreviewContainer').classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
