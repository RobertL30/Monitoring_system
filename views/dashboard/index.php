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

    <!-- Device List with Filtering -->
    <div id="deviceListContainer" style="display: none; margin-top: 30px;">
        <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; border-left: 4px solid #00d4aa;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 id="deviceListTitle">All Devices</h3>
                <div>
                    <button class="btn btn-secondary" onclick="hideDeviceList()" style="padding: 8px 16px; font-size: 14px;">✕ Close</button>
                </div>
            </div>
            <div id="deviceListContent"></div>
        </div>
    </div>

    <!-- Live Monitoring Results -->
    <div id="monitoringResults" style="display: none; margin-top: 30px;">
        <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; border-left: 4px solid #00d4aa;">
            <h3>Live Monitoring Results</h3>
            <div id="resultsContainer"></div>
        </div>
    </div>
</div>

<style>
    /* Additional CSS for device filtering */
    .device-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #444;
    }

    .device-item:last-child {
        border-bottom: none;
    }

    .device-info {
        flex: 1;
    }

    .device-name {
        font-weight: bold;
        margin-bottom: 4px;
    }

    .device-details {
        font-size: 0.9em;
        color: #888;
    }

    .device-status {
        font-weight: bold;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.9em;
    }

    .status-operational {
        color: #00d4aa;
        background: rgba(0, 212, 170, 0.1);
    }

    .status-degraded {
        color: #f39c12;
        background: rgba(243, 156, 18, 0.1);
    }

    .status-down {
        color: #e74c3c;
        background: rgba(231, 76, 60, 0.1);
    }

    .status-unknown {
        color: #888;
        background: rgba(136, 136, 136, 0.1);
    }

    .stats-grid .stat-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stats-grid .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .stats-grid .stat-card.active {
        border-left: 4px solid #f39c12;
        background: #333;
    }
</style>

<script>
    // Global variables
    let autoMonitorInterval;
    let isMonitoring = false;
    let totalDevices = 0;
    let checkedDevices = 0;
    let currentFilter = 'all';
    let dashboardData = null;

    // Load dashboard data
    async function loadDashboard() {
        try {
            const response = await fetch('?action=get_dashboard_data');
            const data = await response.json();
            dashboardData = data; // Store for filtering

            document.getElementById('loading').style.display = 'none';

            let html = `
            <div class="stats-grid">
                <div class="stat-card" onclick="filterDevices('all')" id="card-all">
                    <div class="stat-number">${data.stats.total}</div>
                    <div class="stat-label">Total Systems</div>
                </div>
                <div class="stat-card" onclick="filterDevices('operational')" id="card-operational">
                    <div class="stat-number operational">${data.stats.operational}</div>
                    <div class="stat-label">Operational</div>
                </div>
                <div class="stat-card" onclick="filterDevices('degraded')" id="card-degraded">
                    <div class="stat-number degraded">${data.stats.degraded}</div>
                    <div class="stat-label">Degraded</div>
                </div>
                <div class="stat-card" onclick="filterDevices('down')" id="card-down">
                    <div class="stat-number down">${data.stats.down}</div>
                    <div class="stat-label">Down/Issues</div>
                </div>
            </div>
        `;

            document.getElementById('content').innerHTML = html;
            document.getElementById('lastUpdateTime').textContent =
                'Dashboard updated: ' + new Date().toLocaleString();

            // Update active filter indicator
            updateFilterIndicator();

            return data;

        } catch (error) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('content').innerHTML =
                '<p style="color: red; text-align: center;">Error loading dashboard: ' + error.message + '</p>';
        }
    }

    // Filter devices by status
    function filterDevices(status) {
        if (!dashboardData) {
            alert('Dashboard data not loaded yet. Please wait...');
            return;
        }

        currentFilter = status;
        updateFilterIndicator();

        // Collect all devices
        const allDevices = [];
        Object.entries(dashboardData.devices).forEach(([groupKey, devices]) => {
            devices.forEach(device => {
                device.group_name = dashboardData.device_groups[device.device_group] || device.device_group;
                allDevices.push(device);
            });
        });

        // Filter devices based on status
        let filteredDevices = allDevices;
        let title = 'All Devices';

        if (status !== 'all') {
            filteredDevices = allDevices.filter(device => {
                const deviceStatus = device.status || 'unknown';
                return deviceStatus === status;
            });

            title = status.charAt(0).toUpperCase() + status.slice(1) + ' Devices';
        }

        // Show filtered device list
        showDeviceList(filteredDevices, title);
    }

    // Show device list
    function showDeviceList(devices, title) {
        const container = document.getElementById('deviceListContainer');
        const titleElement = document.getElementById('deviceListTitle');
        const contentElement = document.getElementById('deviceListContent');

        titleElement.textContent = `${title} (${devices.length})`;

        if (devices.length === 0) {
            contentElement.innerHTML = '<p style="color: #888; text-align: center; padding: 20px;">No devices found for this status.</p>';
        } else {
            let html = '';

            devices.forEach(device => {
                const status = device.status || 'unknown';
                const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never checked';
                const responseTime = device.response_time ? ` (${device.response_time}ms)` : '';
                const location = device.location ? ` • ${device.location}` : '';
                const port = device.port ? `:${device.port}` : '';

                html += `
                <div class="device-item">
                    <div class="device-info">
                        <div class="device-name">${device.name}</div>
                        <div class="device-details">
                            ${device.ip_address}${port} • ${device.group_name} • ${device.monitor_type.toUpperCase()}${location}<br>
                            <small>Last check: ${lastCheck}${responseTime}</small>
                        </div>
                    </div>
                    <div class="device-status status-${status}">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </div>
                </div>
            `;
            });

            contentElement.innerHTML = html;
        }

        container.style.display = 'block';

        // Scroll to device list
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Hide device list
    function hideDeviceList() {
        document.getElementById('deviceListContainer').style.display = 'none';
        currentFilter = 'all';
        updateFilterIndicator();
    }

    // Update filter indicator
    function updateFilterIndicator() {
        // Remove active class from all cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.classList.remove('active');
        });

        // Add active class to current filter
        const activeCard = document.getElementById(`card-${currentFilter}`);
        if (activeCard) {
            activeCard.classList.add('active');
        }
    }

    // Start manual monitoring with user warning
    async function startManualMonitoring() {
        if (isMonitoring) return;

        const data = await loadDashboard();
        if (!data || data.stats.total === 0) {
            alert('No devices to monitor. Add some devices first!');
            return;
        }

        // Show user warning
        const estimatedTime = Math.ceil(data.stats.total * 2); // ~2 seconds per device
        const userConfirm = confirm(
            `⚠️ MANUAL MONITORING WARNING ⚠️\n\n` +
            `This will check ${data.stats.total} devices and may take ${estimatedTime}-${estimatedTime*2} seconds.\n` +
            `The system will be less responsive during this time.\n\n` +
            `Continue with manual monitoring?`
        );

        if (!userConfirm) return;

        isMonitoring = true;
        totalDevices = data.stats.total;
        checkedDevices = 0;

        // Hide device list during monitoring
        hideDeviceList();

        // Show progress bar and results container
        document.getElementById('progressContainer').style.display = 'block';
        document.getElementById('monitoringResults').style.display = 'block';
        document.getElementById('resultsContainer').innerHTML = '';

        // Update button and disable other controls
        const btn = document.getElementById('monitorBtn');
        btn.textContent = '⏳ Monitoring...';
        btn.disabled = true;

        // Disable auto-monitoring during manual check
        const autoCheckbox = document.getElementById('autoMonitor');
        const wasAutoEnabled = autoCheckbox.checked;
        autoCheckbox.checked = false;
        autoCheckbox.disabled = true;

        // Start staggered monitoring
        await monitorDevicesStaggered();

        // Reset controls
        btn.textContent = '🔄 Check All Systems';
        btn.disabled = false;
        autoCheckbox.disabled = false;
        autoCheckbox.checked = wasAutoEnabled;

        // Hide progress bar after 3 seconds
        setTimeout(() => {
            document.getElementById('progressContainer').style.display = 'none';
        }, 3000);

        isMonitoring = false;

        // Refresh dashboard with new data
        setTimeout(loadDashboard, 1000);
    }

    // Staggered monitoring to prevent system freeze
    async function monitorDevicesStaggered() {
        try {
            // Get device list
            const response = await fetch('?action=get_dashboard_data');
            const data = await response.json();

            const devices = [];
            Object.values(data.devices).forEach(deviceGroup => {
                devices.push(...deviceGroup);
            });

            // Process devices in batches to prevent system freeze
            const batchSize = 3; // Monitor 3 devices simultaneously
            const batches = [];

            for (let i = 0; i < devices.length; i += batchSize) {
                batches.push(devices.slice(i, i + batchSize));
            }

            // Process each batch
            for (let batchIndex = 0; batchIndex < batches.length; batchIndex++) {
                const batch = batches[batchIndex];
                const currentDeviceNumber = batchIndex * batchSize + 1;

                updateProgress(
                    currentDeviceNumber,
                    devices.length,
                    `Processing batch ${batchIndex + 1}/${batches.length} (${batch.length} devices)...`
                );

                // Start all devices in this batch simultaneously
                const batchPromises = batch.map(device => monitorSingleDevice(device));

                // Wait for all devices in this batch to complete
                await Promise.all(batchPromises);

                // Update progress after batch completion
                const completedDevices = Math.min((batchIndex + 1) * batchSize, devices.length);
                updateProgress(
                    completedDevices,
                    devices.length,
                    `Completed ${completedDevices}/${devices.length} devices`
                );

                // Brief pause between batches to prevent overwhelming the system
                if (batchIndex < batches.length - 1) {
                    await sleep(1500); // 1.5 second pause between batches
                }
            }

            updateProgress(devices.length, devices.length, 'All monitoring complete!');

        } catch (error) {
            console.error('Monitoring error:', error);
            addResult('Error', 'Failed to complete monitoring: ' + error.message, 'error');
        }
    }

    // Monitor a single device
    async function monitorSingleDevice(device) {
        const startTime = Date.now();

        try {
            const formData = new FormData();
            formData.append('device_id', device.id);

            const response = await fetch('?action=monitor_single_device', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            const checkDuration = Date.now() - startTime;

            // Add result to display with attempt info
            const status = result.success ? 'operational' : 'down';
            const statusText = result.success ? 'Online' : 'Offline';
            const responseTime = result.response_time ? ` (${result.response_time}ms)` : '';
            const attempts = result.attempts ? ` [${result.attempts} attempts]` : '';
            const lastCheck = new Date().toLocaleString();

            addResult(
                device.name,
                `${statusText}${responseTime}${attempts}`,
                status,
                lastCheck,
                `Check took ${Math.round(checkDuration/1000)}s`
            );

        } catch (error) {
            const checkDuration = Date.now() - startTime;
            addResult(
                device.name,
                'Check failed: ' + error.message,
                'error',
                null,
                `Failed after ${Math.round(checkDuration/1000)}s`
            );
        }
    }

    // Update progress bar
    function updateProgress(current, total, text) {
        const percent = Math.round((current / total) * 100);
        document.getElementById('progressText').textContent = text;
        document.getElementById('progressPercent').textContent = percent + '%';
        document.getElementById('progressBar').style.width = percent + '%';
    }

    // Add monitoring result with duration info
    function addResult(deviceName, status, statusClass, lastCheck = null, duration = null) {
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
        const durationText = duration ? `<br><small style="color: #666;">${duration}</small>` : '';

        resultDiv.innerHTML = `
        <div>
            <strong>${deviceName}</strong><br>
            ${timeText}${durationText}
        </div>
        <div style="color: ${statusColor}; font-weight: bold;">
            ${status}
        </div>
    `;

        container.appendChild(resultDiv);
        container.scrollTop = container.scrollHeight;
    }

    // Auto-monitoring functions
    function startAutoMonitoring() {
        loadDashboard();

        // Reduced frequency for background monitoring (10 minutes instead of 5)
        autoMonitorInterval = setInterval(() => {
            if (!isMonitoring) {
                console.log('Auto-monitoring: Running quiet background check...');
                runQuietMonitoring();
            }
        }, 600000); // 10 minutes for auto-monitoring

        console.log('Auto-monitoring started (every 10 minutes)');
    }

    // Quiet monitoring for background auto-checks
    async function runQuietMonitoring() {
        try {
            const response = await fetch('?action=monitor_all');
            const data = await response.json();

            console.log(`Auto-monitoring complete: ${data.total} devices checked`);

            // Refresh dashboard silently
            loadDashboard();

        } catch (error) {
            console.error('Auto-monitoring failed:', error);
        }
    }

    function stopAutoMonitoring() {
        if (autoMonitorInterval) {
            clearInterval(autoMonitorInterval);
            autoMonitorInterval = null;
            console.log('Auto-monitoring stopped');
        }
    }

    function setupAutoMonitoring() {
        const checkbox = document.getElementById('autoMonitor');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                startAutoMonitoring();
            } else {
                stopAutoMonitoring();
            }
        });

        if (checkbox.checked) {
            startAutoMonitoring();
        }
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
        setupAutoMonitoring();
    });

    loadDashboard();
</script>

<?php require_once 'views/layouts/footer.php'; ?>