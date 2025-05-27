<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Monitor Dashboard</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">NET</div>
                <div>
                    <h1>Network Monitor</h1>
                    <p style="color: #ccc; font-size: 0.9em;">Real-time Infrastructure Monitoring</p>
                </div>
            </div>
            <div class="nav">
                <a href="?page=dashboard" class="<?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="?page=devices" class="<?= ($_GET['page'] ?? '') === 'devices' ? 'active' : '' ?>">Device Management</a>
                <a href="?page=reports" class="<?= ($_GET['page'] ?? '') === 'reports' ? 'active' : '' ?>">Reports</a>
                <a href="?logout=1">Logout</a>
            </div>
        </div>
    </div>