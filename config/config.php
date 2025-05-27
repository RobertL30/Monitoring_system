<?php
class Config {
    public static $config = [
        // Database settings
        'db_host' => 'localhost',
        'db_name' => 'network_monitor',
        'db_user' => 'root',
        'db_pass' => '',
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
    
    // Device groups
    public static $device_groups = [
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
    public static $monitor_types = [
        'ping' => 'ICMP Ping',
        'http' => 'HTTP Check',
        'https' => 'HTTPS Check',
        'tcp' => 'TCP Port Check'
    ];
}