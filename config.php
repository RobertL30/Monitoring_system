<?php
// config.php - Main configuration file
$config = [
    // MySQL Database Connection - EDIT THESE VALUES
    'db_host' => 'localhost',
    'db_name' => 'network_monitor',
    'db_user' => 'root',
    'db_pass' => 'Pa55w0rD.2014.isaac,,',
    'db_port' => 3306,
    
    // Security settings
    'session_timeout' => 3600, // 1 hour
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes
    
    // Monitoring settings
    'ping_timeout' => 5,
    'monitor_interval' => 300, // 5 minutes
    'max_history_per_device' => 10000,
];

// Database connection function
function getDatabase() {
    global $config;
    
    try {
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
        $db = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage() . 
            "<br><br>Check your MySQL settings in config.php");
    }
}

// Device groups
$device_groups = [
    'routers' => 'Routers',
    'core_infrastructure' => 'Core Infrastructure',
    'switches' => 'Switches', 
    'servers' => 'Servers',
    'systems' => 'Systems',
    'wireless' => 'Wireless Equipment',
    'security' => 'Security Devices',
    'printers' => 'Printers & IoT'
];

// Monitor types
$monitor_types = [
    'ping' => 'ICMP Ping',
    'http' => 'HTTP Check',
    'https' => 'HTTPS Check',
    'tcp' => 'TCP Port Check'
];
?>