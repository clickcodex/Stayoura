<?php
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($base === '/' || $base === '.') {
    $base = '';
}
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
use App\Middleware\AuthMiddleware;
// Remove query string
$uri = parse_url($requestUri, PHP_URL_PATH) ?? '/';

// Remove base path (case-insensitive for local environments)
if ($base !== '' && stripos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}

$uri = trim($uri, '/');
if (stripos($uri, 'index.php') === 0) {
    $uri = trim(substr($uri, strlen('index.php')), '/');
}

// ── Global Platform Maintenance Mode Interceptor ──
if (\App\Helpers\SiteHelper::isMaintenanceActive()) {
    http_response_code(503);
    require_once __DIR__ . '/Views/errors/maintenance.php';
    exit;
}

switch ($uri) {
    // ── SEO Crawling Directives & Sitemaps ──
    case 'sitemap.xml':
    case 'sitemap':
        require_once __DIR__ . '/Controllers/SeoController.php';
        (new App\Controllers\SeoController())->sitemap();
        break;

    case 'robots.txt':
    case 'robots':
        require_once __DIR__ . '/Controllers/SeoController.php';
        (new App\Controllers\SeoController())->robots();
        break;

    case '':
        require_once __DIR__ . '/Controllers/HomeController.php';
        (new App\Controllers\HomeController())->index();
        break;

    case 'home':
        require_once __DIR__ . '/Controllers/HomeController.php';
        (new App\Controllers\HomeController())->index();
        break;
    case 'why-choose-us':
        require_once __DIR__ . '/Controllers/HomeController.php';
        (new App\Controllers\HomeController())->whychooseus();
        break;

    case 'about':
        require_once __DIR__ . '/Controllers/StaticController.php';
        (new App\Controllers\StaticController())->about();
        break;

    case 'contact':
        require_once __DIR__ . '/Controllers/StaticController.php';
        (new App\Controllers\StaticController())->contact();
        break;

    case 'contact-submit':
        require_once __DIR__ . '/Controllers/StaticController.php';
        (new App\Controllers\StaticController())->submitContact();
        break;
    case 'farmhouses':
        require_once __DIR__ . '/Controllers/FarmhousesController.php';
        (new App\Controllers\FarmhousesController())->farmhouses();
        break;

    case 'farmhouse_details':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->farmhouse_details();
        break;

    case 'farmhouse/request_booking':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->request_booking();
        break;

    case 'log_inquiry':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->logInquiry();
        break;

    case 'api/calculate-price':
    case 'booking/calculate-price':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->calculatePrice();
        break;

    case 'api/validate-coupon':
    case 'booking/validate-coupon':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->validateCoupon();
        break;

    case 'booking/voucher':
    case 'booking/invoice':
    case 'booking/download-voucher':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->bookingVoucher();
        break;

    case 'api/submit-review':
    case 'farmhouse/submit-review':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->submitReview();
        break;

    case 'api/room-availability':
    case 'booking/room-availability':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->getRoomAvailability();
        break;

    case 'api/room-calendar-matrix':
    case 'booking/room-calendar-matrix':
        require_once __DIR__ . '/Controllers/FarmdetailsController.php';
        (new App\Controllers\FarmdetailsController())->getRoomCalendarMatrix();
        break;

    case 'wishlist':
    case 'my-wishlist':
    case 'user/wishlist':
        require_once __DIR__ . '/Controllers/WishlistController.php';
        (new App\Controllers\WishlistController)->wishlist();
        break;

    case 'toggle_wishlist':
        require_once __DIR__ . '/Controllers/WishlistController.php';
        (new App\Controllers\WishlistController())->toggle();
        break;

    // Notification Center Routes (F56, F57, F58)
    case 'notifications':
        require_once __DIR__ . '/Controllers/NotificationController.php';
        (new App\Controllers\NotificationController())->index();
        break;

    case 'api/notifications':
        require_once __DIR__ . '/Controllers/NotificationController.php';
        (new App\Controllers\NotificationController())->apiGet();
        break;

    case 'api/notifications/mark-read':
        require_once __DIR__ . '/Controllers/NotificationController.php';
        (new App\Controllers\NotificationController())->apiMarkRead();
        break;

    case 'api/notifications/mark-all-read':
        require_once __DIR__ . '/Controllers/NotificationController.php';
        (new App\Controllers\NotificationController())->apiMarkAllRead();
        break;

    case 'login':
        require_once __DIR__ . '/Controllers/AuthController.php';
        $authCtrl = new App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl->login();
        } else {
            $authCtrl->index();
        }
        break;
    case 'login-process':
    case 'login-submit':
        require_once __DIR__ . '/Controllers/AuthController.php';
        (new App\Controllers\AuthController)->login();
        break;
    case 'logout':
    case 'user/logout':
        require_once __DIR__ . '/Controllers/AuthController.php';
        (new App\Controllers\AuthController)->logout();
        break;

    // Google Social Auth Routes
    case 'auth/google':
    case 'login/google':
        require_once __DIR__ . '/Controllers/AuthController.php';
        (new App\Controllers\AuthController)->googleAuth();
        break;

    case 'auth/google/callback':
    case 'auth/google-one-tap':
        require_once __DIR__ . '/Controllers/AuthController.php';
        (new App\Controllers\AuthController)->googleCallback();
        break;

     case 'forgot-password':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->showForgotForm();
        break;
    
    case 'forgot-password-process':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->sendResetLink();
        break;

    case 'forgot-password-verify':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->showDualVerifyForm();
        break;

    case 'forgot-password-verify-process':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->processDualVerification();
        break;

    case 'forgot-password-resend':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->resendDualOtp();
        break;
    
    case 'reset-password':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->showResetForm();
        break;
    
    case 'reset-password-process':
        require_once __DIR__ . '/Controllers/ForgotPasswordController.php';
        (new App\Controllers\ForgotPasswordController)->resetPassword();
        break;

    // case 'register':
    // require_once __DIR__ . '/Controllers/RegisterController.php';
    // (new App\Controllers\RegisterController)->register();
    // break;

    case 'terms_conditions':
    case 'terms':
        require_once __DIR__ . '/Controllers/TermsconditionsController.php';
        (new App\Controllers\TermsconditionsController)->terms_conditions();
        break;

    case 'privacy':
        require_once __DIR__ . '/Controllers/PrivacyController.php';
        (new App\Controllers\PrivacyController)->privacy();
        break;

    case 'cancellation-policy':
    case 'cancellation':
        require_once __DIR__ . '/Controllers/StaticController.php';
        (new App\Controllers\StaticController)->cancellation();
        break;

    case 'cookie-policy':
    case 'cookies':
        require_once __DIR__ . '/Controllers/StaticController.php';
        (new App\Controllers\StaticController)->cookiePolicy();
        break;

    // case 'wishlist': // This loads the page showing all saved farmhouses
    //     require_once __DIR__ . '/Controllers/WishlistController.php';
    //     (new App\Controllers\WishlistController())->wishlist();
    //     break;

    // case 'toggle_wishlist': 
    //     require_once __DIR__ . '/Controllers/WishlistController.php';
    //     (new App\Controllers\WishlistController())->toggle();
    //     break;
    
    
    
      //=====================User Dashboard============================== 

    // User Registration Routes
    case 'register':
        require_once __DIR__ . '/Controllers/AuthController.php';
        (new App\Controllers\AuthController)->register(); // GET (View) and POST (Process)
        break;

    case 'verify-registration-otp':
        require_once __DIR__ . '/Controllers/AuthController.php';
        $authCtrl = new App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl->verifyRegistrationOtp();
        } else {
            $authCtrl->verifyRegistrationOtpView();
        }
        break;

    case 'resend-registration-otp':
        require_once __DIR__ . '/Controllers/AuthController.php';
        (new App\Controllers\AuthController)->resendRegistrationOtp();
        break;

    // User Dashboard
    case 'dashboard':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/DashboardController.php';
        (new App\Controllers\User\DashboardController)->dashboard();
        break;


    case 'user/farmhouses':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/FarmhouseController.php';
        (new App\Controllers\User\FarmhouseController)->index();
        break;

    // Booking Page
    case 'user/booking':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/BookingController.php';
        (new App\Controllers\User\BookingController)->index();
        break;

    // My Bookings History
    case 'user/bookings':
    case 'user/my-bookings':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/MyBookingsController.php';
        $myBkCtrl = new App\Controllers\User\MyBookingsController;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $myBkCtrl->cancel();
        } else {
            $myBkCtrl->index();
        }
        break;

    case 'user/my-bookings/cancel':
        AuthMiddleware::userOnly();
        require_once __DIR__ . '/Controllers/User/MyBookingsController.php';
        (new App\Controllers\User\MyBookingsController)->cancel();
        break;

    // User Profile
    case 'user/profile':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/ProfileController.php';
        (new App\Controllers\User\ProfileController)->index();
        break;
    case 'profile/update':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/ProfileController.php';
        (new App\Controllers\User\ProfileController)->update();
        break;
    case 'profile/change-password':
    case 'user/profile/change-password':
        AuthMiddleware::userOnly(); // Security Middleware
        require_once __DIR__ . '/Controllers/User/ProfileController.php';
        (new App\Controllers\User\ProfileController)->changePassword();
        break;

    case 'user/profile/verify-phone-otp':
        AuthMiddleware::userOnly();
        require_once __DIR__ . '/Controllers/User/ProfileController.php';
        (new App\Controllers\User\ProfileController)->verifyPhoneOtp();
        break;

    case 'user/profile/resend-phone-otp':
        AuthMiddleware::userOnly();
        require_once __DIR__ . '/Controllers/User/ProfileController.php';
        (new App\Controllers\User\ProfileController)->resendPhoneOtp();
        break;

    case 'user/profile/cancel-phone-change':
        AuthMiddleware::userOnly();
        require_once __DIR__ . '/Controllers/User/ProfileController.php';
        (new App\Controllers\User\ProfileController)->cancelPhoneChange();
        break;

    // Register (Guest Only)
    case 'user/register':
        AuthMiddleware::guestOnly(); // Prevent logged-in users
        require_once __DIR__ . '/Controllers/User/AuthController.php';
        (new App\Controllers\User\AuthController)->register();
        break;
    // ============================================================
    // 🛡️ ADMIN PANEL ROUTES (Fresh Clean Architecture)
    // ============================================================
    case 'admin/login':
        require_once __DIR__ . '/Controllers/Admin/AuthController.php';
        (new App\Controllers\Admin\AuthController())->loginForm();
        break;

    case 'admin/login-submit':
        require_once __DIR__ . '/Controllers/Admin/AuthController.php';
        (new App\Controllers\Admin\AuthController())->loginSubmit();
        break;

    case 'admin/logout':
        require_once __DIR__ . '/Controllers/Admin/AuthController.php';
        (new App\Controllers\Admin\AuthController())->logout();
        break;

    case 'admin/dashboard':
        require_once __DIR__ . '/Controllers/Admin/DashboardController.php';
        (new App\Controllers\Admin\DashboardController())->dashboard();
        break;

    // ── Manage Properties Routes ──
    case 'admin/properties':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->index();
        break;

    case 'admin/properties/create':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->create();
        break;

    case 'admin/properties/store':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->store();
        break;

    case 'admin/properties/edit':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->edit();
        break;

    case 'admin/properties/update':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->update();
        break;

    case 'admin/properties/delete':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->delete();
        break;

    case 'admin/properties/view':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->show();
        break;

    case 'admin/properties/delete-image':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->deleteImage();
        break;

    case 'admin/properties/set-featured':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->setFeatured();
        break;

    case 'admin/properties/update-status':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->updateStatus();
        break;

    case 'admin/properties/save-room':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->saveRoom();
        break;

    case 'admin/properties/delete-room':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->deleteRoom();
        break;

    case 'admin/properties/add-amenity':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->addAmenity();
        break;

    case 'admin/properties/get-amenity':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->getAmenity();
        break;

    case 'admin/properties/update-amenity':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->updateAmenity();
        break;

    case 'admin/properties/delete-amenity':
        require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
        (new App\Controllers\Admin\PropertyController())->deleteAmenity();
        break;

    // ── Dedicated Amenities & Property Rules Management Hub ──
    case 'admin/amenities-rules':
    case 'admin/amenities':
    case 'admin/rules':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->index();
        break;

    case 'admin/amenities-rules/amenity/save':
    case 'admin/amenities/save':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->saveAmenity();
        break;

    case 'admin/amenities-rules/amenity/get':
    case 'admin/amenities/get':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->getAmenity();
        break;

    case 'admin/amenities-rules/amenity/delete':
    case 'admin/amenities/delete':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->deleteAmenity();
        break;

    case 'admin/amenities-rules/rule/save':
    case 'admin/rules/save':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->saveRule();
        break;

    case 'admin/amenities-rules/rule/get':
    case 'admin/rules/get':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->getRule();
        break;

    case 'admin/amenities-rules/rule/delete':
    case 'admin/rules/delete':
        require_once __DIR__ . '/Controllers/Admin/AmenityRuleController.php';
        (new App\Controllers\Admin\AmenityRuleController())->deleteRule();
        break;

    // ============================================================
    // 👑 ADMIN PROPERTY OWNER MANAGEMENT MODULE ROUTES
    // ============================================================
    case 'admin/owners':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->index();
        break;

    case 'admin/owners/create':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->create();
        break;

    case 'admin/owners/store':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->store();
        break;

    case 'admin/owners/view':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->show();
        break;

    case 'admin/owners/edit':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->edit();
        break;

    case 'admin/owners/update':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->update();
        break;

    case 'admin/owners/update-status':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->updateStatus();
        break;

    case 'admin/owners/update-kyc':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->updateKycStatus();
        break;

    case 'admin/owners/upload-document':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->uploadDocument();
        break;

    case 'admin/owners/verify-document':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->verifyDocument();
        break;

    case 'admin/owners/delete-document':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->deleteDocument();
        break;

    case 'admin/owners/reassign-property':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->reassignProperty();
        break;

    case 'admin/owners/delete':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->delete();
        break;

    case 'admin/owners/export':
        require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
        (new App\Controllers\Admin\OwnerController())->export();
        break;

    // ============================================================
    // 👤 ADMIN GUEST & MEMBER MANAGEMENT ROUTES
    // ============================================================
    case 'admin/guests':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->index();
        break;

    case 'admin/guests/create':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->create();
        break;

    case 'admin/guests/store':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->store();
        break;

    case 'admin/guests/update':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->update();
        break;

    case 'admin/guests/update-status':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->updateStatus();
        break;

    case 'admin/guests/update-kyc':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->updateKycStatus();
        break;

    case 'admin/guests/update-membership':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->updateMembership();
        break;

    case 'admin/guests/upload-document':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->uploadDocument();
        break;

    case 'admin/guests/verify-document':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->verifyDocument();
        break;

    case 'admin/guests/delete-document':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->deleteDocument();
        break;

    case 'admin/guests/add-note':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->addNote();
        break;

    case 'admin/guests/delete':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->delete();
        break;

    case 'admin/guests/export':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->export();
        break;

    case 'admin/guests/search-ajax':
        require_once __DIR__ . '/Controllers/Admin/GuestController.php';
        (new App\Controllers\Admin\GuestController())->searchAjax();
        break;

    // ============================================================
    // 🛎️ ADMIN BOOKING & STAY MANAGEMENT ROUTES
    // ============================================================
    case 'admin/bookings':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->index();
        break;

    case 'admin/bookings/view':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->show();
        break;

    case 'admin/bookings/create':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->create();
        break;

    case 'admin/bookings/store':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->store();
        break;

    case 'admin/bookings/update-status':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->updateStatus();
        break;

    case 'admin/bookings/check-in':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->checkIn();
        break;

    case 'admin/bookings/check-out':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->checkOut();
        break;

    case 'admin/bookings/extend-stay':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->extendStay();
        break;

    case 'admin/bookings/add-extra-charge':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->addExtraCharge();
        break;

    case 'admin/bookings/record-payment':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->recordPayment();
        break;

    case 'admin/bookings/calendar':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->calendar();
        break;

    case 'admin/bookings/calendar-data':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->calendarData();
        break;

    case 'admin/bookings/reports':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->reports();
        break;

    case 'admin/bookings/invoice':
        require_once __DIR__ . '/Controllers/Admin/BookingController.php';
        (new App\Controllers\Admin\BookingController())->invoice();
        break;

    // ============================================================
    // ⭐ ADMIN GUEST REVIEW MANAGEMENT ROUTES
    // ============================================================
    case 'admin/reviews':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->index();
        break;

    case 'admin/reviews/update-status':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->updateStatus();
        break;

    case 'admin/reviews/bulk-update':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->bulkUpdate();
        break;

    case 'admin/reviews/edit-content':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->editContent();
        break;

    case 'admin/reviews/delete':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->delete();
        break;

    case 'admin/reviews/add-response':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->addResponse();
        break;

    case 'admin/reviews/delete-response':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->deleteResponse();
        break;

    case 'admin/reviews/export':
        require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
        (new App\Controllers\Admin\ReviewController())->export();
        break;

    // ============================================================
    // ⭐ ADMIN PLATFORM SETTINGS MANAGEMENT ROUTES
    // ============================================================
    case 'admin/settings':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->index();
        break;

    case 'admin/settings/update':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->update();
        break;

    case 'admin/settings/upload-branding':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->uploadBranding();
        break;

    case 'admin/settings/toggle-maintenance':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->toggleMaintenance();
        break;

    case 'admin/settings/test-email':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->testEmail();
        break;

    case 'admin/settings/reset-category':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->resetCategory();
        break;

    case 'admin/settings/clear-cache':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->clearCache();
        break;

    case 'admin/settings/export':
        require_once __DIR__ . '/Controllers/Admin/SettingsController.php';
        (new App\Controllers\Admin\SettingsController())->export();
        break;

    default:
        // Match parameterized admin property routes with encrypted ID or numeric ID
        if (preg_match('#^admin/properties/edit/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
            (new App\Controllers\Admin\PropertyController())->edit();
            break;
        }
        if (preg_match('#^admin/properties/view/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
            (new App\Controllers\Admin\PropertyController())->show();
            break;
        }
        if (preg_match('#^admin/properties/delete/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/PropertyController.php';
            (new App\Controllers\Admin\PropertyController())->delete();
            break;
        }

        // Match parameterized admin owner routes
        if (preg_match('#^admin/owners/view/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
            (new App\Controllers\Admin\OwnerController())->show();
            break;
        }
        if (preg_match('#^admin/owners/edit/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
            (new App\Controllers\Admin\OwnerController())->edit();
            break;
        }
        if (preg_match('#^admin/owners/delete/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/OwnerController.php';
            (new App\Controllers\Admin\OwnerController())->delete();
            break;
        }

        // Match parameterized admin booking routes
        if (preg_match('#^admin/bookings/view/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/BookingController.php';
            (new App\Controllers\Admin\BookingController())->show();
            break;
        }
        if (preg_match('#^admin/bookings/invoice/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/BookingController.php';
            (new App\Controllers\Admin\BookingController())->invoice();
            break;
        }

        // Match parameterized admin guest routes
        if (preg_match('#^admin/guests/view/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/GuestController.php';
            (new App\Controllers\Admin\GuestController())->show();
            break;
        }
        if (preg_match('#^admin/guests/edit/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/GuestController.php';
            (new App\Controllers\Admin\GuestController())->edit();
            break;
        }
        if (preg_match('#^admin/guests/delete/([^/]+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/Controllers/Admin/GuestController.php';
            (new App\Controllers\Admin\GuestController())->delete();
            break;
        }

        // Match parameterized admin review routes
        if (preg_match('#^admin/reviews/view/([^/]+)$#', $uri, $matches) || preg_match('#^admin/reviews/details/([^/]+)$#', $uri, $matches)) {
            $reviewId = (int)$matches[1];
            require_once __DIR__ . '/Controllers/Admin/ReviewController.php';
            (new App\Controllers\Admin\ReviewController())->details($reviewId);
            break;
        }

        http_response_code(404);
        echo "404 - Page not found";
        break;
}
?>
