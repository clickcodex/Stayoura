<?php
namespace App\Controllers\Admin;

use App\Models\Admin\AdminModel;

class DashboardController {
    protected AdminModel $adminModel;

    public function __construct() {
        $this->adminModel = new AdminModel();
    }

    /**
     * Display Admin Executive Dashboard
     */
    public function dashboard(): void {
        // ── 1. Strict Server-Side Single-Active-Session Access Control Guard ──
        $admin = \App\Helpers\AdminAuth::requireAuth();

        // ── 2. Query Real Business KPIs & Trends ──
        $kpis             = $this->adminModel->getDashboardKPIs();
        $recentBookings   = $this->adminModel->getRecentBookings(6);
        $recentFarmhouses = $this->adminModel->getRecentFarmhouses(6);
        $chartData        = $this->adminModel->getMonthlyChartData();
        $categoryDist     = $this->adminModel->getCategoryDistribution();

        $pageTitle = "Admin Dashboard • Stayora Executive Console";

        require_once __DIR__ . '/../../Views/admin/dashboard.php';
    }
}
