<?php
namespace App\Controllers\Admin;

use App\Helpers\AdminAuth;
use App\Models\Admin\PropertyModel;
use App\Models\Admin\OwnerModel;

class PropertyController {
    protected PropertyModel $propertyModel;
    protected OwnerModel $ownerModel;

    public function __construct() {
        $this->propertyModel = new PropertyModel();
        $this->ownerModel    = new OwnerModel();
    }

    /**
     * Display Properties List with search, filtering, and pagination
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();

        // ── 1. Read & Sanitize Filters ──
        $filters = [
            'search'           => trim($_GET['search'] ?? ''),
            'status'           => trim($_GET['status'] ?? ''),
            'property_type_id' => !empty($_GET['property_type_id']) ? (int)$_GET['property_type_id'] : '',
            'location'         => trim($_GET['location'] ?? ''),
            'is_verified'      => isset($_GET['is_verified']) && $_GET['is_verified'] !== '' ? (int)$_GET['is_verified'] : '',
            'sort'             => trim($_GET['sort'] ?? 'newest'),
        ];

        // ── 2. Pagination Calculations ──
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 8;

        $totalRecords = $this->propertyModel->countAll($filters);
        $totalPages   = max(1, (int)ceil($totalRecords / $perPage));
        if ($page > $totalPages && $totalRecords > 0) {
            $page = $totalPages;
        }

        $properties    = $this->propertyModel->getAll($filters, $page, $perPage);
        $kpis          = $this->propertyModel->getKPIStats();
        $propertyTypes = $this->propertyModel->getPropertyTypes();
        $locations     = $this->propertyModel->getDistinctLocations();

        $startItem = $totalRecords > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $endItem   = min($totalRecords, $page * $perPage);

        $pageTitle   = "Manage Properties • Stayora Executive Console";
        $currentPage = "properties";
        $headerTitle = "Manage Properties";

        require_once __DIR__ . '/../../Views/admin/properties/index.php';
    }

    /**
     * Show Add Property Form Page
     */
    public function create(): void {
        $admin = AdminAuth::requireAuth();

        $propertyTypes       = $this->propertyModel->getPropertyTypes();
        $locations           = $this->propertyModel->getDistinctLocations();
        $allAmenitiesGrouped = $this->propertyModel->getAllAmenitiesGrouped();
        $rulePresets         = $this->propertyModel->getRulePresets();
        $owners              = $this->ownerModel->getActiveOwnersList();
        $formData            = $_SESSION['property_form_data'] ?? [];
        unset($_SESSION['property_form_data']);

        $pageTitle   = "Add New Luxury Property • Stayora Executive Console";
        $currentPage = "properties";
        $headerTitle = "Add New Property";

        require_once __DIR__ . '/../../Views/admin/properties/create.php';
    }

    /**
     * Store New Property Submission
     */
    public function store(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/properties');
            return;
        }

        // ── Normalize Structured Fields ──
        $this->normalizeRequestData($_POST);

        // ── Validate Fields ──
        $errors = $this->validateInput($_POST);

        // ── Handle File Uploads ──
        $uploadedImages = $this->handleUploads($_FILES['images'] ?? null);

        // Fallback or additional manual image URLs
        if (!empty($_POST['manual_image_urls'])) {
            $manuals = explode("\n", (string)$_POST['manual_image_urls']);
            foreach ($manuals as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $uploadedImages[] = $url;
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = "Validation failed: " . implode(" ", $errors);
            $_SESSION['property_form_data'] = $_POST;
            redirect('admin/properties/create');
            return;
        }

        // Insert into database
        $newId = $this->propertyModel->create($_POST, $uploadedImages);

        if ($newId > 0) {
            $_SESSION['success'] = "Property '" . htmlspecialchars($_POST['title']) . "' was added successfully!";
            redirect('admin/properties');
        } else {
            $_SESSION['error'] = "Could not save property to database. Please check values and try again.";
            $_SESSION['property_form_data'] = $_POST;
            redirect('admin/properties/create');
        }
    }

    /**
     * Show Edit Property Form Page
     */
    public function edit(): void {
        $admin = AdminAuth::requireAuth();

        $rawId = $_GET['id'] ?? 0;
        $id = decrypt_id($rawId);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid property reference.";
            redirect('admin/properties');
            return;
        }

        $property = $this->propertyModel->findById($id);
        if (!$property) {
            $_SESSION['error'] = "Property not found.";
            redirect('admin/properties');
            return;
        }

        $property['encrypted_id'] = encrypt_id($property['id']);

        // Check if there are cached inputs from a failed validation submission
        $cachedFormData = $_SESSION['property_form_data'] ?? [];
        unset($_SESSION['property_form_data']);
        if (!empty($cachedFormData)) {
            foreach ($cachedFormData as $k => $v) {
                if ($k !== 'id') {
                    $property[$k] = $v;
                }
            }
            if (isset($cachedFormData['amenities']) && is_array($cachedFormData['amenities'])) {
                $property['amenity_ids'] = array_map('intval', $cachedFormData['amenities']);
            }
            if (isset($cachedFormData['rules']) && is_array($cachedFormData['rules'])) {
                $property['rules'] = $cachedFormData['rules'];
            }
        }

        $propertyTypes       = $this->propertyModel->getPropertyTypes();
        $locations           = $this->propertyModel->getDistinctLocations();
        $allAmenitiesGrouped = $this->propertyModel->getAllAmenitiesGrouped();
        $rulePresets         = $this->propertyModel->getRulePresets();
        $owners              = $this->ownerModel->getActiveOwnersList();

        $pageTitle   = "Edit Property: " . htmlspecialchars($property['title']) . " • Stayora Console";
        $currentPage = "properties";
        $headerTitle = "Edit Property";

        require_once __DIR__ . '/../../Views/admin/properties/edit.php';
    }

    /**
     * Update Property Submission
     */
    public function update(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/properties');
            return;
        }

        $rawId = $_POST['id'] ?? $_GET['id'] ?? 0;
        $id = decrypt_id($rawId);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid property reference.";
            redirect('admin/properties');
            return;
        }

        // ── Normalize Structured Fields ──
        $this->normalizeRequestData($_POST);

        // ── Validate Fields ──
        $errors = $this->validateInput($_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = "Validation failed: " . implode(" ", $errors);
            $_SESSION['property_form_data'] = $_POST;
            redirect('admin/properties/edit?id=' . encrypt_id($id));
            return;
        }

        // ── Handle Any Newly Uploaded Images ──
        $uploadedImages = $this->handleUploads($_FILES['images'] ?? null);
        if (!empty($_POST['manual_image_urls'])) {
            $manuals = explode("\n", (string)$_POST['manual_image_urls']);
            foreach ($manuals as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $uploadedImages[] = $url;
                }
            }
        }

        $ok = $this->propertyModel->update($id, $_POST, $uploadedImages);

        if ($ok) {
            $_SESSION['success'] = "Property '" . htmlspecialchars($_POST['title']) . "' updated successfully!";
            redirect('admin/properties');
        } else {
            $_SESSION['error'] = "Failed to update property details in database.";
            $_SESSION['property_form_data'] = $_POST;
            redirect('admin/properties/edit?id=' . encrypt_id($id));
        }
    }

    /**
     * Delete Property
     */
    public function delete(): void {
        $admin = AdminAuth::requireAuth();

        $rawId = $_POST['id'] ?? $_GET['id'] ?? 0;
        $id = decrypt_id($rawId);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid property reference.";
            redirect('admin/properties');
            return;
        }

        $property = $this->propertyModel->findById($id);
        $title = $property['title'] ?? "Property";

        $ok = $this->propertyModel->delete($id);
        if ($ok) {
            $_SESSION['success'] = "Property '{$title}' was permanently deleted.";
        } else {
            $_SESSION['error'] = "Failed to delete property. Check database relationships.";
        }

        redirect('admin/properties');
    }

    /**
     * View Property Details (Returns JSON for AJAX modal or renders view)
     */
    public function show(): void {
        $admin = AdminAuth::requireAuth();

        $rawId = $_GET['id'] ?? 0;
        $id = decrypt_id($rawId);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            return;
        }

        $property = $this->propertyModel->findById($id);
        if (!$property) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Property not found']);
            return;
        }

        $property['encrypted_id'] = encrypt_id($property['id']);

        // Check if AJAX request
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                  || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $property]);
            return;
        }

        $pageTitle   = htmlspecialchars($property['title']) . " • Details";
        $currentPage = "properties";
        $headerTitle = "Property Details";

        require_once __DIR__ . '/../../Views/admin/properties/view.php';
    }

    /**
     * Delete an individual gallery image
     */
    public function deleteImage(): void {
        $admin = AdminAuth::requireAuth();

        $rawImageId     = $_POST['image_id'] ?? $_GET['image_id'] ?? 0;
        $rawFarmhouseId = $_POST['farmhouse_id'] ?? $_GET['farmhouse_id'] ?? 0;

        $imageId     = decrypt_id($rawImageId);
        $farmhouseId = decrypt_id($rawFarmhouseId);

        if ($imageId > 0 && $farmhouseId > 0) {
            $this->propertyModel->deleteImage($imageId, $farmhouseId);
            $_SESSION['success'] = "Image removed from property gallery.";
        } else {
            $_SESSION['error'] = "Invalid image parameters.";
        }

        redirect('admin/properties/edit?id=' . encrypt_id($farmhouseId));
    }

    /**
     * Set an image as primary featured
     */
    public function setFeatured(): void {
        $admin = AdminAuth::requireAuth();

        $rawImageId     = $_POST['image_id'] ?? $_GET['image_id'] ?? 0;
        $rawFarmhouseId = $_POST['farmhouse_id'] ?? $_GET['farmhouse_id'] ?? 0;

        $imageId     = decrypt_id($rawImageId);
        $farmhouseId = decrypt_id($rawFarmhouseId);

        if ($imageId > 0 && $farmhouseId > 0) {
            $this->propertyModel->setFeaturedImage($imageId, $farmhouseId);
            $_SESSION['success'] = "Primary featured photo updated.";
        } else {
            $_SESSION['error'] = "Invalid image parameters.";
        }

        redirect('admin/properties/edit?id=' . encrypt_id($farmhouseId));
    }

    /**
     * Quick status update via POST
     */
    public function updateStatus(): void {
        $admin = AdminAuth::requireAuth();

        $rawId  = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $id = decrypt_id($rawId);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($id <= 0 || !in_array($status, ['active', 'pending', 'rejected'])) {
            if ($isAjax) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                return;
            }
            $_SESSION['error'] = "Invalid property reference or status.";
            redirect('admin/properties');
            return;
        }

        $ok = $this->propertyModel->updateStatus($id, $status);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Status updated' : 'Update failed']);
            return;
        }

        $_SESSION['success'] = "Property status updated to '{$status}'.";
        redirect('admin/properties');
    }

    // ═════════════════════════════════════════════════════════════════════════════
    // ── AJAX ENDPOINTS: ROOM MANAGEMENT & CUSTOM AMENITIES ──
    // ═════════════════════════════════════════════════════════════════════════════

    /**
     * Save/Update an individual room type via AJAX
     */
    public function saveRoom(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $rawFarmhouseId = $_POST['farmhouse_id'] ?? 0;
        $farmhouseId = is_numeric($rawFarmhouseId) ? (int)$rawFarmhouseId : decrypt_id($rawFarmhouseId);

        if ($farmhouseId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
            return;
        }

        $roomTypeId = !empty($_POST['room_type_id']) ? (int)$_POST['room_type_id'] : null;

        $roomName = trim($_POST['room_type_name'] ?? '');
        if (empty($roomName)) {
            echo json_encode(['success' => false, 'message' => 'Room type name is required.']);
            return;
        }

        $savedId = $this->propertyModel->saveRoomType($farmhouseId, $_POST, $roomTypeId);

        if ($savedId > 0) {
            $updatedRooms = $this->propertyModel->getFarmhouseRoomTypes($farmhouseId);
            echo json_encode(['success' => true, 'message' => 'Room type saved successfully', 'room_type_id' => $savedId, 'rooms' => $updatedRooms]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save room type in database.']);
        }
    }

    /**
     * Delete an individual room type with safeguard check
     */
    public function deleteRoom(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $rawFarmhouseId = $_POST['farmhouse_id'] ?? 0;
        $farmhouseId = is_numeric($rawFarmhouseId) ? (int)$rawFarmhouseId : decrypt_id($rawFarmhouseId);
        $roomTypeId = (int)($_POST['room_type_id'] ?? 0);

        if ($farmhouseId <= 0 || $roomTypeId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $result = $this->propertyModel->deleteRoomType($roomTypeId, $farmhouseId);
        if ($result['success']) {
            $updatedRooms = $this->propertyModel->getFarmhouseRoomTypes($farmhouseId);
            $result['rooms'] = $updatedRooms;
        }

        echo json_encode($result);
    }

    /**
     * Add a new amenity to the master catalogue via AJAX
     */
    public function addAmenity(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $iconClass = trim($_POST['icon_class'] ?? 'check_circle');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Amenity name is required']);
            return;
        }

        $amenityId = $this->propertyModel->addCustomAmenity($name, $category, $iconClass);

        if ($amenityId > 0) {
            echo json_encode([
                'success' => true,
                'amenity' => [
                    'id'         => $amenityId,
                    'name'       => $name,
                    'category'   => $category,
                    'icon_class' => $iconClass
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not save amenity']);
        }
    }

    /**
     * Get amenity specifications and usage count via AJAX
     */
    public function getAmenity(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid amenity reference']);
            return;
        }

        $amenity = $this->propertyModel->getAmenityById($id);
        if (!$amenity) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Amenity not found']);
            return;
        }

        $usageCount = $this->propertyModel->getAmenityUsageCount($id);
        echo json_encode([
            'success'     => true,
            'amenity'     => $amenity,
            'usage_count' => $usageCount
        ]);
    }

    /**
     * Update an existing amenity in the master catalog via AJAX
     */
    public function updateAmenity(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $category  = trim($_POST['category'] ?? 'general');
        $iconClass = trim($_POST['icon_class'] ?? 'check_circle');

        if ($id <= 0 || empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Amenity ID and name are required']);
            return;
        }

        $ok = $this->propertyModel->updateAmenity($id, $name, $category, $iconClass);
        if ($ok) {
            $usageCount = $this->propertyModel->getAmenityUsageCount($id);
            echo json_encode([
                'success' => true,
                'message' => 'Amenity updated successfully',
                'amenity' => [
                    'id'          => $id,
                    'name'        => $name,
                    'category'    => $category,
                    'icon_class'  => $iconClass,
                    'usage_count' => $usageCount
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update amenity']);
        }
    }

    /**
     * Delete an amenity and unlink it from properties via AJAX
     */
    public function deleteAmenity(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid amenity reference']);
            return;
        }

        $ok = $this->propertyModel->deleteAmenity($id);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Amenity deleted successfully', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete amenity']);
        }
    }

    // ═════════════════════════════════════════════════════════════════════════════
    // ── PRIVATE HELPERS & VALIDATION ──
    // ═════════════════════════════════════════════════════════════════════════════

    /**
     * Normalize arrays from form or JSON inputs
     */
    private function normalizeRequestData(array &$data): void {
        // Parse JSON rules if sent as JSON string
        if (isset($data['rules_json']) && is_string($data['rules_json'])) {
            $decoded = json_decode($data['rules_json'], true);
            if (is_array($decoded)) {
                $data['rules'] = $decoded;
            }
        }

        // Parse JSON room_types if sent as JSON string
        if (isset($data['room_types_json']) && is_string($data['room_types_json'])) {
            $decoded = json_decode($data['room_types_json'], true);
            if (is_array($decoded)) {
                $data['room_types'] = $decoded;
            }
        }

        // Normalize rental_type
        if (empty($data['rental_type'])) {
            $data['rental_type'] = !empty($data['allow_room_booking']) ? 'both' : 'complete';
        }
    }

    /**
     * Internal input validation with support for all 3 booking modes
     */
    private function validateInput(array $data): array {
        $errors = [];

        $title = trim((string)($data['title'] ?? ''));
        if (empty($title)) {
            $errors[] = "Property title is required.";
        } elseif (mb_strlen($title) < 3) {
            $errors[] = "Property title must be at least 3 characters long.";
        } elseif (mb_strlen($title) > 150) {
            $errors[] = "Property title cannot exceed 150 characters.";
        }

        $location = trim((string)($data['location'] ?? ''));
        if (empty($location)) {
            $errors[] = "City or region location is required.";
        }

        $address = trim((string)($data['address'] ?? ''));
        if (empty($address)) {
            $errors[] = "Physical property address is required.";
        }

        $rentalType = $data['rental_type'] ?? 'complete';
        if (!in_array($rentalType, ['complete', 'room_wise', 'both'])) {
            $errors[] = "Invalid booking mode selected.";
        }

        // Complete property price validation (required for complete & both modes)
        if ($rentalType === 'complete' || $rentalType === 'both') {
            $price = $data['price'] ?? null;
            if (!is_numeric($price) || (float)$price <= 0) {
                $errors[] = "Complete property rate per night must be a positive numeric value.";
            }
        }

        // If room-wise only, ensure room types are provided
        if ($rentalType === 'room_wise') {
            $roomTypes = $data['room_types'] ?? [];
            if (empty($roomTypes) || !is_array($roomTypes)) {
                // If updating and existing rooms already exist, this is fine; otherwise check
            }
        }

        if (isset($data['weekend_price']) && $data['weekend_price'] !== '' && (!is_numeric($data['weekend_price']) || (float)$data['weekend_price'] < 0)) {
            $errors[] = "Weekend price must be a non-negative numeric value.";
        }

        if (isset($data['security_deposit']) && $data['security_deposit'] !== '' && (!is_numeric($data['security_deposit']) || (float)$data['security_deposit'] < 0)) {
            $errors[] = "Security deposit must be a non-negative numeric value.";
        }

        $bedrooms = (int)($data['bedrooms'] ?? 0);
        if ($bedrooms < 1) {
            $errors[] = "Number of bedrooms must be at least 1.";
        }

        $dayCapacity = (int)($data['day_capacity'] ?? 0);
        if ($dayCapacity < 1) {
            $errors[] = "Day guest capacity must be at least 1.";
        }

        $nightCapacity = (int)($data['night_capacity'] ?? 0);
        if ($nightCapacity < 1) {
            $errors[] = "Overnight sleeping capacity must be at least 1.";
        }

        if (!empty($data['contact_phone'])) {
            $phone = preg_replace('/[^0-9+]/', '', (string)$data['contact_phone']);
            if (strlen($phone) < 7 || strlen($phone) > 16) {
                $errors[] = "Contact phone number format appears invalid.";
            }
        }

        return $errors;
    }

    /**
     * Handle multipart image uploads securely
     */
    private function handleUploads(?array $files): array {
        if (!$files || empty($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $uploaded = [];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxBytes = 5 * 1024 * 1024; // 5 MB

        $uploadDir = __DIR__ . '/../../../assets/images/uploads/farmhouses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $name = $files['name'][$i];
            $tmp  = $files['tmp_name'][$i];
            $size = $files['size'][$i];

            if ($size > $maxBytes) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMimes)) {
                continue;
            }

            $filename = 'farmhouse_' . uniqid() . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($tmp, $destination)) {
                $uploaded[] = 'assets/images/uploads/farmhouses/' . $filename;
            }
        }

        return $uploaded;
    }
}
