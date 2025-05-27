<?php
// reports.php - Reports and analytics page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Network Monitor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a1a; color: #fff; }
        .header { background: linear-gradient(135deg, #2c5aa0, #1e3a5f); padding: 20px; }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-icon { width: 50px; height: 50px; background: #00d4aa; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; color: #1a1a1a; }
        .nav { display: flex; gap: 20px; }
        .nav a { color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; transition: background 0.3s; }
        .nav a:hover, .nav a.active { background: rgba(255,255,255,0.1); }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .report-card { background: #2a2a2a; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">NET</div>
                <div><h1>Network Monitor</h1></div>
            </div>
            <div class="nav">
                <a href="?page=dashboard">Dashboard</a>
                <a href="?page=devices">Device Management</a>
                <a href="?page=reports" class="active">Reports</a>
                <a href="?logout=1">Logout</a>
            </div>
        </div>
    </div>
 
    <div class="container">
        <h2>Network Reports & Analytics</h2>
        
        <div class="report-grid">
            <div class="report-card">
                <h3>System Overview</h3>
                <p>Coming soon: Uptime statistics, performance trends, and availability reports.</p>
            </div>
            
            <div class="report-card">
                <h3>Performance Metrics</h3>
                <p>Coming soon: Response time analysis, latency trends, and performance baselines.</p>
            </div>
            
            <div class="report-card">
                <h3>Alert History</h3>
                <p>Coming soon: Historical alert data, failure patterns, and incident reports.</p>
            </div>
            
            <div class="report-card">
                <h3>Device Health</h3>
                <p>Coming soon: Individual device health reports and maintenance recommendations.</p>
            </div>
        </div>
    </div>
</body>
</html>