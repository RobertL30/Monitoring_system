<?php
class DeviceController {
    private $deviceModel;
    private $monitoringModel;

    public function monitorSingleDevice() {
        $device_id = $_POST['device_id'] ?? 0;

        if (!$device_id) {
            echo json_encode(['success' => false, 'error' => 'No device ID provided']);
            return;
        }

        // Get device details
        $device = $this->deviceModel->getDeviceById($device_id);
        if (!$device) {
            echo json_encode(['success' => false, 'error' => 'Device not found']);
            return;
        }

        // Monitor the device
        $result = $this->monitoringModel->monitorDevice($device);

        echo json_encode($result);
    }
    public function editDevice() {
        AuthController::requireAdmin();

        $device_id = $_POST['device_id'] ?? 0;

        if (!$device_id) {
            echo json_encode(['success' => false, 'message' => 'Device ID is required']);
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'ip_address' => trim($_POST['ip_address'] ?? ''),
            'device_group' => $_POST['device_group'] ?? '',
            'monitor_type' => $_POST['monitor_type'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'critical_device' => isset($_POST['critical_device']) ? 1 : 0,
            'port' => null
        ];

        if (!empty($_POST['port']) && is_numeric($_POST['port'])) {
            $data['port'] = (int)$_POST['port'];
        }

        // Validate required fields
        if (empty($data['name']) || empty($data['ip_address']) || empty($data['device_group']) || empty($data['monitor_type'])) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            return;
        }

        try {
            $result = $this->deviceModel->updateDevice($device_id, $data);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Device updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update device']);
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'message' => 'IP address already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
    }
    
    public function __construct() {
        $this->deviceModel = new DeviceModel();
        $this->monitoringModel = new MonitoringModel();
    }
    
    public function index() {
        require_once 'views/devices/index.php';
    }
    
    public function monitorAll() {
        $devices = $this->deviceModel->getEnabledDevices();
        
        $results = [];
        foreach ($devices as $device) {
            $result = $this->monitoringModel->monitorDevice($device);
            $results[] = [
                'device' => $device['name'],
                'status' => $result['status'] ?? 'unknown',
                'response_time' => $result['response_time']
            ];
        }
        
        echo json_encode(['results' => $results, 'total' => count($devices)]);
    }
    
    public function addDevice() {
        AuthController::requireAdmin();
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'ip_address' => trim($_POST['ip_address'] ?? ''),
            'device_group' => $_POST['device_group'] ?? '',
            'monitor_type' => $_POST['monitor_type'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'critical_device' => isset($_POST['critical_device']) ? 1 : 0,
            'port' => null
        ];
        
        if (!empty($_POST['port']) && is_numeric($_POST['port'])) {
            $data['port'] = (int)$_POST['port'];
        }
        
        // Validate required fields
        if (empty($data['name']) || empty($data['ip_address']) || empty($data['device_group']) || empty($data['monitor_type'])) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            return;
        }
        
        try {
            $result = $this->deviceModel->addDevice($data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Device added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to insert device']);
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'message' => 'IP address already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
    }
    
    public function deleteDevice() {
        AuthController::requireAdmin();
        
        $device_id = $_POST['device_id'] ?? 0;
        
        try {
            $result = $this->deviceModel->deleteDevice($device_id);
            echo json_encode(['success' => true, 'message' => 'Device deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function getDeviceHistory() {
        $device_id = $_GET['device_id'] ?? 0;
        $hours = $_GET['hours'] ?? 24;
        
        $history = $this->deviceModel->getDeviceHistory($device_id, $hours);
        echo json_encode($history);
    }
}