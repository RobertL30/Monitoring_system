
<?php
class DashboardController {
    private $deviceModel;
    private $alertModel;
    
    public function __construct() {
        $this->deviceModel = new DeviceModel();
        $this->alertModel = new AlertModel();
    }
    
    public function index() {
        require_once 'views/dashboard/index.php';
    }
    
    public function getDashboardData() {
        $devices = $this->deviceModel->getAllDevicesWithStatus();
        
        // Group devices by category
        $grouped_devices = [];
        foreach ($devices as $device) {
            $group = $device['device_group'];
            if (!isset($grouped_devices[$group])) {
                $grouped_devices[$group] = [];
            }
            $grouped_devices[$group][] = $device;
        }
        
        // Calculate statistics
        $total = count($devices);
        $operational = count(array_filter($devices, fn($d) => $d['status'] === 'operational'));
        $degraded = count(array_filter($devices, fn($d) => $d['status'] === 'degraded'));
        $down = count(array_filter($devices, fn($d) => $d['status'] === 'down'));
        
        // Get recent alerts
        $alerts = $this->alertModel->getUnacknowledgedAlerts();
        
        echo json_encode([
            'devices' => $grouped_devices,
            'device_groups' => Config::$device_groups,
            'stats' => [
                'total' => $total,
                'operational' => $operational,
                'degraded' => $degraded,
                'down' => $down
            ],
            'alerts' => $alerts,
            'last_updated' => date('Y-m-d H:i:s'),
            'user' => [
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ]
        ]);
    }
}