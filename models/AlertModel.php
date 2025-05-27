<?php
class AlertModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUnacknowledgedAlerts($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT a.*, d.name as device_name 
            FROM alerts a
            JOIN devices d ON a.device_id = d.id
            WHERE a.acknowledged = 0
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}