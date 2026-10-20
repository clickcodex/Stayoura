<!-- ══════════════════════════════════════════════════════════════
     REUSABLE VISUAL ICON PICKER MODAL (Material Symbols Outlined)
     ══════════════════════════════════════════════════════════════ -->
<div id="stayora-icon-picker-modal" class="fixed inset-0 z-[70] hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 transition-opacity">
  <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
    
    <!-- Modal Header -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center border border-brand-200 shadow-2xs">
          <span class="material-symbols-outlined text-2xl" id="picker-header-icon-preview">category</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-base text-slate-900 leading-tight">
            Visual Icon Library
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">
            Select a verified Material Symbol for this amenity or stay policy
          </p>
        </div>
      </div>
      <button 
        type="button" 
        onclick="StayoraIconPicker.close()" 
        class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors flex items-center justify-center cursor-pointer"
        aria-label="Close icon library">
        <span class="material-symbols-outlined text-xl">close</span>
      </button>
    </div>

    <!-- Search & Category Filter Bar -->
    <div class="p-4 border-b border-slate-100 space-y-3 bg-white shrink-0">
      
      <!-- Search Input -->
      <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
        <input 
          type="text" 
          id="picker-search-input" 
          placeholder="Search icons (e.g. pool, wifi, pet, chef, music, smoking, ac)..." 
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-xs sm:text-sm font-medium focus:bg-white focus:border-brand-500 focus:outline-none transition-all"
          oninput="StayoraIconPicker.filterIcons()"
        />
        <button 
          type="button" 
          id="picker-search-clear" 
          onclick="StayoraIconPicker.clearSearch()" 
          class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer">
          <span class="material-symbols-outlined text-base">cancel</span>
        </button>
      </div>

      <!-- Category Filter Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar" id="picker-category-filters">
        <button type="button" data-cat="all" onclick="StayoraIconPicker.setCategory('all')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-brand-700 text-white shadow-2xs cursor-pointer">
          All Icons
        </button>
        <button type="button" data-cat="leisure" onclick="StayoraIconPicker.setCategory('leisure')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Leisure &amp; Pool
        </button>
        <button type="button" data-cat="bedroom" onclick="StayoraIconPicker.setCategory('bedroom')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Bed &amp; Bath
        </button>
        <button type="button" data-cat="dining" onclick="StayoraIconPicker.setCategory('dining')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Kitchen &amp; Dining
        </button>
        <button type="button" data-cat="comfort" onclick="StayoraIconPicker.setCategory('comfort')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Comfort &amp; Views
        </button>
        <button type="button" data-cat="tech" onclick="StayoraIconPicker.setCategory('tech')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Tech &amp; Entertainment
        </button>
        <button type="button" data-cat="service" onclick="StayoraIconPicker.setCategory('service')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Services &amp; Security
        </button>
        <button type="button" data-cat="rules" onclick="StayoraIconPicker.setCategory('rules')" class="picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer">
          Rules &amp; Policies
        </button>
      </div>

    </div>

    <!-- Active Selection Preview Chip -->
    <div class="px-4 py-2.5 bg-brand-50/70 border-b border-brand-100 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Current Selection:</span>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white border border-brand-200 shadow-2xs">
          <span class="material-symbols-outlined text-base text-brand-700" id="picker-active-icon">check_circle</span>
          <span class="text-xs font-bold text-slate-900" id="picker-active-name">check_circle</span>
          <span class="text-[10px] text-slate-400 font-semibold" id="picker-active-label">(Check Circle)</span>
        </div>
      </div>
      <div class="text-[11px] text-slate-400 font-medium" id="picker-count-label">
        Showing all icons
      </div>
    </div>

    <!-- Icons Gallery Grid -->
    <div class="p-4 overflow-y-auto flex-1 max-h-[380px] bg-slate-50/40">
      <div id="picker-icons-grid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5">
        <!-- Injected dynamically via StayoraIconPicker JS -->
      </div>
      <div id="picker-no-results" class="hidden py-12 text-center text-slate-400 space-y-2">
        <span class="material-symbols-outlined text-3xl text-slate-300">search_off</span>
        <div class="font-bold text-sm text-slate-600">No matching icons found</div>
        <p class="text-xs text-slate-400">Try searching with a broader term or switch categories.</p>
      </div>
    </div>

    <!-- Modal Footer Controls -->
    <div class="p-4 border-t border-slate-100 flex items-center justify-between shrink-0 bg-white">
      <button 
        type="button" 
        onclick="StayoraIconPicker.close()" 
        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 transition-colors cursor-pointer">
        Cancel
      </button>

      <button 
        type="button" 
        onclick="StayoraIconPicker.confirmSelection()" 
        class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-700/20 transition-all flex items-center gap-1.5 cursor-pointer">
        <span class="material-symbols-outlined text-base">check</span>
        <span>Use This Icon</span>
      </button>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ICON PICKER JAVASCRIPT CONTROLLER
     ══════════════════════════════════════════════════════════════ -->
<script>
  window.StayoraIconPicker = (function() {
    // Curated catalog of ~130 hospitality, amenity & policy icons
    const ICONS_CATALOG = [
      // ── Leisure, Pool & Outdoors ──
      { name: 'pool', label: 'Swimming Pool', category: 'leisure', keywords: 'water swim infinity pool outdoor plunge' },
      { name: 'hot_tub', label: 'Jacuzzi / Hot Tub', category: 'leisure', keywords: 'hydrotherapy spa bath bubble relaxation' },
      { name: 'outdoor_grill', label: 'BBQ Lawn & Grill', category: 'leisure', keywords: 'barbecue grill tandoor cooking outdoor party' },
      { name: 'sports_esports', label: 'Gaming Lounge', category: 'leisure', keywords: 'playstation xbox games billiards snooker foosball' },
      { name: 'sports_tennis', label: 'Badminton / Tennis', category: 'leisure', keywords: 'court racket sports outdoor activity' },
      { name: 'fitness_center', label: 'Gym & Fitness', category: 'leisure', keywords: 'workout weights cardio exercise training' },
      { name: 'spa', label: 'Spa & Wellness', category: 'leisure', keywords: 'massage sauna steam aromatherapy health' },
      { name: 'park', label: 'Garden & Orchard', category: 'leisure', keywords: 'lawn greenery trees meadow landscape nature' },
      { name: 'deck', label: 'Sun Deck / Patio', category: 'leisure', keywords: 'terrace balcony lounge outdoor seating' },
      { name: 'nature_people', label: 'Nature Trails', category: 'leisure', keywords: 'hiking walking jogging countryside outdoor' },
      { name: 'beach_access', label: 'Waterfront / Lake', category: 'leisure', keywords: 'river beach lake riverside coast' },
      { name: 'sailing', label: 'Boating / Lake', category: 'leisure', keywords: 'kayak boat water lake cruise' },
      { name: 'table_restaurant', label: 'Gazebo & Seating', category: 'leisure', keywords: 'pergola outdoor dining sitout umbrella' },
      { name: 'local_fire_department', label: 'Bonfire & Fire Pit', category: 'leisure', keywords: 'fire camp bonfire warmth evening' },
      { name: 'celebration', label: 'Party / Event Lawn', category: 'leisure', keywords: 'party celebration wedding lawn events birthday' },
      { name: 'theater_comedy', label: 'Amphitheater', category: 'leisure', keywords: 'stage performance drama cinema open air' },
      { name: 'golf_course', label: 'Mini Golf', category: 'leisure', keywords: 'putting green golf sports outdoor' },
      { name: 'pedal_bike', label: 'Bicycles', category: 'leisure', keywords: 'cycling ride cycle outdoor trail' },

      // ── Bedroom & Bathroom ──
      { name: 'bed', label: 'Double Bed', category: 'bedroom', keywords: 'bedroom sleeping bed double queen' },
      { name: 'king_bed', label: 'King Size Bed', category: 'bedroom', keywords: 'luxury master king bed sleep mattress' },
      { name: 'single_bed', label: 'Single / Twin Beds', category: 'bedroom', keywords: 'bunk twin single bed kid' },
      { name: 'bedroom_parent', label: 'Master Suite', category: 'bedroom', keywords: 'luxury bedroom suite master parent' },
      { name: 'bathtub', label: 'Bathtub', category: 'bedroom', keywords: 'bath tub soak luxury bathroom wash' },
      { name: 'shower', label: 'Rain Shower', category: 'bedroom', keywords: 'shower head hot water bathroom bath' },
      { name: 'wc', label: 'Attached Washroom', category: 'bedroom', keywords: 'toilet restroom washroom latrine wc' },
      { name: 'water_heater', label: 'Geyser / Hot Water', category: 'bedroom', keywords: 'geyser heating water boiler bath' },
      { name: 'inventory_2', label: 'Wardrobe & Storage', category: 'bedroom', keywords: 'closet wardrobe cupboard hangers luggage' },
      { name: 'mode_fan', label: 'Ceiling Fan', category: 'bedroom', keywords: 'fan ventilation air cool bedroom' },
      { name: 'iron', label: 'Iron & Board', category: 'bedroom', keywords: 'clothes press ironing laundry dry' },
      { name: 'dry', label: 'Hair Dryer', category: 'bedroom', keywords: 'blower styling bathroom bath' },
      { name: 'door_sliding', label: 'Private Balcony', category: 'bedroom', keywords: 'balcony door terrace sliding sitout' },
      { name: 'hotel', label: 'Luxury Bed Linens', category: 'bedroom', keywords: 'linens sheets pillows duvet towels' },

      // ── Kitchen & Dining ──
      { name: 'restaurant', label: 'Private Chef / Dining', category: 'dining', keywords: 'food cook meals chef gourmet kitchen dinner' },
      { name: 'kitchen', label: 'Modular Kitchen', category: 'dining', keywords: 'cooking stove gas burner refrigerator sink' },
      { name: 'local_bar', label: 'Bar Counter', category: 'dining', keywords: 'drinks alcohol wine glasses cocktail pub lounge' },
      { name: 'coffee', label: 'Coffee & Tea Maker', category: 'dining', keywords: 'espresso cafe tea kettle brewing morning' },
      { name: 'countertops', label: 'Cooking Counter', category: 'dining', keywords: 'platform kitchen granite prep table' },
      { name: 'microwave', label: 'Microwave Oven', category: 'dining', keywords: 'reheating bake oven microwave appliance' },
      { name: 'blender', label: 'Mixer / Juicer', category: 'dining', keywords: 'smoothie mixer grinder blender cooking' },
      { name: 'dinner_dining', label: 'Grand Dining Table', category: 'dining', keywords: 'dining room feast table banquet meals' },
      { name: 'lunch_dining', label: 'Outside Food Allowed', category: 'dining', keywords: 'food tiffin lunch delivery catering' },
      { name: 'fastfood', label: 'Snacks & Catering', category: 'dining', keywords: 'quick bites finger food street snacks' },
      { name: 'flatware', label: 'Crockery & Cutlery', category: 'dining', keywords: 'plates forks spoons glassware utensils' },
      { name: 'wine_bar', label: 'Wine & Barware', category: 'dining', keywords: 'cocktail wine liquor glasses bar' },

      // ── Comfort & Views ──
      { name: 'ac_unit', label: 'Central Air Conditioner', category: 'comfort', keywords: 'ac cooling climate chilled air winter summer' },
      { name: 'thermostat', label: 'Climate Control', category: 'comfort', keywords: 'temperature heating cooling comfort' },
      { name: 'fireplace', label: 'Indoor Fireplace', category: 'comfort', keywords: 'hearth wood fire cozy winter heat' },
      { name: 'air', label: 'Fresh Air / Purifier', category: 'comfort', keywords: 'ventilation breeze fresh atmosphere' },
      { name: 'water_drop', label: 'RO Drinking Water', category: 'comfort', keywords: 'purified filtered mineral drinking water' },
      { name: 'balcony', label: 'Balcony Sitout', category: 'comfort', keywords: 'view scenic balcony chair table' },
      { name: 'yard', label: 'Private Courtyard', category: 'comfort', keywords: 'garden yard lawn private open sky' },
      { name: 'roofing', label: 'Gazebo & Canopy', category: 'comfort', keywords: 'roof shelter gazebo shade pavilion' },
      { name: 'landscape', label: 'Mountain View', category: 'comfort', keywords: 'hills valleys panorama scenic view landscape' },
      { name: 'water', label: 'Waterbody View', category: 'comfort', keywords: 'lake river dam pool view water' },

      // ── Tech & Entertainment ──
      { name: 'wifi', label: 'Starlink / High-Speed WiFi', category: 'tech', keywords: 'internet broadband connection wifi speed work' },
      { name: 'tv', label: 'Smart 4K OTT TV', category: 'tech', keywords: 'television netflix prime youtube led screen' },
      { name: 'speaker', label: 'Bluetooth Audio System', category: 'tech', keywords: 'music sound party speaker boombox jbl' },
      { name: 'music_note', label: 'DJ Setup / Music System', category: 'tech', keywords: 'dj audio songs party music console' },
      { name: 'power', label: '100% Power Generator Backup', category: 'tech', keywords: 'generator inverter electricity power uninterrupted' },
      { name: 'ev_station', label: 'EV Vehicle Charger', category: 'tech', keywords: 'electric car vehicle charging station ev' },
      { name: 'smart_display', label: 'Smart Home Automation', category: 'tech', keywords: 'alexa google home tablet automation control' },
      { name: 'desktop_windows', label: 'Dedicated Workstation', category: 'tech', keywords: 'desk office work remote chair screen' },

      // ── Services & Security ──
      { name: 'local_parking', label: 'Private Valet & Parking', category: 'service', keywords: 'car vehicle parking covered garage valet' },
      { name: 'shield', label: '24/7 Security Guard', category: 'service', keywords: 'guard security watchman patrol safety' },
      { name: 'security', label: 'Gated Private Compound', category: 'service', keywords: 'boundary wall gate private secure' },
      { name: 'videocam', label: 'CCTV Surveillance', category: 'service', keywords: 'camera perimeter safety recording video' },
      { name: 'support_agent', label: 'Estate Butler & Caretaker', category: 'service', keywords: 'concierge staff helper service hospitality' },
      { name: 'cleaning_services', label: 'Daily Housekeeping', category: 'service', keywords: 'maid clean sanitization broom turnover' },
      { name: 'badge', label: 'Stayora Certified Listing', category: 'service', keywords: 'verified check audit trusted premium' },
      { name: 'medical_services', label: 'First Aid & Medical Kit', category: 'service', keywords: 'doctor emergency medicine health bandage' },
      { name: 'fire_extinguisher', label: 'Fire Safety Extinguisher', category: 'service', keywords: 'extinguisher safety smoke fire emergency' },
      { name: 'luggage', label: 'Luggage Assistance', category: 'service', keywords: 'bags baggage bellboy porter' },
      { name: 'accessible', label: 'Wheelchair Accessible', category: 'service', keywords: 'ramp elevator disabled handicap access' },

      // ── Rules & Policies ──
      { name: 'pets', label: 'Pets Allowed / Pet Policy', category: 'rules', keywords: 'dogs cats animals furry pet policy' },
      { name: 'smoking_rooms', label: 'Smoking Area Designated', category: 'rules', keywords: 'cigarettes smoke smoking permitted zone' },
      { name: 'smoke_free', label: 'Strict No Smoking Indoors', category: 'rules', keywords: 'smoke prohibited non smoking clean' },
      { name: 'wine_bar', label: 'Alcohol Permitted', category: 'rules', keywords: 'drinks beer wine liquor alcohol allowed' },
      { name: 'no_drinks', label: 'Alcohol Strictly Prohibited', category: 'rules', keywords: 'dry property no drinks alcohol ban' },
      { name: 'volume_up', label: 'Music Allowed (Full Volume)', category: 'rules', keywords: 'loud music party sound songs permitted' },
      { name: 'volume_off', label: 'Music Curfew (10 PM Quiet)', category: 'rules', keywords: 'quiet night noise curfew silence sound' },
      { name: 'groups', label: 'Bachelor Groups Allowed', category: 'rules', keywords: 'friends stags bachelors gathering groups' },
      { name: 'family_restroom', label: 'Families Only Policy', category: 'rules', keywords: 'family kids couples only policy' },
      { name: 'schedule', label: 'Check-in & Check-out Hours', category: 'rules', keywords: 'time hours checkin checkout 12pm 11am' },
      { name: 'id_card', label: 'Valid Govt Photo ID Required', category: 'rules', keywords: 'aadhar passport identity document verification' },
      { name: 'verified', label: 'Standard Verified Rule', category: 'rules', keywords: 'policy approved verified check condition' },
      { name: 'block', label: 'Strictly Prohibited Policy', category: 'rules', keywords: 'ban prohibited forbidden restriction' },
      { name: 'info', label: 'Information Notice', category: 'rules', keywords: 'notice detail advisory guideline info' },
      { name: 'warning', label: 'Mandatory Deposit / Caution', category: 'rules', keywords: 'alert caution security deposit condition fine' },
      { name: 'check_circle', label: 'General Approved Feature', category: 'rules', keywords: 'ok yes check available allowed approved' }
    ];

    let currentSelectedIcon = 'check_circle';
    let currentCategory = 'all';
    let currentSearchTerm = '';
    let onSelectCallback = null;
    let targetInputElement = null;
    let targetPreviewElement = null;

    function renderIcons() {
      const grid = document.getElementById('picker-icons-grid');
      const noResults = document.getElementById('picker-no-results');
      const countLabel = document.getElementById('picker-count-label');
      grid.innerHTML = '';

      const term = currentSearchTerm.toLowerCase().trim();
      const filtered = ICONS_CATALOG.filter(item => {
        const matchesCategory = (currentCategory === 'all' || item.category === currentCategory);
        if (!matchesCategory) return false;
        if (!term) return true;
        return item.name.toLowerCase().includes(term) ||
               item.label.toLowerCase().includes(term) ||
               item.keywords.toLowerCase().includes(term);
      });

      if (filtered.length === 0) {
        noResults.classList.remove('hidden');
        countLabel.textContent = '0 icons found';
      } else {
        noResults.classList.add('hidden');
        countLabel.textContent = `Showing ${filtered.length} icon${filtered.length > 1 ? 's' : ''}`;

        filtered.forEach(item => {
          const isSelected = (item.name === currentSelectedIcon);
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = `picker-icon-card p-2.5 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-1.5 cursor-pointer relative ${
            isSelected 
              ? 'bg-brand-50 border-brand-500 ring-2 ring-brand-500 shadow-xs' 
              : 'bg-white border-slate-200/90 hover:border-slate-300 hover:bg-slate-50'
          }`;
          btn.setAttribute('data-icon', item.name);
          btn.onclick = () => selectIcon(item.name, item.label);

          btn.innerHTML = `
            <span class="material-symbols-outlined text-2xl ${isSelected ? 'text-brand-700' : 'text-slate-700'}">
              ${item.name}
            </span>
            <span class="text-[10px] font-semibold text-slate-600 line-clamp-1 leading-tight w-full px-0.5">
              ${item.label}
            </span>
            ${isSelected ? '<span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-brand-600"></span>' : ''}
          `;

          grid.appendChild(btn);
        });
      }
    }

    function selectIcon(iconName, iconLabel) {
      currentSelectedIcon = iconName;
      document.getElementById('picker-active-icon').textContent = iconName;
      document.getElementById('picker-active-name').textContent = iconName;
      document.getElementById('picker-header-icon-preview').textContent = iconName;
      
      const found = ICONS_CATALOG.find(i => i.name === iconName);
      document.getElementById('picker-active-label').textContent = `(${found ? found.label : iconLabel || iconName})`;

      // Update grid selection highlights
      document.querySelectorAll('.picker-icon-card').forEach(card => {
        const name = card.getAttribute('data-icon');
        const isMatch = (name === iconName);
        if (isMatch) {
          card.className = 'picker-icon-card p-2.5 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-1.5 cursor-pointer relative bg-brand-50 border-brand-500 ring-2 ring-brand-500 shadow-xs';
        } else {
          card.className = 'picker-icon-card p-2.5 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-1.5 cursor-pointer relative bg-white border-slate-200/90 hover:border-slate-300 hover:bg-slate-50';
        }
      });
    }

    function setCategory(cat) {
      currentCategory = cat;
      document.querySelectorAll('.picker-cat-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === cat) {
          btn.className = 'picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-brand-700 text-white shadow-2xs cursor-pointer';
        } else {
          btn.className = 'picker-cat-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer';
        }
      });
      renderIcons();
    }

    function filterIcons() {
      const val = document.getElementById('picker-search-input').value;
      currentSearchTerm = val;
      const clearBtn = document.getElementById('picker-search-clear');
      if (val.trim()) {
        clearBtn.classList.remove('hidden');
      } else {
        clearBtn.classList.add('hidden');
      }
      renderIcons();
    }

    function clearSearch() {
      document.getElementById('picker-search-input').value = '';
      currentSearchTerm = '';
      document.getElementById('picker-search-clear').classList.add('hidden');
      renderIcons();
    }

    function open(options = {}) {
      currentSelectedIcon = options.currentIcon || 'check_circle';
      onSelectCallback = options.onSelect || null;
      targetInputElement = options.targetInputId ? document.getElementById(options.targetInputId) : null;
      targetPreviewElement = options.previewElId ? document.getElementById(options.previewElId) : null;

      if (targetInputElement && !options.currentIcon) {
        currentSelectedIcon = targetInputElement.value || 'check_circle';
      }

      currentCategory = 'all';
      currentSearchTerm = '';
      document.getElementById('picker-search-input').value = '';
      document.getElementById('picker-search-clear').classList.add('hidden');
      setCategory('all');

      selectIcon(currentSelectedIcon);
      document.getElementById('stayora-icon-picker-modal').classList.remove('hidden');
      
      // Auto-focus search input
      setTimeout(() => {
        document.getElementById('picker-search-input').focus();
      }, 100);
    }

    function close() {
      document.getElementById('stayora-icon-picker-modal').classList.add('hidden');
    }

    function confirmSelection() {
      if (targetInputElement) {
        targetInputElement.value = currentSelectedIcon;
        // Trigger change event
        targetInputElement.dispatchEvent(new Event('input', { bubbles: true }));
        targetInputElement.dispatchEvent(new Event('change', { bubbles: true }));
      }
      if (targetPreviewElement) {
        targetPreviewElement.textContent = currentSelectedIcon;
      }
      if (typeof onSelectCallback === 'function') {
        const item = ICONS_CATALOG.find(i => i.name === currentSelectedIcon) || { name: currentSelectedIcon, label: currentSelectedIcon };
        onSelectCallback(currentSelectedIcon, item);
      }
      close();
    }

    return {
      open,
      close,
      setCategory,
      filterIcons,
      clearSearch,
      confirmSelection,
      selectIcon
    };
  })();
</script>
