<?php
$pageTitle   = htmlspecialchars($property['title']) . " • Details • Stayora Console";
$currentPage = "properties";
$headerTitle = "Property Details";

// ── Shared Admin Layout Header ──
require_once __DIR__ . '/../layouts/header.php';

use App\Helpers\HtmlSanitizerHelper;

function resolveViewImg(?string $url): string {
    if (empty($url)) return 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80';
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) return $url;
    return asset($url);
}

$rentalType = $property['rental_type'] ?? (!empty($property['allow_room_booking']) ? 'both' : 'complete');
$roomTypes  = $property['room_types'] ?? [];
$amenities  = $property['amenities'] ?? [];
$rules      = $property['rules'] ?? [];
?>

<!-- Top Breadcrumbs & Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
  <div>
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
      <a href="<?= url('admin/properties') ?>" class="hover:text-brand-700 transition-colors">Manage Properties</a>
      <span>/</span>
      <span class="text-slate-700 font-bold"><?= htmlspecialchars($property['title']) ?> Details</span>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
      <h2 class="font-headline font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
        <?= htmlspecialchars($property['title']) ?>
      </h2>
      <!-- Booking Mode Badge -->
      <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= match($rentalType) {
        'room_wise' => 'bg-purple-50 text-purple-700 border border-purple-200',
        'both'      => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        default     => 'bg-blue-50 text-brand-700 border border-blue-200'
      } ?>">
        <?= match($rentalType) {
          'room_wise' => 'Room-Wise Booking Only',
          'both'      => 'Dual Mode (Complete + Rooms)',
          default     => 'Complete Property Exclusive'
        } ?>
      </span>
    </div>
    <p class="text-xs sm:text-sm text-slate-500 mt-1">
      <?= htmlspecialchars($property['property_type_name'] ?? 'Luxury Farmhouse') ?> • <?= htmlspecialchars($property['location'] ?? 'India') ?>
    </p>
  </div>

  <div class="flex items-center gap-2.5">
    <a href="<?= url('admin/properties') ?>" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors">
      Back to List
    </a>
    <a href="<?= url('admin/properties/edit?id=' . encrypt_id($property['id'])) ?>" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold transition-colors flex items-center gap-1.5 shadow-md shadow-brand-700/20">
      <span class="material-symbols-outlined text-sm">edit</span>
      <span>Edit Property</span>
    </a>
  </div>
</div>

<div class="space-y-6">

  <!-- Gallery Banner -->
  <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <?php 
      $images = $property['images'] ?? [];
      $primaryImg = !empty($images) ? resolveViewImg($images[0]['image_url']) : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80';
    ?>
    <div class="w-full h-80 sm:h-96 rounded-2xl bg-slate-100 overflow-hidden relative shadow-inner">
      <img id="main-view-image" src="<?= $primaryImg ?>" class="w-full h-full object-cover">
      <div class="absolute bottom-4 left-4 bg-slate-900/80 backdrop-blur-xs text-white text-xs px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">photo_camera</span>
        <span><?= count($images) ?> Photos</span>
      </div>
    </div>

    <?php if (count($images) > 1): ?>
      <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
        <?php foreach ($images as $img): ?>
          <button type="button" onclick="document.getElementById('main-view-image').src = '<?= resolveViewImg($img['image_url']) ?>'" class="h-20 rounded-xl overflow-hidden border border-slate-200 hover:ring-2 hover:ring-brand-500 transition-all cursor-pointer">
            <img src="<?= resolveViewImg($img['image_url']) ?>" class="w-full h-full object-cover">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Key Matrices Grid -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
      <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Complete Weekday Price</div>
      <div class="font-headline font-black text-xl text-slate-900 mt-1">
        <?= ($rentalType === 'room_wise') ? '<span class="text-xs text-slate-400 font-semibold">N/A (Room-wise)</span>' : '₹' . number_format((float)($property['price'] ?? 0)) ?>
      </div>
      <div class="text-[11px] text-slate-400">per night</div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
      <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Complete Weekend Price</div>
      <div class="font-headline font-black text-xl text-slate-900 mt-1">
        <?= ($rentalType === 'room_wise') ? '<span class="text-xs text-slate-400 font-semibold">N/A (Room-wise)</span>' : '₹' . number_format((float)($property['weekend_price'] ?? $property['price'] ?? 0)) ?>
      </div>
      <div class="text-[11px] text-slate-400">Fri / Sat / Sun</div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
      <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bedrooms &amp; Capacity</div>
      <div class="font-headline font-black text-xl text-slate-900 mt-1"><?= (int)$property['bedrooms'] ?> Beds</div>
      <div class="text-[11px] text-slate-400"><?= (int)$property['day_capacity'] ?> Day / <?= (int)$property['night_capacity'] ?> Night</div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
      <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Security Deposit</div>
      <div class="font-headline font-black text-xl text-slate-900 mt-1">₹<?= number_format((float)($property['security_deposit'] ?? 0)) ?></div>
      <div class="text-[11px] text-slate-400">Refundable</div>
    </div>
  </div>

  <!-- Formatted Detailed Description (WYSIWYG Rich Text) -->
  <?php require_once __DIR__ . '/../partials/prose_styles.php'; ?>
  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
    <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
      <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-base">notes</span>
      </span>
      <h3 class="font-headline font-bold text-base text-slate-900">Property Walkthrough &amp; Description</h3>
    </div>
    
    <div class="stayora-prose prose max-w-none text-slate-700 leading-relaxed bg-slate-50/80 p-5 rounded-2xl border border-slate-100">
      <?php 
        $cleanedDesc = HtmlSanitizerHelper::clean($property['description'] ?? '');
        echo !empty($cleanedDesc) ? $cleanedDesc : '<span class="text-slate-400 italic">No detailed description entered for this listing.</span>';
      ?>
    </div>

    <!-- Location & Host Meta -->
    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
      <div>
        <div class="font-bold text-slate-700">Physical Address:</div>
        <div class="text-slate-500 mt-0.5"><?= htmlspecialchars($property['address'] ?? 'Not specified') ?></div>
        <?php if (!empty($property['google_map_link'])): ?>
          <a href="<?= htmlspecialchars($property['google_map_link']) ?>" target="_blank" class="inline-flex items-center gap-1 text-brand-700 font-bold mt-2 hover:underline">
            <span class="material-symbols-outlined text-sm">map</span>
            <span>Open in Google Maps</span>
          </a>
        <?php endif; ?>
      </div>

      <div>
        <div class="font-bold text-slate-700">Host Contacts:</div>
        <div class="text-slate-500 mt-0.5">Phone: <?= htmlspecialchars($property['contact_phone'] ?? 'N/A') ?></div>
        <div class="text-slate-500">WhatsApp: <?= htmlspecialchars($property['whatsapp_number'] ?? 'N/A') ?></div>
      </div>
    </div>
  </div>

  <!-- Room Types Inventory (If room_wise or both) -->
  <?php if ($rentalType !== 'complete'): ?>
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-base">hotel</span>
          </span>
          <h3 class="font-headline font-bold text-base text-slate-900">Configured Room Inventory</h3>
        </div>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
          <?= count($roomTypes) ?> Room Type<?= count($roomTypes) > 1 ? 's' : '' ?>
        </span>
      </div>

      <?php if (empty($roomTypes)): ?>
        <p class="text-xs text-slate-400 italic">No room types configured yet for this property.</p>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($roomTypes as $rt): ?>
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-2">
              <div class="flex items-start justify-between">
                <div>
                  <h4 class="font-headline font-bold text-sm text-slate-900"><?= htmlspecialchars($rt['room_type_name']) ?></h4>
                  <span class="text-[11px] text-slate-500"><?= (int)$rt['total_rooms'] ?> Units • <?= (int)$rt['capacity_per_room'] ?> Guests/room</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $rt['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-600' ?>">
                  <?= ucfirst($rt['status']) ?>
                </span>
              </div>
              <div class="flex items-baseline gap-2 pt-1 border-t border-slate-200/60">
                <span class="text-xs font-bold text-slate-700">Weekday: ₹<?= number_format((float)$rt['price_per_room']) ?></span>
                <span class="text-[11px] text-slate-400">· Weekend: ₹<?= number_format((float)($rt['weekend_price'] ?: $rt['price_per_room'] * 1.25)) ?></span>
              </div>
              <?php if (!empty($rt['description'])): ?>
                <p class="text-[11px] text-slate-500 italic truncate"><?= htmlspecialchars($rt['description']) ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Equipped Amenities Section -->
  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">checklist</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900">Equipped Amenities</h3>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
        <?= count($amenities) ?> Certified Facilities
      </span>
    </div>

    <?php if (empty($amenities)): ?>
      <p class="text-xs text-slate-400 italic">No amenities specified for this listing.</p>
    <?php else: ?>
      <div class="flex flex-wrap gap-2.5">
        <?php foreach ($amenities as $am): ?>
          <div class="inline-flex items-center gap-2 py-2 px-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-700">
            <span class="material-symbols-outlined text-base text-brand-700"><?= htmlspecialchars($am['icon_class'] ?: 'check_circle') ?></span>
            <span><?= htmlspecialchars($am['name']) ?></span>
            <span class="text-[10px] text-slate-400 font-semibold">(<?= htmlspecialchars(ucfirst($am['category'] ?? 'general')) ?>)</span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Property & Stay Rules Section (Allowed / Prohibited) -->
  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
          <span class="material-symbols-outlined text-base">gavel</span>
        </span>
        <h3 class="font-headline font-bold text-base text-slate-900">Property &amp; Stay Rules</h3>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
        <?= count($rules) ?> Policies
      </span>
    </div>

    <?php if (empty($rules)): ?>
      <p class="text-xs text-slate-400 italic">No specific stay policies declared.</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach ($rules as $r): 
          $isAllowed = !empty($r['is_allowed']);
        ?>
          <div class="p-3.5 rounded-2xl border border-slate-200 bg-white flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
              <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-base"><?= htmlspecialchars($r['icon_class'] ?? 'info') ?></span>
              </span>
              <div>
                <span class="font-headline font-bold text-xs text-slate-800"><?= htmlspecialchars($r['rule_name']) ?></span>
                <?php if (!empty($r['description'])): ?>
                  <p class="text-[10px] text-slate-400"><?= htmlspecialchars($r['description']) ?></p>
                <?php endif; ?>
              </div>
            </div>

            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-bold <?= $isAllowed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
              <span class="material-symbols-outlined text-xs"><?= $isAllowed ? 'check' : 'close' ?></span>
              <span><?= $isAllowed ? 'Allowed' : 'Not Allowed' ?></span>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- ── Shared Admin Layout Footer ── -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
