<?php
// monitor_background.php - Background monitoring script
require_once 'config.php';
require_once 'monitoring_functions.php';
 
// Prevent web access
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script must be run from command line only.');
}
 
echo "Network Monitor Background Service\n";
echo "==================================\n\n";
 
try {
    $db = getDatabase();
    echo "Database connection: OK\n";
    
    // Get all enabled devices
    $stmt = $db->query("SELECT * FROM devices WHERE enabled = 1");
    $devices = $stmt->fetchAll();
    
    if (empty($devices)) {
        echo "No devices found to monitor.\n";
        exit(0);
    }
    
    echo "Found " . count($devices) . " devices to monitor\n\n";
    
    foreach ($devices as $device) {
        echo "Checking {$device['name']} ({$device['ip_address']})... ";
        
        try {
            $result = monitorDevice($device, $db);
            
            if ($result['success']) {
                echo "OK - {$result['response_time']}ms\n";
            } else {
                echo "FAILED - {$result['error']}\n";
            }
        } catch (Exception $e) {
            echo "ERROR - {$e->getMessage()}\n";
        }
        
        // Small delay between checks
        usleep(500000); // 0.5 seconds
    }
    
    echo "\nMonitoring completed at " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>