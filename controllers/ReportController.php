<?php
class ReportController {
    private $deviceModel;
    private $db;

    public function __construct() {
        $this->deviceModel = new DeviceModel();
        $this->db = Database::getInstance();
    }

    public function index() {
        require_once 'views/reports/index.php';
    }

    public function getDeviceHistory() {
        $device_id = $_GET['device_id'] ?? 0;
        $hours = $_GET['hours'] ?? 24;

        if (!$device_id) {
            echo json_encode(['error' => 'Device ID required']);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    timestamp,
                    success,
                    response_time,
                    packet_loss,
                    error_message
                FROM monitoring_history 
                WHERE device_id = ? 
                AND timestamp > DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY timestamp ASC
            ");
            $stmt->execute([$device_id, $hours]);
            $history = $stmt->fetchAll();

            // Process data for charts
            $processedData = [
                'timestamps' => [],
                'responseTime' => [],
                'availability' => [],
                'packetLoss' => [],
                'events' => []
            ];

            foreach ($history as $record) {
                $processedData['timestamps'][] = $record['timestamp'];
                $processedData['responseTime'][] = $record['response_time'] ? (float)$record['response_time'] : null;
                $processedData['availability'][] = $record['success'] ? 1 : 0;
                $processedData['packetLoss'][] = (float)$record['packet_loss'];

                // Create event if there was a status change or error
                if (!$record['success'] || $record['error_message']) {
                    $processedData['events'][] = [
                        'timestamp' => $record['timestamp'],
                        'message' => $record['error_message'] ?: 'Device offline',
                        'status' => $record['success'] ? 'operational' : 'down'
                    ];
                }
            }

            echo json_encode($processedData);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getDeviceStats() {
        $device_id = $_GET['device_id'] ?? 0;
        $hours = $_GET['hours'] ?? 24;

        if (!$device_id) {
            echo json_encode(['error' => 'Device ID required']);
            return;
        }

        try {
            // Get device basic info
            $device = $this->deviceModel->getDeviceById($device_id);
            if (!$device) {
                echo json_encode(['error' => 'Device not found']);
                return;
            }

            // Get current status
            $stmt = $this->db->prepare("
                SELECT * FROM device_status WHERE device_id = ?
            ");
            $stmt->execute([$device_id]);
            $currentStatus = $stmt->fetch();

            // Calculate statistics from monitoring history
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_checks,
                    SUM(success) as successful_checks,
                    AVG(CASE WHEN success = 1 THEN response_time END) as avg_response_time,
                    MIN(CASE WHEN success = 1 THEN response_time END) as min_response_time,
                    MAX(CASE WHEN success = 1 THEN response_time END) as max_response_time,
                    AVG(packet_loss) as avg_packet_loss,
                    COUNT(CASE WHEN success = 0 THEN 1 END) as failed_checks
                FROM monitoring_history 
                WHERE device_id = ? 
                AND timestamp > DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$device_id, $hours]);
            $stats = $stmt->fetch();

            // Calculate uptime percentage
            $uptime = $stats['total_checks'] > 0 ?
                ($stats['successful_checks'] / $stats['total_checks']) * 100 : 0;

            // Get recent events (last 10)
            $stmt = $this->db->prepare("
                SELECT 
                    timestamp,
                    success,
                    error_message,
                    response_time
                FROM monitoring_history 
                WHERE device_id = ? 
                ORDER BY timestamp DESC 
                LIMIT 10
            ");
            $stmt->execute([$device_id]);
            $recentEvents = $stmt->fetchAll();

            // Process events for timeline
            $events = [];
            $lastStatus = null;

            foreach (array_reverse($recentEvents) as $event) {
                $currentEventStatus = $event['success'] ? 'operational' : 'down';

                // Only add event if status changed or there's an error
                if ($lastStatus !== $currentEventStatus || $event['error_message']) {
                    $message = $event['success'] ?
                        'Device came back online' . ($event['response_time'] ? " ({$event['response_time']}ms)" : '') :
                        'Device went offline' . ($event['error_message'] ? " - {$event['error_message']}" : '');

                    $events[] = [
                        'timestamp' => $event['timestamp'],
                        'message' => $message,
                        'status' => $currentEventStatus
                    ];
                }
                $lastStatus = $currentEventStatus;
            }

            $result = [
                'device' => $device,
                'currentStatus' => $currentStatus,
                'stats' => [
                    'total_checks' => (int)$stats['total_checks'],
                    'successful_checks' => (int)$stats['successful_checks'],
                    'failed_checks' => (int)$stats['failed_checks'],
                    'uptime_percentage' => round($uptime, 2),
                    'avg_response_time' => $stats['avg_response_time'] ? round($stats['avg_response_time'], 2) : null,
                    'min_response_time' => $stats['min_response_time'] ? round($stats['min_response_time'], 2) : null,
                    'max_response_time' => $stats['max_response_time'] ? round($stats['max_response_time'], 2) : null,
                    'avg_packet_loss' => round($stats['avg_packet_loss'], 2)
                ],
                'events' => $events,
                'timeRange' => $hours
            ];

            echo json_encode($result);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getCategoryStats() {
        $category = $_GET['category'] ?? '';
        $hours = $_GET['hours'] ?? 24;

        if (!$category) {
            echo json_encode(['error' => 'Category required']);
            return;
        }

        try {
            // Get all devices in category with their current status
            $stmt = $this->db->prepare("
                SELECT 
                    d.id,
                    d.name,
                    d.ip_address,
                    d.location,
                    d.monitor_type,
                    d.port,
                    d.critical_device,
                    ds.status,
                    ds.response_time,
                    ds.packet_loss,
                    ds.last_check,
                    ds.consecutive_failures
                FROM devices d
                LEFT JOIN device_status ds ON d.id = ds.device_id
                WHERE d.device_group = ? AND d.enabled = 1
                ORDER BY d.name
            ");
            $stmt->execute([$category]);
            $devices = $stmt->fetchAll();

            // Calculate category-wide statistics
            $categoryStats = [
                'total_devices' => count($devices),
                'operational' => 0,
                'degraded' => 0,
                'down' => 0,
                'unknown' => 0,
                'avg_response_time' => 0,
                'critical_issues' => 0
            ];

            $totalResponseTime = 0;
            $responseTimeCount = 0;

            foreach ($devices as $device) {
                $status = $device['status'] ?: 'unknown';
                $categoryStats[$status]++;

                if ($device['response_time']) {
                    $totalResponseTime += $device['response_time'];
                    $responseTimeCount++;
                }

                if ($device['critical_device'] && $status !== 'operational') {
                    $categoryStats['critical_issues']++;
                }
            }

            $categoryStats['avg_response_time'] = $responseTimeCount > 0 ?
                round($totalResponseTime / $responseTimeCount, 2) : 0;

            $categoryStats['uptime_percentage'] = $categoryStats['total_devices'] > 0 ?
                round(($categoryStats['operational'] / $categoryStats['total_devices']) * 100, 2) : 0;

            echo json_encode([
                'devices' => $devices,
                'stats' => $categoryStats,
                'category' => $category,
                'timeRange' => $hours
            ]);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getSystemOverview() {
        try {
            // Get overall system stats
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_devices,
                    SUM(CASE WHEN ds.status = 'operational' THEN 1 ELSE 0 END) as operational,
                    SUM(CASE WHEN ds.status = 'degraded' THEN 1 ELSE 0 END) as degraded,
                    SUM(CASE WHEN ds.status = 'down' THEN 1 ELSE 0 END) as down,
                    SUM(CASE WHEN ds.status IS NULL OR ds.status = 'unknown' THEN 1 ELSE 0 END) as unknown,
                    AVG(CASE WHEN ds.status = 'operational' THEN ds.response_time END) as avg_response_time,
                    SUM(CASE WHEN d.critical_device = 1 AND ds.status != 'operational' THEN 1 ELSE 0 END) as critical_issues
                FROM devices d
                LEFT JOIN device_status ds ON d.id = ds.device_id
                WHERE d.enabled = 1
            ");
            $systemStats = $stmt->fetch();

            // Get category breakdown
            $stmt = $this->db->query("
                SELECT 
                    d.device_group,
                    COUNT(*) as device_count,
                    SUM(CASE WHEN ds.status = 'operational' THEN 1 ELSE 0 END) as operational,
                    SUM(CASE WHEN ds.status = 'degraded' THEN 1 ELSE 0 END) as degraded,
                    SUM(CASE WHEN ds.status = 'down' THEN 1 ELSE 0 END) as down
                FROM devices d
                LEFT JOIN device_status ds ON d.id = ds.device_id
                WHERE d.enabled = 1
                GROUP BY d.device_group
            ");
            $categoryBreakdown = $stmt->fetchAll();

            // Get recent system-wide response times for trending
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(timestamp, '%H:00') as hour,
                    AVG(response_time) as avg_response_time
                FROM monitoring_history 
                WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND success = 1
                GROUP BY DATE_FORMAT(timestamp, '%Y-%m-%d %H')
                ORDER BY hour
            ");
            $responseTrends = $stmt->fetchAll();

            echo json_encode([
                'systemStats' => $systemStats,
                'categoryBreakdown' => $categoryBreakdown,
                'responseTrends' => $responseTrends
            ]);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}