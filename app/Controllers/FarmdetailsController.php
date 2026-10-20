<?php
namespace App\Controllers;

use App\Helpers\CryptoHelper;
use App\Models\FarmdetailModel;
use App\Config\Database;
use Exception;

class FarmdetailsController {

    private $db;

    public function __construct() {
        try {
            $this->db = Database::connect();
        } catch (Exception $e) {
            die("Error initializing system: " . $e->getMessage());
        }
    }

    /**
     * Show the farmhouse detail page.
     */
    public function farmhouse_details() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ── Decrypt & validate ID ──
        if (empty($_GET['id'])) {
            redirect('home');
        }

        $rawId = trim((string)($_GET['id'] ?? ''));
        $id = CryptoHelper::decrypt($rawId);
        if (!$id || !is_numeric($id) || (int)$id <= 0) {
            if (is_numeric($rawId) && (int)$rawId > 0) {
                $id = (int)$rawId;
            } else {
                http_response_code(404);
                die("Invalid farmhouse ID.");
            }
        }
        $id = (int)$id;

        $model = new FarmdetailModel($this->db);

        // ── Core data ──
        $farmhouse = $model->getFarmhouseById($id);
        if (!$farmhouse) {
            http_response_code(404);
            die("Farmhouse not found or not yet active.");
        }

        $images    = $model->getFarmhouseImages($id);
        $amenities = $model->getFarmhouseAmenities($id);
        $rules     = $model->getFarmhouseRules($id);
        $roomTypes = $model->getFarmhouseRoomTypes($id);
        $addons    = $model->getPropertyAddons($id);
        $seasonalPrices = $model->getSeasonalPrices($id);
        $similarFarmhouses = $model->getSimilarFarmhouses($id, $farmhouse['location'] ?? '', (int)($farmhouse['property_type_id'] ?? 0), 4);

        // ── Calendar data ──
        $occupiedDates      = $model->getOccupiedDates($id);
        $userId             = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $userRequestedDates = $model->getUserRequestedDates($id, $userId);
        $myPendingRequests  = $model->getPendingRequestsByUser($id, $userId);

        // ── Current user info (if logged in) ──
        $currentUser = null;
        if ($userId > 0) {
            $uStmt = $this->db->prepare("SELECT id, name, email, phone FROM users WHERE id = ? LIMIT 1");
            if ($uStmt) {
                $uStmt->bind_param("i", $userId);
                $uStmt->execute();
                $currentUser = $uStmt->get_result()->fetch_assoc();
                $uStmt->close();
            }
        }

        // ── Reviews & Multi-Criteria Social Proof ──
        require_once __DIR__ . '/../Models/ReviewModel.php';
        $reviewModel = new \App\Models\ReviewModel($this->db);
        $reviewSummary = $reviewModel->getReviewSummary($id);
        $approvedReviews = $reviewModel->getApprovedReviews($id);

        // ── Site-wide settings for footer / contact / payment QR ──
        $siteSettings = $this->getSiteSettings();

        include __DIR__ . '/../Views/farmhouse_details.php';
    }

    /**
     * Handle booking request form submission (POST).
     */
    public function request_booking() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('home');
        }

        // ── Auth guard ──
        if (empty($_SESSION['user_id'])) {
            redirect('login');
        }
        $user_id = (int)$_SESSION['user_id'];

        // ── Validate farmhouse ID ──
        if (empty($_POST['farmhouse_id'])) {
            die("Missing farmhouse.");
        }
        $farmhouse_id = CryptoHelper::decrypt($_POST['farmhouse_id']);
        if (!$farmhouse_id || (int)$farmhouse_id <= 0) {
            die("Invalid farmhouse ID.");
        }
        $farmhouse_id = (int)$farmhouse_id;

        // ── Validate dates ──
        $check_in       = trim($_POST['check_in']        ?? '');
        $check_out      = trim($_POST['check_out']       ?? '');
        $check_in_time  = trim($_POST['check_in_time']   ?? '');
        $check_out_time = trim($_POST['check_out_time']  ?? '');

        if (empty($check_in) || empty($check_out)) {
            die("Check-in and check-out dates are required.");
        }

        $checkInTs  = strtotime($check_in);
        $checkOutTs = strtotime($check_out);

        if (!$checkInTs || !$checkOutTs) {
            die("Invalid date format.");
        }

        if ($checkOutTs <= $checkInTs) {
            die("Check-out date must be after check-in date.");
        }

        $today = strtotime(date('Y-m-d'));
        if ($checkInTs < $today) {
            die("Check-in date cannot be in the past.");
        }

        $booking_type   = strtolower(trim($_POST['booking_type'] ?? 'complete'));
        if (!in_array($booking_type, ['complete', 'per_room'], true)) {
            $booking_type = 'complete';
        }

        $guests  = max(1, (int)($_POST['guests'] ?? 1));
        $rooms   = max(1, (int)($_POST['rooms']  ?? 1));
        $price   = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
        $message = trim($_POST['message'] ?? '');

        // Validate time format (HH:MM or HH:MM:SS)
        $timeRegex = '/^\d{2}:\d{2}(:\d{2})?$/';
        if (!empty($check_in_time) && !preg_match($timeRegex, $check_in_time)) {
            die("Invalid check-in time format.");
        }
        if (!empty($check_out_time) && !preg_match($timeRegex, $check_out_time)) {
            die("Invalid check-out time format.");
        }

        // Normalise to HH:MM:SS for MySQL TIME column
        $check_in_time  = $check_in_time  ? date('H:i:s', strtotime($check_in_time))  : null;
        $check_out_time = $check_out_time ? date('H:i:s', strtotime($check_out_time)) : null;

        $model        = new FarmdetailModel($this->db);
        $encryptedId  = urlencode(CryptoHelper::encrypt((string)$farmhouse_id));
        $redirectBase = "farmhouse_details?id={$encryptedId}";

        // ── 1. Admin-blocked dates check ──
        if ($model->isBlockedForDates($farmhouse_id, $check_in, $check_out)) {
            $msg = urlencode(
                "The farmhouse is unavailable for your selected dates — the owner has blocked this period. Please choose different dates."
            );
            redirect("{$redirectBase}&booking_error={$msg}");
        }

        // ── 2. Duplicate request check by same user ──
        if ($model->hasDuplicatePendingRequest($user_id, $farmhouse_id, $check_in, $check_out)) {
            $msg = urlencode(
                "You already have an active or pending booking request for this farmhouse during these dates."
            );
            redirect("{$redirectBase}&booking_error={$msg}");
        }

        // ── 3. Capacity & Room Type Check ──
        $farmhouse     = $model->getFarmhouseById($farmhouse_id);
        $nightCapacity = isset($farmhouse['night_capacity']) ? (int)$farmhouse['night_capacity'] : null;
        $alreadyBooked = $model->getOverlappingGuestCount($farmhouse_id, $check_in, $check_out);

        $room_type_id   = !empty($_POST['room_type_id']) ? (int)$_POST['room_type_id'] : null;
        $room_type_name = null;
        $price_per_room = null;

        $rentalType = $farmhouse['rental_type'] ?? (!empty($farmhouse['allow_room_booking']) ? 'both' : 'complete');

        if ($booking_type === 'complete' && $rentalType === 'room_wise') {
            $msg = urlencode("This property is only available for Room-Wise booking. Please select a specific room type.");
            redirect("{$redirectBase}&booking_error={$msg}");
        }

        if ($booking_type === 'per_room') {
            if ($rentalType === 'complete' || empty($farmhouse['allow_room_booking'])) {
                $msg = urlencode("Room-wise booking is not enabled for this property. Please book the complete farmhouse.");
                redirect("{$redirectBase}&booking_error={$msg}");
            }
            if (!empty($room_type_id)) {
                $selectedRt = $model->getRoomTypeById($room_type_id, $farmhouse_id);
                if (!$selectedRt) {
                    $msg = urlencode("The selected room type is unavailable. Please select another room type.");
                    redirect("{$redirectBase}&booking_error={$msg}");
                }
                $room_type_name = $selectedRt['room_type_name'];
                $price_per_room = (float)$selectedRt['price_per_room'];

                // Check room type availability for the requested dates
                $availableRooms = $model->getAvailableRoomInventory($farmhouse_id, $room_type_id, $check_in, $check_out);
                if ($rooms > $availableRooms) {
                    if ($availableRooms === 0) {
                        $msg = urlencode("Sorry, '{$room_type_name}' is fully booked for your selected dates. Please choose different dates or another room type.");
                    } else {
                        $msg = urlencode("Only {$availableRooms} '{$room_type_name}' room(s) available for the selected dates. You requested {$rooms}.");
                    }
                    redirect("{$redirectBase}&booking_error={$msg}");
                }

                // Check capacity per room
                $maxRoomGuests = $rooms * max(1, (int)$selectedRt['capacity_per_room']);
                if ($guests > $maxRoomGuests) {
                    $msg = urlencode("{$rooms} '{$room_type_name}' room(s) can accommodate up to {$maxRoomGuests} guests. You entered {$guests} guests.");
                    redirect("{$redirectBase}&booking_error={$msg}");
                }

                // Auto-calculate total price
                $nights = max(1, (int)round(($checkOutTs - $checkInTs) / 86400));
                $price  = $price_per_room * $rooms * $nights;
            } else {
                // Fallback for farmhouses without configured room types
                $price_per_room = !empty($farmhouse['room_price']) ? (float)$farmhouse['room_price'] : 0.0;
                $room_type_name = 'Standard Room';
                $nights = max(1, (int)round(($checkOutTs - $checkInTs) / 86400));
                $price  = $price_per_room * $rooms * $nights;
            }
        }

        if ($nightCapacity === null || $nightCapacity === 0) {
            // No capacity defined → treat as exclusive (only 1 booking at a time).
            if ($alreadyBooked > 0 && $booking_type === 'complete') {
                $msg = urlencode(
                    "This farmhouse is already fully booked for the selected dates. Please choose different dates."
                );
                redirect("{$redirectBase}&booking_error={$msg}");
            }
        } else {
            // Capacity defined → check whether adding the new guests would exceed it.
            if (($alreadyBooked + $guests) > $nightCapacity) {
                $remaining = max(0, $nightCapacity - $alreadyBooked);
                if ($remaining === 0) {
                    $msg = urlencode(
                        "This farmhouse is fully booked for the selected dates (capacity: {$nightCapacity} guests). Please choose different dates."
                    );
                } else {
                    $msg = urlencode(
                        "Only {$remaining} guest spot(s) are available for the selected dates (capacity: {$nightCapacity}). You requested {$guests}. Please reduce your guest count or choose different dates."
                    );
                }
                redirect("{$redirectBase}&booking_error={$msg}");
            }
        }

        // ── 4. Calculate Verified Price Breakdown with Pricing Engine ──
        $rawAddons  = $_POST['addons'] ?? [];
        $couponCode = trim($_POST['coupon_code'] ?? '');
        $quote = $model->calculateQuote([
            'farmhouse_id' => $farmhouse_id,
            'booking_type' => $booking_type,
            'room_type_id' => $room_type_id,
            'rooms'        => $rooms,
            'guests'       => $guests,
            'check_in'     => $check_in,
            'check_out'    => $check_out,
            'addons'       => $rawAddons,
            'coupon_code'  => $couponCode
        ]);

        $finalPrice      = isset($quote['total_payable']) ? (float)$quote['total_payable'] : $price;
        $weekendNights   = (int)($quote['weekend_nights'] ?? 0);
        $securityDeposit = (float)($quote['security_deposit'] ?? 0.0);
        $cleaningFee     = (float)($quote['cleaning_fee'] ?? 0.0);
        $addonCharges    = (float)($quote['addon_charges'] ?? 0.0);
        $addonsSelected  = !empty($quote['selected_addons']) ? $quote['selected_addons'] : null;
        $discountAmount  = (float)($quote['coupon_discount'] ?? 0.0) + (float)($quote['long_stay_discount'] ?? 0.0);
        $appliedCoupon   = !empty($quote['coupon_valid']) ? $quote['coupon_code'] : null;
        $platformFee     = (float)($quote['platform_fee'] ?? 0.0);
        $ownerAmount     = (float)($quote['owner_amount'] ?? 0.0);

        // ── 5. Payment Method & Proof Upload (F38, F39, F40) ──
        $rawPayMethod = strtolower(trim($_POST['payment_method'] ?? 'upi'));
        $allowedPayMethods = ['upi', 'bank_transfer', 'pay_at_property'];
        $paymentMethod = in_array($rawPayMethod, $allowedPayMethods, true) ? $rawPayMethod : 'upi';

        $utrNumber = trim($_POST['utr_number'] ?? '');
        $paymentProofPath = null;

        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['payment_proof'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (in_array($ext, $allowedExts, true) && $file['size'] <= 8 * 1024 * 1024) {
                $uploadDir = __DIR__ . '/../../assets/images/uploads/payments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'proof_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $paymentProofPath = 'assets/images/uploads/payments/' . $filename;
                }
            }
        }

        $paymentStatus = 'unpaid';
        if ($paymentMethod === 'pay_at_property') {
            $paymentStatus = 'unpaid';
        } elseif (!empty($utrNumber) || !empty($paymentProofPath)) {
            $paymentStatus = 'pending_verification';
        }

        // ── 6. All checks passed — persist the booking request ──
        $data = [
            'user_id'          => $user_id,
            'farmhouse_id'     => $farmhouse_id,
            'booking_type'     => $booking_type,
            'room_type_id'     => $room_type_id,
            'room_type_name'   => $room_type_name,
            'price_per_room'   => $price_per_room,
            'check_in'         => $check_in,
            'check_in_time'    => $check_in_time,
            'check_out'        => $check_out,
            'check_out_time'   => $check_out_time,
            'guests'           => $guests,
            'rooms'            => $rooms,
            'price'            => $finalPrice,
            'weekend_nights'   => $weekendNights,
            'security_deposit' => $securityDeposit,
            'cleaning_fee'     => $cleaningFee,
            'addon_charges'    => $addonCharges,
            'addons_selected'  => $addonsSelected,
            'discount_amount'  => $discountAmount,
            'coupon_code'      => $appliedCoupon,
            'platform_fee'     => $platformFee,
            'owner_amount'     => $ownerAmount,
            'message'          => $message,
            'payment_method'   => $paymentMethod,
            'payment_status'   => $paymentStatus,
            'utr_number'       => $utrNumber ?: null,
            'payment_proof'    => $paymentProofPath,
        ];

        $res = $model->createBookingRequest($data);
        if (is_array($res) && !empty($res['success'])) {
            $reqId = $res['id'];
            $isInstant = !empty($res['is_instant']);
            $successParam = $isInstant ? "instant_success" : "success";
            $holdParam = !empty($res['hold_expires_at']) ? "&hold_expires=" . urlencode($res['hold_expires_at']) : "";
            redirect("{$redirectBase}&booking={$successParam}&req_id={$reqId}{$holdParam}");
        } elseif (is_numeric($res) && $res > 0) {
            redirect("{$redirectBase}&booking=success&req_id={$res}");
        } else {
            $errMsg = is_array($res) && !empty($res['error']) ? $res['error'] : "Something went wrong while securing your booking. Please try again.";
            redirect("{$redirectBase}&booking_error=" . urlencode($errMsg));
        }
    }

    /**
     * Live Dynamic Pricing Calculation API (AJAX endpoint)
     */
    public function calculatePrice(): void {
        header('Content-Type: application/json');
        $params = array_merge($_GET, $_POST);
        $farmhouseId = 0;
        if (!empty($params['farmhouse_id'])) {
            $farmhouseId = (int)$params['farmhouse_id'];
            if ($farmhouseId <= 0) {
                $decrypted = CryptoHelper::decrypt((string)$params['farmhouse_id']);
                if ($decrypted && (int)$decrypted > 0) {
                    $farmhouseId = (int)$decrypted;
                }
            }
        }
        $params['farmhouse_id'] = $farmhouseId;

        $model = new FarmdetailModel($this->db);
        $quote = $model->calculateQuote($params);
        echo json_encode($quote);
        exit;
    }

    /**
     * Coupon Validation API (AJAX endpoint)
     */
    public function validateCoupon(): void {
        header('Content-Type: application/json');
        $code = trim($_GET['code'] ?? $_POST['code'] ?? '');
        $model = new FarmdetailModel($this->db);
        $coupon = $model->getCoupon($code);
        if ($coupon) {
            echo json_encode([
                'valid' => true,
                'code' => $coupon['code'],
                'description' => $coupon['description'],
                'discount_type' => $coupon['discount_type'],
                'discount_value' => (float)$coupon['discount_value'],
                'min_booking_amount' => (float)$coupon['min_booking_amount'],
                'max_discount_amount' => !empty($coupon['max_discount_amount']) ? (float)$coupon['max_discount_amount'] : null
            ]);
        } else {
            echo json_encode(['valid' => false, 'message' => 'Invalid or expired promo code.']);
        }
        exit;
    }

    /**
     * Log contact / inquiry interaction (Call / WhatsApp) via AJAX
     */
    public function logInquiry(): void {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $rawFarmId = $_POST['farmhouse_id'] ?? $_GET['farmhouse_id'] ?? null;
        $type = strtolower(trim($_POST['type'] ?? $_GET['type'] ?? 'call'));
        if (!in_array($type, ['call', 'whatsapp'], true)) {
            $type = 'call';
        }

        if ($rawFarmId !== null) {
            $farmId = (int)$rawFarmId;
            if ($farmId <= 0) {
                $decrypted = CryptoHelper::decrypt((string)$rawFarmId);
                if ($decrypted && (int)$decrypted > 0) {
                    $farmId = (int)$decrypted;
                }
            }

            if ($farmId > 0) {
                $model = new FarmdetailModel($this->db);
                $success = $model->logInquiry($userId, $farmId, $type);
                echo json_encode(['success' => $success]);
                return;
            }
        }

        echo json_encode(['success' => false, 'message' => 'Invalid farmhouse ID']);
    }

    /**
     * Downloadable / Printable PDF Confirmation Voucher & Tax Invoice (F73)
     */
    public function bookingVoucher(): void {
        $rawId = trim($_GET['id'] ?? '');
        $reqId = 0;
        if (is_numeric($rawId)) {
            $reqId = (int)$rawId;
        } else {
            $decrypted = CryptoHelper::decrypt($rawId);
            if ($decrypted && is_numeric($decrypted)) {
                $reqId = (int)$decrypted;
            }
        }

        if ($reqId <= 0) {
            die("Invalid or expired booking reference.");
        }

        $model = new FarmdetailModel($this->db);
        $booking = $model->getBookingRequestById($reqId);
        if (!$booking) {
            die("Booking record not found.");
        }

        $siteSettings = $this->getSiteSettings();
        include __DIR__ . "/../Views/booking_voucher.php";
    }

    /**
     * Granular Room-Wise Availability API (F29)
     */
    public function getRoomAvailability(): void {
        header('Content-Type: application/json');
        $params = array_merge($_GET, $_POST);
        $farmhouseId = 0;
        if (!empty($params['farmhouse_id'])) {
            $farmhouseId = (int)$params['farmhouse_id'];
            if ($farmhouseId <= 0) {
                $decrypted = CryptoHelper::decrypt((string)$params['farmhouse_id']);
                if ($decrypted && (int)$decrypted > 0) {
                    $farmhouseId = (int)$decrypted;
                }
            }
        }

        $checkIn = trim($params['check_in'] ?? date('Y-m-d'));
        $checkOut = trim($params['check_out'] ?? date('Y-m-d', strtotime('+1 day')));

        $model = new FarmdetailModel($this->db);
        $avail = $model->getRoomWiseAvailability($farmhouseId, $checkIn, $checkOut);
        echo json_encode($avail);
        exit;
    }

    /**
     * Monthly Room-Wise Availability Calendar Matrix API (F29)
     */
    public function getRoomCalendarMatrix(): void {
        header('Content-Type: application/json');
        $params = array_merge($_GET, $_POST);
        $farmhouseId = 0;
        if (!empty($params['farmhouse_id'])) {
            $farmhouseId = (int)$params['farmhouse_id'];
            if ($farmhouseId <= 0) {
                $decrypted = CryptoHelper::decrypt((string)$params['farmhouse_id']);
                if ($decrypted && (int)$decrypted > 0) {
                    $farmhouseId = (int)$decrypted;
                }
            }
        }

        $year = (int)($params['year'] ?? date('Y'));
        $month = (int)($params['month'] ?? date('n'));

        $model = new FarmdetailModel($this->db);
        $matrix = $model->getRoomAvailabilityCalendarMatrix($farmhouseId, $year, $month);
        echo json_encode($matrix);
        exit;
    }

    /**
     * Handle review submission (POST).
     */
    public function submitReview(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('home');
        }

        $rawFarmhouseId = $_POST['farmhouse_id'] ?? '';
        $farmhouseId = CryptoHelper::decrypt($rawFarmhouseId);
        if (!$farmhouseId || (int)$farmhouseId <= 0) {
            $farmhouseId = (int)$rawFarmhouseId;
        }

        if (!$farmhouseId) {
            $_SESSION['error'] = 'Invalid farmhouse reference.';
            redirect('home');
        }

        // Optional CSRF check if token present
        if (!empty($_POST['csrf_token'])) {
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'])) {
                $_SESSION['error'] = 'Invalid session token. Please try submitting again.';
                redirect("farmhouse_details?id=" . urlencode(CryptoHelper::encrypt($farmhouseId)));
            }
        }

        $guestName    = trim($_POST['guest_name'] ?? '');
        $guestEmail   = trim($_POST['guest_email'] ?? '');
        $cleanliness  = max(1, min(5, (int)($_POST['cleanliness'] ?? 5)));
        $location     = max(1, min(5, (int)($_POST['location'] ?? 5)));
        $value        = max(1, min(5, (int)($_POST['value'] ?? 5)));
        $hospitality  = max(1, min(5, (int)($_POST['hospitality'] ?? 5)));
        $reviewTitle  = trim($_POST['review_title'] ?? '');
        $reviewText   = trim($_POST['review_text'] ?? '');
        $userId       = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $bookingId    = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;

        if (empty($guestName) || empty($reviewText)) {
            $_SESSION['error'] = 'Please provide your name and review details.';
            redirect("farmhouse_details?id=" . urlencode(CryptoHelper::encrypt($farmhouseId)));
        }

        // Server-side Stay Verification & Duplicate Prevention
        $isVerifiedStay = 0;
        $matchedBookingId = null;

        if ($userId) {
            // Check if user has a completed or confirmed booking for this farmhouse
            $bSql = "SELECT id FROM booking_requests 
                     WHERE user_id = ? AND farmhouse_id = ? AND status IN ('checked_out', 'completed', 'confirmed') ";
            if ($bookingId) {
                $bSql .= " AND id = " . (int)$bookingId;
            }
            $bSql .= " ORDER BY id DESC LIMIT 1";

            $bStmt = $this->db->prepare($bSql);
            if ($bStmt) {
                $bStmt->bind_param("ii", $userId, $farmhouseId);
                $bStmt->execute();
                $bRow = $bStmt->get_result()->fetch_assoc();
                $bStmt->close();

                if ($bRow) {
                    $matchedBookingId = (int)$bRow['id'];
                    $isVerifiedStay = 1;

                    // Duplicate check for this booking
                    $dupStmt = $this->db->prepare("SELECT id FROM reviews WHERE booking_id = ? AND status != 'rejected' LIMIT 1");
                    if ($dupStmt) {
                        $dupStmt->bind_param("i", $matchedBookingId);
                        $dupStmt->execute();
                        $hasDup = ($dupStmt->get_result()->num_rows > 0);
                        $dupStmt->close();

                        if ($hasDup) {
                            $_SESSION['error'] = 'You have already submitted a review for this stay.';
                            redirect("farmhouse_details?id=" . urlencode(CryptoHelper::encrypt($farmhouseId)));
                        }
                    }
                }
            }
        }

        require_once __DIR__ . '/../Models/ReviewModel.php';
        $reviewModel = new \App\Models\ReviewModel($this->db);
        $success = $reviewModel->submitReview([
            'farmhouse_id' => $farmhouseId,
            'user_id'      => $userId,
            'booking_id'   => $matchedBookingId,
            'guest_name'   => $guestName,
            'guest_email'  => $guestEmail,
            'cleanliness'  => $cleanliness,
            'location'     => $location,
            'value'        => $value,
            'hospitality'  => $hospitality,
            'review_title' => $reviewTitle,
            'review_text'  => $reviewText,
            'is_verified'  => $isVerifiedStay,
        ]);

        if ($success) {
            $_SESSION['success'] = 'Thank you! Your ' . ($isVerifiedStay ? 'verified ' : '') . 'review has been submitted for moderation and will appear shortly upon approval.';
        } else {
            $_SESSION['error'] = 'Failed to submit review. Please try again.';
        }

        redirect("farmhouse_details?id=" . urlencode(CryptoHelper::encrypt($farmhouseId)));
    }

    /**
     * Fetch site-wide settings (phone, whatsapp, socials, etc.)
     */
    private function getSiteSettings(): array {
        $result = $this->db->query("SELECT * FROM site_settings LIMIT 1");
        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : [];
    }
}