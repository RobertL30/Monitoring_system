
<?php
class DeviceModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function updateDevice($deviceId, $data) {
        // Copy the updateDevice method from the third artifact
    }

    public function getDeviceById($deviceId) {
        $stmt = $this->db->prepare("SELECT * FROM devices WHERE id = ? AND enabled = 1");
        $stmt->execute([$deviceId]);
        return $stmt->fetch();
    }
    
    public function getAllDevicesWithStatus() {
        $stmt = $this->db->query("
            SELECT d.*, ds.status, ds.response_time, ds.packet_loss, ds.consecutive_failures, 
                   ds.baseline_response, ds.last_check, ds.error_message
            FROM devices d
            LEFT JOIN device_status ds ON d.id = ds.device_id
            WHERE d.enabled = 1
            ORDER BY d.device_group, d.name
        ");
        return $stmt->fetchAll();
    }
    
    public function getEnabledDevices() {
        $stmt = $this->db->query("SELECT * FROM devices WHERE enabled = 1");
        return $stmt->fetchAll();
    }
    
    public function addDevice($data) {
        $stmt = $this->db->prepare("
            INSERT INTO devices
            SET name = ?, ip_address = ?, device_group = ?, monitor_type = ?,
                port = ?, location = ?, description = ?, critical_device = ?,
                enabled = 1, created_at = NOW(), updated_at = NOW()
        ");
        
        return $stmt->execute([
            $data['name'], $data['ip_address'], $data['device_group'], 
            $data['monitor_type'], $data['port'], $data['location'], 
            $data['description'], $data['critical_device']
        ]);
    }
    
    public function deleteDevice($deviceId) {
        $stmt = $this->db->prepare("DELETE FROM devices WHERE id = ?");
        return $stmt->execute([$deviceId]);
    }
    
    public function getDeviceHistory($deviceId, $hours = 24) {
        $stmt = $this->db->prepare("
            SELECT timestamp, success, response_time, packet_loss
            FROM monitoring_history 
            WHERE device_id = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ? HOUR)
            ORDER BY timestamp ASC
        ");
        $stmt->execute([$deviceId, $hours]);
        return $stmt->fetchAll();
    }
}