<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="robots" content="noindex, nofollow, noarchive"/>
    <title>Dashboard - Stayora</title>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    
    <link rel="icon" type="image/webp" href="<?= asset('assets/images/logo.webp') ?>"/>
    <link rel="shortcut icon" href="<?= asset('assets/images/logo.webp') ?>"/>
    <link rel="apple-touch-icon" href="<?= asset('assets/images/logo.webp') ?>"/>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "accent": "#C9A227",
                    "accent-container": "#FDF8E8",
                    /* Main logo blue */
                    "primary": "#1D4ED8",
                    "primary-container": "#1E40AF",
                    "primary-fixed": "#3B82F6",
                    "on-primary": "#ffffff",

                    /* Secondary palette */
                    "secondary": "#0284C7",
                    "secondary-container": "#E0F2FE",

                    /* Tertiary palette */
                    "tertiary": "#0FA0D8",
                    "tertiary-container": "#C8EEF9",

                    /* Surface + background from image */
                    "background": "#F8FAFC",
                    "surface": "#FFFFFF",
                    "surface-bright": "#FAFAFA",
                    "surface-dim": "#E5E5E5",

                    "surface-container-lowest": "#FFFFFF",
                    "surface-container-low": "#F7F7F7",
                    "surface-container": "#EFEFEF",
                    "surface-container-high": "#E8E8E8",
                    "surface-container-highest": "#DDDDDD",

                    /* Text colors */
                    "on-surface": "#0F172A",
                    "on-background": "#0F172A",
                    "on-surface-variant": "#64748B",

                    /* Borders */
                    "outline-variant": "#E2E8F0",

                    /* Error */
                    "error-container": "#FFDAD6"
                },

                "fontFamily": {
                    "headline": ["Epilogue"],
                    "display": ["Epilogue"],
                    "body": ["Inter"],
                    "label": ["Inter"]
                }
            }
        }
    }
</script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        /* Smooth Drawer Transition */
        #sidebar { transition: transform 0.3s ease-in-out; }
        .sidebar-open { transform: translateX(0) !important; }
    </style>
</head>
<body class=" font-body text-on-surface antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden backdrop-blur-sm md:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-72 flex-col z-50 py-8 gap-2 -translate-x-full md:translate-x-0 md:flex flex shadow-2xl md:shadow-none">
        <div class="px-8 mb-8 flex items-center justify-between">
            <h2 class="font-display text-2xl font-bold tracking-tighter text-primary">Stayora</h2>
            <button onclick="toggleSidebar()" class="md:hidden p-1 text-on-surface-variant">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="px-8 mb-6 flex items-center gap-4">
            <img alt="User profile" class="w-12 h-12 rounded-full object-cover border-2 border-primary/20" src="<?= !empty($_SESSION['profile_image']) ? asset('assets/images/uploads/profiles/' . htmlspecialchars($_SESSION['profile_image'])) : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') ?>"/>
            <div>
                <p class="font-label text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Tenant Member</p>
                <p class="font-body text-sm font-bold text-on-surface"><?php echo htmlspecialchars($_SESSION['user_name'] ?? '') ?></p>
            </div>
        </div>

        <nav class="flex-1 flex flex-col gap-2 font-label text-sm font-medium">
            <a class="bg-primary text-on-primary rounded-xl mx-4 px-4 py-3 flex items-center gap-3 shadow-sm" href="<?= url('dashboard') ?>">
                <span class="material-symbols-outlined">grid_view</span> Dashboard
            </a>
            <a class="text-on-surface-variant hover:bg-white/50 mx-4 px-4 py-3 rounded-xl flex items-center gap-3 transition-colors" href="<?= url('user/my-bookings') ?>">
                <span class="material-symbols-outlined">calendar_today</span> My Bookings
            </a>
            
             <a class="text-on-surface-variant hover:bg-white/50 mx-4 px-4 py-3 rounded-xl flex items-center gap-3 transition-colors" href="<?= url('farmhouses') ?>">
                <span class="material-symbols-outlined">grid_view</span> Explore Estates
            </a>

            <a class="text-on-surface-variant hover:bg-white/50 mx-4 px-4 py-3 rounded-xl flex items-center gap-3 transition-colors" href="<?= url('notifications') ?>">
                <span class="material-symbols-outlined">notifications</span> Notifications
            </a>
            
            <a class="text-on-surface-variant hover:bg-white/50 mx-4 px-4 py-3 rounded-xl flex items-center gap-3 transition-colors" href="<?= url('user/profile') ?>">
                <span class="material-symbols-outlined">person</span> My Profile
            </a>
            <a class="text-on-surface-variant hover:bg-white/50 mx-4 px-4 py-3 rounded-xl flex items-center gap-3 transition-colors mt-auto" href="<?= url('logout') ?>">
                <span class="material-symbols-outlined">logout</span> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 md:ml-72 flex flex-col min-h-screen">
        
        