<?php
// dashboard.php - Main dashboard page
global $device_groups;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Monitor Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a; color: #ffffff; line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #2c5aa0, #1e3a5f);
            padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .header-content {
            max-width: 1200px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
        }
        
        .logo { display: flex; align-items: center; gap: 15px; }
        
        .logo-icon {
            width: 50px; height: 50px; background: #00d4aa; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 20px; color: #1a1a1a;
        }
        
        .nav { display: flex; gap: 20px; }
        
        .nav a {
            color: white; text-decoration: none; padding: 10px 20px;
            border-radius: 5px; transition: background 0.3s;
        }
        
        .nav a:hover, .nav a.active { background: rgba(255,255,255,0.1); }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        
        .stat-card {
            background: #2a2a2a; padding: 25px; border-radius: 10px;
            border-left: 4px solid #00d4aa; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-number { font-size: 2.5em; font-weight: bold; margin-bottom: 5px; }
        .stat-label { color: #cccccc; font-size: 1.1em; }
        
        .operational { color: #00d4aa; }
        .degraded { color: #f39c12; }
        .down { color: #e74c3c; }
        
        .controls {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; padding: 20px; background: #2a2a2a; border-radius: 10px;
        }
        
        .btn {
            background: #00d4aa; color: white; border: none; padding: 12px 24px;
            border-radius: 6px; cursor: pointer; font-size: 16px; text-decoration: none;
            display: inline-block; transition: background 0.3s;
        }
        
        .btn:hover { background: #00b894; }
        .btn-secondary { background: #3498db; }
        .btn-secondary:hover { background: #2980b9; }
        
        .loading { text-align: center; padding: 50px; color: #888; }
        
        .spinner {
            border: 4px solid #333; border-top: 4px solid #00d4aa; border-radius: 50%;
            width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;
        }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
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
                <a href="?page=dashboard" class="active">Dashboard</a>
                <a href="?page=devices">Device Management</a>
                <a href="?page=reports">Reports</a>
                <a href="?logout=1">Logout</a>
            </div>
        </div>
    </div>
 
    <div class="container">
        <div class="controls">
            <div>
                <h2>Network Status Dashboard</h2>
                <p style="color: #ccc;">Welcome back, <?= $_SESSION['username'] ?></p>
            </div>
            <div>
                <button class="btn" onclick="runMonitoring()">🔄 Check All Systems</button>
                <button class="btn btn-secondary" onclick="loadDashboard()">↻ Refresh</button>
            </div>
        </div>
 
        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Loading system status...</p>
        </div>
 
        <div id="content"></div>
    </div>
 
    <script>
        async function loadDashboard() {
            try {
                const response = await fetch('?action=get_dashboard_data');
                const data = await response.json();
                
                document.getElementById('loading').style.display = 'none';
                
                let html = `
                    <div class="stats-grid">
                        <div class="stat-card">
${data.stats.total}</div>
                            <div class="stat-label">Total Systems</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number operational">${data.stats.operational}</div>
                            <div class="stat-label">Operational</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number degraded">${data.stats.degraded}</div>
                            <div class="stat-label">Degraded</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number down">${data.stats.down}</div>
                            <div class="stat-label">Down/Issues</div>
                        </div>
                    </div>
                    
                    <p>Dashboard loaded successfully! You can now add devices through Device Management.</p>
                `;
                
                document.getElementById('content').innerHTML = html;
            } catch (error) {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('content').innerHTML = '<p style="color: red; text-align: center;">Error loading dashboard: ' + error.message + '</p>';
            }
        }
        
        async function runMonitoring() {
            alert('Monitoring feature ready! Add some devices first.');
        }
        
        // Load initial data
        loadDashboard();
    </script>
</body>
</html>