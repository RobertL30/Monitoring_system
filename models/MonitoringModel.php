<?php
class MonitoringModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function monitorDevice($device) {
        $result = ['success' => false, 'response_time' => null, 'packet_loss' => 0, 'error' => ''];
        
        // Choose monitoring method based on device type
        switch ($device['monitor_type']) {
            case 'ping':
                $result = $this->pingDevice($device['ip_address']);
                break;
            case 'http':
                $result = $this->httpCheck($device['ip_address'], false);
                break;
            case 'https':
                $result = $this->httpCheck($device['ip_address'], true);
                break;
            case 'tcp':
                $result = $this->tcpCheck($device['ip_address'], $device['port']);
                break;
            default:
                $result = ['success' => false, 'response_time' => null, 'packet_loss' => 0, 'error' => 'Unknown monitor type'];
        }
        
        $this->updateDeviceStatus($device, $result);
        $this->logMonitoringHistory($device['id'], $result);

        $status = $result['success'] ? 'operational' : 'down';
        $result['status'] = $status;  // ← ADD THIS LINE

        return $result;
    }
    
    private function pingDevice($ip) {
        $success_count = 0;
        $total_time = 0;
        $ping_count = 4;
        
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "ping -n $ping_count -w 3000 " . escapeshellarg($ip) . " 2>&1";
            $output = shell_exec($command);
            
            preg_match_all('/time[<=](\d+)ms/', $output, $matches);
            $success_count = count($matches[1]);
            $total_time = array_sum(array_map('intval', $matches[1]));
        } else {
            $command = "ping -c $ping_count -W 3 " . escapeshellarg($ip) . " 2>&1";
            $output = shell_exec($command);
            
            preg_match('/(\d+) received/', $output, $received);
            $success_count = isset($received[1]) ? intval($received[1]) : 0;
            
            preg_match_all('/time=(\d+\.?\d*)/', $output, $matches);
            $total_time = array_sum(array_map('floatval', $matches[1]));
        }
        
        $packet_loss = (($ping_count - $success_count) / $ping_count) * 100;
        $avg_time = $success_count > 0 ? round($total_time / $success_count, 2) : null;
        
        return [
            'success' => $success_count > 0,
            'response_time' => $avg_time,
            'packet_loss' => $packet_loss,
            'error' => $success_count === 0 ? 'Host unreachable' : null
        ];
    }
    
    private function httpCheck($ip, $https = false) {
        $protocol = $https ? 'https' : 'http';
        $url = "$protocol://$ip";
        
        $start = microtime(true);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'HEAD',
                'ignore_errors' => true,
                'user_agent' => 'Network-Monitor/1.0'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $result = @file_get_contents($url, false, $context);
        $response_time = (microtime(true) - $start) * 1000;
        
        $success = ($result !== false);
        
        return [
            'success' => $success,
            'response_time' => $success ? round($response_time) : null,
            'packet_loss' => 0,
            'error' => $success ? null : 'HTTP connection failed'
        ];
    }
    
    private function tcpCheck($ip, $port) {
        $start = microtime(true);
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
        $response_time = (microtime(true) - $start) * 1000;
        
        $success = is_resource($connection);
        if ($success) {
            fclose($connection);
        }
        
        return [
            'success' => $success,
            'response_time' => $success ? round($response_time) : null,
            'packet_loss' => 0,
            'error' => $success ? null : "Port $port not accessible: $errstr"
        ];
    }
    
    private function updateDeviceStatus($device, $result) {
        // Get current device status
        $stmt = $this->db->prepare("SELECT * FROM device_status WHERE device_id = ?");
        $stmt->execute([$device['id']]);
        $current_status = $stmt->fetch();
        
        if (!$current_status) {
            // First time monitoring
            $stmt = $this->db->prepare("
                INSERT INTO device_status (device_id, status, response_time, packet_loss, consecutive_failures, baseline_response, last_check, error_message)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
            ");
            $status = $result['success'] ? 'operational' : 'down';
            $failures = $result['success'] ? 0 : 1;
            $baseline = $result['success'] ? $result['response_time'] : 0;
            $stmt->execute([$device['id'], $status, $result['response_time'], $result['packet_loss'], $failures, $baseline, $result['error']]);
        } else {
            // Update existing status
            $failures = $result['success'] ? 0 : $current_status['consecutive_failures'] + 1;
            
            if ($result['success']) {
                $status = 'operational';
                if ($current_status['baseline_response'] > 0) {
                    $threshold = $current_status['baseline_response'] * 2.5;
                    if ($result['response_time'] > $threshold) {
                        $status = 'degraded';
                    }
                }
                
                $alpha = 0.1;
                $new_baseline = $current_status['baseline_response'] > 0 ? 
                    ($alpha * $result['response_time']) + ((1 - $alpha) * $current_status['baseline_response']) :
                    $result['response_time'];
            } else {
                $status = ($failures >= 3) ? 'down' : $current_status['status'];
                $new_baseline = $current_status['baseline_response'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE device_status 
                SET status = ?, response_time = ?, packet_loss = ?, consecutive_failures = ?, 
                    baseline_response = ?, last_check = NOW(), error_message = ?,
                    last_success = CASE WHEN ? = 1 THEN NOW() ELSE last_success END,
                    last_failure = CASE WHEN ? = 0 THEN NOW() ELSE last_failure END
                WHERE device_id = ?
            ");
            $stmt->execute([
                $status, $result['response_time'], $result['packet_loss'], $failures, 
                $new_baseline, $result['error'], $result['success'] ? 1 : 0, 
                $result['success'] ? 1 : 0, $device['id']
            ]);
        }
    }
    
    private function logMonitoringHistory($deviceId, $result) {
        $stmt = $this->db->prepare("
            INSERT INTO monitoring_history (device_id, success, response_time, packet_loss, error_message)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$deviceId, $result['success'] ? 1 : 0, $result['response_time'], $result['packet_loss'], $result['error']]);
        
        // Clean old history
        $stmt = $this->db->prepare("
            DELETE FROM monitoring_history 
            WHERE device_id = ? AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM monitoring_history WHERE device_id = ? ORDER BY timestamp DESC LIMIT 10000
                ) tmp
            )
        ");
        $stmt->execute([$deviceId, $deviceId]);
    }
}