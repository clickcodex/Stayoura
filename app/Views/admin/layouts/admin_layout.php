<?php
/**
 * Stayora Admin Master Layout
 * Combines header, sidebar, page content slot, and footer into one cohesive layout.
 *
 * Usage:
 *   $pageTitle = "Page Title";
 *   $currentPage = "properties";
 *   require_once __DIR__ . '/header.php';
 *   // Page content here...
 *   require_once __DIR__ . '/footer.php';
 *
 * Or using admin_layout with a view file:
 *   renderAdminLayout('properties/index', $data);
 */

function renderAdminLayout(string $viewPath, array $data = []): void {
    extract($data);
    require __DIR__ . '/header.php';
    $fullViewPath = __DIR__ . '/../' . ltrim($viewPath, '/') . '.php';
    if (file_exists($fullViewPath)) {
        require $fullViewPath;
    } else {
        echo "<div class='p-8 text-rose-500 font-bold'>View not found: " . htmlspecialchars($fullViewPath) . "</div>";
    }
    require __DIR__ . '/footer.php';
}
