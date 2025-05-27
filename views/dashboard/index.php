<?php require_once 'views/layouts/header.php'; ?>

<div class="container">
    <div class="controls">
        <div>
            <h2>Network Status Dashboard</h2>
            <p style="color: #ccc;">Welcome back, <?= $_SESSION['username'] ?></p>
            <p id="lastUpdateTime" style="color: #888; font-size: 0.9em;"></p>
        </div>
        <div>
            <button class="btn" onclick="startManualMonitoring()" id="monitorBtn">🔄 Check All Systems</button>
            <button class="btn btn-secondary" onclick="loadDashboard()">↻ Refresh Dashboard</button>
            <label style="color: #ccc; margin-left: 20px;">
                <input type="checkbox" id="autoMonitor" checked> Auto-monitor every 5 minutes
            </label>
        </div>
    </div>

    <!-- Progress Bar -->
    <div id="progressContainer" style="display: none; margin-bottom: 20px;">
        <div style="background: #2a2a2a; padding: 15px; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span id="progressText">Preparing to check devices...</span>
                <span id="progressPercent">0%</span>
            </div>
            <div style="background: #444; height: 20px; border-radius: 10px; overflow: hidden;">
                <div id="progressBar" style="background: linear-gradient(90deg, #00d4aa, #00b894); height: 100%; width: 0%; transition: width 0.3s;"></div>
            </div>
        </div>
    </div>

    <div id="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading system status...</p>
    </div>

    <div id="content"></div>

    <!-- Live Monitoring Results -->
    <div id="monitoringResults" style="display: none; margin-top: 30px;">
        <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; border-left: 4px solid #00d4aa;">
            <h3>Live Monitoring Results</h3>
            <div id="resultsContainer"></div>
        </div>
    </div>
</div>

<script>
    // Global variables
    let autoMonitorInterval;
    let isMonitoring = false;
    let totalDevices = 0;
    let checkedDevices = 0;

    // Load dashboard data
    async function loadDashboard() {
        try {
            const response = await fetch('?action=get_dashboard_data');
            const data = await response.json();

            document.getElementById('loading').style.display = 'none';

            let html = `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">${data.stats.total}</div>
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
        `;

            document.getElementById('content').innerHTML = html;

            // Update last update time
            document.getElementById('lastUpdateTime').textContent =
                'Dashboard updated: ' + new Date().toLocaleString();

            return data;

        } catch (error) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('content').innerHTML =
                '<p style="color: red; text-align: center;">Error loading dashboard: ' + error.message + '</p>';
        }
    }

    // Start manual monitoring
    async function startManualMonitoring() {
        if (isMonitoring) return;

        const data = await loadDashboard();
        if (!data || data.stats.total === 0) {
            alert('No devices to monitor. Add some devices first!');
            return;
        }

        isMonitoring = true;
        totalDevices = data.stats.total;
        checkedDevices = 0;

        // Show progress bar and results container
        document.getElementById('progressContainer').style.display = 'block';
        document.getElementById('monitoringResults').style.display = 'block';
        document.getElementById('resultsContainer').innerHTML = '';

        // Update button
        const btn = document.getElementById('monitorBtn');
        btn.textContent = '⏳ Monitoring...';
        btn.disabled = true;

        // Start monitoring individual devices
        await monitorDevicesSequentially();

        // Reset button
        btn.textContent = '🔄 Check All Systems';
        btn.disabled = false;

        // Hide progress bar after 3 seconds
        setTimeout(() => {
            document.getElementById('progressContainer').style.display = 'none';
        }, 3000);

        isMonitoring = false;

        // Refresh dashboard with new data
        setTimeout(loadDashboard, 1000);
    }

    // Monitor devices one by one with live updates
    async function monitorDevicesSequentially() {
        try {
            // Get device list
            const response = await fetch('?action=get_dashboard_data');
            const data = await response.json();

            const devices = [];
            Object.values(data.devices).forEach(deviceGroup => {
                devices.push(...deviceGroup);
            });

            // Monitor each device
            for (let i = 0; i < devices.length; i++) {
                const device = devices[i];

                // Update progress
                updateProgress(i + 1, devices.length, `Checking ${device.name}...`);

                // Monitor single device
                await monitorSingleDevice(device);

                // Small delay between checks to prevent overwhelming the network
                await sleep(500);
            }

            updateProgress(devices.length, devices.length, 'Monitoring complete!');

        } catch (error) {
            console.error('Monitoring error:', error);
            addResult('Error', 'Failed to complete monitoring: ' + error.message, 'error');
        }
    }

    // Monitor a single device
    async function monitorSingleDevice(device) {
        try {
            const formData = new FormData();
            formData.append('device_id', device.id);

            const response = await fetch('?action=monitor_single_device', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            // Add result to display
            const status = result.success ? 'operational' : 'down';
            const statusText = result.success ? 'Online' : 'Offline';
            const responseTime = result.response_time ? ` (${result.response_time}ms)` : '';
            const lastCheck = new Date().toLocaleString();

            addResult(device.name, `${statusText}${responseTime}`, status, lastCheck);

        } catch (error) {
            addResult(device.name, 'Check failed: ' + error.message, 'error');
        }
    }

    // Update progress bar
    function updateProgress(current, total, text) {
        const percent = Math.round((current / total) * 100);
        document.getElementById('progressText').textContent = text;
        document.getElementById('progressPercent').textContent = percent + '%';
        document.getElementById('progressBar').style.width = percent + '%';
    }

    // Add monitoring result
    function addResult(deviceName, status, statusClass, lastCheck = null) {
        const container = document.getElementById('resultsContainer');
        const resultDiv = document.createElement('div');
        resultDiv.style.cssText = `
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #444;
    `;

        const statusColor = {
            'operational': '#00d4aa',
            'down': '#e74c3c',
            'error': '#f39c12'
        }[statusClass] || '#888';

        const timeText = lastCheck ? `<small style="color: #888;">Last check: ${lastCheck}</small>` : '';

        resultDiv.innerHTML = `
        <div>
            <strong>${deviceName}</strong><br>
            ${timeText}
        </div>
        <div style="color: ${statusColor}; font-weight: bold;">
            ${status}
        </div>
    `;

        container.appendChild(resultDiv);

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    // Auto-monitoring setup
    function setupAutoMonitoring() {
        const checkbox = document.getElementById('autoMonitor');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                startAutoMonitoring();
            } else {
                stopAutoMonitoring();
            }
        });

        // Start auto-monitoring by default
        if (checkbox.checked) {
            startAutoMonitoring();
        }
    }

    function startAutoMonitoring() {
        // Initial load
        loadDashboard();

        // Set up interval for every 5 minutes (300,000 ms)
        autoMonitorInterval = setInterval(() => {
            if (!isMonitoring) {
                console.log('Auto-monitoring: Running background check...');
                startManualMonitoring();
            }
        }, 300000); // 5 minutes

        console.log('Auto-monitoring started (every 5 minutes)');
    }

    function stopAutoMonitoring() {
        if (autoMonitorInterval) {
            clearInterval(autoMonitorInterval);
            autoMonitorInterval = null;
            console.log('Auto-monitoring stopped');
        }
    }

    // Utility function
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Initialize everything when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
        setupAutoMonitoring();
    });

    // Load initial data
    loadDashboard();
</script>

<?php require_once 'views/layouts/footer.php'; ?>