<?php
class DeviceModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
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

    public function updateDevice($deviceId, $data) {
        $stmt = $this->db->prepare("
            UPDATE devices 
            SET name = ?, ip_address = ?, device_group = ?, monitor_type = ?,
                port = ?, location = ?, description = ?, critical_device = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['name'], $data['ip_address'], $data['device_group'],
            $data['monitor_type'], $data['port'], $data['location'],
            $data['description'], $data['critical_device'], $deviceId
        ]);
    }

    public function deleteDevice($deviceId) {
        // Start transaction to ensure data integrity
        $this->db->beginTransaction();

        try {
            // Delete related monitoring history
            $stmt = $this->db->prepare("DELETE FROM monitoring_history WHERE device_id = ?");
            $stmt->execute([$deviceId]);

            // Delete device status
            $stmt = $this->db->prepare("DELETE FROM device_status WHERE device_id = ?");
            $stmt->execute([$deviceId]);

            // Delete any alerts for this device
            $stmt = $this->db->prepare("DELETE FROM alerts WHERE device_id = ?");
            $stmt->execute([$deviceId]);

            // Finally delete the device itself
            $stmt = $this->db->prepare("DELETE FROM devices WHERE id = ?");
            $result = $stmt->execute([$deviceId]);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
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

    public function getDeviceCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM devices WHERE enabled = 1");
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getDevicesByGroup() {
        $stmt = $this->db->query("
            SELECT device_group, COUNT(*) as count 
            FROM devices 
            WHERE enabled = 1 
            GROUP BY device_group
        ");
        return $stmt->fetchAll();
    }

    public function searchDevices($searchTerm) {
        $searchTerm = '%' . $searchTerm . '%';
        $stmt = $this->db->prepare("
            SELECT d.*, ds.status, ds.response_time, ds.last_check
            FROM devices d
            LEFT JOIN device_status ds ON d.id = ds.device_id
            WHERE d.enabled = 1 
            AND (d.name LIKE ? OR d.ip_address LIKE ? OR d.location LIKE ? OR d.description LIKE ?)
            ORDER BY d.name
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function getDeviceStatistics() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ds.status = 'operational' THEN 1 ELSE 0 END) as operational,
                SUM(CASE WHEN ds.status = 'degraded' THEN 1 ELSE 0 END) as degraded,
                SUM(CASE WHEN ds.status = 'down' THEN 1 ELSE 0 END) as down,
                SUM(CASE WHEN ds.status IS NULL OR ds.status = 'unknown' THEN 1 ELSE 0 END) as unknown
            FROM devices d
            LEFT JOIN device_status ds ON d.id = ds.device_id
            WHERE d.enabled = 1
        ");
        return $stmt->fetch();
    }
}