<?php
/**
 * Stayora - Master Luxury Dummy Data Seeder
 * Populates clean, cohesive, client-ready dummy dataset for Stayora.
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\Database;

try {
    $db = Database::connect();
    echo "Connected to MySQL successfully.\n";

    // Disable foreign key checks for clean truncation
    $db->query("SET FOREIGN_KEY_CHECKS = 0;");

    $tablesToTruncate = [
        'booking_requests',
        'bookings',
        'reviews',
        'images',
        'farmhouse_amenities',
        'farmhouse_rules',
        'blocked_dates',
        'seasonal_prices',
        'farmhouse_room_types',
        'property_addons',
        'farmhouses',
        'contact_inquiries',
        'inquiries',
        'wishlist',
        'notifications',
        'notification_logs',
        'otp_verifications',
        'owner_documents',
        'owner_offline_bookings',
        'owner_payouts',
        'payments',
        'coupons',
        'amenities',
        'property_types',
        'users',
        'owners',
        'admins'
    ];

    foreach ($tablesToTruncate as $tbl) {
        $db->query("TRUNCATE TABLE `$tbl`");
        echo "Truncated $tbl\n";
    }

    $db->query("SET FOREIGN_KEY_CHECKS = 1;");

    $passHash = password_hash('Admin@123', PASSWORD_BCRYPT);

    // 1. Seed Admins
    $stmt = $db->prepare("INSERT INTO admins (name, email, password, profile_image, status) VALUES (?, ?, ?, ?, 'Active')");
    $admins = [
        ['Stayora Chief Executive', 'admin@gmail.com', $passHash, 'default_admin.png'],
        ['Stayora Concierge Admin', 'admin@stayora.com', $passHash, 'default_admin.png']
    ];
    foreach ($admins as $adm) {
        $stmt->bind_param('ssss', $adm[0], $adm[1], $adm[2], $adm[3]);
        $stmt->execute();
    }
    echo "Seeded Admins.\n";

    // 2. Seed Owners (Hosts)
    $stmt = $db->prepare("INSERT INTO owners (name, email, phone, password, status, profile_image, is_phone_verified, is_email_verified) VALUES (?, ?, ?, ?, 'active', ?, 1, 1)");
    $owners = [
        ['Vikramaditya Oberoi', 'owner@stayora.com', '+91 9820123456', $passHash, 'default_profile.png'],
        ['Natasha Singhania', 'owner@farmlelo.com', '+91 9820987654', $passHash, 'default_profile.png'],
        ['Kabir & Tara Mehra', 'kabir.mehra@stayora.com', '+91 9811223344', $passHash, 'default_profile.png'],
        ['Devendra Pratap Singh', 'pratap.villas@stayora.com', '+91 9829012345', $passHash, 'default_profile.png']
    ];
    foreach ($owners as $own) {
        $stmt->bind_param('sssss', $own[0], $own[1], $own[2], $own[3], $own[4]);
        $stmt->execute();
    }
    echo "Seeded Hosts / Owners.\n";

    // 3. Seed Users (Guests)
    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, status, profile_image, is_phone_verified, is_email_verified) VALUES (?, ?, ?, ?, 'active', ?, 1, 1)");
    $users = [
        ['Aarav Sharma', 'user@stayora.com', '+91 9876543210', $passHash, 'default_profile.png'],
        ['Ananya Deshmukh', 'user@farmlelo.com', '+91 9123456789', $passHash, 'default_profile.png'],
        ['Rohan Kapoor', 'rohan@stayora.com', '+91 9988776655', $passHash, 'default_profile.png'],
        ['Meera Sen Gupta', 'meera@stayora.com', '+91 9830112233', $passHash, 'default_profile.png'],
        ['Aditya Singhal', 'aditya@stayora.com', '+91 9711009988', $passHash, 'default_profile.png']
    ];
    foreach ($users as $usr) {
        $stmt->bind_param('sssss', $usr[0], $usr[1], $usr[2], $usr[3], $usr[4]);
        $stmt->execute();
    }
    echo "Seeded Users.\n";

    // 4. Seed Property Types
    $propertyTypes = [
        [1, 'Luxury Pool Villa', 'luxury-pool-villa', 'Private estate with heated or infinity swimming pool, sun loungers, and poolside decks.', 'pool', 1],
        [2, 'Heritage Palace & Haveli', 'heritage-palace', 'Historic royal architecture, courtyards, stone arches, and opulent heritage living.', 'castle', 2],
        [3, 'Beachfront Sanctuary', 'beachfront-sanctuary', 'Steps away from private sands with panoramic sea views and ocean breezes.', 'beach_access', 3],
        [4, 'Hill View Chalet', 'hill-view-chalet', 'Nestled among misty mountain peaks, lush forests, and private firepits.', 'terrain', 4],
        [5, 'Organic Farm Retreat', 'organic-farm-retreat', 'Expansive agrarian estates featuring fruit orchards, green lawns, and country calm.', 'agriculture', 5],
        [6, 'Lakefront Manor', 'lakefront-manor', 'Tranquil waterside residences with private boating decks and sunset terraces.', 'water', 6]
    ];
    $stmt = $db->prepare("INSERT INTO property_types (id, name, slug, description, icon_class, display_order, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    foreach ($propertyTypes as $pt) {
        $stmt->bind_param('issssi', $pt[0], $pt[1], $pt[2], $pt[3], $pt[4], $pt[5]);
        $stmt->execute();
    }
    echo "Seeded Property Types.\n";

    // 5. Seed Amenities
    $amenities = [
        [1, 'Private Infinity Pool', 'leisure', 'pool'],
        [2, 'Starlink High-Speed WiFi', 'technology', 'wifi'],
        [3, 'Private Chef & Gourmet Kitchen', 'dining', 'restaurant'],
        [4, 'Central Air Conditioning', 'comfort', 'ac_unit'],
        [5, 'BBQ Lawn & Bonfire Pit', 'outdoor', 'outdoor_grill'],
        [6, 'Jacuzzi & Hydrotherapy', 'wellness', 'hot_tub'],
        [7, 'Billiards & Game Lounge', 'entertainment', 'sports_esports'],
        [8, 'Pet Friendly Private Gardens', 'general', 'pets'],
        [9, 'Helipad & Valet Parking', 'access', 'local_parking'],
        [10, '24/7 Butler & Security Concierge', 'service', 'shield'],
        [11, 'Smart Home Cinema / 4K OTT', 'entertainment', 'tv'],
        [12, 'Organic Orchard & Nature Trail', 'nature', 'park']
    ];
    $stmt = $db->prepare("INSERT INTO amenities (id, name, category, icon_class) VALUES (?, ?, ?, ?)");
    foreach ($amenities as $am) {
        $stmt->bind_param('isss', $am[0], $am[1], $am[2], $am[3]);
        $stmt->execute();
    }
    echo "Seeded Amenities.\n";

    // 6. Seed Curated Luxury Farmhouses & Villas
    $farmhouses = [
        [
            1, 1, 1,
            'The Grand Azure Pool Villa',
            'Perched on the serene cliffs of Vagator, The Grand Azure offers a 40-foot private infinity pool overlooking the Arabian Sea. Designed with expansive floor-to-ceiling glass, Italian marble, and private landscaped cabanas, this 5-bedroom sanctuary comes with a personal butler and gourmet chef service.',
            'North Goa', 'Vagator Cliff Road, Near Ozran Beach, North Goa - 403509',
            'https://maps.google.com/?q=Vagator+Goa', 'Villa',
            35000.00, 48000.00, 10000.00, 2500.00, 5, 2, 16, 12,
            '+91 9820123456', '+91 9820123456'
        ],
        [
            2, 1, 2,
            'Royal Rajputana Heritage Haveli',
            'A restored 18th-century Rajputana jewel surrounded by the Aravalli hills and overlooking Lake Pichola. Features hand-carved jharokhas, courtyards with marble fountains, royal royal suites with four-poster beds, and sunset rooftop dining.',
            'Udaipur', 'Haridas Ji Ki Magri, Lake Pichola West, Udaipur, Rajasthan - 313001',
            'https://maps.google.com/?q=Udaipur+Rajasthan', 'Heritage Haveli',
            45000.00, 62000.00, 15000.00, 3000.00, 6, 2, 20, 15,
            '+91 9829012345', '+91 9829012345'
        ],
        [
            3, 2, 3,
            'Awas Beachfront Palms & Cabana',
            'Just a 15-minute speedboat ride from Gateway of India, Mumbai, this Alibaug coastal paradise offers direct private beach access, a sunken pool bar, coconut groves, and open-air Balinese dining pavilions.',
            'Alibaug', 'Awas Beach Road, Post Mandwa, Alibaug, Maharashtra - 402201',
            'https://maps.google.com/?q=Alibaug+Maharashtra', 'Beach Villa',
            28000.00, 38000.00, 8000.00, 2000.00, 4, 2, 12, 10,
            '+91 9820987654', '+91 9820987654'
        ],
        [
            4, 2, 4,
            'Misty Pines Alpine Chalet',
            'High above the clouds in Manali with breathtaking vistas of snow-capped Himalayan peaks. Constructed with seasoned deodar cedar wood and local stone, featuring a heated indoor plunge pool, stone fireplace, and stargazing telescope deck.',
            'Manali', 'Old Manali Hills, Log Huts Area, Manali, Himachal Pradesh - 175131',
            'https://maps.google.com/?q=Manali+Himachal', 'Chalet',
            24000.00, 32000.00, 7000.00, 1500.00, 4, 2, 10, 8,
            '+91 9811223344', '+91 9811223344'
        ],
        [
            5, 3, 1,
            'Pawna Lake Panorama Estate',
            'A modernist cliffside villa in Lonavala cantilevered over Pawna Lake. Boasts a temperature-controlled glass infinity pool, sprawling manicured barbecue lawns, open-air amphitheater, and private sunset decks.',
            'Lonavala', 'Tung Fort Road, Pawna Dam Backwaters, Lonavala, Maharashtra - 410401',
            'https://maps.google.com/?q=Pawna+Lake+Lonavala', 'Pool Villa',
            32000.00, 44000.00, 10000.00, 2000.00, 5, 2, 15, 12,
            '+91 9811223344', '+91 9811223344'
        ],
        [
            6, 3, 5,
            'The Whispering Palms Organic Farm',
            'Spanning 12 acres of certified organic mango orchards and lush lawns near Indore. Features private pool suites, authentic mud-brick eco architecture, farm-to-table organic dining, horseback riding tracks, and peaceful gazebos.',
            'Indore', 'Ralamandal Foothills, Khandwa Road, Indore, Madhya Pradesh - 452020',
            'https://maps.google.com/?q=Ralamandal+Indore', 'Farmhouse',
            18000.00, 25000.00, 5000.00, 1500.00, 4, 2, 18, 14,
            '+91 9820123456', '+91 9820123456'
        ],
        [
            7, 4, 6,
            'Emerald Lake Sanctuary & Spa',
            'Situated on the misty banks of Lake Venna in Mahabaleshwar, surrounded by strawberry fields and pine woods. Features outdoor heated jacuzzi, private wooden boat dock, yoga pavilion, and customized ayurvedic wellness spa options.',
            'Mahabaleshwar', 'Venna Lake West Shore, Mahabaleshwar, Maharashtra - 412806',
            'https://maps.google.com/?q=Mahabaleshwar', 'Lake Villa',
            26000.00, 36000.00, 8000.00, 2000.00, 4, 2, 12, 10,
            '+91 9829012345', '+91 9829012345'
        ],
        [
            8, 1, 1,
            'Assagao Heritage Pool Sanctuary',
            'Nestled in the upscale designer village of Assagao, Goa. A restored Indo-Portuguese mansion featuring high vaulted teak ceilings, private turquoise plunge pool, courtyard frangipani garden, and luxury designer furnishings.',
            'North Goa', 'Badem Lane, Assagao, North Goa - 403507',
            'https://maps.google.com/?q=Assagao+Goa', 'Heritage Villa',
            38000.00, 52000.00, 12000.00, 2500.00, 5, 2, 14, 10,
            '+91 9820123456', '+91 9820123456'
        ],
        [
            9, 2, 5,
            'Aravalli Ridge Royal Estate',
            'An opulent private getaway nestled between Gurgaon and Jaipur in the serene Sohna Hills. Sprawls over 8 acres of manicured lawns with a 25-meter lap pool, tennis court, outdoor pizza ovens, and state-of-the-art home theater.',
            'Delhi NCR', 'Sohna Tourist Complex Road, Sohna Hills, Haryana - 122103',
            'https://maps.google.com/?q=Sohna+Gurgaon', 'Luxury Estate',
            42000.00, 58000.00, 15000.00, 3000.00, 6, 2, 22, 16,
            '+91 9820987654', '+91 9820987654'
        ],
        [
            10, 4, 4,
            'Cloud Valley Coffee Plantation Manor',
            'Tucked deep within a 100-acre private Arabica coffee plantation in Coorg. Features panoramic glass walls facing cascading valley waterfalls, outdoor campfire decks, fresh roast coffee bar, and private trekking trails.',
            'Coorg', 'Madikeri Hills Estate, Coorg, Karnataka - 571201',
            'https://maps.google.com/?q=Madikeri+Coorg', 'Plantation Manor',
            22000.00, 30000.00, 6000.00, 1500.00, 4, 2, 12, 8,
            '+91 9811223344', '+91 9811223344'
        ]
    ];

    $stmtFarm = $db->prepare("INSERT INTO farmhouses (
        id, owner_id, property_type_id, title, description, location, address, google_map_link,
        category, price, weekend_price, security_deposit, cleaning_fee,
        bedrooms, bedroom_capacity, day_capacity, night_capacity,
        contact_phone, whatsapp_number, status, admin_approval_status, is_verified,
        verification_notes, booking_mode, created_by, owner_notes
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, 'active', 'approved', 1,
        'Physically Inspected & Certified by Stayora Luxury Team', 'request', 'admin', 'Ready for client showcase bookings'
    )");

    foreach ($farmhouses as $f) {
        $stmtFarm->bind_param(
            'iiisssssddddiiiiiss',
            $f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7],
            $f[8], $f[9], $f[10], $f[11], $f[12],
            $f[13], $f[14], $f[15], $f[16],
            $f[17], $f[18]
        );
        $stmtFarm->execute();
    }
    echo "Seeded 10 Premier Farmhouses & Villas.\n";

    // 7. Seed Property Images (Using stunning luxury Unsplash & existing assets)
    $curatedPhotos = [
        1 => [
            'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80'
        ],
        2 => [
            'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80'
        ],
        3 => [
            'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1544984243-ec57ea16fe25?auto=format&fit=crop&w=1200&q=80'
        ],
        4 => [
            'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80'
        ],
        5 => [
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600565193348-f74bd3c7ccdf?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80'
        ],
        6 => [
            'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1470240731273-7821a6eeb6bd?auto=format&fit=crop&w=1200&q=80'
        ],
        7 => [
            'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1200&q=80'
        ],
        8 => [
            'https://images.unsplash.com/photo-1600585152220-90363fe7e115?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&w=1200&q=80'
        ],
        9 => [
            'https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&w=1200&q=80'
        ],
        10 => [
            'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80'
        ]
    ];

    $stmtImg = $db->prepare("INSERT INTO images (farmhouse_id, image_url, category, is_featured, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($curatedPhotos as $fId => $urls) {
        foreach ($urls as $idx => $url) {
            $isFeat = ($idx === 0) ? 1 : 0;
            $cat = ($idx === 0) ? 'exterior' : (($idx === 1) ? 'pool' : 'interior');
            $stmtImg->bind_param('issii', $fId, $url, $cat, $isFeat, $idx);
            $stmtImg->execute();
        }
    }
    echo "Seeded Property Images.\n";

    // 8. Associate Amenities (3-6 per farmhouse)
    $stmtFa = $db->prepare("INSERT INTO farmhouse_amenities (farmhouse_id, amenity_id) VALUES (?, ?)");
    for ($fId = 1; $fId <= 10; $fId++) {
        $amenitySet = [1, 2, 3, 4, 5, 8, 10]; // standard luxury set
        if ($fId % 2 === 0) $amenitySet[] = 6; // jacuzzi
        if ($fId === 1 || $fId === 5 || $fId === 9) $amenitySet[] = 9; // helipad
        foreach ($amenitySet as $aId) {
            $stmtFa->bind_param('ii', $fId, $aId);
            $stmtFa->execute();
        }
    }
    echo "Seeded Farmhouse Amenities.\n";

    // 9. Seed House Rules
    $rules = [
        ['Pets Allowed', 1],
        ['Smoking Allowed Indoors', 0],
        ['Outdoor Music until 10 PM', 1],
        ['Alcohol Consumption Allowed', 1],
        ['Late Night Parties', 0],
        ['Pool Access 24/7', 1]
    ];
    $stmtRule = $db->prepare("INSERT INTO farmhouse_rules (farmhouse_id, rule_name, is_allowed) VALUES (?, ?, ?)");
    for ($fId = 1; $fId <= 10; $fId++) {
        foreach ($rules as $r) {
            $stmtRule->bind_param('isi', $fId, $r[0], $r[1]);
            $stmtRule->execute();
        }
    }
    echo "Seeded Farmhouse Rules.\n";

    // 10. Seed Authentic Guest Reviews (4.8 - 5.0 rating)
    $reviews = [
        [1, 1, 'Aarav Sharma', 'aarav@stayora.com', 5.0, 'Breathtaking infinity pool and top-notch butler service!', 'Our weekend at The Grand Azure in Vagator was absolute perfection. The sunset views over the ocean while lounging by the private pool are unforgettable. The private chef prepared incredible seafood curries. Worth every penny!'],
        [1, 2, 'Ananya Deshmukh', 'ananya@stayora.com', 4.9, 'Unmatched luxury and ultimate privacy', 'Stayora has elevated Indian luxury villa rentals. Everything was immaculate, sparkling clean, and the staff was extremely discreet yet attentive.'],
        [2, 3, 'Rohan Kapoor', 'rohan@stayora.com', 5.0, 'Felt like royalty in Udaipur', 'Royal Rajputana Haveli is magical. Waking up to peacock calls and the mist over Lake Pichola with breakfast served in the marble courtyard was a bucket-list memory.'],
        [3, 4, 'Meera Sen Gupta', 'meera@stayora.com', 4.8, 'The best beach getaway from Mumbai', 'Took the speedboat from Gateway to Mandwa and was sipping chilled coconut water at the cabana within 25 minutes. Super pet-friendly too!'],
        [5, 5, 'Aditya Singhal', 'aditya@stayora.com', 5.0, 'Stunning architectural masterpiece in Pawna', 'The cantilevered glass pool overlooking the lake is pure Instagram gold. The host Kabir made sure our corporate retreat went flawlessly.']
    ];
    $stmtRev = $db->prepare("INSERT INTO reviews (farmhouse_id, user_id, guest_name, guest_email, overall_rating, review_title, review_text, status, is_verified_stay) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved', 1)");
    foreach ($reviews as $rev) {
        $stmtRev->bind_param('iissdss', $rev[0], $rev[1], $rev[2], $rev[3], $rev[4], $rev[5], $rev[6]);
        $stmtRev->execute();
    }
    echo "Seeded Guest Reviews.\n";

    // 11. Seed Realistic Bookings & Requests
    $bookings = [
        [1, 'Vikram Malhotra', '+91 9820011223', date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+6 days')), 'approved'],
        [2, 'Priya & Siddharth Roy', '+91 9811445566', date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+13 days')), 'approved'],
        [5, 'Gaurav Kothari', '+91 9829887766', date('Y-m-d', strtotime('+15 days')), date('Y-m-d', strtotime('+17 days')), 'approved']
    ];
    $stmtB = $db->prepare("INSERT INTO bookings (farmhouse_id, customer_name, phone, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($bookings as $b) {
        $stmtB->bind_param('isssss', $b[0], $b[1], $b[2], $b[3], $b[4], $b[5]);
        $stmtB->execute();
    }

    $stmtBR = $db->prepare("INSERT INTO booking_requests (
        user_id, farmhouse_id, booking_type, check_in, check_out, start_date, end_date,
        guests, rooms, price, status, payment_method, payment_status, message
    ) VALUES (
        ?, ?, 'complete', ?, ?, ?, ?,
        ?, ?, ?, ?, 'upi', ?, ?
    )");
    $reqs = [
        [1, 1, date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+6 days')), date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+6 days')), 8, 4, 105000.00, 'approved', 'paid', 'Family anniversary celebration, private chef requested.'],
        [2, 3, date('Y-m-d', strtotime('+7 days')), date('Y-m-d', strtotime('+9 days')), date('Y-m-d', strtotime('+7 days')), date('Y-m-d', strtotime('+9 days')), 6, 3, 56000.00, 'pending', 'pending_verification', 'Looking forward to a serene beach retreat in Alibaug.'],
        [3, 5, date('Y-m-d', strtotime('+12 days')), date('Y-m-d', strtotime('+14 days')), date('Y-m-d', strtotime('+12 days')), date('Y-m-d', strtotime('+14 days')), 10, 5, 64000.00, 'pending', 'unpaid', 'Corporate executive quarterly retreat.']
    ];
    foreach ($reqs as $rq) {
        $stmtBR->bind_param(
            'iissssiidsss',
            $rq[0], $rq[1], $rq[2], $rq[3], $rq[4], $rq[5],
            $rq[6], $rq[7], $rq[8], $rq[9], $rq[10], $rq[11]
        );
        $stmtBR->execute();
    }
    echo "Seeded Bookings and Inquiries.\n";

    // 12. Seed Client Inquiries
    $inquiries = [
        ['Sunil Chawla', '+91 9810023456', 'sunil.chawla@techcorp.in', 'Looking to book a luxury 6-bedroom estate with heated pool for our leadership summit in late November. Please share availability.'],
        ['Sanjana Varma', '+91 9821034567', 'sanjana.v@creativeagency.com', 'Inquiring about hosting an intimate sunset pre-wedding cocktail party for 30 guests at The Grand Azure in Goa.']
    ];
    $stmtInq = $db->prepare("INSERT INTO contact_inquiries (full_name, phone, email, Message) VALUES (?, ?, ?, ?)");
    foreach ($inquiries as $inq) {
        $stmtInq->bind_param('ssss', $inq[0], $inq[1], $inq[2], $inq[3]);
        $stmtInq->execute();
    }
    echo "Seeded Contact Inquiries.\n";

    // 13. Ensure Site Settings Table has pristine Stayora details
    $db->query("UPDATE site_settings SET 
        site_name = 'Stayora',
        tagline = 'Luxury Farmhouses & Private Villa Escapes',
        email = 'contact@stayora.com',
        mobile_number = '+91-9111666415',
        whatsapp_number = '+91-9111666415',
        address = 'Stayora Luxury Hospitality, Sector 54 Golf Course Road, Gurugram, India',
        footer_text = '© 2026 Stayora. All rights reserved. Handcrafted luxury stays across India.',
        favicon = 'favicon_stayora.png'
        WHERE id = 1
    ");
    echo "Updated Site Settings.\n";

    echo "\n=== ALL DUMMY DATA SEEDING COMPLETED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
