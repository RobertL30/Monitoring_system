<?php
session_start();
require_once 'config/config.php';
require_once 'models/Database.php';
require_once 'models/UserModel.php';
require_once 'models/DeviceModel.php';
require_once 'models/MonitoringModel.php';
require_once 'models/AlertModel.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/DeviceController.php';
require_once 'controllers/ReportController.php';

// Initialize database
$db = Database::getInstance();

// Get current page and action
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

// Handle logout
if (isset($_GET['logout'])) {
    AuthController::logout();
}

// Check session timeout
AuthController::checkSessionTimeout();

// Route requests
if ($page === 'login') {
    $controller = new AuthController();
    $controller->login();
} else {
    // Require authentication for all other pages
    AuthController::requireLogin();

    // Handle AJAX actions
    if ($action) {
        header('Content-Type: application/json');

        switch ($action) {
            case 'monitor_all':
                $controller = new DeviceController();
                $controller->monitorAll();
                break;
            case 'get_dashboard_data':
                $controller = new DashboardController();
                $controller->getDashboardData();
                break;
            case 'add_device':
                $controller = new DeviceController();
                $controller->addDevice();
                break;
            case 'delete_device':
                $controller = new DeviceController();
                $controller->deleteDevice();
                break;
            case 'get_device_history':
                $controller = new DeviceController();
                $controller->getDeviceHistory();
                break;
            case 'monitor_single_device':
                $controller = new DeviceController();
                $controller->monitorSingleDevice();
                break;
            case 'edit_device':
                $controller = new DeviceController();
                $controller->editDevice();
                break;
            // Reports API endpoints - using unique names
            case 'get_device_history_report':
                $controller = new ReportController();
                $controller->getDeviceHistory();
                break;
            case 'get_device_stats':
                $controller = new ReportController();
                $controller->getDeviceStats();
                break;
            case 'get_category_stats':
                $controller = new ReportController();
                $controller->getCategoryStats();
                break;
            case 'get_system_overview':
                $controller = new ReportController();
                $controller->getSystemOverview();
                break;
            case 'debug_info':
                $controller = new ReportController();
                $controller->debugInfo();
                break;
            default:
                echo json_encode(['error' => 'Unknown action: ' . $action]);
        }
        exit;
    }

    // Route to appropriate page controller
    switch ($page) {
        case 'dashboard':
            $controller = new DashboardController();
            $controller->index();
            break;
        case 'devices':
            $controller = new DeviceController();
            $controller->index();
            break;
        case 'reports':
            $controller = new ReportController();
            $controller->index();
            break;
        default:
            $controller = new DashboardController();
            $controller->index();
    }
}