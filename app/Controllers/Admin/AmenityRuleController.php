<?php
namespace App\Controllers\Admin;

use App\Helpers\AdminAuth;
use App\Models\Admin\PropertyModel;

class AmenityRuleController {
    protected PropertyModel $propertyModel;

    public function __construct() {
        $this->propertyModel = new PropertyModel();
    }

    /**
     * Dedicated Admin Hub for Amenities & Property Rules Management
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();

        // 1. Fetch Master Amenities with Usage Counts
        $amenities = $this->propertyModel->getAllAmenitiesList();

        // 2. Fetch Master Rule Presets with Usage Counts & Descriptions
        $rulePresets = $this->propertyModel->getRulePresetsWithStats();

        // 3. Compute KPI Metrics
        $totalAmenities     = count($amenities);
        $totalRules         = count($rulePresets);
        $totalAmenityUses   = array_sum(array_column($amenities, 'usage_count'));
        $totalRuleUses      = array_sum(array_column($rulePresets, 'usage_count'));

        // Group Amenities by Category for quick filtering / statistics
        $amenitiesByCategory = [];
        foreach ($amenities as $am) {
            $cat = !empty($am['category']) ? strtolower($am['category']) : 'general';
            if (!isset($amenitiesByCategory[$cat])) {
                $amenitiesByCategory[$cat] = [];
            }
            $amenitiesByCategory[$cat][] = $am;
        }

        // Active tab from query param
        $activeTab = in_array($_GET['tab'] ?? '', ['amenities', 'rules']) ? $_GET['tab'] : 'amenities';

        $pageTitle   = "Amenities & Rules Management • Stayora Executive Console";
        $currentPage = "amenities_rules";
        $headerTitle = "Amenities & Rules Management";

        require_once __DIR__ . '/../../Views/admin/amenities_rules/index.php';
    }

    /**
     * Save Amenity (Create or Update) via AJAX
     */
    public function saveAmenity(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $name      = trim((string)($_POST['name'] ?? ''));
        $category  = strtolower(trim((string)($_POST['category'] ?? 'general')));
        $iconClass = trim((string)($_POST['icon_class'] ?? 'check_circle'));

        if (empty($name)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Amenity name is required.']);
            return;
        }

        if (strlen($name) < 2 || strlen($name) > 50) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Amenity name must be between 2 and 50 characters.']);
            return;
        }

        $validCategories = ['general', 'bedroom', 'kitchen', 'leisure', 'wellness', 'technology', 'dining', 'outdoor'];
        if (!in_array($category, $validCategories)) {
            $category = 'general';
        }

        if (empty($iconClass)) {
            $iconClass = 'check_circle';
        }

        // Duplicate check (case-insensitive)
        if ($this->propertyModel->checkAmenityNameExists($name, $id > 0 ? $id : null)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => "An amenity named \"{$name}\" already exists in the catalog."]);
            return;
        }

        if ($id > 0) {
            // Update
            $ok = $this->propertyModel->updateAmenity($id, $name, $category, $iconClass);
            if ($ok) {
                $usageCount = $this->propertyModel->getAmenityUsageCount($id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Amenity updated successfully.',
                    'action'  => 'update',
                    'amenity' => [
                        'id'          => $id,
                        'name'        => $name,
                        'category'    => $category,
                        'icon_class'  => $iconClass,
                        'usage_count' => $usageCount
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update amenity.']);
            }
        } else {
            // Create
            $newId = $this->propertyModel->addCustomAmenity($name, $category, $iconClass);
            if ($newId > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'New amenity added successfully.',
                    'action'  => 'create',
                    'amenity' => [
                        'id'          => $newId,
                        'name'        => $name,
                        'category'    => $category,
                        'icon_class'  => $iconClass,
                        'usage_count' => 0
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create new amenity.']);
            }
        }
    }

    /**
     * Get single Amenity details by ID via AJAX
     */
    public function getAmenity(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid amenity ID.']);
            return;
        }

        $amenity = $this->propertyModel->getAmenityById($id);
        if (!$amenity) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Amenity not found.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'amenity' => $amenity
        ]);
    }

    /**
     * Delete an Amenity and unlink safely via AJAX
     */
    public function deleteAmenity(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid amenity ID.']);
            return;
        }

        $amenity = $this->propertyModel->getAmenityById($id);
        if (!$amenity) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Amenity not found.']);
            return;
        }

        $usageCount = (int)($amenity['usage_count'] ?? 0);
        $ok = $this->propertyModel->deleteAmenity($id);

        if ($ok) {
            $msg = $usageCount > 0 
                ? "Amenity deleted and successfully unlinked from {$usageCount} " . ($usageCount === 1 ? 'property.' : 'properties.') 
                : "Amenity deleted successfully.";
            echo json_encode([
                'success'     => true,
                'message'     => $msg,
                'id'          => $id,
                'usage_count' => $usageCount
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete amenity.']);
        }
    }

    /**
     * Save Property Rule Preset (Create or Update) via AJAX
     */
    public function saveRule(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $ruleName    = trim((string)($_POST['rule_name'] ?? $_POST['name'] ?? ''));
        $iconClass   = trim((string)($_POST['icon_class'] ?? 'gavel'));
        $description = trim((string)($_POST['description'] ?? ''));

        if (empty($ruleName)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Rule name is required.']);
            return;
        }

        if (strlen($ruleName) < 2 || strlen($ruleName) > 100) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Rule name must be between 2 and 100 characters.']);
            return;
        }

        if (empty($iconClass)) {
            $iconClass = 'gavel';
        }

        // Duplicate check (case-insensitive)
        if ($this->propertyModel->checkRulePresetNameExists($ruleName, $id > 0 ? $id : null)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => "A rule preset named \"{$ruleName}\" already exists."]);
            return;
        }

        if ($id > 0) {
            // Update
            $ok = $this->propertyModel->updateRulePreset($id, $ruleName, $iconClass, $description);
            if ($ok) {
                $usageCount = $this->propertyModel->getRulePresetUsageCount($ruleName);
                echo json_encode([
                    'success' => true,
                    'message' => 'Rule preset updated successfully.',
                    'action'  => 'update',
                    'rule'    => [
                        'id'          => $id,
                        'rule_name'   => $ruleName,
                        'icon_class'  => $iconClass,
                        'description' => $description,
                        'usage_count' => $usageCount
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update rule preset.']);
            }
        } else {
            // Create
            $newId = $this->propertyModel->addRulePreset($ruleName, $iconClass, $description);
            if ($newId > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'New rule preset added successfully.',
                    'action'  => 'create',
                    'rule'    => [
                        'id'          => $newId,
                        'rule_name'   => $ruleName,
                        'icon_class'  => $iconClass,
                        'description' => $description,
                        'usage_count' => 0
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create rule preset.']);
            }
        }
    }

    /**
     * Get single Rule Preset details by ID via AJAX
     */
    public function getRule(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid rule preset ID.']);
            return;
        }

        $rule = $this->propertyModel->getRulePresetById($id);
        if (!$rule) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Rule preset not found.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'rule'    => $rule
        ]);
    }

    /**
     * Delete a Rule Preset via AJAX
     */
    public function deleteRule(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid rule preset ID.']);
            return;
        }

        $rule = $this->propertyModel->getRulePresetById($id);
        if (!$rule) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Rule preset not found.']);
            return;
        }

        $usageCount = (int)($rule['usage_count'] ?? 0);
        $ok = $this->propertyModel->deleteRulePreset($id);

        if ($ok) {
            $msg = $usageCount > 0
                ? "Rule preset removed from master catalog. (Note: {$usageCount} existing " . ($usageCount === 1 ? 'property retains' : 'properties retain') . " this custom policy)."
                : "Rule preset deleted successfully.";
            echo json_encode([
                'success'     => true,
                'message'     => $msg,
                'id'          => $id,
                'usage_count' => $usageCount
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete rule preset.']);
        }
    }
}
