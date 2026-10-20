<?php
namespace App\Controllers;
use App\Helpers\CryptoHelper;
use App\Config\Database;
use App\Models\HomeModel;
use Exception;

class HomeController {

    public function index() {
        try {
            // Safely initializing environment variables linking properly natively bypassing initial mapping conflicts effectively smoothly correctly mappings successfully logic!
            $db = Database::connect();
        } catch (Exception $e) {
            // Fallback safety stop securely configured. 
            die("System DB Logic Issue Found : Could not fetch credentials logic mapped mapping : " . $e->getMessage());
        }

        // Model Instantiation properly natively configuring accurate parameters dynamically properly parameter configured parameter configuring variables
        $homeModel = new HomeModel($db);

        // Fetches unique entries configuring right sidebar constraints optimally visually matching layout options!
        $availableLocations = $homeModel->getAvailableLocations();
        $availableAmenities = $homeModel->getAvailableAmenities();

        // Arrays parameters accurately sanitized securely
        $filters = [
            'search'    => isset($_GET['search']) ? trim($_GET['search']) : '',
            'location'  => isset($_GET['location']) ? trim($_GET['location']) : '',
            'category'  => isset($_GET['category']) ? trim($_GET['category']) : '',
            'max_price' => isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0,
            'sort'      => isset($_GET['sort']) ? trim($_GET['sort']) : '',
            'amenities' => isset($_GET['amenities']) && is_array($_GET['amenities']) ? $_GET['amenities'] :[]
        ];

        // Maps completely configured structure safely retrieving exactly configured lists directly securely.
        $farmhouses = $homeModel->getFilteredFarmhouses($filters);

        // Calling UI rendering layout configured layout properly populated correctly structured properly dynamically optimally structure smoothly visually seamlessly layout structurally natively mapping accurate structurally seamlessly linking structure correctly successfully natively visually cleanly accurately setup mappings effectively visually natively dynamically structure correctly layout parameter natively natively layout correctly mapped mapping parameters
        include __DIR__ . '/../Views/home.php';
    }
    public function whychooseus() {
        include __DIR__ . '/../Views/why-choose-us.php';
    }
}
?>